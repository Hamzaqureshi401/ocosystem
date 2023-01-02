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

#selected-location-datatable.dataTable tbody tr {
	border-color: rgba(26, 188, 156, 0.7) !important;
	background-color: rgba(26, 188, 156, -0.3) !important;
}

.btn-link:hover {
	text-decoration: none;
}

#selected-location-datatable.dataTable thead th, table.dataTable thead td {
	padding:0px;
	border-bottom: 0px;
}

#selected-location-datatable.dataTable tbody th, #selected-location-datatable.dataTable tbody td{
	padding:0px !important;
}

#selected-location-datatable.dataTable.no-footer {
	border-bottom: 0px !important;
}

#selected-location-datatable.dataTable tbody tr.selected {
	/*background-color: white !important;*/
	color:green !important;
}

#selected-location-datatable.dataTable tbody tr.selected h5{
	font-weight: 600;
}
</style>

<div id="default-content">
	<?php
		$fmerchantId = $_REQUEST['franchisemerchant_id'];
		$fmerchant = \App\Models\FranchiseMerchant::where('id', $fmerchantId)->first();
		//dd($fmerchant);
		if(!is_null($fmerchant)){
			$merchant = \App\Models\Merchant::where('company_id', $fmerchant->franchisee_merchant_id)->first();
			if(!is_null($merchant)){
				$company = \App\Models\Company::where('id', $merchant->company_id)->first();
			}
		}
	?>


	<div class="row py-2 align-items-center"
		style="padding-top:0 !important;padding-bottom: 0px !important" >
		<div class="col" style="width:80%">
			<h2 class="mb-0">Franchisee Terminal List</h2>
		</div>
		<div style="width: 25%;" >
			<h5 class="mb-0">{{$company->name}}</h5>
			<h5 class="mb-0">{{$company->systemid}}</h5>
		</div>
		<div class="col col-auto align-self-center">
			<button class="btn btn-success btn-log sellerbutton mr-0"
				style="padding-left:0;padding-right:0"
				id="add_new_tab_alliance" data-toggle="modal" data-target="#location">
				<span>+Terminal</span>
			</button>
		</div>
	</div>


	<table class="table table-bordered" id="franchiseTerminal" style="width:100%;">
		<thead class="thead-dark">
			<tr>
				<th style="width:30px; vertical-align:middle">No</th>
				<th style="width:110px; vertical-align:middle">Location ID</th>
				<th style="vertical-align:middle">Branch</th>
				<th style="width:100px; vertical-align:middle;">Terminal ID</th>
				<th style="width:30px; vertical-align:middle"></th>
			</tr>
		</thead>
		<tbody>
			<th style="text-align: center"></th>
			<th style="text-align: center"></th>
			<th> </th>
			<th style="text-align: center"></th>
			<th style="text-align: center"></th>
		</tbody>
	</table>
</div>

<div  class="modal fade" id="location" tabindex="-1"
	role="dialog" aria-labelledby="exampleModalCenterTitle"
	aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered " role="document">
	<div class="modal-content bg-greenmidlobster" style="height: auto; ">
		<div class="modal-header">
			<h3 class="modal-title">Franchise Location</h3>
		</div>
		<div class="modal-body">
			<table id="selected-location-datatable" style="width:100%;">
				<thead>
				<tr><th></th></tr>
				</thead>
			</table>
		</div>
	</div>
	</div>
</div>

</div>

@section('js')
<div id="showEditInventoryModal"></div>

<script type="text/javascript">
$(document).ready(function() {
});
var franchiseId;
var urlString;

function parseURLParams(url) {
	var queryStart = url.indexOf("?") + 1,
		queryEnd   = url.indexOf("#") + 1 || url.length + 1,
		query = url.slice(queryStart, queryEnd - 1),
		pairs = query.replace(/\+/g, " ").split("&"),
		parms = {}, i, n, v, nv;

	if (query === url || query === "") return;

	for (i = 0; i < pairs.length; i++) {
		nv = pairs[i].split("=", 2);
		n = decodeURIComponent(nv[0]);
		v = decodeURIComponent(nv[1]);

		if (!parms.hasOwnProperty(n)) parms[n] = [];
		parms[n].push(nv.length === 2 ? v : null);
	}
	return parms;
}


