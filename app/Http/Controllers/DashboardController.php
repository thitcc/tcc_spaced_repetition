<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $courses = $user->courses()->with('teacher')->get();

        return Inertia::render('Dashboard', [
            'courses' => $courses,
            'userRole' => auth()->user()->getRoleNames()->first(),
        ]);
    }
}
