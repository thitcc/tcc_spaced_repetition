<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $sortBy = $request->query('sortBy', 'created_at');
        $sortDirection = $request->query('sortDirection', 'desc');
    
        $courses = $user->courses()
                        ->with('teacher')
                        ->orderBy($sortBy, $sortDirection)
                        ->get();
    
        return Inertia::render('Dashboard', [
            'user' => $user,
            'courses' => $courses,
            'userRole' => $user->getRoleNames()->first(),
        ]);
    }
}
