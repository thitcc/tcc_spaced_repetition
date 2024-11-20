<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\User;
use Inertia\Inertia;


class CourseController extends Controller
{
    public function show(Course $course)
    {
        return Inertia::render('CourseShow', [
            'user' => auth()->user(),
            'course' => $course->load('teacher', 'subjects'),
            'userRole' => auth()->user()->getRoleNames()->first(),
        ]);
    }
    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $course = Course::create([
            'name' => $request->name,
            'teacher_id' => auth()->id(),
        ]);

        return redirect('/dashboard')->with('success', 'Curso criado com sucesso.');
    }

    public function getAvailableStudents(Request $request, Course $course)
    {
        $search = $request->input('search', '');

        return User::role('student')
            ->whereNotIn('id', $course->students->pluck('id'))
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->select('id', 'name', 'email')
            ->get();
    }

    public function addStudent(Request $request, $courseId)
    {
        $course = Course::findOrFail($courseId);
        $studentIds = $request->input('student_ids', []);

        $students = User::whereIn('id', $studentIds)
            ->role('student')
            ->get();

        $course->students()->attach($students);

        return back()->with('success', 'Students added successfully.');
    }
}
