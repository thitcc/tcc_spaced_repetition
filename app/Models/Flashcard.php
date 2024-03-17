<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Flashcard extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id', 
        'question', 
        'option_a', 
        'option_b', 
        'option_c', 
        'option_d', 
        'correct_answer'
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isCorrect($option)
    {
        return $this->correct_answer === $option;
    }
}
