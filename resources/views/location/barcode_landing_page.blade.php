@extends('layouts.layout')

@section('content')
<div id="landing-view">

<style type="text/css">
    .block_1 {
        float: right;
        padding-left: 10px;
    }

    #decrease {
        border-radius: 15px;
    }

    #increase {
        border-radius: 15px;
    }

	input[type=number]::-webkit-inner-spin-button, 
	input[type=number]::-webkit-outer-spin-button { 
        -webkit-appearance: none; 
        margin: 0; 
	}

    input#number {
        text-align: center;
        border: none;
        border-radius: 5px;
        background-color: #d4d3d36b !important;
        vertical-align: text-bottom;
    }

    .inside_qty {
        margin-top: -3px;
    }

    .minus_plus {
        cursor: pointer;
        font-size: 28px;
        font-weight: bold;
    }

    a:link {
        text-decoration: none !important;
    }

    #para_middle:first-letter {
        padding-left: 15%;
    }
    td > a.sellerbutton{
        float: none;
        margin-right: 0px;
    }
@if (!$show_barcode)
a.name {color:#000;cursor:unset;}
@endif
@php
	$role = \App\Models\usersrole::where('user_id',Auth::user()->id)->first()->role_id ?? 0;
@endphp
</style>

<div class="row" style="padding-top:0;">
	<div class="py-2 col-md-8 align-self-center">
		<div>
			<!-- <h2 style="margin-bottom:0px;">Location Barcode</h2> -->
			<h3 class="mb-0">{{$location->branch}}</h3>
			<p class="mb-0">{{$location->systemid}}</p>
		</div> 
	</div>
	
	@if ($show_barcode)
	<div class="col-md-4">
		<div style=" padding-left: 20px;">
			<div style="float: right">
			<a href="#" data-toggle="modal"
				data-target="#group_barcode_modal0">
				<button class="btn btn-success sellerbutton mr-0 "
						style="padding:0;">
					<span>+Barcode</span>
				</button>
			</a>
			</div>
		</div>
	</div>
	@endif
</div>

<table class="table table-bordered align-content-center"
	id="location_barcode" style="width:100%;">
	<thead class="thead-dark">
		<tr>
			<th style="width:30px;text-align: center;">No</th>
			<th style="width:100px;text-align: center;">Barcode</th>
			<th style="width:30px;text-align: center;background-image:none">QR&nbsp;Code</th>
			@if($role == '18')
			<th style="width:150px;text-align: center;">IP Address</th>
			@endif
			<th style="">Address</th>
			<th style="">Notes</th>
			<th style="width:30px;text-align: center;background-image:none"></th>
			<th style="width:30px;text-align: center;background-image:none"></th>
		</tr>
	</thead>
	<tbody class="tablebody">
	</tbody>
</table>
<style>
.tablebody > tr > td {vertical-align:middle}
</style>


<input type="hidden" name="my_system" id="my_system"
	value="@{{ $system_id }}">
<input type="hidden" name="my_product" id="my_product"
	value="@{{ $product_id }}">

<div class="modal fade" id="ipaddressupdate" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content bg-greenlobster">
      <div class="modal-header">
        <h5 class="modal-title" id="ipaddressupdate">Internet Protocol Address</h5>
      </div>
      <div class="modal-body">
        <form>
        	<div class="form-row">
        		<div class="form-group col-md-2">
		            <label for="recipient-name"
						class="col-form-label mb-1">Public IP
					</label><br>
		            <label for="message-text"
						class="col-form-label">Local IP
					</label>
		        </div>
		        <div class="form-group col-md-6">

		        	<div class="row p-0 m-0">
		        		<div class="col-md-8 p-0">
		        			<input type='text' name='publicipaddress'
								id='publicipaddress'
								class='form-control ipvalidator mb-2 text-center' placeholder='Public IP'/>	
		        		</div>
		        		<div class="col-md-1 font-weight-bolder p-0 text-center">
		        			:
		        		</div>
		        		<div class="col-md-3 p-0">
		        			<input type='text' name='publicport'
								id='publicport'
								class='form-control portvalidator mb-2 p-0 text-center' placeholder='Port'/>	
		        		</div>
		        	</div>

		        	<div class="row p-0 m-0">
		        		<div class="col-md-8 p-0">
		        			<input type='text' name='localipaddress'
								id='localipaddress'
								class='form-control ipvalidator text-center' placeholder='Local IP'/>	
		        		</div>
		        		<div class="col-md-1 font-weight-bolder p-0 text-center">
		        			:
		        		</div>
		        		<div class="col-md-3 p-0">
		        			<input type='text' name='localport'
								id='localport'
								class='form-control portvalidator mb-2 p-0 text-center' placeholder='Port'/>	
		        		</div>
		        	</div>
		        </div>
		        <div class="form-group col-md-4">
		            <a href="#"
					style="width: 140px; height: 70px; border-radius:10px; "
					class="btn btn-success btn-log bg-retail mt-3">
					<h5 style="margin-top: inherit;padding-top:2px;font-size: 1rem;">
					Configure
					</h5>
					</a>
		        </div>
        	</div>
        </form>
      </div>
      <!-- <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Send message</button>
      </div> -->
    </div>
  </div>
</div>

@if ($show_barcode)

<div class="modal fade" id="group_barcode_modal0" tabindex="-1" role="dialog"
	 aria-labelledby="productcontModallabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document"
		style="max-width:600px;">

		<div class="modal-content bg-greenlobster">
			<div class="modal-header">
				<h3 class="modal-title">Barcode</h3>
			</div>
			<div class="modal-body">
			<div class="row">
				<div class="col-md-4" style="padding-right:0">
					<h5>Note:</h5>
					Enter or scan barcodes. Separate with senicolon(;) or Enter
					<div class="col-md-12 d-flex justify-content-center"
						 style="align-items:end;padding-top:80px;padding-left:0;padding-right:0">
						<button class="btn btn-primary save-barcode sellerbutton" style="padding-left:9px">
							<span>Submit</span>
						</button>
					</div>
				</div>
				<div class="col-md-8" style="padding-top:10px">
				<textarea style="width:95%;" name="group_barcode" rows="10" cols="45"
					  placeholder="&nbsp;&nbsp;&nbsp;Please Enter/Scan Barcode"></textarea>
				</div>
			</div>
			</div>
		</div>
	</div>
</div>
@endif

<div class="modal fade" id="barcode_name" tabindex="-1"
    role="dialog" aria-labelledby="productcontModallabel" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered" role="document" >
    <div class="modal-content">
	<form style="margin-bottom:0"
		class="m-form  m-form--state m-form--label-align-right " >
		<div class="modal-body">
			<div class="m-form__content">
				<input type="hidden" id="modal-barcode_id"
					name="barcode_id" value="">
				<input  type="text" name="name" id="modal-name"
					class="form-control m-input" placeholder="Barcode Name">
			</div>
		</div>
	<!--end::Form-->
	</form>
    </div>
</div>
</div>
</div>
<div class="modal fade" id="showConfirm" tabindex="-1" role="dialog" aria-labelledby="showMsgModal" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered  mw-75 w-50" role="document">
		<div class="modal-content modal-inside bg-greenlobster">
			<div style="border-width:0" class="modal-header text-center"></div>
			<div class="modal-body text-center">
				<h5 class="modal-title text-white"
				id="statusModalLabel">Do you want to permanently delete this
				barcode?</h5>
			</div>
			<div class="modal-footer"
				style="border-top:0 none; padding-left: 0px; padding-right: 0px;">
				<div class="row"
					style="width: 100%; padding-left: 0px; padding-right: 0px;">
					<div class="col col-m-12 text-center">
						<input type="hidden" id="my_code"/>
						<button type="button"
						class="btn bg-primary primary-button"
						onclick="delete_code()"
						data-dismiss="modal" style="color: white">Yes</button>
						<button type="button"
						class="btn btn-danger primary-button"
						data-dismiss="modal">No</button>
					</div>
				</div>

				<form id="status-form" action="{{ route('logout') }}"
					method="POST" style="display: none;">
					@csrf
				</form>
			</div>
		</div>
	</div>
</div>
<div id="showBarcodeModal"></div>
@endsection

@section('scripts')
@include('settings.buttonpermission')
<script> 
    
    var system_id = '{{Auth::User()->system_id}}';
    var location_id = '{{$location->systemid}}';
    
    $(document).ready(function () {
        tablelocation_barcode.draw();
    });


	var tablelocation_barcode = $('#location_barcode').DataTable({
		"processing": false,
		"serverSide": true,
		"autoWidth": false,
		"ajax": "{{route('location.barcode_fetch',$location->systemid)}}",
		columns: [
			{data: 'DT_RowIndex', name: 'DT_RowIndex'},
			{data: 'barcode', name: 'barcode'},
			{data: 'qrcode', name: 'qrcode'},
			@if($role == 18)
			{data: 'ipaddress', name: 'ipaddress'},
			@endif
			{data: 'address', name: 'address'},
			{data: 'note', name: 'note'},
			{data: 'print', name: 'print'},
			{data: 'deleted', name: 'deleted'}
		],
		"order": [],
		"columnDefs": [
			{"className": "dt-center dt-valign", "targets": [0,1,2,6]},
			{"className": "dt-left dt-valign", "targets": 5},
		],
	});



	@if ($show_barcode)
    $('.save-barcode').click(function(){
        $("#msgModal").remove();
        var barcodes = $('textarea[name=group_barcode]').val();
        $.ajax({
            url: "{{route('location.barcode_new',$location->systemid)}}",
            data: {
                   'barcodes' : barcodes,
                   'location_id' : location_id
                  },
            type: 'POST',
            dataType: "json",
            success: function (response) {
                if(response != 0){
                    $("#showBarcodeModal").html(response);
                }
                $('#location_barcode').DataTable().ajax.reload(null, true);
                $('textarea[name=group_barcode]').val("");
                $('#group_barcode_modal0').modal('hide');
            },
            error: function (e) {
                if(e.responseText != 0){
                    $("#showBarcodeModal").html(e.responseText);
                }
                $('#location_barcode').DataTable().ajax.reload(null, true);
                $('textarea[name=group_barcode]').val("");
                 $('#group_barcode_modal0').modal('hide');
            }
        });
    });
	

    $(document).on("click", "a.name" , function() {
		$("#modal-barcode_id").val($(this).attr('data-barcode_id'));
		$("#barcode_name").modal("show");
	});

    $('#modal-name').change(function() {
		var name = $.trim($('#modal-name').val());
		var barcode_id = $('#modal-barcode_id').val();

		if (barcode_id != "") {
			$.ajax({
				url: "{{route('location.location_barcode_name_update',$location->systemid)}}",
				type: "POST",
				data: {
					name: name,
					barcode_id: barcode_id,
					location_id: location_id
				},
				cache: false,
				success: function(dataResult){
					$("#barcode_name").modal('hide');
					$("#showBarcodeModal").html(dataResult);
					$('#location_barcode').DataTable().ajax.reload(null, true);
				}
			});
		}
	});


    $(document).on("click", "div.remove-barcode" , function() {
		var barcode_id = $(this).attr('data-barcode_id')
        	$("#my_code").val(barcode_id);
	        $("#showConfirm").modal("show");
	});


    function delete_code(){
    $("#msgModal").remove();
    var barcode_id = $("#my_code").val();
    $.ajax({
		url: "{{route('location.location_barcode_delete', $location->systemid)}}",
		data: {
			'barcode_id' : barcode_id,
              'location_id' : location_id
			  },
		type: 'POST',
		success: function (response) {
            $(".modal-backdrop").remove();
            $("#showBarcodeModal").html(response);
            tablelocation_barcode.ajax.reload(null, true);
		},
		error: function (e) {
            console.log(e.responseText)
			$('#tableinventorybarcode_default').DataTable().ajax.reload(null, true);
		}
	});
}

	@endif

	$(document).on('focus click', '#publicipaddress',  function(e)
	{
        console.log("focused on " + e.target.id);
	});

	$(document).on('blur', '#publicipaddress',  function(e)
	{
         var ipaddress = /^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/;
         var ipvalue = $('#publicipaddress').val();
   
		 if(ipaddress.test(ipvalue))
		 {
			 $('#publicipaddress').css("border-color","green");
		 }
		 else
		 {
			 $('#publicipaddress').css("border-color","red");
		 }
	});

	$(document).on('focus click', '#localipaddress',  function(e)
	{
        console.log("focused on " + e.target.id);
	});

	$(document).on('blur', '#localipaddress',  function(e)
	{
         var ipaddress = /^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/;
         var ipvalue = $('#localipaddress').val();
   
		 if(ipaddress.test(ipvalue))
		 {
			 $('#localipaddress').css("border-color","green");
		 }
		 else
		 {
			 $('#localipaddress').css("border-color","red");
		 }
	});

	var first3BytesRg = /^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?))$/;
	var portregex = /^([0-9]{1,4}|[1-5][0-9]{4}|6[0-4][0-9]{3}|65[0-4][0-9]{2}|655[0-2][0-9]|6553[0-5])$/;

	$(document).on('keypress', '.ipvalidator',  function(e)
	{
         return validateIP(e.target.value + String.fromCharCode(e.which));
	});

	$(document).on('keypress', '.portvalidator',  function(e) {
         return validatePort(e.target.value +
		 	String.fromCharCode(e.which));
	});

	function validateIP(ip) {
		var splitted = ip.split(".");
		var nb = splitted.length;
		var portsplit = ip.split();

		if (nb > 4) return false;
		if (splitted[nb - 2] == "") return false;
		if (splitted[nb - 1] == "") return true;
	  
		if (nb <= 4) {
			return first3BytesRg.test(splitted[nb - 1]);
		}
	}

	function validatePort(port) {
	    return portregex.test(port);
	}


	// IP model update
	$('#ipaddressupdate').on('show.bs.modal', function (event) {
	  var button = $(event.relatedTarget) // Button that triggered the modal
	  var localip = button.data('localip').split(':')[0]
	  var publicip = button.data('publicip').split(':')[0]

	  var localport = button.data('localip').split(':')[1];
	  var publicport = button.data('publicip').split(':')[1];

	  // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
	  // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
	  var modal = $(this)
	  modal.find('.modal-title').text('IP Address')
	  modal.find('.modal-body #localipaddress').val(localip)
	  modal.find('.modal-body #publicipaddress').val(publicip)
	  modal.find('.modal-body #localport').val(localport)
	  modal.find('.modal-body #publicport').val(publicport)
	})
</script>

@endsection
