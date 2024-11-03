<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        // Create teachers
        $teachers = [
            [
                'name' => 'John Smith',
                'email' => 'john.smith@teacher.com',
                'password' => Hash::make('password123')
            ],
            [
                'name' => 'Mary Johnson',
                'email' => 'mary.johnson@teacher.com',
                'password' => Hash::make('password123')
            ]
        ];

        foreach ($teachers as $teacher) {
            $user = User::create($teacher);
            $user->assignRole('teacher');
        }

        // Create students  
        $students = [
            [
                'name' => 'Alice Brown',
                'email' => 'alice.brown@student.com',
                'password' => Hash::make('password123')
            ],
            [
                'name' => 'Bob Wilson',
                'email' => 'bob.wilson@student.com', 
                'password' => Hash::make('password123')
            ],
            [
                'name' => 'Carol Davis',
                'email' => 'carol.davis@student.com',
                'password' => Hash::make('password123')
            ]
        ];

        foreach ($students as $student) {
            $user = User::create($student);
            $user->assignRole('student');
        }
    }
}