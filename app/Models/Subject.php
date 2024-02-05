<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id', 'name', 'time_limit'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function flashcards()
    {
        return $this->hasMany(Flashcard::class);
    }
}
