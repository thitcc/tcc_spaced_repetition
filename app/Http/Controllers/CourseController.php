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
    
    public function addStudent(Request $request, $courseId)
    {
        $course = Course::findOrFail($courseId);
    
        $studentEmails = $request->input('student_emails');
        $emailsArray = explode(',', $studentEmails);
    
        $students = User::whereIn('email', $emailsArray)->get();
    
        foreach ($students as $student) {
            if ($student->hasRole('student')) {
                $course->students()->attach($student);
            }
        }
    
        return back()->with('success', 'Students added successfully.');
    }
}
