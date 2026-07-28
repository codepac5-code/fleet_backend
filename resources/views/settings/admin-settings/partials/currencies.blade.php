@php
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';
    // Default currency rate (the base, always 1.0). Used to derive human "1 USD = N SYP".
    $defaultRate = $default ? (float) $default->exchange_rate : 1.0;
    $usd = $currencies->firstWhere('code', 'USD');
    $usdRate = $usd ? (float) $usd->exchange_rate : 0.0;
@endphp

<form id="currencyForm" class="cur-card" method="POST" action="{{ route('settings.currencies.update') }}">
    @csrf

    <div class="cur-intro">
        <p>{{ $isAr
            ? 'سعر الصرف = عدد وحدات هذه العملة مقابل وحدة واحدة من العملة الأساسية (' . ($default->code ?? '—') . '). القيمة 0 تعني «غير محدَّد» فيرفض النظام التحويل بدل حساب مبلغ خاطئ.'
            : 'Exchange rate = units of this currency per 1 unit of the base currency (' . ($default->code ?? '—') . '). A value of 0 means “unset” — the system refuses conversion rather than charging a wrong amount.' }}</p>
    </div>

    <table class="cur-table">
        <thead>
            <tr>
                <th>{{ $isAr ? 'العملة' : 'Currency' }}</th>
                <th>{{ $isAr ? 'سعر الصرف (لكل 1 ' . ($default->code ?? '—') . ')' : 'Rate (per 1 ' . ($default->code ?? '—') . ')' }}</th>
                <th>{{ $isAr ? 'الحالة' : 'Status' }}</th>
            </tr>
        </thead>
        <tbody>
        @foreach($currencies as $c)
            @php $rate = (float) $c->exchange_rate; @endphp
            <tr class="{{ $rate == 0 && ! $c->is_default ? 'cur-unset' : '' }}">
                <td class="cur-code">
                    <span class="cur-sym">{{ $c->symbol }}</span>
                    <strong>{{ $c->code }}</strong>
                    <span class="cur-name">{{ $c->name }}</span>
                    @if($c->is_default)
                        <span class="cur-badge">{{ $isAr ? 'أساسية' : 'BASE' }}</span>
                    @endif
                </td>
                <td>
                    @if($c->is_default)
                        <input type="text" class="cur-input" value="1.000000" disabled>
                    @else
                        <input type="number" step="0.000001" min="0" max="100000000"
                               name="rates[{{ $c->code }}]"
                               value="{{ rtrim(rtrim(number_format($rate, 6, '.', ''), '0'), '.') }}"
                               class="cur-input"
                               data-code="{{ $c->code }}">
                    @endif
                </td>
                <td>
                    @if($c->is_default)
                        <span class="cur-ok">—</span>
                    @elseif($rate == 0)
                        <span class="cur-warn">{{ $isAr ? 'غير محدَّد' : 'Unset' }}</span>
                    @else
                        <span class="cur-ok">{{ $isAr ? 'مُفعّل' : 'Active' }}</span>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{-- Business-critical pair: the Syrian rider tops up in USD, credited in SYP. --}}
    @if($usd && $currencies->firstWhere('code', 'SYP'))
        @php $syp = $currencies->firstWhere('code', 'SYP'); @endphp
        <div class="cur-derived" id="curDerived">
            <span class="cur-derived-label">{{ $isAr ? 'الناتج:' : 'Derived:' }}</span>
            <span id="curDerivedText">
                {{ $isAr ? '١ دولار =' : '1 USD =' }}
                <strong id="curDerivedValue">
                    @php
                        $sypRate = (float) $syp->exchange_rate;
                        $perUsd = ($usdRate > 0 && $sypRate > 0) ? ($sypRate / $usdRate) : 0;
                    @endphp
                    {{ $perUsd > 0 ? number_format($perUsd, 2) : '—' }}
                </strong>
                {{ $isAr ? 'ل.س' : 'SYP' }}
            </span>
        </div>
    @endif

    <button type="submit" class="cur-save">{{ $isAr ? 'حفظ أسعار الصرف' : 'Save exchange rates' }}</button>
</form>

<script>
(function () {
    // Live "1 USD = N SYP" as the admin types, so the SYP rate is set with its
    // real-world meaning visible — the number the Syrian rider actually pays.
    var form = document.getElementById('currencyForm');
    if (!form) return;
    var usdInput = form.querySelector('input[data-code="USD"]');
    var sypInput = form.querySelector('input[data-code="SYP"]');
    var out = document.getElementById('curDerivedValue');
    if (!out) return;
    function recompute() {
        var usd = usdInput ? parseFloat(usdInput.value) : 0;
        var syp = sypInput ? parseFloat(sypInput.value) : 0;
        if (usd > 0 && syp > 0) {
            out.textContent = (syp / usd).toLocaleString(undefined, {maximumFractionDigits: 2});
        } else {
            out.textContent = '—';
        }
    }
    [usdInput, sypInput].forEach(function (el) { if (el) el.addEventListener('input', recompute); });
})();
</script>

<style>
.cur-card { font-family: 'Cairo', sans-serif; padding: 6px 2px; }
.cur-intro p { color: #6c6c7a; font-size: 13px; line-height: 1.7; margin: 0 0 18px; }
.cur-table { width: 100%; border-collapse: collapse; }
.cur-table th { text-align: inherit; font-size: 12px; color: #6c6c7a; font-weight: 600; padding: 10px 12px; border-bottom: 2px solid #ececf3; }
.cur-table td { padding: 12px; border-bottom: 1px solid #f2f2f7; vertical-align: middle; }
.cur-code { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.cur-sym { color: #312873; font-weight: 700; min-width: 18px; }
.cur-name { color: #9a9aa6; font-size: 12px; }
.cur-badge { background: rgba(49,40,115,0.08); color: #312873; font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 8px; }
.cur-input { width: 160px; padding: 9px 12px; border: 1.5px solid #ececf3; border-radius: 10px; font-family: inherit; font-size: 14px; }
.cur-input:focus { outline: none; border-color: #312873; }
.cur-input:disabled { background: #f6f6fa; color: #9a9aa6; }
.cur-unset .cur-input { border-color: #FCB902; background: #fffbf0; }
.cur-ok { color: #1a9c5b; font-size: 12px; font-weight: 600; }
.cur-warn { color: #d08700; font-size: 12px; font-weight: 700; }
.cur-derived { margin: 18px 0 6px; padding: 12px 16px; background: rgba(49,40,115,0.05); border-radius: 12px; font-size: 14px; color: #312873; }
.cur-derived-label { color: #6c6c7a; font-size: 12px; margin-inline-end: 6px; }
.cur-derived strong { font-size: 16px; }
.cur-save { margin-top: 18px; background: #312873; color: #fff; border: 0; padding: 12px 28px; border-radius: 12px; font-family: inherit; font-weight: 700; font-size: 14px; cursor: pointer; transition: 0.2s; }
.cur-save:hover { background: #251d5c; transform: translateY(-2px); }
</style>
