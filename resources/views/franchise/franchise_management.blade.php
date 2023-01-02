@extends('layouts.layout')

@section('content')
<div id="landing-view">
<style>
.tabcontent{
	background-color: white;
}

.tab {
	border: unset;
}

.tab {
	background: white;
}

.tabcorners {
	border-top-right-radius: 10px;
	border-top-left-radius: 10px;
}

.tab button.active {
	background-color: #fff;
	border: 1px solid #aaa;
	border-bottom: unset;
	font-weight: 700;
	border-right: 0px solid #aaa;
	cursor: pointer !important;
	font-size: 19px;
	border-top-left-radius: 10px;
	border-top-right-radius: 10px;
}

.tab button {
	font-size: 19px;
	border-left: 1px solid #aaa;
	border-top: 1px solid #aaa;
	border-top-left-radius: 10px;
	border-top-right-radius: 10px;
	padding-top: 8px;
	padding-bottom: 10px;
	background-color: #f0f0f0;
}

.tab button:last-child {
	border-right: 1px solid #aaa;
}

.modal-content1 {
	width: 40%  !important;
	left: 200px;
}

.franchiseList a:hover {
	text-decoration: none;
}

.btn.bg-primary.primary-button:hover {
	color:  white;
}
</style>

<div id="default-content">
	<div class="row py-2"
		style="padding-top:0 !important;padding-bottom: 0px !important">
		<div class="col align-self-center" style="width:80%">
			<h2 class="mb-0">Franchise Management</h2>
		</div>
		<div class="col col-auto align-self-center" >
			<button class="btn btn-success btn-log sellerbuttonwide"
			style="padding-left:0;padding-right:0;margin-right:0;
				background-color: #008000; border: none"
			id="addNewFranchise">
			<span>+Franchise</span>
			</button>
		</div>
	</div>


	<table class="table table-bordered datatable" style="width:100%" id="franchise">
		<thead class="thead-dark">
		<tr>
			<th style="width:30px;text-align: center;">No</th>
			<th style="width:100px;text-align: center;">Franchise ID</th>
			<th>Franchise Name</th>
			<th class="text-center">Royalty</th>
			<th>Product</th>
			<th></th>
		</tr>
		</thead>
		<tbody></tbody>
	</table>
</div>
<br><br>

<div class="modal fade" id="franchiseNameModal"
	tabindex="-1" role="dialog"
	aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-body">
			<form action="#" method="post">
			<input  type="text"
				style="width:100%; border: 1px solid #ddd;
					padding: 1px 5px 1px 4px"
				name="franchise_edit_name" id="franchise_edit_name"
				placeholder="Franchise Name">
            <input  type="hidden"
				name="franchise_edit_name_old" id="franchise_edit_name_old">
			<input  type="hidden"
				style="width:100%; border: 1px solid #ddd;
					padding: 4px 1px 1px 5px"
				name="franchise_edit_id" id="franchise_edit_id">
			</form>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="msgModalFranchise"  tabindex="-1"
	role="dialog" aria-labelledby="staffNameLabel"
	aria-hidden="true" style="text-align: center;">

	<div class="modal-dialog modal-dialog-centered  mw-75 w-50"
		 role="document" style="display: inline-flex;">
		<div class="modal-content modal-inside bg-greenlobster"
			 style="width: 100%;">
			<div style="border:0" class="modal-header">&nbsp;</div>
			<div class="modal-body text-center">
				<h5 class="modal-title text-white"
					style="margin-bottom:0"
					id="status-msg-element">
				</h5>
			</div>

			<div class="modal-footer"
				 style="border-top:0 none;padding-left:0;padding-right:0;">
				<div class="row"
					 style="width: 100%;padding-left:0;padding-right:0;">
				</div>

				<form id="status-form" action="{{ route('logout') }}"
					  method="POST" style="display: none;">
					@csrf
				</form>
			</div>
		</div>
	</div>
</div>
</div>
    
