<?php

namespace Tests\Feature;

use App\Models\AtsQuestion;
use App\Models\Machine;
use App\Models\ShiftRecord;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AtsRecordTest extends TestCase
{
    use RefreshDatabase;

    /** 1x1 px PNG. */
    private const PNG = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    /** Bytes reales del mismo PNG (sin depender de la extensión GD). */
    private function pngBytes(): string
    {
        return base64_decode(explode(',', self::PNG, 2)[1]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/registro')->assertRedirect(route('login'));
    }

    public function test_operator_sees_only_their_machines_on_their_landing(): void
    {
        $operator = $this->makeActiveUserWithRole('Operador');
        $own = $this->makeMachine('Traspaleta TP-1');
        $other = $this->makeMachine('Montacargas MC-9');

        $own->users()->attach($operator);

        $response = $this->actingAs($operator)->get('/registro');

        $response->assertOk();
        $response->assertSee('Traspaleta TP-1');
        $response->assertDontSee('Montacargas MC-9');
    }

    public function test_inactive_machine_cannot_be_started(): void
    {
        $operator = $this->makeActiveUserWithRole('Operador');
        $machine = $this->makeMachine('Traspaleta TP-1', is_active: false);
        $machine->users()->attach($operator);

        $this->actingAs($operator)
            ->get(route('ats.start', $machine))
            ->assertRedirect(route('ats.mine'));
    }

    public function test_operator_not_enabled_on_a_machine_gets_a_403(): void
    {
        $operator = $this->makeActiveUserWithRole('Operador');
        $machine = $this->makeMachine('Traspaleta TP-1');

        // En el formulario se avisa con un mensaje amigable; al enviar, 403.
        $this->actingAs($operator)->get(route('ats.start', $machine))
            ->assertRedirect(route('ats.mine'));

        $this->actingAs($operator)->post(route('ats.store', $machine), [])->assertForbidden();
    }

    public function test_operator_can_start_and_finish_a_record(): void
    {
        Storage::fake('public');

        $operator = $this->makeActiveUserWithRole('Operador');
        $machine = $this->makeMachine('Traspaleta TP-1');
        $machine->users()->attach($operator);

        $startIds = AtsQuestion::query()->active()->stage('start')->pluck('id');
        $finishIds = AtsQuestion::query()->active()->stage('finish')->pluck('id');

        $this->actingAs($operator)->post(route('ats.store', $machine), [
            'photo_data' => self::PNG,
            'signature_data' => self::PNG,
            // Los formularios envían strings: se envía igual que un navegador.
            'answers' => $startIds->mapWithKeys(fn ($id) => [$id => '1'])->all(),
        ])->assertRedirect(route('ats.mine'));

        $record = ShiftRecord::query()->sole();
        $this->assertTrue($record->isPending());
        $this->assertSame($operator->id, $record->user_id);
        $this->assertSame($machine->id, $record->machine_id);
        $this->assertCount($startIds->count(), $record->answers()->get());
        Storage::disk('public')->assertExists($record->photo_path);
        Storage::disk('public')->assertExists($record->signature_path);

        $this->actingAs($operator)->get('/registro/finalizar')->assertOk()->assertSee('Firma');

        $this->actingAs($operator)->post(route('ats.finish.store', $record), [
            'answers' => $finishIds->mapWithKeys(fn ($id) => [$id => '0'])->all(),
        ])->assertRedirect(route('ats.mine'));

        $record->refresh();
        $this->assertTrue($record->isCompleted());
        $this->assertNotNull($record->finished_at);
        $this->assertSame(
            $finishIds->count() + $startIds->count(),
            $record->answers()->count()
        );
    }

    public function test_start_requires_every_answer_photo_and_signature(): void
    {
        $operator = $this->makeActiveUserWithRole('Operador');
        $machine = $this->makeMachine('Traspaleta TP-1');
        $machine->users()->attach($operator);

        $this->actingAs($operator)
            ->post(route('ats.store', $machine), [])
            ->assertSessionHasErrors(['photo_file', 'signature_file']);

        $this->actingAs($operator)
            ->post(route('ats.store', $machine), [
                'photo_data' => self::PNG,
                'signature_data' => self::PNG,
            ])
            ->assertSessionHasErrors('answers.'.$this->firstStartQuestion()->id);
    }

    public function test_a_second_record_cannot_start_while_one_is_pending(): void
    {
        $operator = $this->makeActiveUserWithRole('Operador');
        $machine = $this->makeMachine('Traspaleta TP-1');
        $machine->users()->attach($operator);

        $answers = AtsQuestion::query()->active()->stage('start')
            ->pluck('id')->mapWithKeys(fn ($id) => [$id => '1'])->all();

        $payload = [
            'photo_data' => self::PNG,
            'signature_data' => self::PNG,
            'answers' => $answers,
        ];

        $this->actingAs($operator)->post(route('ats.store', $machine), $payload)
            ->assertRedirect(route('ats.mine'));

        $second = $this->makeMachine('Montacargas MC-9');
        $second->users()->attach($operator);

        $this->actingAs($operator)->post(route('ats.store', $second), $payload)
            ->assertSessionHasErrors('record');

        $this->assertSame(1, ShiftRecord::query()->count());
    }

    public function test_operator_cannot_finish_another_operators_record(): void
    {
        $owner = $this->makeActiveUserWithRole('Operador');
        $other = $this->makeActiveUserWithRole('Operador');
        $machine = $this->makeMachine('Traspaleta TP-1');
        $machine->users()->attach($owner);

        $record = $this->makePendingRecord($owner, $machine);

        $this->actingAs($other)->post(route('ats.finish.store', $record), [])->assertForbidden();
        $this->actingAs($other)->get(route('ats.show', $record))->assertForbidden();
    }

    public function test_supervisor_can_review_records_but_operator_cannot_see_the_supervision_list(): void
    {
        $supervisor = $this->makeActiveUserWithRole('Supervisor');
        $operator = $this->makeActiveUserWithRole('Operador');
        $machine = $this->makeMachine('Traspaleta TP-1');
        $machine->users()->attach($operator);

        $record = $this->makePendingRecord($operator, $machine);

        $this->actingAs($supervisor)->get('/registros')->assertOk();
        $this->actingAs($supervisor)->get(route('ats.show', $record))->assertOk();
        $this->actingAs($operator)->get('/registros')->assertForbidden();
        $this->actingAs($supervisor)->get('/registro')->assertForbidden();
    }

    public function test_admin_can_manage_the_ats_questions(): void
    {
        $admin = $this->makeActiveUserWithRole('Administrador');
        $supervisor = $this->makeActiveUserWithRole('Supervisor');

        $this->actingAs($supervisor)->get('/preguntas')->assertForbidden();
        $this->actingAs($admin)->get('/preguntas/create')->assertOk();

        $this->actingAs($admin)->post(route('ats.questions.store'), [
            'title' => '¿Se revisó el nivel de combustible?',
            'stage' => 'start',
            'position' => '9',
        ])->assertRedirect(route('ats.questions.index'));

        $question = AtsQuestion::query()->where('title', '¿Se revisó el nivel de combustible?')->sole();
        $this->assertTrue($question->is_active);
        $this->assertSame(9, $question->position);

        $this->actingAs($admin)->get(route('ats.questions.edit', $question))->assertOk();

        $this->actingAs($admin)->patch(route('ats.questions.status', $question), ['is_active' => '0'])
            ->assertRedirect();
        $this->assertFalse($question->fresh()->is_active);

        $this->actingAs($admin)->delete(route('ats.questions.destroy', $question))
            ->assertRedirect(route('ats.questions.index'));
        $this->assertDatabaseMissing('ats_questions', ['id' => $question->id]);
    }

    public function test_operator_can_open_the_start_form(): void
    {
        $operator = $this->makeActiveUserWithRole('Operador');
        $machine = $this->makeMachine('Traspaleta TP-1');
        $machine->users()->attach($operator);

        $response = $this->actingAs($operator)->get(route('ats.start', $machine));

        $response->assertOk();
        $response->assertSee('Foto en tiempo real');
        $response->assertSee('Firmar y guardar ATS de inicio');
        $response->assertSee('¿El área de trabajo', false);
    }

    public function test_a_question_with_answers_cannot_be_deleted(): void
    {
        $admin = $this->makeActiveUserWithRole('Administrador');
        $question = AtsQuestion::query()->active()->stage('start')->firstOrFail();

        $operator = $this->makeActiveUserWithRole('Operador');
        $machine = $this->makeMachine('Traspaleta TP-1');
        $record = $this->makePendingRecord($operator, $machine);
        $record->answers()->create(['ats_question_id' => $question->id, 'answer' => true]);

        $this->actingAs($admin)->delete(route('ats.questions.destroy', $question))
            ->assertSessionHasErrors('question');

        $this->assertDatabaseHas('ats_questions', ['id' => $question->id]);
    }

    public function test_photo_and_signature_can_be_uploaded_as_a_file(): void
    {
        Storage::fake('public');

        $operator = $this->makeActiveUserWithRole('Operador');
        $machine = $this->makeMachine('Traspaleta TP-1');
        $machine->users()->attach($operator);

        $answers = AtsQuestion::query()->active()->stage('start')
            ->pluck('id')->mapWithKeys(fn ($id) => [$id => '0'])->all();

        $this->actingAs($operator)->post(route('ats.store', $machine), [
            'photo_file' => UploadedFile::fake()->createWithContent('operador.png', $this->pngBytes()),
            'signature_file' => UploadedFile::fake()->createWithContent('firma.png', $this->pngBytes()),
            'answers' => $answers,
        ])->assertRedirect(route('ats.mine'));

        $record = ShiftRecord::query()->sole();
        Storage::disk('public')->assertExists($record->photo_path);
        Storage::disk('public')->assertExists($record->signature_path);
    }

    private function firstStartQuestion(): AtsQuestion
    {
        return AtsQuestion::query()->active()->stage('start')->firstOrFail();
    }

    private function makeActiveUserWithRole(string $role): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole($role);

        return $user;
    }

    private function makeMachine(string $name, bool $is_active = true): Machine
    {
        return Machine::create([
            'name' => $name,
            'description' => 'Máquina de prueba',
            'is_active' => $is_active,
        ]);
    }

    private function makePendingRecord(User $operator, Machine $machine): ShiftRecord
    {
        return ShiftRecord::create([
            'user_id' => $operator->id,
            'machine_id' => $machine->id,
            'photo_path' => 'ats/0/photo.png',
            'signature_path' => 'ats/0/signature.png',
            'status' => ShiftRecord::STATUS_PENDING,
            'started_at' => now(),
        ]);
    }
}
