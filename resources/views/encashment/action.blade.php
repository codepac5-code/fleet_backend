
<?php
$auth_user= authSession();
?>
@if($auth_user->user_type == "admin")
<div class="align-items-center" style="align-text: center;">
    <a class="text-danger" data-toggle="modal" data-target="#exampleModalCenter{{ $data->id }}" href="#"
    @if($data->status == "Canceled")
        style="pointer-events: none"
    @endif
    >
        <i class="fas fa-pen text-secondary"></i>
    </a>

</div>

<!-- Modal -->
<div class="modal fade" id="exampleModalCenter{{ $data->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle{{ $data->id }}">Transaction ID: <span class="text-info">{{ $data->transaction_id }}</span></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-group">
            <label>Name</label>
            <input type="text" class="form-control" value="{{ $data->display_name }}" disabled/>
        </div>
        <div class="form-group">
            <label>Amount</label>
            <input type="text" class="form-control" value="{{ $data->amount }}" disabled/>
        </div>
        <div class="form-group">
            <label>Mode of transaction</label>
            <input type="text" class="form-control" value="{{ $data->type }}" disabled/>
        </div>
        <div class="form-group">
            <label>Status</label>
            <select class="form-select form-control" aria-label="Default select example" id="modalSelect{{ $data->id }}">
        
            </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="modalSaveEdit{{ $data->id }}">Save changes</button>
      </div>
    </div>
  </div>
</div>
@else
<div class="align-items-center" style="align-text: center;">
    <a class="text-danger" id="delBtn{{ $data->id }}" href="#"
    @if($data->status != "Pending")
        style="pointer-events: none"
    @endif
    >
        <i class="far fa-trash-alt"></i>
    </a>

</div>
@endif

<script>
var stat = "{{ $stat }}";
if(stat == "Pending"){
   $("#modalSelect{{ $data->id }}").append(`
      <option selected value="${stat}">${stat}</option>
      <option value="Canceled">Canceled</option>
      <option value="Approve">Approve</option>
    `) 
}else if(stat == "Canceled"){
    $("#modalSelect{{ $data->id }}").append(`
      <option selected value="${stat}">${stat}</option>
      <option value="Pending">Pending</option>
      <option value="Approve">Approve</option>
    `) 
}
else if(stat == "Approve"){
    $("#modalSelect{{ $data->id }}").append(`
      <option selected value="${stat}">${stat}</option>
      <option value="Canceled">Canceled</option>
      <option value="Pending">Pending</option>
    `) 
}
$("#modalSaveEdit{{ $data->id }}").on('click', function(){
    var id = "{{ $data->id }}";
    var status = $("#modalSelect{{ $data->id }}").val();
    var userid = "{{ $data->user_id }}";
    var postData = {
        id: id,
        status: status,
        userid: userid
    }
    
    $.ajax({
        type: "POST",
        url: '{{ route("admin_encashment") }}',
        data: postData,
        dataType: 'JSON',
        success: function(data) {
            console.log(data.suc)
            if(data.suc == "success"){
                $('#modalClose').trigger('click');
                Swal.fire({
                  title: "Success!",
                  text: "Depot encashment status has been Changed.",
                  icon: "success"
                }).then(()=>{
                    location.reload();
                }); 
            }else{
                $('#modalClose').trigger('click');
                Swal.fire({
                  title: "Error!",
                  text: data.er,
                  icon: "error"
                }).then(()=>{
                    location.reload();
                });
            }
        }
    })
})
$('#delBtn{{ $data->id }}').on('click',function(){
    Swal.fire({
      title: "Are you sure?",
      text: "You won't be able to revert this!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Yes, delete it!"
    }).then((result) => {
      if (result.isConfirmed) {
        var url = "{{ route('encashment_delete', $data->id) }}";
        location.replace(url);
      }
    });
})

</script>
