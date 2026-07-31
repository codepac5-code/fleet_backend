<?php

namespace Database\Seeders;

use App\Models\CancellationReason;
use App\Models\Document;
use App\Models\RatingTag;
use App\Models\Service;
use Database\Seeders\Production\PermissionGroupSeeder;
use Database\Seeders\Production\RolesAndPermissionsSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class ShardSeeder extends Seeder
{
    private const CANCELLATION_REASONS = [
        ['code' => 'driver_late', 'label_en' => 'Driver is taking too long', 'label_ar' => 'السائق يتأخّر كثيراً', 'audience' => 'rider', 'sort' => 1],
        ['code' => 'found_another', 'label_en' => 'Found another ride', 'label_ar' => 'وجدت رحلة أخرى', 'audience' => 'rider', 'sort' => 2],
        ['code' => 'wrong_pickup', 'label_en' => 'Wrong pickup location', 'label_ar' => 'موقع الانطلاق خاطئ', 'audience' => 'rider', 'sort' => 3],
        ['code' => 'rider_no_show', 'label_en' => 'Rider did not show up', 'label_ar' => 'الراكب لم يحضر', 'audience' => 'driver', 'sort' => 1],
        ['code' => 'rider_unreachable', 'label_en' => 'Cannot reach the rider', 'label_ar' => 'تعذّر الوصول للراكب', 'audience' => 'driver', 'sort' => 2],
        ['code' => 'too_far', 'label_en' => 'Pickup is too far', 'label_ar' => 'نقطة الانطلاق بعيدة', 'audience' => 'driver', 'sort' => 3],
        ['code' => 'changed_mind', 'label_en' => 'Changed my mind', 'label_ar' => 'غيّرت رأيي', 'audience' => 'both', 'sort' => 10],
        ['code' => 'other', 'label_en' => 'Other', 'label_ar' => 'سبب آخر', 'audience' => 'both', 'sort' => 99],
    ];
    private const RATING_TAGS = [
        ['code' => 'clean_car', 'label_en' => 'Clean car', 'label_ar' => 'سيارة نظيفة', 'audience' => 'rider', 'stars_min' => 4, 'stars_max' => 5, 'sort' => 1],
        ['code' => 'safe_driving', 'label_en' => 'Safe driving', 'label_ar' => 'قيادة آمنة', 'audience' => 'rider', 'stars_min' => 4, 'stars_max' => 5, 'sort' => 2],
        ['code' => 'great_route', 'label_en' => 'Great route', 'label_ar' => 'اختيار طريق ممتاز', 'audience' => 'rider', 'stars_min' => 4, 'stars_max' => 5, 'sort' => 3],
        ['code' => 'late_arrival', 'label_en' => 'Arrived late', 'label_ar' => 'وصل متأخراً', 'audience' => 'rider', 'stars_min' => 1, 'stars_max' => 3, 'sort' => 4],
        ['code' => 'dirty_car', 'label_en' => 'Car was not clean', 'label_ar' => 'السيارة غير نظيفة', 'audience' => 'rider', 'stars_min' => 1, 'stars_max' => 3, 'sort' => 5],
        ['code' => 'rough_driving', 'label_en' => 'Rough driving', 'label_ar' => 'قيادة متهورة', 'audience' => 'rider', 'stars_min' => 1, 'stars_max' => 3, 'sort' => 6],
        ['code' => 'ready_on_time', 'label_en' => 'Ready on time', 'label_ar' => 'كان جاهزاً في الموعد', 'audience' => 'driver', 'stars_min' => 4, 'stars_max' => 5, 'sort' => 1],
        ['code' => 'clear_directions', 'label_en' => 'Clear directions', 'label_ar' => 'إرشادات واضحة', 'audience' => 'driver', 'stars_min' => 4, 'stars_max' => 5, 'sort' => 2],
        ['code' => 'long_wait', 'label_en' => 'Kept me waiting', 'label_ar' => 'أبقاني منتظراً', 'audience' => 'driver', 'stars_min' => 1, 'stars_max' => 3, 'sort' => 3],
        ['code' => 'wrong_address', 'label_en' => 'Wrong pickup address', 'label_ar' => 'عنوان انطلاق خاطئ', 'audience' => 'driver', 'stars_min' => 1, 'stars_max' => 3, 'sort' => 4],
        ['code' => 'polite', 'label_en' => 'Polite', 'label_ar' => 'لبق ومحترم', 'audience' => 'both', 'stars_min' => 4, 'stars_max' => 5, 'sort' => 10],
        ['code' => 'rude', 'label_en' => 'Rude behaviour', 'label_ar' => 'تصرّف غير لائق', 'audience' => 'both', 'stars_min' => 1, 'stars_max' => 3, 'sort' => 11],
        ['code' => 'fast_response', 'label_en' => 'Fast response', 'label_ar' => 'استجابة سريعة', 'audience' => 'office', 'stars_min' => 4, 'stars_max' => 5, 'sort' => 1],
        ['code' => 'fair_price', 'label_en' => 'Fair price', 'label_ar' => 'سعر عادل', 'audience' => 'office', 'stars_min' => 4, 'stars_max' => 5, 'sort' => 2],
        ['code' => 'great_support', 'label_en' => 'Great support', 'label_ar' => 'دعم ممتاز', 'audience' => 'office', 'stars_min' => 4, 'stars_max' => 5, 'sort' => 3],
        ['code' => 'slow_support', 'label_en' => 'Slow support', 'label_ar' => 'دعم بطيء', 'audience' => 'office', 'stars_min' => 1, 'stars_max' => 3, 'sort' => 4],
        ['code' => 'price_too_high', 'label_en' => 'Price too high', 'label_ar' => 'السعر مرتفع', 'audience' => 'office', 'stars_min' => 1, 'stars_max' => 3, 'sort' => 5],
    ];

    private const SERVICES = [
        ['title' => 'رحلة', 'title_en' => 'Ride', 'description' => 'رحلات المدينة اليومية.', 'description_en' => 'Everyday city rides.', 'travel_service' => false],
        ['title' => 'سفر', 'title_en' => 'Travel', 'description' => 'رحلات بين المدن والمطار.', 'description_en' => 'Intercity and airport trips.', 'travel_service' => true],
    ];

    private const DOCUMENTS = [
        ['name' => 'رخصة القيادة', 'is_required' => 1],
        ['name' => 'الهوية الشخصية', 'is_required' => 1],
        ['name' => 'دفتر المركبة', 'is_required' => 1],
        ['name' => 'تأمين المركبة', 'is_required' => 0],
    ];

    public function run(): void
    {
        // The permission CATALOG has to exist in the same database as the rows
        // that point at it: employees live per shard, so spatie writes their
        // `model_has_permissions` there — while `permissions` was only ever
        // seeded into the platform DB. Every employee on a non-reference shard
        // therefore resolved to ZERO permissions no matter what was granted.
        $this->call([
            RolesAndPermissionsSeeder::class,
            PermissionGroupSeeder::class,
        ]);

        if (Schema::hasTable('services')) {
            foreach (self::SERVICES as $service) {
                Service::query()->firstOrCreate(
                    ['title_en' => $service['title_en']],
                    [
                        'title' => $service['title'],
                        'description' => $service['description'],
                        'description_en' => $service['description_en'],
                        'travel_service' => $service['travel_service'],
                        'status' => true,
                    ]
                );
            }
        }

        if (Schema::hasTable('documents')) {
            foreach (self::DOCUMENTS as $document) {
                Document::query()->firstOrCreate(
                    ['name' => $document['name']],
                    ['status' => 1, 'is_required' => $document['is_required']]
                );
            }
        }

        if (Schema::hasTable('cancellation_reasons')) {
            foreach (self::CANCELLATION_REASONS as $reason) {
                CancellationReason::query()->firstOrCreate(
                    ['code' => $reason['code']],
                    [
                        'label_en' => $reason['label_en'],
                        'label_ar' => $reason['label_ar'],
                        'audience' => $reason['audience'],
                        'sort' => $reason['sort'],
                        'is_active' => true,
                    ]
                );
            }
        }

        if (Schema::hasTable('rating_tags')) {
            foreach (self::RATING_TAGS as $tag) {
                RatingTag::query()->firstOrCreate(
                    ['code' => $tag['code']],
                    [
                        'label_en' => $tag['label_en'],
                        'label_ar' => $tag['label_ar'],
                        'audience' => $tag['audience'],
                        'stars_min' => $tag['stars_min'],
                        'stars_max' => $tag['stars_max'],
                        'sort' => $tag['sort'],
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
