<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class SubjectController extends Controller
{
    public function show(Subject $subject)
    {
        $subject->load(['flashcards' => function ($query) {
            $query->with(['userResponse']);
        }]);
    
        $isDueForReview = $subject->next_review_at && Carbon::now()->gte($subject->next_review_at);
    
        return Inertia::render('SubjectShow', [
            'user' => auth()->user(),
            'subject' => $subject,
            'userRole' => auth()->user()->getRoleNames()->first(),
            'isDueForReview' => $isDueForReview,
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

    public function reviewSubject(Request $request, Subject $subject)
    {
        $request->validate([
            'quality' => 'required|integer|min:0|max:5',
        ]);

        $quality = $request->input('quality');

        $subject->updatePriority($quality);

        return response()->json(['success' => true]);
    }
}
