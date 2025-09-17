<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Driver;

class RatingsSeeder extends Seeder
{
    public function run()
    {
        DB::table('ratings')->truncate();

        $userIds   = range(50, 65);
        $driverIds = range(11, 20); 

        $commentsFromUsers = [
            "الرحلة كانت مريحة جداً والسائق محترم.",
            "وصل بسرعة وكان الطريق آمن.",
            "الخدمة ممتازة لكن السيارة تحتاج تنظيف.",
            "سائق لطيف وتعامل راقي.",
            "وصل متأخر قليلاً لكن بشكل عام جيد.",
            "أفضل تجربة لي حتى الآن مع التطبيق.",
            "القيادة كانت سريعة نوعاً ما.",
            "السائق يعرف الطرق جيداً وساعدني كثير.",
            "الخدمة جيدة لكن السيارة قديمة.",
            "سائق ودود والتجربة رائعة.",
            "التطبيق سهل والسائق محترف.",
            "الرحلة كانت طويلة لكن مريحة.",
            "تأخر بسيط في الوصول لكن عوّض بالخدمة الممتازة.",
            "سائق هادئ ويقود بأمان.",
            "موسيقى جميلة في السيارة والجو كان رائع.",
        ];

        $commentsFromDrivers = [
            "الراكب محترم جداً وسهل التعامل.",
            "كان دقيق في الوقت وهذا ساعدني.",
            "الراكب تحدث معي بطريقة ودية.",
            "لا مشاكل، تجربة جيدة معه.",
            "الراكب كان هادئ طوال الطريق.",
            "أعطاني تعليمات واضحة لوصول المكان.",
            "رحلة مريحة مع هذا الراكب.",
            "الراكب متعاون ويعرف وجهته.",
            "تعامل راقي وأنصح به.",
            "الراكب ملتزم بالوقت ومكان الاستلام.",
            "رحلة ممتعة بدون أي مشاكل.",
            "شخص لطيف ويقدّر الخدمة.",
            "الراكب دفع بسرعة وكان صريح.",
            "تعامل ممتاز وسأكون سعيد بخدمته مجدداً.",
            "الراكب متفهم وظريف."
        ];

        for ($i = 0; $i < 25; $i++) {
            DB::table('ratings')->insert([
                'rater_id' => $userIds[array_rand($userIds)],
                'rater_type' => get_class(new User()),
                'rated_person_id' => $driverIds[array_rand($driverIds)],
                'rated_person_type' => get_class(new Driver()),
                'description' => $commentsFromUsers[array_rand($commentsFromUsers)],
                'rating' => rand(1, 5),
                'orderId' => rand(1, 100),
                'officeId' => (rand(0, 1) ? rand(1, 5) : null),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        for ($i = 0; $i < 25; $i++) {
            DB::table('ratings')->insert([
                'rater_id' => $driverIds[array_rand($driverIds)],
                'rater_type' => get_class(new Driver()),
                'rated_person_id' => $userIds[array_rand($userIds)],
                'rated_person_type' => get_class(new User()),
                'description' => $commentsFromDrivers[array_rand($commentsFromDrivers)],
                'rating' => rand(1, 5),
                'orderId' => rand(1, 100),
                'officeId' => (rand(0, 1) ? rand(1, 5) : null),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
