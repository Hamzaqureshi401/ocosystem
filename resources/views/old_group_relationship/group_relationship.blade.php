
<div id="landing-view">
<style>
.backgroung-plain{
   background-color:white;
}
button {
	padding-left: 0;
	padding-right: 0;
}
.btn:disabled{
	border: 1px solid #a0a0a0;
	outline-color: #a0a0a0;
	color: #a0a0a0;
	font-weight: normal;
}
.btn.disabled{
	border: 1px solid #a0a0a0;
	outline-color: #a0a0a0;
	color: #a0a0a0;
	font-weight: normal;
}
.btn.disabled:hover{
	color: #34dabb;
	font-weight: bold;
}
.btn.active{
	border: 1px solid green;
	outline-color: green;
	color: green !important;
	font-weight: bold;
}
.hover:hover{
	border: 1px solid #34dabb;
	color: black;
	font-weight: bold;
}
.date_table >  tbody > tr > th {
	font-size:22px;
	color:white;
	background-color: rgba(255, 255,255, 0.5);
}

.backgroung-plain{
   background-color:white;
}
button {
	padding-left: 0;
	padding-right: 0;
}
.btn:disabled{
	border: 1px solid #a0a0a0;
	outline-color: #a0a0a0;
	color: #a0a0a0;
	font-weight: normal;
}
.btn.disabled{
	border: 1px solid #a0a0a0;
	outline-color: #a0a0a0;
	color: #a0a0a0;
	font-weight: normal;
}
.btn.disabled:hover{
	color: #34dabb;
	font-weight: bold;
}
.btn.active{
	border: 1px solid green;
	outline-color: green;
	color: green !important;
	font-weight: bold;
}
.hover:hover{
	border: 1px solid #34dabb;
	color: #34dabb;
	font-weight: bold;
}
.date_table >  tbody > tr > th {
	font-size:22px;
	color:white;
	background-color: rgba(255, 255,255, 0.5);
}

.date_table > tbody > tr > td {
	color:#fff;
	font-weight: 600;
	border:unset;
	font-size: 20px;
	cursor:pointer;
}

.date_table1 >  tbody > tr > th {
	font-size:22px;
	color:white;
	background-color: rgba(255, 255,255, 0.5);
}

.date_table > tbody > tr > td:empty,.date_table1 > tbody > tr > td:empty {
	cursor: not-allowed;
}
.disable-day {
	color: #a0a0a0 !important;
	cursor: not-allowed !important;
}
.grey{
	border: 1px solid green;
	color: green;
}
.grey:hover{
	border: 1px solid green;
	outline-color: black;
	color: black !important;
	font-weight: bold;
}
.btn:focus{
	box-shadow: none;
}
table.dataTable.display tbody tr.odd > .sorting_1, table.dataTable.order-column.stripe tbody tr.odd > .sorting_1 {
	background-color: white;
}

table.dataTable tbody td {
	vertical-align: middle;
}

input[type=number]::-webkit-inner-spin-button, 
input[type=number]::-webkit-outer-spin-button { 
-webkit-appearance: none; 
margin: 0; 
}
ul, li { list-style: none; margin: 0; padding: 0; }
ul { padding-left: 1em; }

.date_table > tbody > tr > td:empty,.date_table1 > tbody > tr > td:empty {
	cursor: not-allowed;
}
.disable-day {
	color: #a0a0a0 !important;
	cursor: not-allowed !important;
}
/* .grey{
	border: 0.9px solid green;
	color: green;
} */
.grey:hover{
	border: 1px solid green;
	outline-color: green;
	color: black !important;
	font-weight: bold;
}
.btn:focus{
	box-shadow: none;
}
table.dataTable.display tbody tr.odd > .sorting_1, table.dataTable.order-column.stripe tbody tr.odd > .sorting_1 {
	background-color: white;
}
input[type=number]::-webkit-inner-spin-button, 
input[type=number]::-webkit-outer-spin-button { 
-webkit-appearance: none; 
margin: 0; 
}
ul, li { list-style: none; margin: 0; padding: 0; }
ul { padding-left: 1em; }

li { padding-left: 1em;
border: 1px dotted black;
border-width: 0 0 1px 1px; 
}

li.container { border-bottom: 0px; }

li.empty { font-style: italic;
color: silver;
border-color: black;
}

li p { margin: 0;
position: relative;
top: 0.5em; 
}