urlString = location.href;
urlParams = parseURLParams(urlString);
merchantId = urlParams.franchisemerchant_id[0];
locationId = '';

var franchiseDataTable = $('#franchiseTerminal').DataTable({
	pageLength: 10,
	bPaginate: true,
	info: false,
	ordering: true,
	responsive: true,
	processing: true,
	serverSide: true,
	bFilter: true,
	"ajax": "{{route('data.ajax.getFranchiseeTerminalList')}}?merchant_id="+merchantId,
	initComplete: function(setting, json) {

	},
	columns: [
		{
			// no
			className: "text-center  vertical-center",
			mRender: function(data, type, full) {
				return full.indexNumber;
			}
		},
		{
			// location ID
			className: "text-center  vertical-center",
			mRender: function(data, type, full) {
				return full.location_systemid;
			}
		},
		{
			// branch name
			className: "text-left  vertical-center",
			mRender: function(data, type, full) {
				return full.branch;
			}

		},
		{
			// terminal ID
			className: "text-center  vertical-center",
			mRender: function(data, type, full) {
				return full.terminal_systemid;
			}
		},
		{
			// Delete
			className: "text-center",
			mRender: function(data, type, full) {
				return '<div data-field="deleted" \
					onclick="removeFranchiseTerminalModel('+
					full.terminal_systemid+')" class="remove"> \
					<img class="" src="/images/redcrab_50x50.png" \
					style="width:25px;height:25px;cursor:pointer"/>\
					</div>';
			}
		},
	],
	/*
	"order": [0, 'desc'],
	*/
	"columnDefs": [{'orderable' : false },
		{orderable: false, targets: [4]},
	]

});
var locationDatatable = $('#selected-location-datatable').DataTable({
	"initComplete": function(setting, json) {
		console.log("HELLO");
	}
});
$("#add_new_tab_alliance").click(function(){
	locationDatatable.destroy();
	locationDatatable = $('#selected-location-datatable').DataTable({
		"paging":   false,
		"ordering": false,
		"info":     false,
		"searching": false,
		"ajax": "{{route('data.ajax.getTerminalLocations')}}?merchant_id="+merchantId,
		"initComplete": function(setting, json) {
		
			$('#selected-location-datatable tbody').on( 'click', 'tr', function () {
				locationId = $(this).find("h5").data("id");
				$('#selected-location-datatable tbody tr').removeClass('selected');
				// $(this).toggleClass('selected');
				if (locationId != '') {
					$('#selected-location-datatable').find('[data-id="'+locationId+'"]').parents('tr').addClass('selected');
				}
			});	
		},
		columns: [
			{
				// branch
				mRender: function(data, type, full) {
					var branch = full.branch == null ? 'Branch' : full.branch;
					return '<h5 style="margin-bottom:5px;margin-top:5px;cursor:pointer;" data-id="'+full.id+'" data-dismiss="modal">'+branch+'</h5>';
				}
			},
		]
	});
	if ( ! locationDatatable.data().any() ) {
		locationDatatable.destroy();
	}
});

$('#location').on('hidden.bs.modal', function (e) {

	var selectedLocationId = '';
	locationDatatable.rows('.selected').data().each(function(location, index){
		selectedLocationId = location.id;
	});
	$.ajax({
		url:'{{route('data.ajax.saveFranchiseeTerminalList')}}',
		type: 'post',
		data: {
			locationId: selectedLocationId,
			merchantId: merchantId,
		},
		dataType:'json',
		success: function(response) {
			if (response.status === 'true') {
				if ($.fn.DataTable.isDataTable('#franchiseTerminal') ) {
					$("#franchiseTerminal").DataTable().ajax.reload(null, false );
					$('#selected-location-datatable tbody tr').removeClass('selected');
				}
			}
		}
	});

});

function removeFranchiseTerminalModel(id) {
   $.ajax({
	   url: "{{route('franchiseTerminal.edit.modal')}}",
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
