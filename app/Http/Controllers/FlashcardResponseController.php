<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Flashcard;
use App\Models\FlashcardResponse;
use Illuminate\Http\Request;
use App\Models\Subject;
use Carbon\Carbon;

class FlashcardResponseController extends Controller
{
    public function store(Request $request, Flashcard $flashcard)
    {
        $validatedData = $request->validate([
            'selected_answer' => 'required|in:a,b,c,d',
        ]);
    
        $user = $request->user();
    
        $existingResponse = FlashcardResponse::where('flashcard_id', $flashcard->id)
            ->where('user_id', $user->id)
            ->first();
    
        if ($existingResponse) {
            return response()->json(['message' => 'Answer already submitted'], 400);
        }
    
        $isCorrect = $flashcard->correct_answer === $validatedData['selected_answer'];
    
        FlashcardResponse::create([
            'flashcard_id' => $flashcard->id,
            'user_id' => $user->id,
            'selected_answer' => $validatedData['selected_answer'],
            'is_correct' => $isCorrect,
        ]);
    
        $subject = $flashcard->subject;
        $totalFlashcards = $subject->flashcards()->count();
        $answeredFlashcards = FlashcardResponse::whereIn('flashcard_id', $subject->flashcards()->pluck('id'))
            ->where('user_id', $user->id)
            ->count();
    
        if ($totalFlashcards > 0 && $totalFlashcards == $answeredFlashcards) {
            $subject->completed = true;
            $subject->save();
        }
    
        return response()->json(['is_correct' => $isCorrect]);
    }

    public function resetResponses(Request $request, Subject $subject)
    {
        $user = $request->user();
    
        FlashcardResponse::whereHas('flashcard', function ($query) use ($subject) {
            $query->where('subject_id', $subject->id);
        })->where('user_id', $user->id)->delete();
    
        $subject->completed = false;
    
        $subject->repetition_count = 0;
        $subject->easiness_factor = 2.5;
        $subject->interval = 1;
        $subject->next_review_at = Carbon::now();
    
        $subject->save();
    
        return response()->json(['success' => true]);
    }
}
