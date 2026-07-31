<?php

namespace Tests\Feature\Fleet;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * In "All countries" mode `dynamic` points at cross-DB UNION VIEWS, which MySQL
 * cannot write to — a save surfaced as a raw "target table is not updatable" SQL
 * error. Guarding route by route had left 101 of 134 admin writes exposed, so the
 * guard is now applied to the whole group and the platform-wide writes opt out.
 *
 * This pins the shape: every panel write is guarded unless it is on a short,
 * deliberate exemption list — so a newly added tenant write is protected by
 * default rather than by whoever remembers.
 */
class AggregateWriteGuardTest extends TestCase
{
    private const GUARD = 'panel.single-shard';

    /** Writes whose data lives on the platform connection or in the session. */
    private const EXEMPT = [
        // Leaving aggregate mode is itself a POST — guarding it would lock the
        // operator into the mode the guard is telling them to leave.
        'panel.admin.switch-country',
        'panel.admin.notifications.read', 'panel.office.notifications.read',
        'panel.admin.wallet.reveal', 'panel.admin.wallet.hide',
        'panel.office.wallet.reveal', 'panel.office.wallet.hide',
        'panel.admin.security.two-factor.start', 'panel.admin.security.two-factor.confirm', 'panel.admin.security.two-factor.disable',
        'panel.office.security.two-factor.start', 'panel.office.security.two-factor.confirm', 'panel.office.security.two-factor.disable',
        'panel.admin.settings.security.save', 'panel.admin.settings.security.reset',
        'panel.admin.settings.site.save', 'panel.admin.settings.commissions.update',
        'panel.admin.settings.system.update', 'panel.admin.settings.payments.save',
        'panel.admin.settings.whatsapp.save', 'panel.admin.settings.whatsapp.test',
        'panel.admin.app-status.save', 'panel.admin.legal.save',
        'panel.admin.faqs.save', 'panel.admin.faqs.delete',
        'panel.admin.plans.store', 'panel.admin.plans.update', 'panel.admin.plans.toggle',
        'panel.admin.regions.billing.update',
        'panel.admin.countries.store', 'panel.admin.countries.test', 'panel.admin.countries.update',
        'panel.admin.countries.toggle', 'panel.admin.countries.provision',
        'panel.admin.currencies.store', 'panel.admin.currencies.update', 'panel.admin.currencies.toggle',
        'panel.admin.notification-templates.save',
        'panel.admin.leads.drivers.status', 'panel.admin.leads.offices.review', 'panel.admin.leads.contacts.review',
    ];

    public function test_every_panel_write_is_single_country_unless_deliberately_exempt(): void
    {
        $unguarded = [];

        foreach (Route::getRoutes() as $route) {
            $name = (string) $route->getName();

            if (! str_starts_with($name, 'panel.admin.') && ! str_starts_with($name, 'panel.office.')) {
                continue;
            }

            if (! array_intersect($route->methods(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
                continue;
            }

            $exemptHere = in_array(self::GUARD, $route->excludedMiddleware(), true);

            if ($exemptHere && ! in_array($name, self::EXEMPT, true)) {
                $unguarded[] = $name;
            }
        }

        $this->assertSame([], $unguarded, 'these writes opted out of the single-country guard without being on the list: ' . implode(', ', $unguarded));
    }

    public function test_reads_are_never_blocked(): void
    {
        // Aggregate mode exists to read across countries; a guard that also
        // caught GETs would break the very screens it protects.
        $middleware = new \App\Http\Middleware\Panel\RequireSingleShard();

        $request = \Illuminate\Http\Request::create('/panel/admin/offices', 'GET');

        $this->assertSame('passed', $middleware->handle($request, fn () => 'passed'));
    }
}
