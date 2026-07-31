<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Catalog\LocalizedName;
use Tests\TestCase;

/**
 * A corridor is labelled from three tables with three different column pairs —
 * `sub_services` (name/name_en), `cities` (name/name_on_google_map, there is no
 * en_name column) and `services` (title/title_en) — and every caller read the
 * native column directly. So an English rider booked
 * "استقبال من المطار · دمشق ← حمص", and which language a screen showed came
 * down to whichever column its author happened to pick.
 */
class LocalizedNameTest extends TestCase
{
    private function city(): object
    {
        return (object) ['name' => 'دمشق', 'name_on_google_map' => 'Damascus'];
    }

    private function subService(): object
    {
        return (object) ['name' => 'استقبال من المطار', 'name_en' => 'Airport Pickup'];
    }

    public function test_arabic_takes_the_native_column_and_english_the_latin_one(): void
    {
        $this->assertSame('دمشق', LocalizedName::of($this->city(), true));
        $this->assertSame('Damascus', LocalizedName::of($this->city(), false));

        $this->assertSame('استقبال من المطار', LocalizedName::of($this->subService(), true));
        $this->assertSame('Airport Pickup', LocalizedName::of($this->subService(), false));
    }

    public function test_a_service_titles_pair_resolves_the_same_way(): void
    {
        $service = (object) ['title' => 'خدمة السفر', 'title_en' => 'Travel Service'];

        $this->assertSame('خدمة السفر', LocalizedName::of($service, true));
        $this->assertSame('Travel Service', LocalizedName::of($service, false));
    }

    public function test_a_missing_translation_falls_back_to_the_other_language(): void
    {
        // Half a name beats an id: a row with only one language is still named.
        $onlyArabic = (object) ['name' => 'إدلب', 'name_on_google_map' => null];
        $onlyLatin = (object) ['name' => '', 'name_en' => 'Homs'];

        $this->assertSame('إدلب', LocalizedName::of($onlyArabic, false));
        $this->assertSame('Homs', LocalizedName::of($onlyLatin, true));
    }

    public function test_null_and_blank_rows_resolve_to_null_not_an_empty_label(): void
    {
        $this->assertNull(LocalizedName::of(null));
        $this->assertNull(LocalizedName::of((object) ['name' => '   ']));
    }

    public function test_the_corridor_reads_in_one_language_and_one_direction(): void
    {
        $homs = (object) ['name' => 'حمص', 'name_on_google_map' => 'Homs'];

        // Arabic reads right-to-left, so the arrow points the other way.
        $this->assertSame('دمشق ← حمص', LocalizedName::corridor($this->city(), $homs, true));
        $this->assertSame('Damascus → Homs', LocalizedName::corridor($this->city(), $homs, false));
    }

    public function test_it_works_on_array_rows_too(): void
    {
        $this->assertSame('Damascus', LocalizedName::of(['name' => 'دمشق', 'name_on_google_map' => 'Damascus'], false));
    }
}
