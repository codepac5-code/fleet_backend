<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\GeoServices\CountryNameResolver;
use App\Models\InfrastructureNode;
use Illuminate\Support\Facades\DB;

/**
 * Two countries sharing one database (Saudi Arabia and Qatar both live in
 * `fleet`) used to make every support row "ambiguous" and therefore
 * unattributable. The office names its own country in plain text, and this is
 * what turns that text back into a country code.
 */
class CountryNameResolverTest extends FleetTestCase
{
    protected array $globalMigrations = [
        '2026_05_29_220120_create_infrastructure_nodes_table.php',
        '2024_11_12_070712_create_countries_table.php',
    ];

    private CountryNameResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $previous = DB::getDefaultConnection();
        DB::setDefaultConnection('global');

        foreach ([['SA', 'Saudi Arabia', 'fleet'], ['QA', 'Qatar', 'fleet'], ['SY', 'Syria', 'fleet_sy']] as [$code, $name, $db]) {
            InfrastructureNode::query()->create([
                'type' => 'country', 'country_code' => $code, 'name' => $name,
                'db_name' => $db, 'is_active' => true,
            ]);
        }

        DB::connection('global')->table('countries')->insert([
            ['iso2' => 'qa', 'name' => 'قطر', 'en_name' => 'Qatar'],
            ['iso2' => 'sa', 'name' => 'السعودية', 'en_name' => 'Saudi Arabia'],
            ['iso2' => 'sy', 'name' => 'سوريا', 'en_name' => 'Syria'],
        ]);

        DB::setDefaultConnection($previous);

        $this->resolver = new CountryNameResolver();
    }

    public function test_an_office_country_picks_one_of_the_shared_database_countries(): void
    {
        // The exact shape the live data has: offices in `fleet` say "QATAR".
        $this->assertSame('qa', $this->resolver->match('QATAR', ['sa', 'qa']));
        $this->assertSame('sa', $this->resolver->match('Saudi Arabia', ['sa', 'qa']));
    }

    public function test_matching_ignores_case_and_spacing(): void
    {
        $this->assertSame('qa', $this->resolver->match('  qatar  ', ['sa', 'qa']));
        $this->assertSame('sa', $this->resolver->match('SAUDI   ARABIA', ['sa', 'qa']));
    }

    public function test_an_arabic_name_resolves_too(): void
    {
        $this->assertSame('qa', $this->resolver->match('قطر', ['sa', 'qa']));
    }

    public function test_an_iso_code_resolves(): void
    {
        $this->assertSame('sy', $this->resolver->match('sy', ['sy', 'qa']));
    }

    public function test_an_unknown_name_resolves_to_nothing(): void
    {
        $this->assertNull($this->resolver->match('Atlantis', ['sa', 'qa']));
        $this->assertNull($this->resolver->match('', ['sa', 'qa']));
        $this->assertNull($this->resolver->match(null, ['sa', 'qa']));
    }

    public function test_a_name_outside_the_candidates_is_not_forced_in(): void
    {
        // Syria is a real country but not one of the two sharing this database,
        // so the row stays unattributed rather than being mislabelled.
        $this->assertNull($this->resolver->match('Syria', ['sa', 'qa']));
    }

    public function test_no_candidates_resolves_to_nothing(): void
    {
        $this->assertNull($this->resolver->match('Qatar', []));
    }
}
