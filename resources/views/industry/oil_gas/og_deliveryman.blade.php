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
	
 
<div class="d-flex" style="width: 100%; height: 75px;">
	<div style="padding-left:0" class="col align-self-center col-md-6">
		<h2 class="mb-0">Deliveryman Management: Oil & Gas</h2>
	</div>


	<div style="padding-right:0;margin-left: auto;margin-top:10px;"
		class="col col-auto align-self-right">
	</div>
</div>

<div style="padding-left:0;padding-right:0;padding-top:0" class="col-sm-12">
	<table id="vehicleManagementTable"
		class="table table-bordered dataTable no-footer">
		<thead class="thead-dark">
			<tr>
				<th class="text-center" style="width:30px">No</th>
				<th class="text-center" style="width:150px">Deliveryman ID</th>
				<th class="text-left" style="text-align:left">Name</th>
				<th class="text-center" style="width:130px"> Number Plate</th>
				<th class="text-center" style="width:30px"></th>
			</tr>
		</thead>
		<tbody>
		</tbody>
	</table>
</div>


<script>
vehicleManagementTable = $('#vehicleManagementTable').DataTable({
		"destroy": true,
		"processing": false,
		"serverSide": true,
		"autoWidth": false,
		"ajax": {
			url:"{{route('og_cadmin.deliveryman_table')}}",
			type: "POST",
        },
        columns: [
		{data: 'DT_RowIndex', name: 'DT_RowIndex'},
		{data: 'systemid', name: 'systemid'},
		{data: 'name', name: 'name'},
		{data: 'numberPlate', name: 'numberPlate'},
		{data: 'bluecrab', name: 'bluecrab'},
        ],
        "order": [],
        "columnDefs": [  
			{"className": "dt-center vt_middle slimcell", "targets": [0,1,3,4]},
			{"className":	"dt-left", targets:[2]},
			{"width":"30px","targets":[0]},	
			{"width":"50px","targets":[4]},	
			{ orderable: false, targets: [0,4]}
		],
});

</script>