li ul { 
border-top: 1px dotted black; 
margin-left: -1em;     
padding-left: 2em; 
}
ul li:last-child ul {
margin-left: -17px;
}
.delete-icon{
	padding: 4px 7px;
	border-color:red;
	-webkit-text-stroke: 1px red 
}
</style>
<style type="text/css">
	.upload-area {
		width: 70%;
		border: 2px solid lightgray;
		border-radius: 3px;
		margin: 0 auto;
		text-align: center;
		overflow: auto;
	}

	.upload-area:hover {
		cursor: pointer;
	}

	.upload-area h1 {
		text-align: center;
		font-weight: normal;
		font-family: sans-serif;
		line-height: 50px;
		color: darkslategray;
	}

	#file {
		display: none;
	}

	/* Thumbnail */
	.thumbnail {
		width: 180px;
		height: 185px;
		padding: 4px;
		border: 2px solid lightgray;
		border-radius: 3px;
		float: left;
	}

	.size {
		font-size: 17px;
		color: #fff;
	}

	#uploadfile > button > i {
		color: #fff
	}

	.green {
		color: #28a745 !important;
	}
	.selectedType{
		color:green;
		font-weight: bold;
		font-size: 20px;
	}
</style>

<link href="{{ asset('css/ionicons.min.css') }}" rel="stylesheet">

<div class="modal fade" id="msgModal"  tabindex="-1"
	 role="dialog" aria-labelledby="staffNameLabel"
	 aria-hidden="true" style="text-align: center;">

	<div class="modal-dialog modal-dialog-centered  mw-75 w-50"
		 role="document" style="display: inline-flex;">
		<div class="modal-content modal-inside bg-greenlobster"
			 style="width: 100%;">
			<div class="modal-header" style="border:0">&nbsp;</div>
			<div class="modal-body text-center">
				<h5 class="modal-title text-white"
					id="statusModalLabel">
					Product not found
				</h5>
			</div>
			<div class="modal-footer"
				 style="border-top:0 none;padding-left:0;padding-right:0;">
				&nbsp;
				<form id="status-form1" action="{{ route('logout') }}"
					  method="POST" style="display: none;">
					@csrf
				</form>
			</div>
		</div>
	</div>
</div>

