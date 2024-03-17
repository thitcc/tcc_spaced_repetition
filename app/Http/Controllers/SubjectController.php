<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubjectController extends Controller
{
    public function show(Subject $subject)
    {
        return Inertia::render('SubjectShow', [
            'user' => auth()->user(),
            'subject' => $subject->load('flashcards'),
            'userRole' => auth()->user()->getRoleNames()->first(),
        ]);
    }
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'time_limit' => 'nullable|integer|min:1',
            'course_id' => 'required|exists:courses,id'
        ]);

        $subject = Subject::create($validatedData);

        return redirect()->back()->with('message', 'Tópico adicionado com sucesso!');
    }
}
