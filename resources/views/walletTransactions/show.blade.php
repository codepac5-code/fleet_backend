<x-master-layout>
    <style>
body {
    background-color: #f5f7fa;
    color: #312873;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    transition: background-color 0.3s, color 0.3s;
}

body.dark {
    background-color: #121212;
    color: #d3d3d3;
}

.card-custom {
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(49, 40, 115, 0.15);
    border: none;
    background-color: white;
    transition: background-color 0.3s, box-shadow 0.3s;
}

body.dark .card-custom {
    background-color: #1e1e2f;
    box-shadow: 0 8px 24px rgba(248, 166, 9, 0.4);
}

.card-header-custom {
    background-color: #312873;
    color: #F8A609;
    font-weight: 700;
    font-size: 1.8rem;
    padding: 1.5rem 2rem;
    border-top-left-radius: 12px;
    border-top-right-radius: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: background-color 0.3s, color 0.3s;
}

body.dark .card-header-custom {
    background-color: #3f3685;
    color: #F8A609;
}

.transaction-id {
    font-size: 2.5rem;
    font-weight: 900;
    color: #33;
    letter-spacing: 2px;
    user-select: text;
    transition: color 0.3s;
}

body.dark .transaction-id {
    color: #f8a609;
}

.label-custom {
    font-weight: 600;
    font-size: 1.1rem;
    color: #312873;
    margin-bottom: 6px;
    transition: color 0.3s;
}

body.dark .label-custom {
    color: #f8a609;
}

.value-custom {
    font-size: 1.4rem;
    font-weight: 500;
    background-color: white;
    padding: 18px 22px;
    border-radius: 10px;
    box-shadow: 0 1px 8px rgb(49 40 115 / 0.1);
    margin-bottom: 20px;
    min-height: 54px;
    word-break: break-word;
    transition: background-color 0.3s, color 0.3s, box-shadow 0.3s;
}

body.dark .value-custom {
    background-color: #292a42;
    color: #f0e68c;
    box-shadow: 0 1px 8px rgba(248, 166, 9, 0.3);
}

.badge-status-completed {
    background-color: #2aa70bce;
    color: #f8f7ff;
    font-weight: 600;
    font-size: 1.1rem;
    padding: 0.6em 1.3em;
    border-radius: 9999px;
    text-transform: uppercase;
    display: inline-block;
    transition: background-color 0.3s, color 0.3s;
}

body.dark .badge-status-completed {
    background-color: #1b5e0f;
    color: #f8f7ff;
}

.badge-status-pending {
    background-color: #F8A609;
    color: #312873;
    font-weight: 600;
    font-size: 1.1rem;
    padding: 0.6em 1.3em;
    border-radius: 9999px;
    text-transform: uppercase;
    display: inline-block;
    transition: background-color 0.3s, color 0.3s;
}

body.dark .badge-status-pending {
    background-color: #f8a609;
    color: #312873;
}

.badge-status-other {
    background-color: #d1c4e9;
    color: #312873;
    font-weight: 600;
    font-size: 1.1rem;
    padding: 0.6em 1.3em;
    border-radius: 9999px;
    text-transform: uppercase;
    display: inline-block;
    transition: background-color 0.3s, color 0.3s;
}

body.dark .badge-status-other {
    background-color: #6a54a0;
    color: #f8a609;
}

.btn-custom-yellow {
    background-color: #F8A609;
    border: none;
    font-weight: 600;
    font-size: 1.2rem;
    padding: 0.85rem 2.2rem;
    border-radius: 10px;
    color: #312873;
    transition: background-color 0.3s ease, color 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    user-select: none;
    text-decoration: none;
}

.btn-custom-yellow:hover {
    background-color: #c17e05;
    color: white;
    text-decoration: none;
}

body.dark .btn-custom-yellow {
    background-color: #f8a609;
    color: #312873;
}

body.dark .btn-custom-yellow:hover {
    background-color: #c17e05;
    color: white;
}

.card-footer-custom {
    background-color: transparent;
    border-top: none;
    padding: 1.2rem 2rem 2rem 2rem;
    display: flex;
    justify-content: flex-start;
}


.row-custom {
    display: flex;
    flex-wrap: wrap;
    gap: 25px 40px; 
    justify-content: flex-start;
}

.col-custom {
    flex: 1 1 45%; 
    min-width: 280px; 
    box-sizing: border-box;
}

.col-full {
    flex: 1 1 100%;
}



    </style>

    <div class="container container-custom">
        <div class="card card-custom">
            <div class="card-header card-header-custom">
                <span>{{ __('Transaction Details') }}</span>
                <span class="transaction-id">#{{ $transaction->id }}</span>
            </div>

            <div class="card-body">
                <div class="row-custom">

                    <div class="col-custom">
                        <div class="label-custom">{{ __('From') }}</div>
                        <div class="value-custom">{{ $transaction->from_name }}</div>
                    </div>

                    <div class="col-custom">
                        <div class="label-custom">{{ __('To') }}</div>
                        <div class="value-custom">{{ $transaction->to_name }}</div>
                    </div>

                    <div class="col-custom">
                        <div class="label-custom">{{ __('Amount') }}</div>
                        <div class="value-custom">{{ number_format($transaction->amount, 2) }} {{ __('currency') }}</div>
                    </div>

                    <div class="col-custom">
                        <div class="label-custom">{{ __('Transaction Type') }}</div>
                        <div class="value-custom">{{ $transaction->transaction_type }}</div>
                    </div>

                    <div class="col-custom">
                        <div class="label-custom">{{ __('Status') }}</div>
                        <div class="value-custom">
                            @if($transaction->status == 'completed')
                                <span class="badge-status-completed">{{ __('Completed') }}</span>
                            @elseif($transaction->status == 'pending')
                                <span class="badge-status-pending">{{ __('Pending') }}</span>
                            @else
                                <span class="badge-status-other">{{ ucfirst($transaction->status) }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-custom">
                        <div class="label-custom">{{ __('Balance Before') }}</div>
                        <div class="value-custom">{{ number_format($transaction->balance_before, 2) }} {{ __('currency') }}</div>
                    </div>

                    <div class="col-custom">
                        <div class="label-custom">{{ __('Balance After') }}</div>
                        <div class="value-custom">{{ number_format($transaction->balance_after, 2) }} {{ __('currency') }}</div>
                    </div>

                    <div class="col-full">
                        <div class="label-custom">{{ __('Description') }}</div>
                        <div class="value-custom" style="min-height: 100px;">{{  app()->getLocale() == 'en' ?  $transaction->description_en : $transaction->description }}</div>
                    </div>

                    <div class="col-custom">
                        <div class="label-custom">{{ __('Created At') }}</div>
                        <div class="value-custom">{{ $transaction->created_at->format('Y-m-d') }}</div>
                    </div>

                </div>
            </div>

            <div class="card-footer card-footer-custom">
                <a href="{{ route('wallet-transactions.index') }}" class="btn btn-custom-yellow">
                    <i class="fas fa-arrow-left"></i> {{ __('Back to list') }}
                </a>
            </div>
        </div>
    </div>

    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</x-master-layout>