<div id="landing-content" style="width: 100%">
	<div class="row py-2" style="padding-bottom:0 !important;padding-top:0 !important;display:flex">
	<div class="col-md-4 align-self-center" style="width:10%">
		<h2 style="margin-bottom:0;">Group Relationship</h2>
	</div>
	<div class="col-md-7 align-self-center" style="width:100%">
		<div class="row" >
			{{-- <div style="cursor:point" id="" data-toggle="modal" data-target="#NameIconModal"> --}}
				<div class="col-sm-1" style="align-self:center;cursor:pointer;" data-toggle="modal" data-target="#NameIconModal">
					{{-- <input type="file" accept="image/*"  id="file" name="file" hidden/> --}}
					<img src="
					@if(empty($group_info->groupicon))
					{{asset('images/placeholder.jpg')}}
					@else
						{{asset('images/company/'.$group_info->owner_user_id.'/'.$group_info->groupicon)}}
					@endif
					" alt="Logo" width="70px" height="70px" style="object-fit:contain;float:right;margin-left:0;margin-top:0;" id="group_icon">
				</div>
				<div class="col-sm-5" style="align-self:center;float:left;padding-left:0" style="cursor:pointer">
					<h4 data-toggle="modal" data-target="#NameIconModal" style="cursor:pointer;text-transform:capitalize;margin-bottom:0px;padding-top: 0;line-height:1.5;" id="group_name_text">
					@if(empty($group_info->groupname))
						Group Name
					@else
						{{$group_info->groupname}}
					@endif
					</h4>
					
			</div>
		</div>
	</div>

	<div class="modal fade" id="NameIconModal" tabindex="-1"
	 role="dialog" aria-labelledby="staffNameLabel"
	 aria-hidden="true" style="text-align: center;">

	<div class="modal-dialog modal-dialog-centered  mw-75 w-50"
		 role="document" style="display: inline-flex;min-width: 43vw !important;">
		<div class="modal-content modal-inside bg-greenlobster"
			 style="width: 100%;">
		<div class="modal-header" style="">
			<h3 class="text-white"
				id="statusModalLabel" style="margin-bottom:0">
				Group Name
			</h3>
		</div>
		<div class="modal-body text-center" style="padding-top: 20px;">
		<form action="#" id="updateProspectFields_edit" method="post"
			  enctype="multipart/form-data"
			  onsubmit="return false;" autocomplete="off">
			<div class="row" style="padding-top: unset;">
			<div class="col-md-6" style="padding-right: unset;padding-top: 0px;">
			<div class="upload-area" id="uploadfile" style='border:
				unset;height:255px;background: grey;display:block;
				margin-left: 0.9%;overflow: hidden;width: 100%;'>
				<input type="file" name="file" id="file" class="hidden"/>

			
				{{-- <div id="thumbnail_1" class="thmb" style="">
					<a href='/images/{{$group_info->owner_user_id}}/{{$group_info->groupicon}}'
						target="_blank"> <img style="background-color:white;object-fit:contain"
						src="
							@if(empty($group_info->groupicon))
							{{asset('images/placeholder.jpg')}}
							@else
								{{asset('images/company/'.$group_info->owner_user_id.'/'.$group_info->groupicon)}}
							@endif
						"
						width="100%" height="255px">
					</a>
				</div> --}}
				<div id="to_show"></div>
				@if (!empty($group_info->groupicon))
				<div id="thumbnail_1" class="thmb" style="">
					<a href='/images/company/{{$group_info->owner_user_id}}/{{$group_info->groupicon}}'
						target="_blank"> <img style="background-color:white;object-fit:contain"
						src="{{asset('images/company/'.$group_info->owner_user_id.'/'.$group_info->groupicon)}}"
						width="100%" height="255px" id="internal_modal_image">
					</a>
				</div>
				<button class="redCrabShell"
					style="position: absolute;bottom: 20px;right: 63px;
					padding-bottom: 24px;padding-left: 5px;border: none;"
					onclick='del_picture("{{$group_info->owner_user_id}}");return null'>
					<i class="fa fa-times redCrab" style="padding: 0px;"></i>
				</button>

				<button class="btn btn-sm  btn-add"
					style="position: absolute;bottom: 10px;right:10px;
					font-size: 17px" id="uploadLogo">
					<i class="fa fa-camera green" id='logo_upload_cam'
					   style="font-size: 40px" onclick="return false;">
					</i>
				</button>
				@else
				<div id="to_hide">
					<h1 id='upload_text' style="color:#fff;margin: 40px"></h1>
					<button class="btn btn-sm  btn-add"
						style="position: absolute;bottom: 10px;right: 10px;
						font-size: 17px" id="uploadLogo"
						onclick="return false;">
						<i class="fa fa-camera" id='logo_upload_cam'
						style="font-size: 40px"></i>
					</button>
				</div>
				@endif
			 </div>
			</div>
			<div class="col-md-6">
				<div class="form-group">
					<input type="text" id="group_name_field"
						class="form-control" placeholder="Group Name"
						autocomplete="off" value="@if(empty($group_info))@else{{ucfirst($group_info->groupname)}}@endif"required/>
				</div>
			</div>

			</div>
			<input type="hidden" name="systemid" id="systemid" value=""> 
		</form>
		</div>
		</div>
	</div>
</div> 


		<div class="col-md-1 col-auto align-self-center"
			style="margin-bottom:0;margin-top:0;text-align: center;">
			<button class="btn btn-warning text-white btn-log sellerbutton text-center"
				style="margin-right:0;padding-left:0;padding-right:0;float:right"
				id="ageingreportbuttonty"
				data-toggle="modal" data-target=".twoWayModal"
				onclick="">
				<span >Two Way</span>
			</button>
		</div>
	</div>

    <table class="table table-bordered display"
		id="group_relationship_tbl" style="width:100%;">
		<thead class="thead-dark">
			<tr>
				<th class="text-center" style="width:30px">No.</th>
				<th class="text-center" style="width:150px">Merchant&nbsp;ID</th>
				<th class="text-center" style="width:150px">Business Reg. No.</th>
				<th class="text-left" style="">Company</th>
				<th class="text-center" style="width:100px;">% Holding</th>
				<th class="text-center" style="width:25px;"></th>
				<th class="text-center" style="width:25px;"></th>
				<th class="text-center" style="width:25px;"></th>
			</tr>
		</thead>
		<tbody>
			{{-- group table --}}
		</tbody>
	</table>
</div>
</div>


