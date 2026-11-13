<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $email = 'elmannafadmin@yopmail.com';

        if (User::where('email', $email)->exists()) {
            $this->command?->warn('Akun dengan email tersebut sudah ada; tidak ada perubahan.');

            return;
        }

        $password = '12345678Aa.';

        $user = new User;
        $user->name = 'superadmin';
        $user->email = $email;
        $user->password = $password;
        $user->role = 'admin';
        $user->email_verified_at = now();
        $user->save();
    }
}
