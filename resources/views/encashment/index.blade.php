<x-master-layout>
<head>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
  </head>
<?php
$auth_user= authSession();
?>
    <div class="container-fluid">
        <div class="row">
            
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <h4 class="">Encashment</h4>
                            @if($auth_user->user_type != "admin")
                                <h4 class="">
                                    <span class="text-primary">Wallet</span> | ₱ {{ isset($wallet->amount) ? $wallet->amount : "0.00"}} 
                                    <button class="ml-5 text-primary btn btn-primary text-white" type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModalCenter">
                                        encash
                                    </button>
                                </h4>
                            @endif
                        </div>
                    </div>
                </div>
                <!-- start table -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-start">
                            <div class="input-group col-xl-4 col-sm-12 col-md-12">
                                <span class="input-group-text" id="addon-wrapping"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control dt-search" placeholder="Search..." aria-label="Search" aria-describedby="addon-wrapping" aria-controls="dataTableBuilder">
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="datatable" class="table table-striped border">

                            </table>
                        </div>
                    </div>
                </div>
              
            </div>
            <!-- Modal -->
            <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Encashment</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                  <div class="modal-body">
                    
                    <div class="form-group mb-2">
                        <label>Mode of transaction</label>
                        <select class="form-select form-control" aria-label="Default select example" id="transtype" required>
                          <option selected value="">Open this select menu</option>
                          <option value="GCash">GCash</option>
                          <option value="Cheque">Cheque</option>
                        </select>
                    <label class="text-danger classType" style="display: none;">This Field is required</label>
                    </div>
                    <label>Amount</label>
                    <input type="number" class="form-control" id="amountEncashment" required/>
                    <label class="text-danger classAmount" style="display: none;">This field is required</label>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" id="modalClose">Close</button>
                    <button type="button" class="btn btn-primary" id="submitEncashment">Submit</button>
                  </div>
                </div>
              </div>
            </div>
        </div>
    </div>
    @section('bottom_script')
    <script>
        document.addEventListener('DOMContentLoaded', (event) => {
            $('#submitEncashment').on('click', ()=>{
                var amount = $('#amountEncashment').val();
                var type = $('#transtype').val();
                if(type == ""){
                    $('.classType').show();
                    return
                }else{
                    $('.classType').hide();
                }
                if(amount == ""){
                    $('.classAmount').show();
                    return
                }else{
                    $('.classAmount').hide();
                }
                var postData = {
                    amount: amount,
                    type: type
                }
                
                $.ajax({
                    type: "POST",
                    url: '{{ route("depot_encashment") }}',
                    data: postData,
                    dataType: 'JSON',
                    success: function(data) {
                        console.log(data.suc)
                        if(data.suc == "success"){
                            $('#modalClose').trigger('click');
                            Swal.fire({
                              title: "Success!",
                              text: "Your encashment has been submitted.",
                              icon: "success"
                            }).then(()=>{
                                location.reload();
                            }); 
                        }else{
                           $('#modalClose').trigger('click');
                            Swal.fire({
                              title: "Error!",
                              text: data.suc,
                              icon: "error"
                            })
                        }
                    }
                })
            })
            var msg = "{{ isset($msg) ? $msg : '' }}";
            if(msg){
                if(msg == "success"){
                    Swal.fire({
                      title: "Deleted!",
                      text: "Your encashment has been deleted.",
                      icon: "success"
                    });   
                }else{
                    
                }
            }
            window.renderedDataTable = $('#datatable').DataTable({
                    processing: true,
                    serverSide: true,
                    autoWidth: false,                                                                                                                                                                         
                    responsive: true,
                    
                    dom: '<"row align-items-center"><"table-responsive my-3" rt><"row align-items-center" <"col-md-6" l><"col-md-6" p>><"clear">',
                    ajax: {
                    "type"   : "GET",
                    "url"    : '{{ route("encashment_table") }}',
                    "data"   : function( d ) {
                        d.search = {
                        value: $('.dt-search').val()
                        };
                        d.filter = {
                        column_status: $('#column_status').val()
                        }
                    },
                    },
                    columns: [
                        {
                            data: 'transaction_id',
                            name: 'transaction_id',
                            title: "Transaction ID",
                            searchable: true,
                        },
                        {
                            data: 'display_name',
                            name: 'display_name',
                            title: "Name",
                            searchable: true,
                        },
                        {
                            data: 'user_type',
                            name: 'user_type',
                            title: "User Type",
                            searchable: true,
                        },
                        {
                            data: 'amount',
                            name: 'amount',
                            title: "Amount",
                            searchable: false,
                        },
                        {
                            data: 'type',
                            name: 'type',
                            title: "Type",
                            searchable: false,
                        },
                        {
                            data: 'status',
                            name: 'status',
                            title: "Status",
                            searchable: true,
                        },
                        {
                            data: 'created_at',
                            name: 'created_at',
                            title: "Created At",
                            searchable: false,
                        },
                        {
                            data: 'updated_at',
                            name: 'updated_at',
                            title: "Updated At",
                            searchable: false,
                        },
                        {
                            data: 'action',
                            name: 'action',
                            title: "action",
                            searchable: false,
                        },
                        
                        
                    ]
                    
                });
           
        });
        
        function resetQuickAction () 
        {
            const actionValue = $('#quick-action-type').val();
            console.log(actionValue)
            if (actionValue != '') {
                $('#quick-action-apply').removeAttr('disabled');

                if (actionValue == 'change-status') {
                    $('.quick-action-field').addClass('d-none');
                    $('#change-status-action').removeClass('d-none');
                } else {
                    $('.quick-action-field').addClass('d-none');
                }
            } else {
                $('#quick-action-apply').attr('disabled', true);
                $('.quick-action-field').addClass('d-none');
            }
        }

        $('#quick-action-type').change(function () {
            resetQuickAction()
        });

        $(document).on('update_quick_action', function() {

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
    @endsection
</x-master-layout>