<!--pop up -->
<div class="modal fade" tabindex="-1" id="crossCompanyPopUp" role="dialog">
	<input id="cross_company_id" value="" hidden/>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-greenlobster">
            <div class="modal-header">
                <h3 style="margin-bottom:0">Cross Company LogIn Authorization</h3>
            </div>
            <div class="modal-body modalTypeBody">
                    <div class="row">
                        <div class="col-md-9" style="padding: 8px;margin: 0px auto; cursor:pointer" onclick="">
							<h5 class="" style="margin: 0px auto;">We allow you to login to our page</h5>
						</div>
						<div class="col-md-3">
							 <button type="button"
							 id="btn_login_active"
							 active="false"
							 class="btn grey"style="width:70px!important;padding-top: 6px;padding-bottom:6px;padding-left: 0px;padding-right: 0px;"
							 onclick="">
                           		 Active
							</button>
						</div>
					</div><br>
					 <div class="row">
                        <div disabled class="col-md-9" style="padding: 8px;margin: 0px auto; cursor:pointer" onclick="">
							<h5 class="" style="margin: 0px auto;">You allow us to login to our page</h5>
						</div>
						<div class="col-md-3">
							 <button type="button" id="outbound_login" class="btn" style="width:70px!important;padding-top: 6px;padding-bottom:6px;padding-left: 0px;padding-right: 0px;" disabled>
                           		 Active
							</button>
						</div>
					</div>
                </div>
        </div>
    </div>
</div>
</div>

<!--pop up -->
<div class="modal fade" tabindex="-1" id="groupHierarchy" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-greenlobster">
            <div class="modal-header">
                <h3 style="margin-bottom:0">Group Relationship</h3>
            </div>
            <div class="modal-body modalTypeBody">
				<div style="margin-left:0;margin-right:0;"
					class="row">
					{{$company->name}}
				</div>
				@if(!empty($group_holding))
				@foreach ($level_arr as $item)
					@if($item['name'] != $company->name)
					<div style="margin-left:0;margin-right:0;"
						class="row">
						<div class="col-sm-1"></div>
						<div class="col-sm-auto">{{$item['name']}}</div>
						<div class="col-sm-1"></div>
					</div>
					@if(!empty($item['next']))
						@foreach ($item['next'] as $next_item)
						<div style="margin-left:0;margin-right:0;"class="row">
							<div class="col-sm-1"></div>
							<div class="col-sm-1"></div>
							<div class="col-sm-auto">{{ $next_item->name }}</div>
						</div>
						@endforeach
					@endif
					@endif
				@endforeach
				@endif
			</div>
        </div>
    </div>
</div>


<!--pop up -->
<div class="modal fade" tabindex="-1" id="holdingPopup" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header thead-dark">
                <h3 style="margin-bottom:0" id="holding-company-name">
				Company Name
				</h3>
            </div>
            <div class="modal-body modalTypeBody" style="">
				<table class="table table-bordered"
					id="group_holding_tbl" style="width:100%;">
					<thead class="thead-dark">
					<tr>
					<th class="text-center" style="width:30px">No.</th>
					<th class="text-left" style="">Company Name</th>
					<th class="text-center" style="width:100px;">P/S/A</th>
					<th class="text-center" style="width:100px;">%</th>
					</thead>
					<tbody> </tbody>
				</table>
            </div>
        </div>
    </div>
</div>

<!--- popup -->
<div class="modal fade" tabindex="-1" role="dialog" id="psapopup">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-greenlobster">
            <div class="modal-header">
				<h3 style="margin-bottom:0">Parent/Subsidary/Associate</h3>
				<input value="" id="userid" hidden/>
            </div>
            <div class="modal-body modalTypeBody" style="">
				<div class="row">
					<div class="col-md-12"
						style="padding: 8px;margin: 0px auto; cursor:pointer">
						<h5 class="" style="margin: 0px auto;"
						id="parent_id" onclick="change_psa(this.id)">Parent</h5>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12"
						style="padding: 8px;margin: 0px auto; cursor:pointer"  >
						<h5 class="" style="margin: 0px auto;"
						id="subsidary_id" onclick="change_psa(this.id)">Subsidary</h5>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12"
						style="padding: 8px;margin: 0px auto; cursor:pointer">
						<h5 class="" style="margin: 0px auto;"
						id="associate_id" onclick="change_psa(this.id)">Associate</h5>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12"
						style="padding: 8px;margin: 0px auto; cursor:pointer">
						<h5 class="" style="margin: 0px auto;"
						id="norelation_id" onclick="change_psa(this.id)">No relationship</h5>
					</div>
				</div>
            </div>
        </div>
    </div>
