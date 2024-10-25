<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'name',
        'completed',
        'repetition_count',
        'easiness_factor',
        'interval',
        'next_review_at'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function flashcards()
    {
        return $this->hasMany(Flashcard::class);
    }

    public function complete()
    {
        $this->completed = true;
        $this->save();
    }
    
    public function updatePriority($quality)
    {
        $quality = max(0, min(5, $quality));

        if ($quality >= 3) {
            if ($this->repetition_count == 0) {
                $this->interval = 1;
            } elseif ($this->repetition_count == 1) {
                $this->interval = 6;
            } else {
                $this->interval = round($this->interval * $this->easiness_factor);
            }
            $this->repetition_count += 1;
        } else {
            $this->repetition_count = 0;
            $this->interval = 1;
        }

        $this->easiness_factor = $this->easiness_factor + (0.1 - (5 - $quality) * (0.08 + (5 - $quality) * 0.02));
        $this->easiness_factor = max(1.3, $this->easiness_factor);

        $this->next_review_at = Carbon::now()->addDays($this->interval);

        $this->save();
    }
}
