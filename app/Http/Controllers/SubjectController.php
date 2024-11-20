<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\SubjectUserCompletion;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class SubjectController extends Controller
{
    public function show(Subject $subject)
    {
        $user = auth()->user();

        $subject->load([
            'flashcards.responses' => function ($query) use ($user) {
                $query->where('user_id', $user->id);
            },
            'userCompletions' => function ($query) use ($user) {
                $query->where('user_id', $user->id);
            }
        ]);

        $subject->flashcards->each(function ($flashcard) {
            $flashcard->answered = count($flashcard->responses) > 0;
        });

        $completion = $subject->userCompletions->first();
        $isDueForReview = $completion && $completion->next_review_at && Carbon::now()->gte($completion->next_review_at);

        return Inertia::render('SubjectShow', [
            'user' => $user,
            'subject' => $subject,
            'userRole' => $user->getRoleNames()->first(),
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

        $user = auth()->user();
        $quality = $request->input('quality');

        $completion = SubjectUserCompletion::updateOrCreate(
            ['user_id' => $user->id, 'subject_id' => $subject->id],
            ['completed' => $quality >= 4]
        );

        $completion->updatePriority($quality);

        return response()->json(['success' => true]);
    }
}