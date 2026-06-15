<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subtopic extends Model
{
    use HasFactory;

    protected $fillable = [
        'topic_id',
        'title',
        'sort_order',
        'video_url',
        'documentation_path',
        'documentation_filename',
        'status',
    ];

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }
}
