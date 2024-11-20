<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ActivitiesController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\FlashcardController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('registro'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Route::group(['middleware' => ['role:admin']], function () {
//     // Rotas que somente o admin pode acessar
// });

// Route::group(['middleware' => ['role:teacher']], function () {
//     // Rotas que somente professores podem acessar
// });

// Route::group(['middleware' => ['role:student']], function () {
//     // Rotas que somente alunos podem acessar
// });

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');
Route::get('/activities', [ActivitiesController::class, 'index'])->middleware(['auth', 'verified'])->name('activities');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{notification}/markAsRead', [NotificationController::class, 'markAsRead']);
    Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');
    Route::get('/courses/{course}/available-students', [CourseController::class, 'getAvailableStudents'])->name('courses.available-students');
    Route::get('/subjects/{subject}', [SubjectController::class, 'show'])->name('subjects.show');
});

Route::middleware(['auth', 'role:teacher'])->group(function () {
    Route::post('/courses', [CourseController::class, 'store'])->name('courses.store');
    Route::post('/courses/{course}/addStudent', [CourseController::class, 'addStudent'])->name('courses.addStudent');
    Route::post('/subjects', [SubjectController::class, 'store'])->name('subjects.store');
    Route::post('/flashcards', [FlashcardController::class, 'store'])->name('flashcards.store');
    Route::put('/flashcards/{flashcard}', [FlashcardController::class, 'update'])->name('flashcards.update');
});

require __DIR__.'/auth.php';
