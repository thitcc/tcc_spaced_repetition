<?php

namespace App\Http\Controllers;

use App\Models\Flashcard;
use Illuminate\Http\Request;

class FlashcardController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'question' => 'required|string|max:255',
            'option_a' => 'required|string|max:255',
            'option_b' => 'required|string|max:255',
            'option_c' => 'required|string|max:255',
            'option_d' => 'required|string|max:255',
            'correct_answer' => 'required|in:a,b,c,d',
            'subject_id' => 'required|exists:subjects,id'
        ]);

        $flashcard = Flashcard::create($validatedData);

        return redirect()->back()->with('success', 'Flashcard adicionado com sucesso!');
    }

    public function update(Request $request, Flashcard $flashcard)
    {
        $validatedData = $request->validate([
            'question' => 'required|string|max:255',
            'option_a' => 'required|string|max:255',
            'option_b' => 'required|string|max:255',
            'option_c' => 'required|string|max:255',
            'option_d' => 'required|string|max:255',
            'correct_answer' => 'required|in:a,b,c,d',
        ]);
    
        $flashcard->update($validatedData);
    
        return redirect()->back()->with('success', 'Flashcard atualizado com sucesso!');
    }
}