</div>

<!-- two way modal pop up starts here-->
<div class="modal fade twoWayModal" tabindex="-1" role="dialog"  aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" style="width:600px;">
    <div class="modal-content bg-greenlobster">
        <div class="modal-header" style="border-bottom-width:0">
            <h3 style="margin-bottom:0"
				class="modal-title" id="myLargeModalLabel">
				Two Way
			</h3>
        </div>
        <div class="modal-body">
		<div class="row">
			<div class="col-md-10">
				<input type="text" id="merchant_id" class="form-control"
				placeholder="Merchant ID" style="outline:none;">
			</div>
			<div class="col-md-2">
				<button id="merchant_add_button"
					class="btn btn-success search-btn sellerbutton">
					<span class="fa fa-plus"
						style="padding-top:5px;font-size: 25px">
					</span>
				</button>
			</div>
		</div>
        </div>
    </div>
  </div>
</div>
<!-- two way modal pop up ends here-->
<!---messsage popup -->
<div class="modal fade" id="msgModal"  tabindex="-1"
	 role="dialog" aria-labelledby="staffNameLabel"
	 aria-hidden="true" style="text-align: center;">

	<div class="modal-dialog modal-dialog-centered  mw-75 w-50"
		 role="document" style="display: inline-flex;">
		<div class="modal-content modal-inside bg-greenlobster"
			 style="width: 100%;">
			<div class="modal-header" style="border:0">&nbsp;</div>
			<div class="modal-body text-center">
				<h5 class="modal-title text-white"
					id="statusModalLabel">
					
				</h5>
			</div>
			<div class="modal-footer"
				 style="border-top:0 none;padding-left:0;padding-right:0;">
				&nbsp;
				<form id="status-form" action="{{ route('logout') }}"
					  method="POST" style="display: none;">
					@csrf
				</form>
			</div>
		</div>
	</div>
