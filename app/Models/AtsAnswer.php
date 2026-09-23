<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AtsAnswer extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'shift_record_id',
        'ats_question_id',
        'answer',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'answer' => 'boolean',
        ];
    }

    public function record(): BelongsTo
    {
        return $this->belongsTo(ShiftRecord::class, 'shift_record_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(AtsQuestion::class, 'ats_question_id')->orderBy('ats_questions.position');
    }
}
