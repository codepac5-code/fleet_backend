<?php

namespace App\Http\Services\Panel\Admin\Offices\Request;

trait NormalizesWorkingHours
{
    private const WH_DAYS = ['sat', 'sun', 'mon', 'tue', 'wed', 'thu', 'fri'];

    protected function normalizedWorkingHours(): array
    {
        $input = (array) $this->input('working_hours', []);
        $normalized = [];

        foreach (self::WH_DAYS as $day) {
            $row = (array) ($input[$day] ?? []);
            $closed = ! empty($row['closed']);

            $normalized[$day] = [
                'closed' => $closed,
                'open' => $closed ? null : ($row['open'] ?: null),
                'close' => $closed ? null : ($row['close'] ?: null),
            ];
        }

        return $normalized;
    }
}
