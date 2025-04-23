<x-master-layout>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                            <h5 class="font-weight-bold">{{ $pageTitle ?? __('messages.list') }}</h5>
                            {{-- @if($auth_user->can('coupon list')) --}}
                                <a href="{{ route('coupon.index') }}" class="float-right btn btn-sm btn-primary">
                                    <i class="fa fa-angle-double-left"></i> {{ __('messages.back') }}
                                </a>
                            {{-- @endif --}}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('coupon.store') }}" method="POST" id="coupon" data-toggle="validator">
                            @csrf
                            <input type="hidden" name="id" value="{{ old('id', $coupondata->id) }}">

                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label class="form-control-label">
                                        {{ __('messages.code') }} <span class="text-danger">*</span>
                                    </label>
                                    @if($coupondata->id == null)
                                        <input type="text" name="code" class="form-control" placeholder="{{ __('messages.code') }}" required value="{{ old('code') }}">
                                    @else
                                        <p>{{ $coupondata->code }}</p>
                                    @endif
                                    <small class="help-block with-errors text-danger"></small>
                                </div>

                                <div class="form-group col-md-4">
                                    <label class="form-control-label">
                                        {{ __('messages.discount_type') }} <span class="text-danger">*</span>
                                    </label>
                                    <select name="discounType" class="form-control select2js" required>
                                        <option value="fixed" {{ old('discounType') == 'fixed' ? 'selected' : '' }}>{{ __('messages.fixed') }}</option>
                                        <option value="percentage" {{ old('discounType') == 'percentage' ? 'selected' : '' }}>{{ __('messages.percentage') }}</option>
                                    </select>
                                </div>

                                <div class="form-group col-md-4">
                                    <label class="form-control-label">
                                        {{ __('messages.discount') }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" name="discount" class="form-control" min="0" step="any" placeholder="{{ __('messages.discount') }}" required value="{{ old('discount', $coupondata->discount) }}">
                                </div>

                                <div class="form-group col-md-4">
                                    <label class="form-control-label">
                                        {{ __('messages.expire_date') }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="expireDate" class="form-control datetimepicker" placeholder="{{ __('messages.expire_date') }}" required value="{{ old('expire_date', $coupondata->expire_date) }}">
                                    <small class="help-block with-errors text-danger"></small>
                                </div>


                                <div class="form-group col-md-4">
                                    <label class="form-control-label">
                                        {{ __('messages.limit') }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" name="limit" class="form-control" min="0" step="1" placeholder="{{ __('messages.limit') }}" required value="{{ old('discount', $coupondata->limit) }}">
                                </div>

                                <div class="form-group col-md-4">
                                    <label class="form-control-label">
                                        {{ __('messages.select_name', ['select' => __('messages.service')]) }} <span class="text-danger">*</span>
                                    </label>
                                    <br />
                                    @php
                                    $selected_services = $coupondata->serviceAdded->pluck('serviceId')->toArray();
                                @endphp
                                
                                <select name="serviceIds[]" class="select2js form-group service" multiple="multiple" required 
                                        data-placeholder="{{ __('messages.select_name', ['select' => __('messages.service')]) }}">
                                
                                    @foreach($services as $service)
                                        <option value="{{ $service->id }}" {{ in_array($service->id, $selected_services) ? 'selected' : '' }}>
                                            {{ $service->title }}
                                        </option>
                                    @endforeach
                                
                                </select>
                                
                                </div> 

                                <div class="form-group col-md-4">
                                    <label class="form-control-label">
                                        {{ __('messages.status') }} <span class="text-danger">*</span>
                                    </label>
                                    <select name="isActive" class="form-control select2js" required>
                                        <option value="1" {{ old('status', $coupondata->isActive) == '1' ? 'selected' : '' }}>{{ __('messages.active') }}</option>
                                        <option value="0" {{ old('status', $coupondata->isActive) == '0' ? 'selected' : '' }}>{{ __('messages.inactive') }}</option>
                                    </select>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-md btn-primary float-right">{{ __('messages.save') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-master-layout>
