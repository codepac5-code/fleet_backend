<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Issue;
use App\Models\Employee;
use App\Models\Department;

class IssueSeeder extends Seeder
{
    public function run()
    {
        $statuses = ['open', 'processing', 'closed'];
        $priorities = ['low', 'medium', 'high'];
    
        for ($i = 1; $i <= 20; $i++) {
            $employee = Employee::inRandomOrder()->first();
            $department = Department::inRandomOrder()->first();
        
            Issue::create([
                'owner_id' => 1,
                'owner_type' => 'App\Models\User',
                'subject' => "بلاغ رقم $i",
                'description' => "وصف البلاغ رقم $i...",
                'status' => $statuses[array_rand($statuses)],
                'priority' => $priorities[array_rand($priorities)],
                'assigned_to_id' => $employee->id,
                'assigned_to_type' => get_class($employee), // أو 'App\Models\Employee'
                'department_id' => $department->id,
                'mode' => 'app',
                'isClosed' => false,
            ]);
        }
        
    }
}
