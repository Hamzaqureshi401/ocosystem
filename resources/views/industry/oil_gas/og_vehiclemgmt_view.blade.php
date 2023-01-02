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
	height: 30px;
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
#masterBackBar {
	position:relative;
	z-index:0;
	height: 30px;
}

#myProgress {
	position:absolute;
	z-index:1;
	width: 100%;
	height: 30px;
	background-color: #ddd;
	color: black;
	padding-top: 2px;
	text-align: right;
	padding-right:10px;
}

#myBar {
	position:absolute;
	z-index:2;
	width: 10%;
	opacity:50%;
	height: 30px;
	text-align: center;
}

.reg_font {
	font-weight:normal !important;
	padding-top: 4px !important;
	padding-bottom: 4px !important;
}
.slim_cell {
	padding-top: 2px !important;
	padding-bottom: 2px !important;
}
.fat_cell {
	padding-top: 8px !important;
	padding-bottom: 8px !important;
}
</style>


<div class="modal fade" id="fleetmgmt_modal"  tabindex="-1" 
	role="dialog" aria-labelledby="staffNameLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-lg  mw-75 w-50" role="document">
	<div class="modal-content modal-inside bg-greenlobster">
		<div class="modal-header" >
			<h3 class="modal-title text-white mb-0"  id="statusModalLabel">
			Fleet Management
			</h3>
		</div>
		<div class="modal-body">
			<div class="row align-items-center" id="flt_tank">
				<div class="col-md-2">
					<i class="fas fa-gas-pump" style="font-size:30px"></i>
				</div>
				<div class="col-md-10">
					<h5 class="mb-0"><b>15.04 &ell;<b></h5>
				</div>
			</div>
			<div id="flt_gps">
				<div class="row">
				<div class="col-md">
				<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d7967.519440271063!2d101.70750072348626!3d3.157927753374491!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31cc37d12d669c1f%3A0x9e3afdd17c8a9056!2sPetronas%20Twin%20Towers!5e0!3m2!1sen!2smy!4v1598536660526!5m2!1sen!2smy" width="400" height="300" frameborder="0" style="border:0;" allowfullscreen="" aria-hidden="false" tabindex="0"></iframe>
				</div>
				<div class="col-md pr-0" style="width:100%">
					<h5 class="mb-0"><b>Location</b></h5>
					<div class="row pl-0 pr-0 pb-0 pt-2">
						<div class="col-md-4 pl-0">
							<h5 class="mb-0 text-right">Longitude</h5>
						</div>
						<div class="col-md-8 pr-0">
							<h5 class="mb-0 text-left">
								<b>101.707492&deg;</b>
							</h5>
						</div>
					</div>
					<div class="row pl-0 pr-0 pb-0 pt-2">
						<div class="col-md-4 pl-0">
							<h5 class="mb-0 text-left">Latitude</h5>
						</div>
						<div class="col-md-8 pr-0">
							<h5 class="mb-0 text-right">
								<b>3.158432&deg;</b>
							</h5>
						</div>
					</div>
				</div>
				</div>
			</div>
		</div>
	</div>
	</div>
</div>
	
	
<div class="modal fade" id="myVehicle"  tabindex="-1" 
	role="dialog" aria-labelledby="staffNameLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered  mw-75 w-50" role="document">
	<div class="modal-content modal-inside bg-greenlobster"
		style="width:400px">
		<div class="modal-header" >
			<h3 class="modal-title text-white"  id="statusModalLabel">
			Vehicle
			</h3>
		</div>
		<div class="modal-body">
		<form id="vehicle_add">
		<ul style="padding: 0;margin: 0;">
			@for ($x = 1 ; $x <= 8; $x++)
			<li style="display:flex;align-items: center;
				justify-content: center;margin-bottom:5px">
				<span>Compartment {{$x}}&nbsp;&nbsp;</span>
				<input class="form-control vehicle_text_input text-right"
					type="text" style="width: 30%;margin: 0px 5px 0 10px;"
					name='compt_details[]' input-coparment-id="{{$x}}" />
				<span style="font-size:20px">&nbsp;&ell;</span>
			</li>
			@endfor
		</ul>
		</form>
		</div>
	</div>
	</div>
</div>
 
<div class="d-flex py-2"
	style="padding-top:0 !important; padding-bottom:0 !important">
	<div style="padding-left:0" class="col align-self-center col-md-8">
		<h2 class="mb-0">Vehicle Management: Oil & Gas</h2>
	</div>

	<div style="padding-right:0;margin-left: auto;margin-top:0;"
		class="col col-auto align-self-right">

		<button class="btn btn-success sellerbutton"
			style="padding-left:0;padding-right:0;float:right;
				margin: 0px 0px 5px 0px;"
			data-target="#myVehicle" data-toggle="modal"
			id="addVehicle ">+Vehicle 
		</button>
	</div>