@section('js')
 <div id="showEditInventoryModal"></div>
    <script type="text/javascript">
		$(document).ready(function() {
		  $(window).keydown(function(event){
			if(event.keyCode == 13) {
			  event.preventDefault();
			  return false;
			}
		  });
		});
		
        var franchiseTable = $('#franchise').DataTable({
        "processing": false,
        "serverSide": true,
        "ajax": {
            "url": "{{route('data.ajax.franchiseManagementList')}}",
            "type": "POST",
            'headers': {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'og_franchise_id', name: 'og_franchise_id'},
            {data: 'og_franchise_name', name: 'og_franchise_name'},
            {data: 'og_franchise_royalty', name: 'og_franchise_royalty'},
            {data: 'og_franchise_product', name: 'og_franchise_product'},
            {data: 'deleted', name: 'deleted'},
        ],
        "order": [0, 'desc'],
        "columnDefs": [
            {"width": "30px", "targets": 0},
            {"width": "150px", "targets": 1},
            {"width": "150px", "targets": 3},
            {"width": "50px", "targets": 4},
            {"width": "30px", "targets": 5},
            {"className": "dt-right", "targets": [3]},
            {"className": "dt-center", "targets": [0, 1, 4, 5]},
            {orderable: false, targets: [-1]},
        ],
     });

     $('#addNewFranchise').on('click', function () {

        addNewFranchise();
    });
    var product = franchiseTable;
    var prd = false;

    function addNewFranchise() {
        $.ajax({
            url: "{{route('data.ajax.saveFranchise')}}",
            type: "POST",
            enctype: 'multipart/form-data',
            processData: false,
            contentType: false,
            cache: false,
            dataType:"JSON",
            success:function(response) {
                 //franchiseTable.ajax.reload();
                if (response.status == 'true') {
                    displayStatusMsgPopup(response.msg);
                    
                }else{
                    displayStatusMsgPopup(response.msg);
                    
                }
				
                franchiseTable.ajax.reload();
            }, error: function (e) {
                console.log(e.message);
            }
        });
    }
    
    function details(franchise_id) {
        $.ajax({
            url: "{{route('franchise.details.dialog')}}",
            type: 'POST',
            'headers': {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: {
                "franchise_id": franchise_id
            },
            success: function (response) {
                console.log(response)
                //$("#productResponce").html(response);
                //$('#modal').modal('show')
                if (response.status == 'true') {
                    if (response.name != 'Franchise Name') {
                        $("#franchise_edit_name").val(response.name);
                        $("#franchise_edit_id").val(franchise_id);
                        $("#franchise_edit_name_old").val(response.name);
                        $('#franchiseNameModal').modal('show')
                    }else{
                        $("#franchise_edit_id").val(franchise_id);
                        $('#franchiseNameModal').modal('show')  
                    }
                }else{
                    displayStatusMsgPopup(response.msg);
                }
            },
            error: function (e) {
                console.log('error', e);
            }
        });
    }
    function saveFranchiseName(){
        
        $.ajax({
            url: "{{route('data.ajax.franchiseUpdate')}}",
            type: 'POST',
            'headers': {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: {
                "franchise_name": $("#franchise_edit_name").val(),
                "systemid": $("#franchise_edit_id").val()
            },
            success: function (response) {
                
                if (response.status == 'true') {
                    displayStatusMsgPopup(response.msg);
                    franchiseTable.ajax.reload();
                }else{
                    displayStatusMsgPopup(response.msg);
                    franchiseTable.ajax.reload();
                }
               /* setTimeout(function(){
                        window.location.reload();
                }, 1000);*/
            },
            error: function (e) {
                console.log('error', e);
            }
        });
    }
        $('#franchiseNameModal').on('hidden.bs.modal', function (e) {
            
            if($("#franchise_edit_name_old").val() != $("#franchise_edit_name").val()){
                saveFranchiseName();
            }
            
        });
        function displayStatusMsgPopup(msg) {
            $("#status-msg-element").text(msg);
            $("#msgModalFranchise").modal('show');
            setTimeout(function() {
                $("#msgModalFranchise").modal('hide');
                $('.modal-backdrop').remove();
            },3500);
        }
        
        function removeFranchiseManagementModel(id)
        {
           $.ajax({
               url: "{{route('franchiseManagment.edit.modal')}}",
               type: 'post',
               'headers': {
                   'X-CSRF-TOKEN': '{{ csrf_token() }}'
               },
               data: {
                   'id': id,
               },
               success: function (response) {

                   $("#showEditInventoryModal").html(response);
                   $("#showMsgModal").modal('show');
				   franchiseTable.ajax.reload();
               },
               error: function (e) {
                   console.log('error', e);
               }
           });
        }

    </script>

@include('settings.buttonpermission')
@endsection
@endsection
