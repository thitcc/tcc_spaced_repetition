<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Subject;
use Carbon\Carbon;

class ActivitiesController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $courses = collect();
        $subjects = collect();

        $courses = $user->studentCourses()->with('teacher')->get();

        foreach ($courses as $course) {
            $subject = $course->subjects()->with('course')->with('flashcards')->get();
            $subjects = $subjects->merge($subject);
        }

        $subjects = $subjects->map(function ($subject) {
            $now = Carbon::now();

            if ($subject->next_review_at) {
                $diffInHours = $now->diffInHours($subject->next_review_at, false);

                if ($diffInHours <= 0) {
                    $subject->priority = 10;
                } else {
                    $subject->priority = max(1, 10 - floor($diffInHours / 24));
                }
            } else {
                $subject->priority = 5;
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