</div>

<div style="padding-left:0;padding-right:0;padding-top:0" class="col-sm-12">
	<table id="vehicleManagementTable"
		class="table table-bordered dataTable no-footer">
		<thead class="thead-dark">
			<tr>
				<th class="text-center" style="width:30px">No</th>
				<th class="text-center" style="width:100px">Number&nbsp;Plate</th>
				<th class="text-center" style="width:80px">Ownership</th>
				<th class="text-center">Deliveryman</th>
		
				<th class="text-center fat_cell">1</th>
				<th class="text-center fat_cell">2</th>
				<th class="text-center fat_cell">3</th>
				<th class="text-center fat_cell">4</th>
				<th class="text-center fat_cell">5</th>
				<th class="text-center fat_cell">6</th>
				<th class="text-center fat_cell">7</th>
				<th class="text-center fat_cell">8</th>

				<th class="text-center pt-0 pb-0" style="width:30px" >
				<i style="color:white;font-size:19px" class="fas fa-truck"></i>
				</th>
				<th class="text-center" style="width:30px" >DO</th>
				<th class="text-center" style="width:30px" ></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>1</td>
				<td>BKX 1234</td>
				<td>Own</td>
				<td>Stephen Tan</td>
				<td>
					<div id="myProgress">
						<div id="myBar">10%&nbsp;</div>
					</div>
				</td>

				<td>
					<div id="myProgress">
						<div id="myBar">10%&nbsp;</div>
					</div>
				</td>

				<td>
					<div id="myProgress">
						<div id="myBar">10%&nbsp;</div>
					</div>
				</td>
			
				<td>
					<div id="myProgress">
						<div id="myBar">10%&nbsp;</div>
					</div>
				</td>

				<td>
					<div id="myProgress">
						<div id="myBar">10%&nbsp;</div>
					</div>
				</td>

				<td>
					<div id="myProgress">
						<div id="myBar">10%&nbsp;</div>
					</div>
				</td>

				<td>
					<div id="myProgress">
						<div id="myBar">10%&nbsp;</div>
					</div>
				</td>

				<td>
					<div id="myProgress">
						<div id="myBar">10%&nbsp;</div>
					</div>
				</td>

				<td>
					<img style="width:25px;height:25px;cursor:pointer" 
						id="fleetmgmt_btn"
						class="mt=0 mb-0 text-center"
						data-toggle="modal"
						data-target="#fleetmgmt_modal"
						src="/images/yellowcrab_25x25.png"/>
				</td>

				<td>
					<img style="width:25px;height:25px;cursor:pointer" 
						class="mt=0 mb-0 text-center"
						src="/images/bluecrab_25x25.png"/>
				</td>

				<td>
					<img style="width:25px;height:25px;cursor:pointer"
						class="mt=0 mb-0 text-center"
						src="/images/redcrab_25x25.png"/>
				</td>
			</tr>
		</tbody>
	</table>
</div>
<style>
.active_deliveryman {color:darkgreen}
</style>

<div class="modal fade" id="confirmModal" tabindex="-1" 
	role="dialog" aria-labelledby="logoutModalLabel" aria-modal="true">
<div class="modal-dialog modal-dialog-centered  mw-75 w-50" role="document">
	<div class="modal-content modal-inside bg-greenlobster">
	<div style="border:0" class="modal-header"></div>
	<div class="modal-body text-center">
		<h5 class="modal-title text-white" id="logoutModalLabel">
		Do you really want to delete?</h5>
	</div>
	<div class="modal-footer"
		style="border-top:0 none; padding-left: 0px;
			padding-right: 0px;">
		<div class="row" style="width: 100%; padding-left: 0px;
			padding-right: 0px;">
			<div class="col col-m-12 text-center">
				<a class="btn btn-primary" href="#!" style="width:100px" 
				onclick="delete_confirm.action()" data-dismiss="modal">
					Yes
				</a>
				<button type="button" class="btn btn-danger"
					data-dismiss="modal" style="width:100px">No
				</button>
			</div>
		</div>
	</div>
	</div>
</div>
</div>

<div class="modal fade" id="vehicle_modal" aria-modal="true" >
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <!-- Modal body -->
                <div class="modal-body">
					<input class="form-control input-30" id='vehicle_plate'
						placeholder="Number Plate"
						style="margin-left: 2px;width: 100%;display: inline-flex;" />
                </div>
            </div>
        </div>
</div>

