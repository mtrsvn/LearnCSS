<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'sort_order',
        'status',
        'description',
        'video_url',
        'videos',
        'documentation_path',
        'documentation_filename',
    ];

    protected $casts = [
        'videos' => 'array',
    ];

    public function lessons()
    {
        return $this->hasMany(Lesson::class)->orderBy('sort_order');
    }

    public function subtopics()
    {
        return $this->hasMany(Subtopic::class)->orderBy('sort_order');
    }

    public function quizQuestions()
    {
        return $this->hasMany(QuizQuestion::class);
    }
}
