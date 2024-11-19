<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TestStudentsSeeder extends Seeder
{
  public function run()
  {
    $students = [
      'hugo',
      'laryssa',
      'dinha',
      'raggi',
      'banana',
      'jeferson',
      'ricardo',
      'rodrigo',
      'paulo',
      'guiga',
      'lucax',
      'isa',
      'malta',
      'alfredo',
      'ranilson',
      'tati',
      'jam',
      'arthur',
      'gabi',
      'neto',
      'lucasrafa',
      'rafalucas',
      'agra',
      'ph',
      'iana',
      'igor',
      'mimi',
      'camila',
      'caio',
      'helena',
      'mari',
      'lilian',
      'filipe',
      'amanda',
      'leticia',
      'victor'
    ];

    foreach ($students as $name) {
      $user = User::create([
        'name' => ucfirst($name),
        'email' => "{$name}@{$name}.com",
        'password' => Hash::make('senha123')
      ]);
      $user->assignRole('student');
    }
  }
}