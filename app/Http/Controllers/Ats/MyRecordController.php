<?php

namespace App\Http\Controllers\Ats;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ats\FinishShiftRecordRequest;
use App\Http\Requests\Ats\StartShiftRecordRequest;
use App\Models\AtsAnswer;
use App\Models\AtsQuestion;
use App\Models\Machine;
use App\Models\ShiftRecord;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MyRecordController extends Controller
{
    /**
     * LANDING: máquinas disponibles para el operador, su registro
     * pendiente y su historial.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $machines = $user->machines()->withCount('users')->get();

        $pending = ShiftRecord::query()
            ->where('user_id', $user->id)
            ->where('status', ShiftRecord::STATUS_PENDING)
            ->with('machine:id,name')
            ->first();

        $history = ShiftRecord::query()
            ->where('user_id', $user->id)
            ->where('status', ShiftRecord::STATUS_COMPLETED)
            ->with('machine:id,name')
            ->latest('finished_at')
            ->limit(15)
            ->get();

        return view('ats.mine', compact('machines', 'pending', 'history'));
    }

    /**
     * PASOS 1-3: foto + firma + preguntas de inicio.
     */
    public function create(Request $request, Machine $machine): View|RedirectResponse
    {
        $user = $request->user();

        if ($blocked = $this->machineBlockReason($user, $machine)) {
            return redirect()->route('ats.mine')->withErrors(['machines' => $blocked]);
        }

        $pending = $this->pendingRecord($user->id);

        if ($pending) {
            return redirect()->route('ats.finish')->withErrors([
                'record' => 'Ya tienes un registro pendiente de finalizar.',
            ]);
        }

        return view('ats.start', [
            'machine' => $machine,
            'questions' => AtsQuestion::query()
                ->active()
                ->stage(AtsQuestion::STAGE_START)
                ->ordered()
                ->get(),
        ]);
    }

    public function store(StartShiftRecordRequest $request, Machine $machine): RedirectResponse
    {
        $user = $request->user();

        if ($blocked = $this->machineBlockReason($user, $machine)) {
            throw ValidationException::withMessages(['machines' => $blocked]);
        }

        if ($this->pendingRecord($user->id)) {
            throw ValidationException::withMessages([
                'record' => 'Ya tienes un registro pendiente de finalizar.',
            ]);
        }

        $data = $request->validated();
        $answers = $data['answers'] ?? [];

        unset($data['answers'], $data['photo_data'], $data['photo_file'], $data['signature_data'], $data['signature_file']);

        DB::transaction(function () use ($data, $answers, $user, $machine, $request) {
            $record = new ShiftRecord($data);
            $record->user_id = $user->id;
            $record->machine_id = $machine->id;
            $record->status = ShiftRecord::STATUS_PENDING;
            $record->started_at = now();
            $record->save();

            $record->photo_path = $this->persistImage(
                $request->input('photo_data'),
                $request->file('photo_file'),
                $record,
                'photo'
            );

            $record->signature_path = $this->persistImage(
                $request->input('signature_data'),
                $request->file('signature_file'),
                $record,
                'signature'
            );

            $record->save();

            foreach ($answers as $questionId => $value) {
                $record->answers()->create([
                    'ats_question_id' => (int) $questionId,
                    'answer' => (bool) $value,
                ]);
            }
        });

        return redirect()
            ->route('ats.mine')
            ->with('success', 'ATS de inicio guardado. Tu registro queda pendiente de finalizar al terminar la labor.');
    }

    /**
     * CIERRE: preguntas de finalización con la firma ya autocompletada.
     */
    public function finish(Request $request): View|RedirectResponse
    {
        $record = $this->pendingRecord($request->user()->id);

        if (! $record) {
            return redirect()->route('ats.mine')->withErrors([
                'record' => 'No tienes ningún registro pendiente de finalizar.',
            ]);
        }

        $record->load('machine:id,name');

        return view('ats.finish', [
            'record' => $record,
            'questions' => AtsQuestion::query()
                ->active()
                ->stage(AtsQuestion::STAGE_FINISH)
                ->ordered()
                ->get(),
        ]);
    }

    public function storeFinish(FinishShiftRecordRequest $request, ShiftRecord $record): RedirectResponse
    {
        abort_unless($record->user_id === $request->user()->id, 403);
        abort_unless($record->isPending(), 403);

        $answers = $request->validated()['answers'] ?? [];

        DB::transaction(function () use ($record, $answers) {
            foreach ($answers as $questionId => $value) {
                AtsAnswer::firstOrCreate(
                    [
                        'shift_record_id' => $record->id,
                        'ats_question_id' => (int) $questionId,
                    ],
                    ['answer' => (bool) $value]
                );
            }

            $record->status = ShiftRecord::STATUS_COMPLETED;
            $record->finished_at = now();
            $record->save();
        });

        return redirect()
            ->route('ats.mine')
            ->with('success', 'ATS finalizado correctamente. Gracias por tu registro.');
    }

    private function pendingRecord(int $userId): ShiftRecord|null
    {
        return ShiftRecord::query()
            ->where('user_id', $userId)
            ->where('status', ShiftRecord::STATUS_PENDING)
            ->first();
    }

    /**
     * Motivo por el que no se puede iniciar un turno en esa máquina, o null.
     */
    private function machineBlockReason(User $user, Machine $machine): string|null
    {
        if (! $machine->is_active) {
            return 'La máquina "'.$machine->name.'" está inactiva.';
        }

        if (! $user->machines()->whereKey($machine->id)->exists()) {
            return 'No estás habilitado para usar la máquina "'.$machine->name.'".';
        }

        return null;
    }

    /**
     * Guarda una imagen que llega como data-URL (cámara/firma dibujada)
     * o como archivo subido.
     */
    private function persistImage(
        string|null $dataUrl,
        UploadedFile|null $file,
        ShiftRecord $record,
        string $name
    ): string {
        $directory = 'ats/'.$record->id;

        if ($dataUrl) {
            $extension = 'png';

            if (preg_match('/^data:image\/(jpe?g|png);base64,/', $dataUrl, $matches) === 1) {
                $extension = strtolower($matches[1]);
            }

            if ($extension === 'jpeg') {
                $extension = 'jpg';
            }

            $encoded = substr($dataUrl, strpos($dataUrl, ',') + 1);
            $binary = base64_decode($encoded, true);

            if ($binary === false) {
                throw ValidationException::withMessages([
                    $name => 'No fue posible procesar la imagen enviada.',
                ]);
            }

            $path = $directory.'/'.$name.'.'.$extension;
            Storage::disk('public')->put($path, $binary);

            return $path;
        }

        /** @var UploadedFile $file */
        return $file->storeAs(
            $directory,
            $name.'.'.$file->getClientOriginalExtension(),
            'public'
        );
    }
}
