<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/** চার রোলের ডিফল্ট অ্যাকাউন্ট — ডিপ্লয়মেন্টের পরই পাসওয়ার্ড বদলান */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'সুপার অ্যাডমিন', 'email' => 'admin@mahalcharinews.com', 'role' => 'super_admin', 'designation' => 'ব্যবস্থাপনা সম্পাদক'],
            ['name' => 'সম্পাদক',        'email' => 'editor@mahalcharinews.com', 'role' => 'editor',      'designation' => 'নির্বাহী সম্পাদক'],
            ['name' => 'রিপোর্টার',      'email' => 'reporter@mahalcharinews.com','role' => 'reporter',    'designation' => 'স্টাফ রিপোর্টার'],
            ['name' => 'মডারেটর',        'email' => 'moderator@mahalcharinews.com','role' => 'moderator',  'designation' => 'কনটেন্ট মডারেটর'],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(['email' => $data['email']], [
                'name' => $data['name'],
                'password' => Hash::make('ChangeMe@123'),   // প্রোডাকশনে অবশ্যই বদলান
                'role' => $data['role'],
                'designation' => $data['designation'],
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
        }
    }
}
