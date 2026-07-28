@props(['status' => null, 'tone' => null])

@php
    $map = [
        'success' => ['active', 'available', 'paid', 'completed', 'approved', 'verified', 'published', 'online', 'sent'],
        'warning' => ['pending', 'upcoming', 'unassigned', 'due', 'cash', 'limited', 'draft', 'scheduled'],
        'primary' => ['ongoing', 'busy', 'wallet', 'enabled', 'review', 'reviewed'],
        'danger'  => ['rejected', 'cancelled', 'canceled', 'blocked', 'high', 'failed', 'offline', 'inactive', 'missing'],
    ];

    $resolved = $tone;

    if (! $resolved && $status !== null) {
        $needle = mb_strtolower((string) $status);
        $resolved = 'gray';
        foreach ($map as $t => $words) {
            foreach ($words as $w) {
                if (str_contains($needle, $w)) {
                    $resolved = $t;
                    break 2;
                }
            }
        }
    }

    $resolved = $resolved ?: 'gray';
@endphp

<span {{ $attributes->merge(['class' => "p-badge p-badge--{$resolved}"]) }}>{{ trim($slot) === '' ? $status : $slot }}</span>
