<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShiftRecord extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_COMPLETED = 'completed';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'machine_id',
        'photo_path',
        'signature_path',
        'status',
        'started_at',
        'finished_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(AtsAnswer::class);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Respuestas de una etapa, en el orden de las preguntas.
     *
     * @return \Illuminate\Support\Collection<int, AtsAnswer>
     */
    public function answersForStage(string $stage)
    {
        return $this->answers
            ->filter(fn (AtsAnswer $answer) => $answer->question?->stage === $stage)
            ->sortBy(fn (AtsAnswer $answer) => $answer->question?->position)
            ->values();
    }
}
