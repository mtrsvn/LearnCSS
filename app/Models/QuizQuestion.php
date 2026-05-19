<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    use HasFactory;

    protected $fillable = ['topic_id', 'question', 'options', 'answer'];

    protected function casts(): array
    {
        return [
            'options' => 'array',
        ];
    }

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }
}
