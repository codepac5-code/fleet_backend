@foreach($buttonTypes as $key => $value)
<button type="button" class="badge py-2 px-3 badge-pending text-capitalize border-0 mt-2 mx-1" id="variable_button" data-value="{{ '[[ '.$value->value.' ]]' }}">{{ $value->name }}</button>
@endforeach
