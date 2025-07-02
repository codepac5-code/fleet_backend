@if(isset($walletBalance))

{{-- <a href="{{ route('wallet.show', ['wallet' => $wallet->id]) }}"> --}}
  <div class="d-flex gap-2 align-items-center" style="background-color: rgba(194, 221, 179, 0.603); border: 1px solid #eca41f; padding: 8px; border-radius: 8px;">
    <div class="text-start">
        <h6 class="m-0" style="font-size: 1.0rem; font-weight: bold; color: #0a6425;"> 
            @if(isset($walletBalance))
                {{ getPriceFormat($walletBalance) }}
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