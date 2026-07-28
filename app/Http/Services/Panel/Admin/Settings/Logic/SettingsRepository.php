<?php

namespace App\Http\Services\Panel\Admin\Settings\Logic;

use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\FleetOffice;
use App\Models\SystemSetting;

class SettingsRepository
{
    private const COMMISSION_FIELDS = [
        'fleet_commission_value_with_driver',
        'fleet_commission_value_with_office',
        'office_commission_value',
        'driver_commission_value',
    ];

    private const SYSTEM_KEYS = ['language', 'currency', 'timezone'];

    private function connection(): ?string
    {
        return TenantConnection::current();
    }

    public function commissions(): array
    {
        $row = FleetOffice::on($this->connection())->first();

        $values = [];
        foreach (self::COMMISSION_FIELDS as $field) {
            $values[$field] = $row ? (float) $row->{$field} : 0.0;
        }

        return $values;
    }

    public function updateCommissions(array $data): void
    {
        $payload = array_intersect_key($data, array_flip(self::COMMISSION_FIELDS));

        $row = FleetOffice::on($this->connection())->first();

        if ($row) {
            $row->fill($payload)->save();

            return;
        }

        $model = new FleetOffice($payload);
        if ($connection = $this->connection()) {
            $model->setConnection($connection);
        }
        $model->save();
    }

    public function system(): array
    {
        $stored = SystemSetting::whereIn('key', self::SYSTEM_KEYS)->pluck('value', 'key');

        $values = [];
        foreach (self::SYSTEM_KEYS as $key) {
            $value = $stored->get($key);
            $values[$key] = is_array($value) ? ($value[0] ?? '') : (string) ($value ?? '');
        }

        return $values;
    }

    public function updateSystem(array $data): void
    {
        foreach (self::SYSTEM_KEYS as $key) {
            if (! array_key_exists($key, $data)) {
                continue;
            }

            SystemSetting::updateOrCreate(['key' => $key], ['value' => $data[$key]]);
        }
    }
}
