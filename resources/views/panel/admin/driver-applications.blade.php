@php
    $ar = app()->getLocale() === 'ar';
    $t = fn($en, $arText) => $ar ? $arText : $en;
    $docs = [
        'profileImage' => $t('Profile', 'شخصيّة'), 'idFrontImage' => $t('ID front', 'هويّة أمام'), 'idBackImage' => $t('ID back', 'هويّة خلف'),
        'licenseFrontImage' => $t('License front', 'رخصة أمام'), 'licenseBackImage' => $t('License back', 'رخصة خلف'), 'mechanicalImage' => $t('Mechanical', 'ميكانيكيّ'),
    ];
    $photos = [
        'frontCarImage' => $t('Front', 'أمام'), 'backCarImage' => $t('Back', 'خلف'), 'rightCarImage' => $t('Right', 'يمين'), 'leftCarImage' => $t('Left', 'يسار'),
        'insideCarImage' => $t('Interior', 'داخل'), 'frontSeatsImage' => $t('Front seats', 'مقاعد أماميّة'), 'backSeatsImage' => $t('Back seats', 'مقاعد خلفيّة'),
    ];
    $tabs = ['pending' => [$pending, $t('Pending', 'قيد المراجعة')], 'approved' => [$approved, $t('Approved', 'مقبول')], 'rejected' => [$rejected, $t('Rejected', 'مرفوض')]];
@endphp
<x-master-layout>
<div class="dash">
    <div class="head">
        <div>
            <h1>{{ $t('Driver applications', 'طلبات السائقين') }}</h1>
            <p>{{ $t('Review applicants, their documents and vehicle, then approve or reject.', 'راجع المتقدّمين ووثائقهم ومركباتهم، ثمّ اقبل أو ارفض.') }}</p>
        </div>
        <a class="btn-soft" href="{{ route('admin.submissions.hub') }}"><i class="fa-solid fa-arrow-{{ $ar ? 'right' : 'left' }}"></i> {{ $t('All submissions', 'كلّ الطلبات') }}</a>
    </div>

    @if(session('status'))<div class="flash ok"><i class="fa-solid fa-circle-check"></i> {{ $t('Updated successfully.', 'تمّ التحديث بنجاح.') }}</div>@endif

    <div class="tabs">
        @foreach($tabs as $key => $tab)
            <button class="tab {{ $key === 'pending' ? 'active' : '' }}" onclick="switchTab('{{ $key }}')">{{ $tab[1] }} <span>{{ $tab[0]->count() }}</span></button>
        @endforeach
    </div>

    @foreach($tabs as $key => $tab)
        <div id="tab-{{ $key }}" class="tab-content {{ $key === 'pending' ? 'active' : '' }}">
            @forelse($tab[0] as $app)
                <div class="card {{ $key }}" onclick="toggleCard(event, this)">
                    <div class="chead">
                        <div class="cwho">
                            @if($app->profileImage)<img class="av" src="{{ asset('storage/' . $app->profileImage) }}" onclick="lightbox(event,this.src)">@else<span class="av ph"><i class="fa-solid fa-user"></i></span>@endif
                            <div><h3>{{ $app->name }}</h3><p>{{ $app->phoneNumber }} · {{ trim($app->brand . ' ' . $app->model . ' ' . $app->year) }}</p></div>
                        </div>
                        <span class="badge {{ $key }}">{{ $tab[1] }}</span>
                    </div>

                    <div class="cbody">
                        <div class="info">
                            <div><strong>{{ $t('Vehicle', 'المركبة') }}</strong><span>{{ trim($app->brand . ' ' . $app->model) }} · {{ $app->year }} · {{ $app->color }}</span></div>
                            <div><strong>{{ $t('Plate', 'اللوحة') }}</strong><span>{{ $app->plateNumber }}</span></div>
                            <div><strong>{{ $t('Submitted', 'أُرسل') }}</strong><span>{{ $app->created_at?->diffForHumans() }}</span></div>
                        </div>

                        <div class="glabel">{{ $t('Documents', 'الوثائق') }}</div>
                        <div class="gallery">
                            @foreach($docs as $field => $label)
                                @if($app->$field)
                                    <figure onclick="lightbox(event, '{{ asset('storage/' . $app->$field) }}')"><img src="{{ asset('storage/' . $app->$field) }}" loading="lazy"><figcaption>{{ $label }}</figcaption></figure>
                                @endif
                            @endforeach
                        </div>

                        <div class="glabel">{{ $t('Vehicle photos', 'صور المركبة') }}</div>
                        <div class="gallery">
                            @foreach($photos as $field => $label)
                                @if($app->$field)
                                    <figure onclick="lightbox(event, '{{ asset('storage/' . $app->$field) }}')"><img src="{{ asset('storage/' . $app->$field) }}" loading="lazy"><figcaption>{{ $label }}</figcaption></figure>
                                @endif
                            @endforeach
                        </div>

                        <div class="cactions">
                            @if($app->status !== 'approved')
                                <form method="POST" action="{{ route('admin.submissions.drivers.status', $app->id) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="approved"><button class="act ok"><i class="fa-solid fa-check"></i> {{ $t('Approve', 'قبول') }}</button></form>
                            @endif
                            @if($app->status !== 'rejected')
                                <form method="POST" action="{{ route('admin.submissions.drivers.status', $app->id) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="rejected"><button class="act no"><i class="fa-solid fa-xmark"></i> {{ $t('Reject', 'رفض') }}</button></form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <p class="muted">{{ $t('Nothing here yet.', 'لا شيء هنا بعد.') }}</p>
            @endforelse
        </div>
    @endforeach
</div>

