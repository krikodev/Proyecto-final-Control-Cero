<?php

namespace App\Http\Requests\Ats;

use App\Models\AtsQuestion;
use App\Models\Machine;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;

class StartShiftRecordRequest extends FormRequest
{
    /**
     * Acepta cualquier imagen en data-URL (cámara) o archivo subido.
     */
    public const DATA_URL_PATTERN = '/^data:image\/(jpe?g|png);base64,[A-Za-z0-9+\/=]+$/';

    protected Collection|null $startQuestions = null;

    /**
     * La máquina debe estar habilitada y el operador debe estar
     * autorizado para usarla: si no, 403 antes de validar el resto.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        $machine = $this->route('machine');

        return ($user?->can('ats.crear') ?? false)
            && $machine instanceof Machine
            && (bool) $machine->is_active
            && $user->machines()->whereKey($machine->id)->exists();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'photo_data' => ['nullable', 'string', 'regex:'.self::DATA_URL_PATTERN],
            'photo_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120', 'required_without:photo_data'],

            'signature_data' => ['nullable', 'string', 'regex:'.self::DATA_URL_PATTERN],
            'signature_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048', 'required_without:signature_data'],
        ];

        foreach ($this->startQuestions() as $question) {
            $rules["answers.{$question->id}"] = ['required', 'boolean'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'photo_data.regex' => 'La foto debe ser una imagen JPEG o PNG válida.',
            'photo_file.image' => 'La foto debe ser un archivo de imagen.',
            'photo_file.mimes' => 'Formato de foto no permitido (usa JPG o PNG).',
            'photo_file.max' => 'La foto supera el peso máximo de 5 MB.',
            'photo_file.required_without' => 'Debes tomar una foto con la cámara o subir una imagen.',

            'signature_data.regex' => 'La firma debe ser una imagen JPEG o PNG válida.',
            'signature_file.image' => 'La firma debe ser un archivo de imagen.',
            'signature_file.mimes' => 'Formato de firma no permitido (usa JPG o PNG).',
            'signature_file.max' => 'La firma supera el peso máximo de 2 MB.',
            'signature_file.required_without' => 'Debes dibujar tu firma o subir un archivo con ella.',

            'answers.*.required' => 'Debes responder esta pregunta.',
            'answers.*.boolean' => 'La respuesta seleccionada no es válida.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        $attributes = [
            'photo_data' => 'foto del operador',
            'photo_file' => 'foto del operador',
            'signature_data' => 'firma',
            'signature_file' => 'firma',
        ];

        foreach ($this->startQuestions() as $question) {
            $attributes["answers.{$question->id}"] = $question->title;
        }

        return $attributes;
    }

    /**
     * @return Collection<int, AtsQuestion>
     */
    public function startQuestions(): Collection
    {
        return $this->startQuestions ??= AtsQuestion::query()
            ->active()
            ->stage(AtsQuestion::STAGE_START)
            ->ordered()
            ->get();
    }
}
