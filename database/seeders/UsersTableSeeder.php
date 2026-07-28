<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersTableSeeder extends Seeder
{
    public function run()
    {

        DB::table('users')->delete();

        // $dialCode = '963';
        $dialCode = '974';
        // $dialCode = '963';

        $baseUsers = [
            [
                "id" => 3,
                "firstName" => "mohammad",
                "lastName" => "alwagha",
                "gender" => "male",
                "phoneNumber" => "933884491",
                "email_verified_at" => null,
                "walletBalance" => 2039668,
                "photo" => "/storage/user/profile/7c4358b1-1e85-4dbf-a1d0-239d76ee3d00.jpg",
                "isActive" => 1,
                "deleted_at" => null,
                "referralCode" => "REF-A1B2C3",
                "dialCode"=> $dialCode,// => "",
                "password" => Hash::make('Aa123456**'),
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => 4,
                "firstName" => "Bassam",
                "lastName" => "Nakkez",
                "gender" => "male",
                "phoneNumber" => "933817393",
                "email_verified_at" => null,
                "walletBalance" => 2007276,
                "photo" => "\\storage\\images\\system\\user_no_photo4a305405-ad0b-473a-a40c-456397a18b96.png",
                "isActive" => 1,
                "deleted_at" => null,
                "referralCode" => "REF-A1B2F5",
                "dialCode"=> $dialCode,// => "",
                "password" => Hash::make('Aa123456**'),
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => 5,
                "firstName" => "user 1",
                "lastName" => "user 1",
                "gender" => "male",
                "phoneNumber" => "933445555",
                "email_verified_at" => null,
                "walletBalance" => 2000000,
                "photo" => "\\storage\\images\\system\\user_no_photo4a305405-ad0b-473a-a40c-456397a18b96.png",
                "isActive" => 1,
                "deleted_at" => null,
                "referralCode" => "REF-A1R3C3",
                "dialCode"=> $dialCode,// => "",
                "password" => Hash::make('Aa123456**'),
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => 6,
                "firstName" => "user 2",
                "lastName" => "user 2",
                "gender" => "male",
                "phoneNumber" => "933888999",
                "email_verified_at" => null,
                "walletBalance" => 2000000,
                "photo" => "\\storage\\images\\system\\user_no_photo4a305405-ad0b-473a-a40c-456397a18b96.png",
                "isActive" => 1,
                "deleted_at" => null,
                "referralCode" => "REF-R5B2C6",
                "dialCode"=> $dialCode,// => "",
                "password" => Hash::make('Aa123456**'),
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => 7,
                "firstName" => "user 3",
                "lastName" => "user",
                "gender" => "male",
                "phoneNumber" => "933777999",
                "email_verified_at" => null,
                "walletBalance" => 200000,
                "photo" => "\\storage\\images\\system\\user_no_photo4a305405-ad0b-473a-a40c-456397a18b96.png",
                "isActive" => 1,
                "deleted_at" => null,
                "referralCode" => "REF-BDE554",
                "dialCode"=> $dialCode,// => "",
                "password" => Hash::make('Aa123456**'),
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => 8,
                "firstName" => "Mousab",
                "lastName" => "Alsyoufi",
                "gender" => "male",
                "phoneNumber" => "933111396",
                "email_verified_at" => null,
                "walletBalance" => 325000,
                "photo" => "/storage/user/profile/1cdc06a2-5a6a-4ae5-9bdf-4b1c3f7c0a2b.jpg",
                "isActive" => 1,
                "deleted_at" => null,
                "referralCode" => "REF-A1B2Gk",
                "dialCode"=> $dialCode,// => "",
                "password" => Hash::make('Aa123456**'),
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => 11,
                "firstName" => "user",
                "lastName" => "test",
                "gender" => "male",
                "phoneNumber" => "586000926",
                "email_verified_at" => null,
                "walletBalance" => 0,
                "photo" => "\\storage\\images\\system\\user_no_photo4a305405-ad0b-473a-a40c-456397a18b96.png",
                "isActive" => 1,
                "deleted_at" => null,
                "referralCode" => "REF-A1B299",
                "dialCode"=> $dialCode,// => "",
                "password" => Hash::make('Aa123456**'),
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => 13,
                "firstName" => "Moh",
                "lastName" => "Sy",
                "gender" => "male",
                "phoneNumber" => "997257688",
                "email_verified_at" => null,
                "walletBalance" => 50000,
                "photo" => "\\storage\\images\\system\\user_no_photo4a305405-ad0b-473a-a40c-456397a18b96.png",
                "isActive" => 1,
                "deleted_at" => null,
                "referralCode" => "REF-A1B2U6",
                "dialCode"=> $dialCode,// => "",
                "password" => Hash::make('Aa123456**'),
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => 14,
                "firstName" => "majd",
                "lastName" => "taki",
                "gender" => null,
                "phoneNumber" => "932636079",
                "email_verified_at" => null,
                "walletBalance" => 0,
                "photo" => null,
                "isActive" => 1,
                "deleted_at" => null,
                "referralCode" => "REF-A1B277",
                "dialCode"=> $dialCode,// => "",
                "password" => Hash::make('Aa123456**'),
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => 15,
                "firstName" => "bassam",
                "lastName" => "nakkez",
                "gender" => null,
                "phoneNumber" => "933817363",
                "email_verified_at" => null,
                "walletBalance" => 0,
                "photo" => null,
                "isActive" => 1,
                "deleted_at" => null,
                "referralCode" => "REF-T1B2C3",
                "dialCode"=> $dialCode,// => "",
                "password" => Hash::make('Aa123456**'),
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => 17,
                "firstName" => "belal",
                "lastName" => "Alsyoufi",
                "gender" => "male",
                "phoneNumber" => "982313171",
                "email_verified_at" => null,
                "walletBalance" => 10000,
                "photo" => "\\storage\\images\\system\\user_no_photo4a305405-ad0b-473a-a40c-456397a18b96.png",
                "isActive" => 1,
                "deleted_at" => null,
                "referralCode" => null,
                "dialCode"=> $dialCode,// => "",
                "password" => Hash::make('Aa123456**'),
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => 27,
                "firstName" => "bassam",
                "lastName" => "syr",
                "gender" => null,
                "phoneNumber" => "985980366",
                "email_verified_at" => null,
                "walletBalance" => 0,
                "photo" => null,
                "isActive" => 1,
                "deleted_at" => null,
                "referralCode" => null,
                "dialCode"=> $dialCode,// => "",
                "password" => Hash::make('Aa123456**'),
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => 40,
                "firstName" => "محمد",
                "lastName" => "الوغا",
                "gender" => null,
                "phoneNumber" => "932831045",
                "email_verified_at" => null,
                "walletBalance" => 0,
                "photo" => "\\storage\\images\\system\\user_no_photo4a305405-ad0b-473a-a40c-456397a18b96.png",
                "isActive" => 1,
                "deleted_at" => null,
                "referralCode" => "REF-4ABA75",
                "dialCode"=> $dialCode,// => "+963",
                "password" => Hash::make('Aa123456**'),
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => 42,
                "firstName" => "mustafa",
                "lastName" => "shakil",
                "gender" => null,
                "phoneNumber" => "3184137759",
                "email_verified_at" => null,
                "walletBalance" => 0,
                "photo" => "\\storage\\images\\system\\user_no_photo4a305405-ad0b-473a-a40c-456397a18b96.png",
                "isActive" => 1,
                "deleted_at" => null,
                "referralCode" => "REF-5E6516",
                "dialCode"=> $dialCode,// => "+92",
                "password" => Hash::make('Aa123456**'),
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => 43,
                "firstName" => "mustafa",
                "lastName" => "shakil",
                "gender" => null,
                "phoneNumber" => "3265846282",
                "email_verified_at" => null,
                "walletBalance" => 0,
                "photo" => "\\storage\\images\\system\\user_no_photo4a305405-ad0b-473a-a40c-456397a18b96.png",
                "isActive" => 1,
                "deleted_at" => null,
                "referralCode" => "REF-9F167D",
                "dialCode"=> $dialCode,// => "+92",
                "password" => Hash::make('Aa123456**'),
                "created_at" => now(),
                "updated_at" => now(),
            ],
        ];


        DB::table('users')->insert($baseUsers);


        $femaleNames = [
            ['Aisha', 'Khan'], ['Fatima', 'Ali'], ['Layla', 'Hassan'],
            ['Noor', 'Omar'], ['Mariam', 'Yousef'], ['Huda', 'Nasser'],
            ['Rania', 'Ibrahim'], ['Sara', 'Hamad'], ['Zahra', 'Adel'],
        ];

        $maleNames = [
            ['Omar', 'Saleh'], ['Hassan', 'Omar'], ['Abdullah', 'Yousef'],
            ['Khalid', 'Nasser'], ['Bilal', 'Ibrahim'], ['Sami', 'Hamad'],
            ['Majed', 'Adel'], ['Tariq', 'Hatem'],
        ];

        $extraUsers = [];

        foreach ($femaleNames as $index => $name) {
            $extraUsers[] = [
                'firstName' => $name[0],
                'lastName' => $name[1],
                'gender' => 'female',
                'phoneNumber' => '5' . rand(10000000, 99999999),
                'dialCode' => '+971',
                'email_verified_at' => null,
                'password' => Hash::make('Aa123456**'),
                'walletBalance' => rand(0, 100000),
                'photo' => 'storage\\profile-images\\user\\f' . ($index + 1) . '.png',
                'isActive' => 1,
                'referralCode' => 'REF-' . strtoupper(Str::random(6)),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach ($maleNames as $index => $name) {
            $extraUsers[] = [
                'firstName' => $name[0],
                'lastName' => $name[1],
                'gender' => 'male',
                'phoneNumber' => '5' . rand(10000000, 99999999),
                'dialCode' => '+971',
                'email_verified_at' => null,
                'password' => Hash::make('Aa123456**'),
                'walletBalance' => rand(0, 100000),
                'photo' => 'storage\\profile-images\\user\\m' . ($index + 1) . '.png',
                'isActive' => 1,
                'referralCode' => 'REF-' . strtoupper(Str::random(6)),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }


        $id = 50;
        foreach ($extraUsers as &$user) {
            $user['id'] = $id;
            $id++;
        }
        DB::table('users')->insert($extraUsers);
    }
}
