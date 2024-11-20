<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SubjectUserCompletion extends Model
{
  protected $table = 'subject_user_completion';

  protected $fillable = [
    'user_id',
    'subject_id',
    'completed',
    'repetition_count',
    'easiness_factor',
    'interval',
    'next_review_at'
  ];

  protected $casts = [
    'completed' => 'boolean',
    'next_review_at' => 'datetime'
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function subject()
  {
    return $this->belongsTo(Subject::class);
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