<div id="res"></div>
<script>
vehicleManagementTable = $('#vehicleManagementTable').DataTable({
	"processing": true,
	"serverSide": true,
	"autoWidth": false,
	"ajax": {
		"url": "{{route('vehicle.table')}}",
		"type": "POST",
		'headers': {
			'X-CSRF-TOKEN': '{{ csrf_token() }}'
		},
	},
	columns: [
		{data: 'DT_RowIndex', name: 'DT_RowIndex'},
		{data: 'number', name:'number'},
		{data: 'ownership', name:'ownership'},
		{data: 'deliveryman_user_id',  name:'deliveryman_user_id'},
		@for ($x = 1; $x <=8; $x++)
			{data: "c{{$x}}_max", name: "c{{$x}}_max"},
		@endfor
		{data: 'map', name: 'map'},
		{data: 'blueCrab', name: 'blueCrab'},
		{data: 'redCrab', name: 'redCrab'},
	],
	order:[],
	
	"columnDefs": [
		{"targets": [-1,-2,-3], 'orderable' : false},
		//{"targets": [0,1,2,3,4,5,6,7,8,9,10,11], 'className':function(data,type,row) { if (row.DT_RowIndex != 0) return "reg_font"; }},
		{"targets": "_all", 'className': "reg_font"},
		{"targets": [4,5,6,7,8,9,10,11], 'className': "slim_cell"},
	]
});
$('#myVehicle').on('hidden.bs.modal', function (e) {
	addVehicle();
});

function addVehicle() {
	
	//iif ($('input[input-coparment-id=1]').val() == '') {
	//	return;
	//}
	input_ = $('.vehicle_text_input');
	var allow = false;
	input_.each(function() {
		console.log("VAL",$(this).val())
		if ($(this).val() != '') {
			allow = true;
		}
	});
	
	if (allow == false) {
		$('.vehicle_text_input').attr('disabled',true);
		$('input[input-coparment-id=1]').removeAttr('disabled');
		return;
	}

	const form = $('#vehicle_add')[0];
	const formData = new FormData(form);
	$.ajax({
		url: "{{route('vehicle.new')}}",
		type: "POST",
		enctype: 'multipart/form-data',
		processData: false,  // Important!
		contentType: false,
		cache: false,
		data: formData,
		success: function (response) {
			vehicleManagementTable.ajax.reload();
			$('#vehicle_add')[0].reset()

			$('.vehicle_text_input').attr('disabled',true);
			$('input[input-coparment-id=1]').removeAttr('disabled');

			$("#res").html(response);
		}, error: function (e) {
			console.log(e.message)
		}
	});
}


function deliverymanSelectFunc(id,fKey) {
	$.post("{{route('deliveryman.getList')}}", {
			"selected_id":id,
			'fKey':fKey
	}).done(function(res) {
		$("#res").html(res);
		$("#delivermanModel").modal('show');
	});
}

function select_deliveryman(user_id, key) {
	$.post("{{route('vehicle.selectDeliveryman')}}", {
			"user_id":user_id,
			'ogVehicle_id':key
	}).done(function(res) {
		$("#delivermanModel").modal('hide');
		$("#res").html(res);
		vehicleManagementTable.ajax.reload();
	});
}
function deleteDelivermanFunc(id) {
	$.post("{{route('vehicle.selectDeliveryman')}}", {
			'ogVehicle_id':id
	}).done(function(res) {
		$("#res").html(res);
		vehicleManagementTable.ajax.reload();
	});

}

delete_confirm = {
		id: null,
		action: null,
		display_confirm: function(url) {
				$('#confirmModal').modal('show')
		},
		actionx: function() {
				this.action();
				$('#confirmModal').modal('hide');
		}
}

function delete_deliverymanFunc(id) {
	delete_confirm.id = id
	delete_confirm.action = function() {
		$.post("{{route('vehicle.deleteDeliveryman')}}", {
			'ogVehicle_id':id
		}).done(function(res) {
			vehicleManagementTable.ajax.reload();
			$("#res").html(res);
		});
	}

	delete_confirm.display_confirm();
}

//input-coparment-id
$('.vehicle_text_input').attr('disabled',true);
$('input[input-coparment-id=1]').removeAttr('disabled');

$('.vehicle_text_input').on('change', function(e) {
	
	e = e.target;
	next_comparment = parseInt($(e).attr('input-coparment-id')) + 1;
	
	if ($(e).val() != '') {
		$(`input[input-coparment-id=${next_comparment}]`).removeAttr('disabled');
	}

});

var ogVehicleId = 0;

$('#vehicle_modal').on('hidden.bs.modal', function (e) {
	 updateNumberPlate($("#vehicle_plate").val(), ogVehicleId);
	 $("#vehicle_plate").val('');
	 ogVehicleId = 0;
 });

ogVehicleNumberUpdate = function(id, data) { 
	ogVehicleId = id;
	$("#vehicle_plate").val(data)
}

updateNumberPlate = function(data, fk) {
	
	if (data == '') {
		return;
	}

	$.post('{{route('vehicle.updateNumberPlate')}}',{
		data: data,
		ogVehicle_id: fk
	}).done(function(res) {
		vehicleManagementTable.ajax.reload();
		$("#res").html(res);
	});	
}
</script>
