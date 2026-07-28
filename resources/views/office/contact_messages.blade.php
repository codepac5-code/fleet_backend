@php
    $ar = app()->getLocale() === 'ar';
    $t = fn($en, $arText) => $ar ? $arText : $en;
    $intents = [
        'demo'     => $t('Book a demo', 'حجز عرض توضيحيّ'),
        'sales'    => $t('Talk to sales', 'التحدّث للمبيعات'),
        'support'  => $t('Support', 'دعم'),
        'waitlist' => $t('Waitlist', 'قائمة انتظار'),
    ];
@endphp
<x-master-layout>
<div class="dashboard">

    <div class="header">
        <h1>{{ $t('Demo & contact requests', 'طلبات العروض والتواصل') }}</h1>
        <p>{{ $t('Leads submitted from the website contact form.', 'العملاء المحتملون من نموذج التواصل في الموقع.') }}</p>
    </div>

    <div class="tabs">
        <button class="tab active" onclick="switchTab('new')">{{ $t('New', 'جديد') }} <span>{{ $new->count() }}</span></button>
        <button class="tab" onclick="switchTab('reviewed')">{{ $t('Reviewed', 'تمّت المراجعة') }} <span>{{ $reviewed->count() }}</span></button>
    </div>

    @foreach (['new' => $new, 'reviewed' => $reviewed] as $tab => $items)
        <div id="tab-{{ $tab }}" class="tab-content {{ $tab === 'new' ? 'active' : '' }}">
            @forelse ($items as $item)
                <div class="card {{ $tab }}" onclick="toggleCard(this)">
                    <div class="card-header">
                        <div>
                            <h3>{{ $item->name }}</h3>
                            <p>{{ $item->company ?: $t('Individual', 'فرد') }}</p>
                        </div>
                        <span class="badge {{ $tab }}">{{ $tab === 'new' ? $t('New', 'جديد') : $t('Reviewed', 'تمّت المراجعة') }}</span>
                    </div>
                    <div class="card-preview">
                        <span>{{ $intents[$item->intent] ?? $item->intent }}</span>
                        <span>{{ $item->email }}</span>
                        @if ($item->phone)<span>{{ $item->phone }}</span>@endif
                    </div>
                    <div class="card-body">
                        <div class="info-grid">
                            <div><strong>{{ $t('Reason', 'السبب') }}</strong><span>{{ $intents[$item->intent] ?? $item->intent }}</span></div>
                            <div><strong>{{ $t('Email', 'البريد') }}</strong><span>{{ $item->email }}</span></div>
                            <div><strong>{{ $t('Phone', 'الهاتف') }}</strong><span>{{ $item->phone ?: '—' }}</span></div>
                            <div><strong>{{ $t('Company', 'الشركة') }}</strong><span>{{ $item->company ?: '—' }}</span></div>
                            <div style="grid-column:1/-1"><strong>{{ $t('Message', 'الرسالة') }}</strong><span>{{ $item->message ?: '—' }}</span></div>
                            <div><strong>{{ $t('Received', 'وصل في') }}</strong><span>{{ $item->created_at?->diffForHumans() }}</span></div>
                        </div>
                        @if ($tab === 'new')
                            <button onclick="markReviewed(event, {{ $item->id }})">{{ $t('Mark as reviewed', 'تعليم كمُراجَع') }}</button>
                        @endif
                    </div>
                </div>
            @empty
                <p style="color:#6b7280; padding:20px 4px">{{ $t('Nothing here yet.', 'لا شيء هنا بعد.') }}</p>
            @endforelse
        </div>
    @endforeach

</div>

<div id="toast"></div>

