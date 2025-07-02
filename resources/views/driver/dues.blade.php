@if(isset($dues))

{{-- <a href="{{ route('wallet.show', ['wallet' => $wallet->id]) }}"> --}}
  <div class="d-flex gap-2 align-items-center" style="background-color: rgba(233, 200, 140, 0.3); border: 1px solid #eca41f; padding: 8px; border-radius: 8px;">
    <div class="text-start">
        <h6 class="m-0" style="font-size: 1.0rem; font-weight: bold; color: #e6543b;"> 
            @if(isset($dues))
                {{ getPriceFormat($dues) }}
            @else
                <span style="color: #edeeec;">-</span> 
            @endif
        </h6>
    </div>
</div>

</a>
@else

<div class="align-items-center">
    <h6 class="text-center">{{ '-' }} </h6>
</div>
@endif