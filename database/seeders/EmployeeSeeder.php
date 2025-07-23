<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Office;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    public function run()
    {
        for ($i = 1; $i <= 5; $i++) {
            Employee::create([
                'firstName' => "موظف $i",
                'lastName' => "الاسم الأخير $i",
                'email' => "employee$i@example.com",
                'phoneNumber' => "050000000$i",
                'employeeJobName_en' => 'Support Agent',
                'employeeJobName_ar' => 'موظف دعم',
                'job_description_en' => 'Handles support issues',
                'job_description_ar' => 'يتعامل مع بلاغات الدعم',
                'officeId' => Office::first()->id, 
                'address' => 'عنوان الموظف',
                'country' => 'السعودية',
                'city' => 'الرياض',
                'region' => 'الوسطى',
                'isActive' => true,
                'isOnline' => true,
                'gender' => 'male',
                'password' => Hash::make('password'),
            ]);
        }
    }
}
