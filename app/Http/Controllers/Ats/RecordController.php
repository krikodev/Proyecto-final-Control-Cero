<?php

namespace App\Http\Controllers\Ats;

use App\Http\Controllers\Controller;
use App\Models\Machine;
use App\Models\ShiftRecord;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecordController extends Controller
{
    /**
     * SUPERVISIÓN: todos los registros, con filtros (ats.ver_todos).
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));
        $status = trim((string) $request->query('status'));
        $machineId = (int) $request->query('machine');

        $query = ShiftRecord::query()
            ->with(['user:id,name,last_name', 'machine:id,name'])
            ->orderByDesc('started_at');

        if ($search !== '') {
            $like = '%'.$search.'%';

            $query->where(function ($builder) use ($like) {
                $builder->whereHas('user', function ($users) use ($like) {
                    $users->where(function ($q) use ($like) {
                        $q->where('name', 'like', $like)
                            ->orWhere('last_name', 'like', $like)
                            ->orWhere('email', 'like', $like);
                    });
                })->orWhereHas('machine', function ($machines) use ($like) {
                    $machines->where('name', 'like', $like);
                });
            });
        }

        if (in_array($status, [ShiftRecord::STATUS_PENDING, ShiftRecord::STATUS_COMPLETED], true)) {
            $query->where('status', $status);
        }

        if ($machineId > 0) {
            $query->where('machine_id', $machineId);
        }

        $records = $query->paginate(15)->withQueryString();

        return view('ats.records.index', [
            'records' => $records,
            'search' => $search,
            'status' => $status,
            'machines' => Machine::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Detalle: foto, firma y respuestas. Puede verlo el dueño del
     * registro o quien tenga ats.ver_todos.
     */
    public function show(Request $request, ShiftRecord $record): View
    {
        $user = $request->user();

        abort_unless(
            $record->user_id === $user->id || $user->can('ats.ver_todos'),
            403
        );

        $record->load([
            'user:id,name,last_name,email',
            'machine:id,name',
            'answers.question',
        ]);

        return view('ats.records.show', [
            'record' => $record,
            'startAnswers' => $record->answersForStage('start'),
            'finishAnswers' => $record->answersForStage('finish'),
        ]);
    }
}
