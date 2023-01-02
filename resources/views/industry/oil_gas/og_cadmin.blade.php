<style>
.butns{
	display: none
}

th, td{
	vertical-align: middle !important
}

td{
	text-align: center
}

#myProgress {
	width: 100%;
	background-color: #ddd;
}

#myBar {
	width: 10%;
	height: 30px;
	background-color: #4CAF50;
	text-align: center;
	line-height: 30px;
	color: white;
}
</style>
    
<div class="d-flex align-items-center" style="width: 100%; height: 75px;">
	<div style="padding-left:0" class="col align-self-center col-md-6">
		<h2 class="mb-0">Central Administration: Oil & Gas</h2>
	</div>

	<div style="padding-right:0;margin-left: auto;margin-top:0;"
		class="col col-auto align-self-right">

		<!-- <button class="btn btn-success sellerbutton"
		style="padding-left:0;padding-right:0;float:right;
			margin: 0px 0px 5px 0px;"
		id="addInvenProduct">+Product
		</button> -->

		<div id="branch_name"
			style="float:right;width:150px;">
			<h5 class="os-linkcolor text-center"
				data-toggle="modal" data-target="#myAllLocations"
				style="cursor:pointer;margin-bottom:0;padding-top:0"
				id="location_modal">All&nbsp;Location
			</h5>
		</div>

	</div>
</div>

<div style="padding-left:0;padding-right:0" class="col-sm-12">
	<table id="tableCadmin"
		class="table table-bordered dataTable no-footer">
		<thead class="thead-dark">
			<tr>
				<th class="text-center" style="width:30px">No</th>
				<th class="text-center" style="width:120px">Document No</th>
				<th class="text-center" style="width:60px">Date</th>
				<th class="text-center" style="width:130px">Delivery&nbsp;Order&nbsp;ID</th>
				<th class="text-center" style="width:auto">Storage</th>
				<th class="text-center" style="width:auto">Petrol Station</th>
				<th class="text-center" style="width:80px">Status</th>
				<th class="text-center" style="width:80px">Urgency</th>
				<th class="text-center" style="width:80px">Number&nbsp;Plate</th>
				<th class="text-center" style="width:30px">Approval</th>
			</tr>
		</thead>
		<tbody>
		</tbody>
	</table>
</div>
<br><br>

<!-- Modal -->
<div class="modal fade" id="myAllLocations" role="dialog" style="">
	<div class="modal-dialog modal-dialog-centered modal-md" role="document">
		<!-- Modal content-->
		<div class="modal-content bg-greenlobster">
			<div class="modal-header">
			<h3 class="mb-0">Location</h3>
		</div>
		<div class="modal-body location_link">
			<h5 style="cursor:pointer" class="active"
				onclick="display(this.id,this)" id="all"
				data-dismiss="modal">All&nbsp;Location</h5>

				@foreach ($branch_location as $key => $value)
				<h5 style="cursor: pointer;text-transform: capitalize"
					id="{{$value->id}}"
					onclick="display(this.id,this)"
					name="{{$value->branch}}"
					date="{{$value->created_at}}"
					class="location select_date_range loc_list_item 
						{{empty($value->direct) ? '':'direct_loc'}}
						{{$value->foodcourt == 1 ? 'foodcourt_loc':''}}
						{{empty($value->franchise)? '':'franchise_loc'}}" 
					data-dismiss="modal">
					{{$value->branch}}
				</h5>
				@endforeach
			</div>
		</div>
	</div>
</div>
</div>

<script>
var CATableData = {};
var tableCadmin;

	/*"columnDefs": [
				],*/


	tableCadmin = $('#tableCadmin').DataTable({
		"processing": false,
		"serverSide": true,
		"autoWidth": false,
		"ajax": {
			"url": "{{route('og_cadmin.dtable')}}",
			"type": "POST",      
			data: function ( d ) {
			   return  $.extend(d, CATableData);
			},
			'headers': {
			  'X-CSRF-TOKEN': '{{ csrf_token() }}'
		  },

		},
		columns: [
		    {data: 'DT_RowIndex', name: 'DT_RowIndex'},
			{data:	'source_doc', name:'source_doc'},
		   	{data:	'date', name:'date'},
		   	{data:	'delivery_id', name:'delovery_id'},
			{data:	'storage', name:'storage'},
			{data:	'petrol', name:'petrol'},
			{data:	'status',		name:'status'},
			{data:	'yellowcrab', name:'yellowcrab'},
			{data:	'yellowcrab', name:'yellowcrab'},
			{data:	'bluecrab', name:'bluecrab'},
		],
		"order": [],
		"columnDefs": [
			{width:"30px",   targets: [0,8,9]},	// No., yellowcrab, bluecrab
			{width:"120px",  targets: 1},		// Source Doc
			{width:"120px",  targets: 2},		// Source
			{width:"120px",  targets: [3,4]},	// Date, Delivery Order ID
			{width:"180px",  targets: 5},		// To
			{width:"80px",  targets: 7},		// Status
            {className: "dt-center", targets: [0,1,2,3,4,5,6,7,8,9]},
            {orderable: false, targets: [8,9]},	// yellowcrab, bluecrab
		],
	});


	function display(id, e) {
		location_name = $(e).text();
		$("#location_modal").html(location_name);
		CATableData.location_id = id;
		tableCadmin.ajax.reload();
	}


</script>
