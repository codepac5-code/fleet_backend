<form method="POST" action="{{ route('saveLangContent') }}" id="flatArray" data-toggle="validator">
    @csrf
    <input type="hidden" value="{{ $filename }}" name="filename"/>
    <input type="hidden" value="{{ $requestLang }}" name="requestLang"/>
    
    <div class="table-responsive mb-0">
        <table class="table lang_table table-sm table-fixed">
            <thead>
                <tr class="text-secondary">
                    <th scope="col">{{ __('messages.key') }}</th>
                    <th scope="col">{{ __('messages.value') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($flatArray as $key => $val)
                    <tr>
                        <td>{{ $key }}</td>
                        <td>
                            <input type="text" class="form-control" name="{{ $key }}" value="{{ $val }}" />
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <button type="submit" class="btn btn-md btn-primary float-right">{{ __('messages.save') }}</button>
</form>
