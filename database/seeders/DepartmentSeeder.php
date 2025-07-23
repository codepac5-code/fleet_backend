<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    public function run()
    {
        $departments = ['الدعم الفني', 'الموارد البشرية', 'المبيعات', 'الشؤون القانونية'];

        foreach ($departments as $name) {
            Department::create([
                'name_ar' => $name,
                'name_en'=>$name
            ]);
        }
    }
}
