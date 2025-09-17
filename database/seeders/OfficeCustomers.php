<?php

namespace Database\Seeders;

use App\Models\Office;
use App\Models\officeUserStats;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OfficeCustomers extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('office_user_stats')->delete();

        $customers = User::all();
        $extraUsers = [];

        foreach ($customers as $customer){

            $extraUsers[] = [
                'officeId'=> rand(1, 2), 
                'userId'=> $customer->id, 
                'totalBookings' =>rand(85,3), 
                'totalAmount'=>rand(7000,25) , 
                'totalDistance'=>rand(25,2) , 
                'lastBookingAt'=>now(), 
                'averageRating'=>rand( 5 ,0), 
                'lastPaymentStatus'=>'paid'
            ];

            $extraUsers[] = [
                'officeId'=> rand(3, 5), 
                'userId'=> $customer->id, 
                'totalBookings' =>rand(85,3), 
                'totalAmount'=>rand(7000,25) , 
                'totalDistance'=>rand(25,2) , 
                'lastBookingAt'=>now(), 
                'averageRating'=>rand( 5 ,0), 
                'lastPaymentStatus'=>'paid'
            ];
        }

        DB::table('office_user_stats')->insert($extraUsers);
   
    }
}