<script>
    function toggleCard(card) { card.classList.toggle('open'); }
    function switchTab(tab) {
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        document.querySelector(`[onclick="switchTab('${tab}')"]`).classList.add('active');
        document.getElementById('tab-' + tab).classList.add('active');
    }
    function markReviewed(e, id) {
        e.stopPropagation();
        fetch(`/admin/contact-messages/${id}/status`, { method: 'PATCH', headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" } })
            .then(r => r.json())
            .then(() => { showToast("{{ $t('Status updated', 'تمّ تحديث الحالة') }}"); setTimeout(() => location.reload(), 700); })
            .catch(() => showToast("{{ $t('Something went wrong', 'حدث خطأ') }}"));
    }
    function showToast(msg) { let t = document.getElementById('toast'); t.innerText = msg; t.classList.add('show'); setTimeout(() => t.classList.remove('show'), 3000); }
</script>

<style>
    body { background: #f4f5f7; font-family: 'Plus Jakarta Sans', sans-serif; }
    .dashboard { max-width: 1100px; margin: auto; padding: 40px 20px; }
    .header h1 { font-size: 2rem; font-weight: 800; color: #312873; }
    .header p { color: #4b5563; }
    .tabs { display: flex; gap: 10px; margin: 25px 0; }
    .tab { background: #31287372; border: none; padding: 10px 18px; border-radius: 12px; font-weight: 700; cursor: pointer; display: flex; gap: 8px; align-items: center; box-shadow: 0 5px 15px rgba(0, 0, 0, .05); transition: .3s; }
    .tab span { background: #F29C0B; padding: 2px 8px; border-radius: 8px; font-size: .7rem; color: #fff; }
    .tab.active { background: linear-gradient(135deg, #312873, #4c3bb3); color: #fff; }
    .tab-content { display: none; }
    .tab-content.active { display: block; }
    .card { border-radius: 18px; padding: 18px; margin-bottom: 14px; cursor: pointer; transition: .3s; position: relative; overflow: hidden; backdrop-filter: blur(10px); }
    .card:hover { transform: translateY(-3px); box-shadow: 0 12px 25px rgba(0, 0, 0, .08); }
    .card.new { background: #fff7e6; border: 1px solid #f2b50b; }
    .card.new .card-header h3, .card.new .info-grid span { color: #1f1f1f; }
    .card.new .card-header p, .card.new .info-grid strong { color: #4b5563; }
    .card.new .card-preview span { background: #fef3c7; color: #78350f; }
    .card.reviewed { background: rgba(49, 40, 115, .792); border: 1px solid rgba(76, 59, 179, .6); box-shadow: 0 8px 20px rgba(0, 0, 0, .15); }
    .card.reviewed .card-header h3 { color: #fff; }
    .card.reviewed .card-header p { color: #d1d5db; }
    .card.reviewed .info-grid span { color: #f3f4f6; }
    .card.reviewed .info-grid strong { color: #e5e7eb; }
    .card.reviewed .card-preview span { background: rgba(76, 59, 179, .7); color: #fff; font-weight: 600; }
    .card-header { display: flex; justify-content: space-between; }
    .card-header h3 { font-size: 1.2rem; font-weight: 800; }
    .card-header p { font-size: .8rem; }
    .badge { font-size: .65rem; font-weight: 800; padding: 6px 10px; border-radius: 8px; height: 20px; }
    .badge.new { background: #f2b50b; color: #fff; }
    .badge.reviewed { background: #16a34aa4; color: #fff; }
    .card-preview { display: flex; gap: 10px; margin-top: 12px; flex-wrap: wrap; }
    .card-preview span { font-size: .75rem; padding: 5px 10px; border-radius: 8px; font-weight: 600; }
    .card-body { max-height: 0; overflow: hidden; opacity: 0; transform: translateY(-10px); transition: all .35s ease; }
    .card.open .card-body { max-height: 600px; margin-top: 15px; opacity: 1; transform: translateY(0); }
    .info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
    .info-grid div { border-radius: 10px; padding: 10px; background: rgba(188, 149, 6, .195); }
    .info-grid strong { display: block; font-size: .7rem; }
    button { margin-top: 15px; width: 100%; padding: 12px; border: none; border-radius: 12px; background: linear-gradient(135deg, #16a34a, #22c55e); color: #fbf9ffdc; font-weight: 800; cursor: pointer; transition: .3s; }
    button:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(34, 197, 94, .25); }
    #toast { position: fixed; bottom: 30px; inset-inline-end: 30px; background: #312873; color: #fff; padding: 14px 20px; border-radius: 10px; opacity: 0; transition: .3s; font-weight: 700; z-index: 1000; }
    #toast.show { opacity: 1; }
</style>
</x-master-layout>
