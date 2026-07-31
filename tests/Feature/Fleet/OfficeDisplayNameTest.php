<?php

namespace Tests\Feature\Fleet;

use App\Models\Office;

/**
 * Seeded offices carried the literal string "NULL" in `displayName`, which is a
 * perfectly valid string — so every `$office->displayName ?? $office->officeName`
 * fallback in the panel printed the word NULL as the office's name (the corridor
 * picker, the sidebar, the account menu). The model now reports a real absence.
 */
class OfficeDisplayNameTest extends FleetTestCase
{
    private function office(?string $displayName): Office
    {
        return (new Office())->forceFill([
            'officeName' => 'Damascus Luxury Fleet',
            'displayName' => $displayName,
        ]);
    }

    public function test_the_literal_null_string_reads_as_absent(): void
    {
        foreach (['NULL', 'null', 'Null', ' null '] as $stored) {
            $this->assertNull($this->office($stored)->displayName, $stored . ' should read as absent');
        }
    }

    public function test_blank_reads_as_absent(): void
    {
        $this->assertNull($this->office('')->displayName);
        $this->assertNull($this->office('   ')->displayName);
        $this->assertNull($this->office(null)->displayName);
    }

    public function test_the_fallback_now_lands_on_the_office_name(): void
    {
        $office = $this->office('NULL');

        $this->assertSame('Damascus Luxury Fleet', $office->displayName ?? $office->officeName);
    }

    public function test_a_real_contact_name_is_untouched(): void
    {
        $this->assertSame('Sami Haddad', $this->office('Sami Haddad')->displayName);
        $this->assertSame('Nullify Co', $this->office('Nullify Co')->displayName, 'only an exact NULL is treated as absent');
    }
}