</div>
<input id="company_id_to_exclude" value="{{Auth::user()->id}}" hidden/>
<!--- message popup end ---->
{{-- <button id="testbtn">click me!</button> --}}
<script>

	$(document).ready(function () {
		// alert($("#to_hide").html().length)
		groupTable.draw();

		// $('#logo_upload_cam').click(function(){ $('#file').trigger('click'); });

	});
	var groupTable = $("#group_relationship_tbl").DataTable({
		"autoWidth" : false,
        "processing": true,
        "serverSide": true,
        "ajax": {
            url: "{{route('data.ajax.showGroupRelationship')}}",
            type: "GET",
            header: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
        },
        columns: [
            {data: 'DT_RowIndex', name:'DT_RowIndex'},
            {data: 'merchant_id', name: 'merchant_id'},
            {data: 'business_reg_no', name: 'business_reg_no'},
            {data: 'name', name: 'name'},
            {data: 'holding', name: 'holding'},
            {data: 'yellow_crab', name: 'yellow_crab'},
            {data: 'blue_crab', name: 'blue_crab'},
            {data: 'red_crab', name: 'red_crab'},
            
        ],
		"aoColumnDefs": [
			{ "bSortable": false, "aTargets": [5,6,7] },
			{"className": "text-center",  targets: [0]},
			{"className": "background-plain",  targets: [0,1,2,3,4,5,6,7]},
		]
	})

	//add 
	$('#merchant_add_button').click(function(){
		var id = $('#merchant_id').val();
		$.ajax({
			type:'POST',
			url:'{{ route('data.ajax.GroupsaveMerchantTwoWayLinking') }}',
			datatype: 'json',
			data:{merchant_id:id},
			success: function(response){
				// console.log(response)
				$('.twoWayModal').modal('hide');
				if(response.status == 'true') {
					groupTable.ajax.reload()
					$('#merchant_id').val('');
				}
				$("#statusModalLabel").text(response.msg);
				$("#msgModal").modal('show')
				setTimeout(function(){
						$("#msgModal").modal('hide')
				},2500)
			}
		});
    });

	function showHolding(auth_user_id,user_id){
		$("#company_id_to_exclude").val(user_id)
		// alert($("#company_id_to_exclude").val())
		$('#group_holding_tbl').DataTable().clear().destroy();
		$('#group_holding_tbl').DataTable({
			"autoWidth" : false,
			"processing": true,
			"ajax": {
				url: "{{route('data.ajax.showGroupHolding')}}",
				data: {'user_id':user_id},
				type: "POST",
				header: {
					'X-CSRF-TOKEN': '{{ csrf_token() }}'
				},
			},
			columns: [
				{data: 'DT_RowIndex', name:'DT_RowIndex'},
				{data: 'company_name', name: 'company_name'},
				{data: 'psa', name: 'psa'},
				{data: 'per', name: 'per'},
			],
			"columnDefs": [
				{"className": "text-center",  targets: [0]},
				{"className": "", "targets": [1,2,3] },
				{ "width": "30px", "targets": 0 },
				{ "width": "70px", "targets": 3 }
			],
		}).draw()
		

		$.ajax({
			type:'POST',
			url:'{{ route('data.ajax.getCompanyHoldingDetails') }}',
			datatype: 'json',
			data:{'user_id':user_id},
			success: function(resp){
				$('#holding-company-name').text(resp)
			}
		})
			// $("#group_holding_tbl tr #psa_field_text"+user_id).parents('tr').remove()
		// groupHolding.	draw()
		// groupHolding.ajax.reload()
		$('#holdingPopup').modal('show')
	}


	function show_psa_popup(user_id, company_id){
		// alert(user_id+' '+' '+company_id)
		$.ajax({
			type:'POST',
			url:'{{ route('data.ajax.getHoldingData') }}',
			datatype: 'json',
			data:{'company_id':company_id},
			success: function(resp){
				// console.log(resp)
				if(resp.status == 'true'){
					switch(resp.data.psa){
						case 'no_relationship':
							$('#norelation_id').addClass('selectedType')
							$('#subsidary_id').removeClass('selectedType')
							$('#associate_id').removeClass('selectedType')
							$('#parent_id').removeClass('selectedType')
							break;
						case 'parent':
							$('#associate_id').removeClass('selectedType')
							$('#subsidary_id').removeClass('selectedType')
							$('#parent_id').addClass('selectedType')
							$('#norelation_id').removeClass('selectedType')
							break;
						case 'subsidiary':
							$('#parent_id').removeClass('selectedType')
							$('#associate_id').removeClass('selectedType')
							$('#subsidary_id').addClass('selectedType')
							$('#norelation_id').removeClass('selectedType')
							break;
						case 'associate':
							$('#parent_id').removeClass('selectedType')
							$('#associate_id').addClass('selectedType')
							$('#subsidary_id').removeClass('selectedType')
							$('#norelation_id').removeClass('selectedType')
							break;
					}
					
				} else {
					$('#parent_id').removeClass('selectedType')
					$('#associate_id').removeClass('selectedType')
					$('#subsidary_id').removeClass('selectedType')
					$('#norelation_id').removeClass('selectedType')
				}
				// $('#holding-company-name').text(resp)
			}
		})
		$("#userid").val(company_id);
		$('#psapopup').modal('show')
	}


	function atm_money(num) {
		
		if(num == 0){
			return '0.00';
		}
		if (num.toString().length == 1) {
			return '00.0' + num.toString()

		} else if (num.toString().length == 2) {
			return '00.' + num.toString()

		} else if (num.toString().length == 3) {
			return '0' + num.toString()[0] +'.'+ num.toString()[1] + 
				num.toString()[2];

		} else if (num.toStringbuffer().length >= 4) {
			return num.toString().slice(0,(num.toString().length - 2)) +
				'.'+ num.toString()[(num.toString().length - 2)] + 
				num.toString()[(num.toString().length - 1)];
		}
	}

	$("#holdingPercentInput").on( "keydown", function( event ) {
		event.preventDefault()

		
		var number = parseInt($("#holdingPercentInput").val())
		
		if(number > 100){
			// $("#holdingPercentInput").val('0.00');
			// alert('yes')
			$("#holdingPercentInput").val('0.00');
		}
		

		if (event.keyCode == 8) {
			$("#buffer_main_price").val('')
			$("#holdingPercentInput").val('')
			return null
		}

		
	  
	  if (isNaN(event.key) || $.inArray( event.keyCode, [13,38,40,37,39] ) !== -1 || event.keyCode == 13  ) {
		if ($("#buffer_main_price").val() != '') {
		$("#holdingPercentInput").val(atm_money(parseInt($("#buffer_main_price").val())))
		} else {
		  $("#holdingPercentInput").val('')
		}
			return null;
		}

	   const input =  event.key;
	   old_val = $("#buffer_main_price").val()
		
	   if (old_val === '0.00') {
			$("#buffer_main_price").val('')
			$("#holdingPercentInput").val('')
			old_val = ''
	   }
	   

	   $("#buffer_main_price").val(''+old_val+input)
	   $("#holdingPercentInput").val(atm_money(parseInt($("#buffer_main_price").val())))
	});

	
	function update_group_name(){
		// $('#group_name_field').on('focusout', function(){
			var group_name = $('#group_name_field').val();
			if(group_name != ''){
				$('#group_name_text').text(group_name)
				$.ajax({
					url: "{{route('group.updateName')}}",
					type: 'POST',
					data: {
						"group_name": group_name
					},
					success: function (response) {
						// console.log(response);
						if(response.status == 'true'){
							$("#statusModalLabel").text('Group name updated');
							$("#msgModal").modal('show')
						}else{
							$("#statusModalLabel").text('Failed to update');
							$("#msgModal").modal('show')
						}
						setTimeout(function(){
								$("#msgModal").modal('hide')
						},2500)
					},
					error: function (e) {
						console.log('Error:' + e);
					}
				})

			}else{
				$("#statusModalLabel").text('Field cannot be blank');
				$("#msgModal").modal('show')
				setTimeout(function(){
						$("#msgModal").modal('hide')
				},2500)
			}
		// })
	}

	function change_psa(id){
		var user_id = $("#userid").val();
		switch(id){
			case 'parent_id':
				if($('#parent_id').hasClass('selectedType')){
					$('#parent_id').removeClass('selectedType')
				}
				$('#parent_id').addClass('selectedType')
				$('#parent_id').css("font-weight", "bold")
				$('#subsidary_id').removeClass('selectedType')
				$('#associate_id').removeClass('selectedType')
				$('#norelation_id').removeClass('selectedType')
				$('#psa_field_text'+user_id).text('Parent')
				update_psa(user_id, 'parent')
				break;
			case 'subsidary_id':
				$('#parent_id').removeClass('selectedType')
				$('#associate_id').removeClass('selectedType')
				$('#subsidary_id').addClass('selectedType')
				$('#norelation_id').removeClass('selectedType')
				$('#psa_field_text'+user_id).text('Subsidiary')
				update_psa(user_id, 'subsidiary')
				break;
			case 'associate_id':
				$('#associate_id').addClass('selectedType')
				$('#subsidary_id').removeClass('selectedType')
				$('#parent_id').removeClass('selectedType')
				$('#norelation_id').removeClass('selectedType')
				$('#psa_field_text'+user_id).text('Associate')
				update_psa(user_id, 'associate')
				break;
			case 'norelation_id':
				$('#norelation_id').addClass('selectedType')
				$('#subsidary_id').removeClass('selectedType')
				$('#associate_id').removeClass('selectedType')
				$('#parent_id').removeClass('selectedType')
				$('#psa_field_text'+user_id).text('No relationship')
				update_psa(user_id, 'no_relationship')
				break;
		}
		$('#psapopup').modal('hide');
	}

	function update_psa(id, psa){
		$.ajax({
			url: "{{route('group.updateHoldingPSA')}}",
			type: 'post',
			data: {
				"company_id": id,
				"psa": psa
			},
			success: function (response) {
				console.log(response);
				// groupHolding.ajax.reload();
				$('#group_holding_tbl').DataTable().ajax.reload()
				groupTable.ajax.reload();
			},
			error: function (e) {
				console.log('Error:' + e);
			}
		})
	}

	function del_company(company_id){
		$.ajax({
			url: "{{route('group.delGroup')}}",
			type: 'post',
			data: {
				"company_id": company_id
			},
			success: function (response) {
				// console.log(response);
				groupTable.ajax.reload();
			},
			error: function (e) {
				console.log('Error:' + e);
			}
		})
	}

	$("#holdingPopup").on('hidden.bs.modal', function(){
		var percentage = []
        $("#group_holding_tbl tr :input[type='text']").each(function (i,v) {
            percentage.push({'id': $(this).data('company-id'), 'per': $(this).val()});
        });
		$.ajax({
			url: "{{route('group.updateHoldingPercentage')}}",
			type: 'post',
			data: {
				"percentage": percentage
			},
			success: function (response) {
				console.log(response);
				if(response.status == 'true'){
					$("#statusModalLabel").text('Shareholding updated');
					$("#msgModal").modal('show')
				}
				setTimeout(function(){
						$("#msgModal").modal('hide')
				},2500)
				// groupTable.ajax.reload();
			},
			error: function (e) {
				console.log('Error:' + e);
			}
		})
		
		// console.log(percentage)
	})

	function showCrossCompany(company_id, inbound, outbound){
		var element = $('#btn_login_active')
		if(inbound == 1){
			element.removeClass('grey')
			element.addClass('active')
			element.attr('active', 'true')
		}else{
			element.removeClass('active')
			element.addClass('grey')
			element.attr('active', 'false')
		}

		if(outbound == 1){
			$("#outbound_login").addClass('active')
			$("#outbound_login").attr('disabled', false)
		}else{
			$("#outbound_login").removeClass('active')
			$("#outbound_login").attr('disabled', true)
		}
		$("#cross_company_id").val(company_id)
		$("#crossCompanyPopUp").modal('show')
	}

	$('#btn_login_active').on('click', function(){
		var element = $('#btn_login_active')
		var cross_company_id = $("#cross_company_id").val()
		var active = element.attr('active')
		if(active == 'false'){
			element.removeClass('grey')
			element.addClass('active')
			element.attr('active', 'true')
		}else{
			element.removeClass('active')
			element.addClass('grey')
			element.attr('active', 'false')
		}
		$.ajax({
			url: "{{route('group.InboundRemoteLogin')}}",
			type: 'post',
			data: {
				"cross_company_id": cross_company_id,
				"active": active
			},
			success: function (response) {
				console.log(response);
				if(response.status == 'true'){
					// $("#statusModalLabel").text('Share holding Updated');
					// $("#msgModal").modal('show')
					// alert('yea')
				}
				setTimeout(function(){
						$("#msgModal").modal('hide')
				},2500)
				// groupTable.ajax.reload();
			},
			error: function (e) {
				console.log('Error:' + e);
			}
		})
	})


	$("#btn_login_active").on('click', function(){
		
		
	})

		//Image upload functions

	async function del_picture() {
		await $.ajax({
			url: "{{route('group.delPicture')}}",
			type: 'post',
			data: {
				"systemid": ''
			},
			success: function (response) {
				console.log(response)
				$("#uploadfile > .redCrabShell").hide();
				$("#logo_upload_cam").toggleClass('green')
				$("#group_icon").hide()
				$("#internal_modal_image").hide() 
			},
			error: function (e) {
				console.log('Error:' + e);
			}
		});
		return false
	}

	$(function () {
		// preventing page from redirecting
		$("html").on("dragover", function (e) {
			e.preventDefault();
			e.stopPropagation();
			$("h1").text("Drag here");
		});

		$("html").on("drop", function (e) {
			e.preventDefault();
			e.stopPropagation();
		});

		// Drag enter
		$('.upload-area').on('dragenter', function (e) {
			e.stopPropagation();
			e.preventDefault();
			$("h1").text("Drop");
		});

		// Drag over
		$('.upload-area').on('dragover', function (e) {
			e.stopPropagation();
			e.preventDefault();
			$("upload_text").text("Drop");
		});

		// Drop
		$('.upload-area').on('drop', function (e) {
			e.stopPropagation();
			e.preventDefault();
			$("#upload_text").text("Upload");
			var file = e.originalEvent.dataTransfer.files;
			var fd = new FormData();
			fd.append('file', file[0]);
			uploadData(fd);
		});

		// Open file selector on div click
		$("#uploadLogo").click(function () {
			$("#file").click();
		});

	});

	$("input:file").change(function () {
		var fd = new FormData();
			var files = $('#file')[0].files[0];
			fd.append('file',files);
			del_picture()
			$.ajax({
				url: '{{route('group.updateIcon')}}',
				type: 'POST',
				data: fd,
				contentType: false,
				processData: false,
				cache: false,
				success: function(response){
					$("#group_icon").show()
					$("#internal_modal_image").show()
					$("#uploadfile > .redCrabShell").show();
					$("#logo_upload_cam").toggleClass('green')
					$("#group_icon").attr("src",response.src); 
					$("#internal_modal_image").attr("src",response.src);
				},
			});

	});


	$('#NameIconModal').on('hidden.bs.modal', function (e) {
		update_group_name()
	});
</script>
