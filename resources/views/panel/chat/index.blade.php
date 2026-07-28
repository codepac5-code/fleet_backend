@extends('panel.layouts.master')

@section('title', textByLanguage('محادثات الركّاب', 'Rider chats'))
@section('page-title', textByLanguage('محادثات الركّاب', 'Rider chats'))

@php $r = fn ($name) => "panel.{$entity}.{$name}"; @endphp

@section('content')

    <x-panel.page-toolbar
        :title="textByLanguage('محادثات الركّاب', 'Rider chats')"
        :subtitle="textByLanguage('محادثات ركّاب مكتبك', 'Conversations with your office riders')" />

    <div class="p-card">
        @if(count($conversations))
            <x-panel.table :headers="array_filter([
                '#',
                shardIsAll() ? textByLanguage('الدولة', 'Country') : null,
                textByLanguage('الراكب', 'Rider'),
                textByLanguage('الرحلة', 'Trip'),
                textByLanguage('آخر رسالة', 'Last message'),
                '',
            ], fn($h) => $h !== null)">
                @foreach($conversations as $c)
                    <tr>
                        <td>#{{ $c['id'] }}</td>
                        @if(shardIsAll())<td><x-panel.badge tone="primary"><i class="bi bi-globe2"></i> {{ shardCountry($c) ?: '—' }}</x-panel.badge></td>@endif
                        <td><strong>{{ textByLanguage('راكب', 'Rider') }} #{{ $c['user_id'] }}</strong></td>
                        <td>{{ $c['booking_id'] ? '#' . $c['booking_id'] : '—' }}</td>
                        <td>{{ $c['last_message_at'] ? \Illuminate\Support\Carbon::parse($c['last_message_at'])->diffForHumans() : '—' }}</td>
                        <td>
                            <a href="{{ shardLink($r('chat.show'), $c['id'], $c) }}" class="p-icon-btn" title="{{ textByLanguage('فتح', 'Open') }}"><i class="bi bi-chat-dots"></i></a>
                        </td>
                    </tr>
                @endforeach
            </x-panel.table>
        @else
            <p class="p-empty"><i class="bi bi-chat-left"></i> {{ textByLanguage('لا توجد محادثات', 'No conversations') }}</p>
        @endif
    </div>

@endsection
