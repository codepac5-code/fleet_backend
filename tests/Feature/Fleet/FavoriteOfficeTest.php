<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Marketplace\FavoriteOfficeService;

class FavoriteOfficeTest extends FleetTestCase
{
    protected array $tenantMigrations = [
        '2026_06_25_000012_create_favorite_offices_table.php',
    ];

    private FavoriteOfficeService $favorites;

    protected function setUp(): void
    {
        parent::setUp();
        $this->favorites = new FavoriteOfficeService();
    }

    public function test_add_is_idempotent_and_listed(): void
    {
        $this->favorites->add(7, 3);
        $this->favorites->add(7, 3);
        $this->favorites->add(7, 9);

        $this->assertSame([9, 3], $this->favorites->list(7));
        $this->assertTrue($this->favorites->isFavorite(7, 3));
        $this->assertFalse($this->favorites->isFavorite(7, 100));
    }

    public function test_remove(): void
    {
        $this->favorites->add(7, 3);
        $this->favorites->remove(7, 3);

        $this->assertFalse($this->favorites->isFavorite(7, 3));
        $this->assertSame([], $this->favorites->list(7));
    }

    public function test_favorites_are_per_user(): void
    {
        $this->favorites->add(7, 3);
        $this->favorites->add(8, 5);

        $this->assertSame([3], $this->favorites->list(7));
        $this->assertSame([5], $this->favorites->list(8));
    }
}