<div id="lb" onclick="this.classList.remove('on')"><img id="lbimg" src=""></div>

<script>
    function switchTab(k) {
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        document.querySelector(`[onclick="switchTab('${k}')"]`).classList.add('active');
        document.getElementById('tab-' + k).classList.add('active');
    }
    function toggleCard(e, card) { if (e.target.closest('.cactions') || e.target.closest('.gallery') || e.target.closest('.av')) return; card.classList.toggle('open'); }
    function lightbox(e, src) { e.stopPropagation(); const lb = document.getElementById('lb'); document.getElementById('lbimg').src = src; lb.classList.add('on'); }
</script>

<style>
    .dash { max-width: 1100px; margin: auto; padding: 40px 20px; font-family: 'Plus Jakarta Sans','Cairo',sans-serif; }
    .head { display: flex; justify-content: space-between; align-items: flex-end; gap: 1rem; flex-wrap: wrap; }
    .head h1 { font-size: 1.9rem; font-weight: 800; color: #312873; } .head p { color: #6b7280; margin-top: .3rem; max-width: 520px; }
    .btn-soft { background: #312873; color: #fff; padding: .65rem 1.05rem; border-radius: 10px; font-weight: 700; display: inline-flex; gap: .5rem; align-items: center; font-size: .85rem; }
    .flash { display: flex; align-items: center; gap: .6rem; padding: .85rem 1.1rem; border-radius: 12px; font-weight: 700; margin: 1rem 0; background: #ecfdf5; color: #047857; }
    .tabs { display: flex; gap: 10px; margin: 1.5rem 0; flex-wrap: wrap; }
    .tab { background: #ece9f6; border: none; padding: 10px 18px; border-radius: 12px; font-weight: 700; cursor: pointer; display: flex; gap: 8px; align-items: center; color: #312873; }
    .tab span { background: #F29C0B; color: #fff; padding: 2px 8px; border-radius: 8px; font-size: .7rem; }
    .tab.active { background: linear-gradient(135deg,#312873,#4c3bb3); color: #fff; }
    .tab-content { display: none; } .tab-content.active { display: block; }
    .card { background: #fff; border: 1px solid #eceefb; border-radius: 16px; padding: 1.1rem 1.2rem; margin-bottom: .8rem; cursor: pointer; transition: .2s; }
    .card:hover { box-shadow: 0 12px 28px rgba(49,40,115,.08); }
    .card.approved { border-inline-start: 4px solid #22c55e; } .card.rejected { border-inline-start: 4px solid #ef4444; } .card.pending { border-inline-start: 4px solid #F29C0B; }
    .chead { display: flex; justify-content: space-between; align-items: center; gap: 1rem; }
    .cwho { display: flex; align-items: center; gap: .9rem; }
    .av { width: 52px; height: 52px; border-radius: 12px; object-fit: cover; background: #ece9f6; display: grid; place-items: center; color: #312873; cursor: zoom-in; }
    .cwho h3 { font-size: 1.1rem; font-weight: 800; color: #312873; } .cwho p { font-size: .82rem; color: #6b7280; }
    .badge { font-size: .68rem; font-weight: 800; padding: 5px 11px; border-radius: 999px; white-space: nowrap; }
    .badge.pending { background: #fef3c7; color: #92400e; } .badge.approved { background: #dcfce7; color: #166534; } .badge.rejected { background: #fee2e2; color: #991b1b; }
    .cbody { max-height: 0; overflow: hidden; opacity: 0; transition: all .35s ease; }
    .card.open .cbody { max-height: 1400px; opacity: 1; margin-top: 1.1rem; }
    .info { display: grid; grid-template-columns: repeat(3,1fr); gap: 10px; margin-bottom: 1rem; }
    .info div { background: #faf9ff; border-radius: 10px; padding: 10px; } .info strong { display: block; font-size: .68rem; color: #6b7280; } .info span { font-weight: 700; color: #312873; font-size: .88rem; }
    .glabel { font-size: .74rem; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; color: #F29C0B; margin: .6rem 0 .5rem; }
    .gallery { display: grid; grid-template-columns: repeat(auto-fill, minmax(110px,1fr)); gap: .6rem; }
    .gallery figure { border-radius: 10px; overflow: hidden; border: 1px solid #eceefb; cursor: zoom-in; background: #fff; transition: .2s; position: relative; }
    .gallery figure:hover { transform: translateY(-3px); box-shadow: 0 10px 22px rgba(0,0,0,.1); }
    .gallery img { width: 100%; height: 84px; object-fit: cover; display: block; }
    .gallery figcaption { font-size: .68rem; color: #6b7280; padding: 4px 6px; text-align: center; font-weight: 600; }
    .cactions { display: flex; gap: .7rem; margin-top: 1.2rem; } .cactions form { margin: 0; }
    .act { border: none; padding: .7rem 1.3rem; border-radius: 10px; font-weight: 800; cursor: pointer; color: #fff; display: inline-flex; gap: .5rem; align-items: center; }
    .act.ok { background: linear-gradient(135deg,#16a34a,#22c55e); } .act.no { background: linear-gradient(135deg,#dc2626,#ef4444); }
    .muted { color: #9aa1bd; padding: 1rem 0; }
    #lb { position: fixed; inset: 0; background: rgba(18,13,46,.85); display: none; place-items: center; z-index: 9999; padding: 2rem; cursor: zoom-out; }
    #lb.on { display: grid; } #lb img { max-width: 92vw; max-height: 90vh; border-radius: 14px; box-shadow: 0 30px 80px rgba(0,0,0,.5); }
    @media (max-width: 600px) { .info { grid-template-columns: 1fr; } }
</style>
</x-master-layout>
