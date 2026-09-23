<?php

namespace App\Http\Controllers\Ats;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ats\StoreAtsQuestionRequest;
use App\Http\Requests\Ats\UpdateAtsQuestionRequest;
use App\Http\Requests\Ats\UpdateAtsQuestionStatusRequest;
use App\Models\AtsQuestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class QuestionController extends Controller
{
    public function index(): View
    {
        return view('ats.questions.index', [
            'questions' => AtsQuestion::query()
                ->orderBy('stage')
                ->orderBy('position')
                ->orderBy('id')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('ats.questions.create');
    }

    public function store(StoreAtsQuestionRequest $request): RedirectResponse
    {
        AtsQuestion::create($this->payload($request->validated()));

        return redirect()
            ->route('ats.questions.index')
            ->with('success', 'Pregunta creada correctamente.');
    }

    public function edit(AtsQuestion $question): View
    {
        return view('ats.questions.edit', compact('question'));
    }

    public function update(UpdateAtsQuestionRequest $request, AtsQuestion $question): RedirectResponse
    {
        $question->update($this->payload($request->validated()));

        return redirect()
            ->route('ats.questions.index')
            ->with('success', 'Pregunta actualizada correctamente.');
    }

    public function updateStatus(
        UpdateAtsQuestionStatusRequest $request,
        AtsQuestion $question
    ): RedirectResponse {
        $question->is_active = $request->boolean('is_active');
        $question->save();

        return back()->with(
            'success',
            $question->is_active
                ? 'La pregunta está activa.'
                : 'La pregunta quedó inactiva y ya no se mostrará.'
        );
    }

    public function destroy(AtsQuestion $question): RedirectResponse
    {
        // Borrar la pregunta borraría también sus respuestas históricas.
        if ($question->answers()->exists()) {
            return redirect()
                ->route('ats.questions.index')
                ->withErrors([
                    'question' => 'La pregunta ya tiene respuestas registradas; desactívala en lugar de eliminarla.',
                ]);
        }

        $question->delete();

        return redirect()
            ->route('ats.questions.index')
            ->with('success', 'Pregunta eliminada.');
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(array $data): array
    {
        $data['position'] = (int) ($data['position'] ?? 0);
        $data['is_active'] = array_key_exists('is_active', $data)
            ? (bool) $data['is_active']
            : true;

        return $data;
    }
}
