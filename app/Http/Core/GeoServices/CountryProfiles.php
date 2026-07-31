<?php

namespace App\Http\Core\GeoServices;

class CountryProfiles
{
    public static function for(string $iso2): ?array
    {
        return self::all()[strtoupper(trim($iso2))] ?? null;
    }

    public static function has(string $iso2): bool
    {
        return isset(self::all()[strtoupper(trim($iso2))]);
    }

    public static function provinces(string $iso2): array
    {
        return self::for($iso2)['provinces'] ?? [];
    }

    private static function p(string $ar, string $en): array
    {
        return ['ar' => $ar, 'en' => $en];
    }

    public static function all(): array
    {
        return [
            'SY' => [
                'currency_code' => 'SYP', 'currency_symbol' => 'ل.س', 'currency_name' => 'Syrian Pound', 'dial_code' => '963', 'decimals' => 0,
                'provinces' => [
                    self::p('دمشق', 'Damascus'), self::p('ريف دمشق', 'Rif Dimashq'), self::p('حلب', 'Aleppo'),
                    self::p('حمص', 'Homs'), self::p('حماة', 'Hama'), self::p('اللاذقية', 'Latakia'),
                    self::p('طرطوس', 'Tartus'), self::p('إدلب', 'Idlib'), self::p('درعا', 'Daraa'),
                    self::p('السويداء', 'As-Suwayda'), self::p('القنيطرة', 'Quneitra'), self::p('دير الزور', 'Deir ez-Zor'),
                    self::p('الرقة', 'Raqqa'), self::p('الحسكة', 'Al-Hasakah'),
                ],
            ],
            'QA' => [
                'currency_code' => 'QAR', 'currency_symbol' => 'ر.ق', 'currency_name' => 'Qatari Riyal', 'dial_code' => '974', 'decimals' => 2,
                'provinces' => [
                    self::p('الدوحة', 'Doha'), self::p('الريان', 'Al Rayyan'), self::p('الوكرة', 'Al Wakrah'),
                    self::p('الخور', 'Al Khor'), self::p('أم صلال', 'Umm Salal'), self::p('الظعاين', 'Al Daayen'),
                    self::p('الشمال', 'Al Shamal'), self::p('الشيحانية', 'Al Shahaniya'),
                ],
            ],
            'SA' => [
                'currency_code' => 'SAR', 'currency_symbol' => 'ر.س', 'currency_name' => 'Saudi Riyal', 'dial_code' => '966', 'decimals' => 2,
                'provinces' => [
                    self::p('الرياض', 'Riyadh'), self::p('مكة المكرمة', 'Makkah'), self::p('المدينة المنورة', 'Madinah'),
                    self::p('القصيم', 'Qassim'), self::p('المنطقة الشرقية', 'Eastern Province'), self::p('عسير', 'Asir'),
                    self::p('تبوك', 'Tabuk'), self::p('حائل', 'Hail'), self::p('الحدود الشمالية', 'Northern Borders'),
                    self::p('جازان', 'Jazan'), self::p('نجران', 'Najran'), self::p('الباحة', 'Al Bahah'), self::p('الجوف', 'Al Jawf'),
                ],
            ],
            'AE' => [
                'currency_code' => 'AED', 'currency_symbol' => 'د.إ', 'currency_name' => 'UAE Dirham', 'dial_code' => '971', 'decimals' => 2,
                'provinces' => [
                    self::p('أبوظبي', 'Abu Dhabi'), self::p('دبي', 'Dubai'), self::p('الشارقة', 'Sharjah'),
                    self::p('عجمان', 'Ajman'), self::p('أم القيوين', 'Umm Al Quwain'), self::p('رأس الخيمة', 'Ras Al Khaimah'),
                    self::p('الفجيرة', 'Fujairah'),
                ],
            ],
            'KW' => [
                'currency_code' => 'KWD', 'currency_symbol' => 'د.ك', 'currency_name' => 'Kuwaiti Dinar', 'dial_code' => '965', 'decimals' => 3,
                'provinces' => [
                    self::p('العاصمة', 'Al Asimah'), self::p('حولي', 'Hawalli'), self::p('الفروانية', 'Farwaniya'),
                    self::p('مبارك الكبير', 'Mubarak Al-Kabeer'), self::p('الأحمدي', 'Ahmadi'), self::p('الجهراء', 'Jahra'),
                ],
            ],
            'BH' => [
                'currency_code' => 'BHD', 'currency_symbol' => 'د.ب', 'currency_name' => 'Bahraini Dinar', 'dial_code' => '973', 'decimals' => 3,
                'provinces' => [
                    self::p('العاصمة', 'Capital'), self::p('المحرق', 'Muharraq'), self::p('الشمالية', 'Northern'), self::p('الجنوبية', 'Southern'),
                ],
            ],
            'OM' => [
                'currency_code' => 'OMR', 'currency_symbol' => 'ر.ع', 'currency_name' => 'Omani Rial', 'dial_code' => '968', 'decimals' => 3,
                'provinces' => [
                    self::p('مسقط', 'Muscat'), self::p('ظفار', 'Dhofar'), self::p('مسندم', 'Musandam'), self::p('البريمي', 'Al Buraimi'),
                    self::p('الداخلية', 'Ad Dakhiliyah'), self::p('شمال الباطنة', 'Al Batinah North'), self::p('جنوب الباطنة', 'Al Batinah South'),
                    self::p('شمال الشرقية', 'Ash Sharqiyah North'), self::p('جنوب الشرقية', 'Ash Sharqiyah South'),
                    self::p('الظاهرة', 'Ad Dhahirah'), self::p('الوسطى', 'Al Wusta'),
                ],
            ],
            'JO' => [
                'currency_code' => 'JOD', 'currency_symbol' => 'د.أ', 'currency_name' => 'Jordanian Dinar', 'dial_code' => '962', 'decimals' => 3,
                'provinces' => [
                    self::p('عمّان', 'Amman'), self::p('إربد', 'Irbid'), self::p('الزرقاء', 'Zarqa'), self::p('البلقاء', 'Balqa'),
                    self::p('مادبا', 'Madaba'), self::p('الكرك', 'Karak'), self::p('الطفيلة', 'Tafilah'), self::p('معان', 'Maan'),
                    self::p('العقبة', 'Aqaba'), self::p('المفرق', 'Mafraq'), self::p('جرش', 'Jerash'), self::p('عجلون', 'Ajloun'),
                ],
            ],
            'LB' => [
                'currency_code' => 'LBP', 'currency_symbol' => 'ل.ل', 'currency_name' => 'Lebanese Pound', 'dial_code' => '961', 'decimals' => 0,
                'provinces' => [
                    self::p('بيروت', 'Beirut'), self::p('جبل لبنان', 'Mount Lebanon'), self::p('الشمال', 'North'),
                    self::p('الجنوب', 'South'), self::p('البقاع', 'Beqaa'), self::p('النبطية', 'Nabatieh'),
                    self::p('بعلبك الهرمل', 'Baalbek-Hermel'), self::p('عكار', 'Akkar'),
                ],
            ],
            'IQ' => [
                'currency_code' => 'IQD', 'currency_symbol' => 'د.ع', 'currency_name' => 'Iraqi Dinar', 'dial_code' => '964', 'decimals' => 0,
                'provinces' => [
                    self::p('بغداد', 'Baghdad'), self::p('البصرة', 'Basra'), self::p('نينوى', 'Nineveh'), self::p('أربيل', 'Erbil'),
                    self::p('النجف', 'Najaf'), self::p('كربلاء', 'Karbala'), self::p('كركوك', 'Kirkuk'), self::p('الأنبار', 'Anbar'),
                    self::p('بابل', 'Babil'), self::p('ذي قار', 'Dhi Qar'), self::p('ديالى', 'Diyala'), self::p('السليمانية', 'Sulaymaniyah'),
                    self::p('واسط', 'Wasit'), self::p('صلاح الدين', 'Saladin'), self::p('القادسية', 'Al-Qadisiyyah'),
                    self::p('ميسان', 'Maysan'), self::p('المثنى', 'Muthanna'), self::p('دهوك', 'Dohuk'),
                ],
            ],
            'EG' => [
                'currency_code' => 'EGP', 'currency_symbol' => 'ج.م', 'currency_name' => 'Egyptian Pound', 'dial_code' => '20', 'decimals' => 2,
                'provinces' => [
                    self::p('القاهرة', 'Cairo'), self::p('الجيزة', 'Giza'), self::p('الإسكندرية', 'Alexandria'),
                    self::p('الدقهلية', 'Dakahlia'), self::p('الشرقية', 'Sharqia'), self::p('القليوبية', 'Qalyubia'),
                    self::p('الغربية', 'Gharbia'), self::p('المنوفية', 'Monufia'), self::p('البحيرة', 'Beheira'),
                    self::p('كفر الشيخ', 'Kafr El Sheikh'), self::p('الفيوم', 'Faiyum'), self::p('بني سويف', 'Beni Suef'),
                    self::p('المنيا', 'Minya'), self::p('أسيوط', 'Asyut'), self::p('سوهاج', 'Sohag'), self::p('قنا', 'Qena'),
                    self::p('الأقصر', 'Luxor'), self::p('أسوان', 'Aswan'), self::p('البحر الأحمر', 'Red Sea'),
                    self::p('الإسماعيلية', 'Ismailia'), self::p('السويس', 'Suez'), self::p('بورسعيد', 'Port Said'),
                    self::p('دمياط', 'Damietta'), self::p('مطروح', 'Matrouh'), self::p('شمال سيناء', 'North Sinai'),
                    self::p('جنوب سيناء', 'South Sinai'), self::p('الوادي الجديد', 'New Valley'),
                ],
            ],
            'TR' => [
                'currency_code' => 'TRY', 'currency_symbol' => '₺', 'currency_name' => 'Turkish Lira', 'dial_code' => '90', 'decimals' => 2,
                'provinces' => [
                    self::p('إسطنبول', 'Istanbul'), self::p('أنقرة', 'Ankara'), self::p('إزمير', 'Izmir'), self::p('بورصة', 'Bursa'),
                    self::p('أنطاليا', 'Antalya'), self::p('أضنة', 'Adana'), self::p('غازي عنتاب', 'Gaziantep'), self::p('قونية', 'Konya'),
                    self::p('مرسين', 'Mersin'), self::p('هطاي', 'Hatay'),
                ],
            ],
            'US' => [
                'currency_code' => 'USD', 'currency_symbol' => '$', 'currency_name' => 'US Dollar', 'dial_code' => '1', 'decimals' => 2,
                'provinces' => [
                    self::p('ألاباما', 'Alabama'), self::p('ألاسكا', 'Alaska'), self::p('أريزونا', 'Arizona'), self::p('أركنساس', 'Arkansas'),
                    self::p('كاليفورنيا', 'California'), self::p('كولورادو', 'Colorado'), self::p('كونيتيكت', 'Connecticut'), self::p('ديلاوير', 'Delaware'),
                    self::p('فلوريدا', 'Florida'), self::p('جورجيا', 'Georgia'), self::p('هاواي', 'Hawaii'), self::p('أيداهو', 'Idaho'),
                    self::p('إلينوي', 'Illinois'), self::p('إنديانا', 'Indiana'), self::p('آيوا', 'Iowa'), self::p('كانساس', 'Kansas'),
                    self::p('كنتاكي', 'Kentucky'), self::p('لويزيانا', 'Louisiana'), self::p('مين', 'Maine'), self::p('ماريلاند', 'Maryland'),
                    self::p('ماساتشوستس', 'Massachusetts'), self::p('ميشيغان', 'Michigan'), self::p('مينيسوتا', 'Minnesota'), self::p('ميسيسيبي', 'Mississippi'),
                    self::p('ميزوري', 'Missouri'), self::p('مونتانا', 'Montana'), self::p('نبراسكا', 'Nebraska'), self::p('نيفادا', 'Nevada'),
                    self::p('نيوهامبشير', 'New Hampshire'), self::p('نيوجيرسي', 'New Jersey'), self::p('نيومكسيكو', 'New Mexico'), self::p('نيويورك', 'New York'),
                    self::p('كارولينا الشمالية', 'North Carolina'), self::p('داكوتا الشمالية', 'North Dakota'), self::p('أوهايو', 'Ohio'), self::p('أوكلاهوما', 'Oklahoma'),
                    self::p('أوريغون', 'Oregon'), self::p('بنسلفانيا', 'Pennsylvania'), self::p('رود آيلاند', 'Rhode Island'), self::p('كارولينا الجنوبية', 'South Carolina'),
                    self::p('داكوتا الجنوبية', 'South Dakota'), self::p('تينيسي', 'Tennessee'), self::p('تكساس', 'Texas'), self::p('يوتا', 'Utah'),
                    self::p('فيرمونت', 'Vermont'), self::p('فيرجينيا', 'Virginia'), self::p('واشنطن', 'Washington'), self::p('فيرجينيا الغربية', 'West Virginia'),
                    self::p('ويسكونسن', 'Wisconsin'), self::p('وايومنغ', 'Wyoming'), self::p('واشنطن العاصمة', 'District of Columbia'),
                ],
            ],
        ];
    }
}
