<x-master-layout>
    <head>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    </head>
    <style>
        .input-group-text{
            width: 170px !important;
            text-align: center !important;
        }
    </style>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                            <h5 class="font-weight-bold">{{ $pageTitle ?? trans('messages.list') }}</h5>
                            {{-- @if($auth_user->can('booking add')) --}}
                            <a href="{{ route('booking.create') }}" class="float-right mr-1 btn btn-sm btn-primary"><i class="fa fa-plus-circle"></i> {{ __('messages.add_form_title',['form' => __('messages.booking')  ]) }}</a>
                            {{-- @endif --}}
                        </div>
                       
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
                <div id="commissionToast" class="ml-2">
                    
                </div>
                <div class="mb-2">
                    <div class="input-group ml-2">
                        <span class="input-group-text" id="addon-wrapping">Service Provider</span>
                        <input type="text" class="form-control " placeholder="{{ $commissions->service_office }}" id="provider" value="{{ $commissions->service_office }}">
                    </div>
                    <span class="input-group ml-2">Service Provider Current Commission : <span class="text-info" id="providerlb">{{ $commissions->service_office }} %</span></span>
                </div>
                <div class="mb-2">
                    <div class="input-group ml-2">
                        <span class="input-group-text" id="addon-wrapping">Neopreneur</span>
                        <input type="text" class="form-control " placeholder="{{ $commissions->neopreneur }}" id="neopreneur" value="{{ $commissions->neopreneur }}">
                    </div>
                    <span class="input-group ml-2">Neopreneur Current Commission : <span class="text-info" id="neopreneurlb">{{ $commissions->neopreneur }} %</span></span>
                </div>
                <div class="mb-2">
                    <div class="input-group ml-2">
                        <span class="input-group-text" id="addon-wrapping">Upline</span>
                        <input type="text" class="form-control " placeholder="{{ $commissions->upline }}" id="upline" value="{{ $commissions->upline }}">
                    </div>
                    <span class="input-group ml-2">Upline Current Commission : <span class="text-info" id="uplinelb">{{ $commissions->upline }} %</span></span>
                </div>
                <div class="mb-2">
                    <div class="input-group ml-2">
                        <span class="input-group-text" id="addon-wrapping">City Manager</span>
                        <input type="text" class="form-control " placeholder="{{ $commissions->city_manager }}" id="cityManager" value="{{ $commissions->city_manager }}">
                    </div>
                    <span class="input-group ml-2">City Manager Current Commission : <span class="text-info" id="cityManagerlb">{{ $commissions->city_manager }} %</span></span>
                </div>
                <div class="mb-2">
                    <div class="input-group ml-2">
                        <span class="input-group-text" id="addon-wrapping">Admin</span>
                        <input type="text" class="form-control " placeholder="{{ $commissions->admin }}" id="admin" value="{{ $commissions->admin }}">
                    </div>
                    <span class="input-group ml-2">Admin Current Commission : <span class="text-info" id="adminlb">{{ $commissions->admin }} %</span></span>
                </div>
                <div class="justify-content-end float-right">
                    <button id="commissionBtn" class="btn btn-primary">{{__('messages.apply')}}</button>
                </div>
            
        </div>
    </div>

    <script>
     
  $('#commissionBtn').on('click', ()=>{
    $('#commissionToast').html("");
    var vdata = {
        provider: $('#provider').val(),
        neopreneur: $('#neopreneur').val(),
        upline: $('#upline').val(),
        cityManager: $('#cityManager').val(),
        admin: $('#admin').val()
    }
    $.ajax({
        type: 'GET',
        url: '{{ route("commission.update") }}',
        data: vdata,
        dataType: 'JSON',
        success: function(data) {
            console.log(data);
            $('#commissionToast').append(`
                <div class="alert ${data.class}" role="alert">
                    ${data.messege}
                </div>
            `)
            var theData = data.commmission;
            $('#provider').attr("placeholder", theData.service_office);
            $('#neopreneur').attr("placeholder", theData.neopreneur);
            $('#upline').attr("placeholder", theData.upline);
            $('#cityManager').attr("placeholder", theData.city_manager);
            $('#admin').attr("placeholder", theData.admin);
            
            // $('#provider').val("")
            // $('#neopreneur').val("")
            // $('#upline').val("")
            // $('#cityManager').val("")
            // $('#admin').val("")
            
            $('#providerlb').text(theData.service_office);
            $('#neopreneurlb').text(theData.neopreneur);
            $('#uplinelb').text(theData.upline);
            $('#cityManagerlb').text(theData.city_manager);
            $('#adminlb').text(theData.admin);
        }
    });
  })
    $(document).on('click', '[data-ajax="true"]', function (e) {
      e.preventDefault();
      const button = $(this);
      const confirmation = button.data('confirmation');
    
      if (confirmation === 'true') {
          const message = button.data('message');
          if (confirm(message)) {
              const submitUrl = button.data('submit');
              const form = button.closest('form');
              form.attr('action', submitUrl);
              form.submit();
          }
      } else {
          const submitUrl = button.data('submit');
          const form = button.closest('form');
          form.attr('action', submitUrl);
          form.submit();
      }
  });

    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
</x-master-layout>