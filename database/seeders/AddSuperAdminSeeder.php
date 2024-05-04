<?php

namespace Database\Seeders;

use App\Models\SessionYear;
use App\Models\Settings;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AddSuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        //Add Super Admin User
        $super_admin_role = Role::where('name', 'Super Admin')->first();
        $user = User::updateOrCreate(['id' => 1], [
            'first_name' => 'super',
            'last_name' => 'admin',
            'email' => 'superadmin@gmail.com',
            'password' => Hash::make('superadmin'),
            'gender' => 'Male',
            'image' => 'logo.svg',
            'mobile' => ""
        ]);
        $user->assignRole([$super_admin_role->id]);

        SessionYear::updateOrCreate(['id' => 1],[
            'name' => '2022-23',
            'default' => 1,
            'start_date' => '2022-06-01',
            'end_date' => '2023-04-30',
        ]);

        // add session year in setting table
        $session_year = new Settings();
        $session_year->type = 'session_year';
        $session_year->message = 1;
        $session_year->save();
    }
}
