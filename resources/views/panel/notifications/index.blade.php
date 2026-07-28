@extends('panel.layouts.master')

@section('title', textByLanguage('الإشعارات', 'Notifications'))
@section('page-title', textByLanguage('الإشعارات', 'Notifications'))

@section('content')

    <x-panel.page-toolbar :title="textByLanguage('مركز الإشعارات', 'Notification center')"
        :subtitle="textByLanguage('آخر الأنشطة المتعلّقة بك', 'Recent activity relevant to you')" />

    <div class="p-card">
        @if(!empty($notifications))
            <div class="notif-list">
                @foreach($notifications as $n)
                    <a class="notif-row {{ $n['unread'] ? 'is-unread' : '' }}" href="{{ $n['link'] ?? '#' }}">
                        <span class="panel-bell__icon p-badge--{{ $n['tone'] }}"><i class="bi {{ $n['icon'] }}"></i></span>
                        <div class="notif-row__body">
                            <strong>{{ $n['title'] }}</strong>
                            <span>{{ $n['body'] }}</span>
                        </div>
                        <time class="notif-row__time">{{ $n['ago'] }}</time>
                    </a>
                @endforeach
            </div>
        @else
            <p class="p-empty"><i class="bi bi-bell-slash"></i> {{ textByLanguage('لا توجد إشعارات', 'No notifications') }}</p>
        @endif
    </div>

@endsection
