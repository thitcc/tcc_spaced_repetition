<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ActivitiesController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $courses = $user->studentCourses()->with('teacher')->get();
        $subjects = collect();

        foreach ($courses as $course) {
            $subject = $course->subjects()
                ->with('course')
                ->with('flashcards')
                ->with([
                    'userCompletions' => function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    }
                ])
                ->get();
            $subjects = $subjects->merge($subject);
        }

        $subjects = $subjects->map(function ($subject) use ($user) {
            $completion = $subject->userCompletions->first();

            if ($completion && $completion->completed) {
                $subject->priority = 0;
                $subject->completed = true;
                $subject->next_review_at = $completion->next_review_at;
            } elseif ($completion && $completion->next_review_at) {
                $diffInHours = Carbon::now()->diffInHours($completion->next_review_at, false);
                if ($diffInHours <= 0) {
                    $subject->priority = 10;
                } else {
                    $subject->priority = max(1, 10 - floor($diffInHours / 24));
                }
                $subject->completed = false;
                $subject->next_review_at = $completion->next_review_at;
            } else {
                $subject->priority = 5;
                $subject->completed = false;
            }

            return $subject;
        })->sortByDesc('priority')->values();

        return Inertia::render('Activities', [
            'user' => $user,
            'courses' => $courses,
            'subjects' => $subjects,
            'userRole' => $user->getRoleNames()->first(),
        ]);
    }
}