@extends('layouts.layout')

@section('content')
<div id="landing-view">
<style>
h3 {
	margin-bottom:0 !important;
}

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

.btns:hover {
    background-color: transparent !important;
    color: #34dabb !important;
    width: 100%;
}
.btns {
    border: 1px solid #a0a0a0;
    color: #a0a0a0;
}

/* remove small icons from input number */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
	-webkit-appearance: none;
	margin: 0;
}

/* Firefox */
input[type=number] {
	-moz-appearance:textfield;
}

.month_table > tr > th {
	font-size: 22px;
	color: white;
	background-color: rgba(255, 255, 255, 0.5);
}

.month_table  > tr > td {
	color: #fff;
	font-weight: 600;
	border: unset;
	font-size: 20px;
	cursor: pointer;
}

.date_table > tbody > tr > th {
	font-size: 22px;
	color: white;
	background-color: rgba(255, 255, 255, 0.5);
}

.date_table > tbody > tr > td {
	color: #fff;
	font-weight: 600;
	border: unset;
	font-size: 20px;
	cursor: pointer;
}

table.dataTable tbody td{
	border-left: 1px solid #dee2e6;
	border-right: 1px solid #dee2e6;
	border-top: none;
	/*border-bottom: none;*/
}

.btn-green {
	background-color: green !important;
	color: #fff !important;
	box-shadow: none !important;
	border: 0px !important;
}

.btn-green:focus {
	background-color: green !important;
	color: #fff !important;
	box-shadow: none !important;
	border: 0px !important;
}

.bg-blue {
	background-color: #007bff;
	color: #fff;
}

.date_table1 > tbody > tr > th ,
{
	font-size: 22px;
	color: white;
	background-color: rgba(255, 255, 255, 0.5);
}

.date_table1 > tbody > tr > td {
	color: #fff;
	font-weight: 600;
	border: unset;
	font-size: 20px;
	cursor: pointer;
}

.selected_date {
   /*/ color: #fff !important;/*/
	color: #008000 !important;
	font-weight: 600 !important;
}

.selected_date1 {
	color: #008000 !important;
	font-weight: 700 !important;
}

#Datepick .d-table {
	display: -webkit-flex !important;
	display: -ms-flexbox !important;
	display: flex !important;
}

.dataTables_filter input {
	width: 300px;
}

.greenshade {
	height: 30px;
	background-color: green; /* For browsers that do not support gradients */
	background-image: linear-gradient(-90deg, green, white); /* Standard syntax (must be last) */
}
.dt-button{
	display: none;
}

.bg-purplelobster{
	background-color: rgba(26, 188, 156, 0.7);
	border-color: rgba(26, 188, 156, 0.7);
}

/*//for calender short day*/
.shortDay ul{
	llist-style: none;
	background-color: rgba(255, 255, 255, 0.5);
	position: relative;
	left: -75px;
	width: 124%;
	height: 55px;
	line-height: 42px;
 }

.shortDay ul > li{
	font-size: 22px;
	color: white;
	font-weight: 700 !important;
	/* background-color: #2b1f1f; */
	padding: 5px 24px;
	text-align: left !important;
}

.list-inline-item:not(:last-child){
	margin-right: 0 !important;
}

.modal-content{
	overflow: hidden;
}

.modal-inside .row {
	margin: 0px;
	color: #fff;
	margin-top: 15px;
	padding: 0px !important;
}

th, td {
	vertical-align: middle !important;
	text-align: center
}

td {
	text-align: center;
}

.modalBtns {
	margin-top: 5px
}

.slim-cell {
	padding-top: 2px !important;
	padding-bottom: 2px !important;
}
</style>

<div id="default-content">
	<div class="row py-2"
		style="height:75px;padding-top:0 !important;padding-bottom: 0 !important">
		<div class="col-md-6  align-self-center" style="">
			<h2 class="mb-0">Franchise Location Product</h2>
		</div>

		<div class="col-md-2 align-self-center " style="">
			<h5 class="m-0 os-linkcolor" onclick="date_picker(this)"
				id="date_pick" style="cursor:pointer">Date
			</h5>
		</div>

		<div class="col-md-2 align-self-center" style="">
			<h5 class="m-0">{{$franchise->name ?? "Franchise Name"}}</h5>
			<h5 class="m-0">{{$franchise->systemid}}</h5>
		</div>

		<div class="col-md-2 align-self-center" style="">
			<h5 class="m-0">{{$location->branch ?? "Branch"}}</h5>
			<h5 class="m-0">{{$location->systemid}}</h5>
		</div>
	</div>


	<table class="table table-bordered datatable" style="width:100%" id="franchise">
		<thead class="thead-dark">
		<tr>
			<th style="width:30px;text-align: center;">No</th>
			<th style="width:100px;text-align: center;">Product ID</th>
			<th>Product Name</th>
			<th class="text-center">Minimum</th>
			<th class="text-center">Price</th>
			<th class="text-center">Maximum</th>
			<th class="text-center">Loyalty</th>
			<th class="text-center"
				style="background-color:#ff735f">
				Stock Level
			</th>
			<th class="text-center">Value</th>
			<th style="width:80px;margin:auto;text-align:center;padding:2px;">
				<button type="button" onclick="select_all_btn(this)"
					class="active_product highlight-off btn btn12 btns {{$is_all_active == true ? 'active_button_activated':''}}"
					data-state="{{$is_all_active}}"
					data-status="none" id="all" style="width:75px">
					All
				</button>
			</th>
		</tr>
		</thead>
		<tbody></tbody>
	</table>
</div>
<br><br>

 <style>
	.vt_middle{vertical-align: middle !important;}
	.active_button:hover,.active_button:active {
		background: transparent;
		color: #34dabb;
		border: 1px #34dabb solid;
		font-weight: bold;
	}
	.active_button {
		background: transparent;
		color: #ccc ;
		border: 1px #ccc solid;   
	}
	.active_button_activated{
		background: transparent;
		color: #34dabb;
		border: 1px #34dabb solid;
		font-weight: bold;       
	}
	.slim_cell{
		padding-top:2px !important;
		padding-bottom:2px !important;
	}


</style>  

<div class="modal fade" id="normalPriceModal"  tabindex="-1"
	role="dialog" aria-labelledby="staffNameLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered  mw-75 w-50"
		role="document">
		<div class="modal-content modal-inside bg-greenlobster">
		<div class="modal-header" >
			<h3 class="mb-0 modal-title text-white"
				id="statusModalLabel">Price Range
			</h3>
		</div>
		<div class="modal-body">
			<div class='text-center col-8' style="margin:auto">
				<input type="text" id="retail_price_normal_fk"
					style="text-align:right" class="form-control"
					placeholder='0.00'/>
				<input type="hidden" id='retail_price_normal' />
			</div>
		</div>
		</ul>
		<!-- div class="modal-footer" style="border:0;">
		</div --->
		</div>
	</div>
</div>

<div class="modal fade" id="showDateModal" tabindex="-1"
  role="dialog" aria-labelledby="staffNameLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered  mw-75 w-50" role="document">
    <div class="modal-content modal-inside bg-greenlobster">
      <div class="modal-body text-center"
	  	style="min-height: 485px;max-height:485px">
        <div class="row">
          <div class="col-md-2">
            <i class="prev-month fa fa-chevron-left fa-3x"
            style="cursor:pointer;display: inline-flex;"></i>
          </div>
          <div class="col-md-8 d-flex align-items-center justify-content-center">
            <div class="mb-0 month-year text-center text-white"></div>
          </div>
          <div class="col-md-2">
            <i style="cursor:pointer"
            class="next-month fa fa-chevron-right fa-3x"></i>
          </div>
        </div>
        <div class="row">
          <div class="shortDay">
            <ul>
              <li class="list-inline-item">S</li>
              <li class="list-inline-item">M</li>
              <li class="list-inline-item">T</li>
              <li class="list-inline-item">W</li>
              <li class="list-inline-item">T</li>
              <li class="list-inline-item">F</li>
              <li class="list-inline-item">S</li>
            </ul>
          </div>
        </div>
        <table class="table date_table">
          <tr style="display: none;">
            <th>S</th>
            <th>M</th>
            <th>T</th>
            <th>W</th>
            <th>T</th>
            <th>F</th>
            <th>S</th>
          </tr>
        </table>
      </div>
    </div>
    <form id="status-form" action="{{ route('logout') }}"
      method="POST" style="display: none;">
      @csrf
    </form>
  </div>
</div>



<input id="date" type="hidden"/>
<div id="res"></div>
@section('scripts')
 <div id="showEditInventoryModal"></div>

<script src="{{asset('/js/osmanli_calendar.js')}}"></script>
<script type="text/javascript">

var franchiseid = {{$franchise->id}};
var	locationid = {{$location->id}};

var tableData = {};
tableData['franchiseid'] = franchiseid;
tableData['locationid'] = locationid;

var franchiseTable = $('#franchise').DataTable({
	"processing": false,
	"serverSide": true,
	"ajax": {
	"url": "{{route('franchise.location_price.table')}}",
		"type": "POST",
		data: function ( d ) {
			return  $.extend(d, tableData);
		},
		'headers': {
			'X-CSRF-TOKEN': '{{ csrf_token() }}'
		},
	},
	columns: [
		{data: 'DT_RowIndex', name: 'DT_RowIndex'},
		{data: 'product_systemid', name: 'systemid'},
		{data: 'product_name', name: 'product_name'},
		{data: 'product_lower', name: 'product_lower'},
		{data: 'product_price', name: 'product_price'},
		{data: 'product_upper', name: 'product_upper'},
		{data: 'product_loyalty', name: 'product_loyalty'},
		{data: 'product_stock', name: 'product_stock'},
		{data: 'product_value', name: 'product_value'},
		{data: 'active', name: 'active'},
	],
	"order": [0, 'desc'],
	"columnDefs": [
		{"width": "30px", "targets": 0},
		{"width": "120px", "targets": 1},
		{"width": "100px", "targets": [3,4,5]},
		{"width": "100px", "targets": [6,7,8]},
		{"className": "dt-left vt_middle", "targets": [2]},
		{"className": "dt-right vt_middle", "targets": [3,4,5,8]},
		{"className": "dt-center vt_middle", "targets": [0,1,3,5,6,7,8,6,7]},
		{"className": "vt_middle", "targets":[2]},
		{"className": "slim-cell", "targets":[-1]},
		{orderable: false, targets: [-1]},
	],
});

var f_pid = null;

low_price 	= null;
high_price 	= null;
validation = null;
var hardwareData = {!! json_encode($og_controller, true) !!}
updatePrice = function(val, fr_pid, lp, hp, v) {
	
	f_pid = fr_pid;
	low_price = parseInt(lp);
	high_price = parseInt(hp);
	validation = v;

	if (val != '')
		$("#retail_price_normal_fk").val((val/100).toFixed(2));
	else
		$("#retail_price_normal_fk").val('');

	$("#retail_price_normal").val(val);
	$("#normalPriceModal").modal('show');
}

$('#normalPriceModal').on('hidden.bs.modal', function (e) {
	val_	= $("#retail_price_normal").val();
	updateFieldAjax('price', val_, f_pid);
	f_pid	= null;
	low_price = null;
	high_price = null;
	validation = null;
});

activate_func = function(id) {
	updateFieldAjax('active', 0, id);
}

updateFieldAjax = function(key, val, f_id) {
	$.post('{{route('franchise.location_price.update')}}', {
		"field"			: key,
		"data"			: val,
		"location_id"	: locationid,
		"f_id"			: f_id
	}).done(function (res) {

		franchiseTable.ajax.reload();
		$("#res").html(res.output);
		if (res.fuelGrade != undefined) {
		 	fuelgrades = res.fuelGrade;
			hardwareData.forEach((data) => {
				@if (env('PTS_MODE') == 'local')
					pumpSetFuelGrades(fuelgrades, data.ipaddress)
				@else
					pumpSetFuelGrades(fuelgrades, data.public_ipaddress)
				@endif
			});
		 }
	});
}


function atm_money(num) {
	if (num.toString().length == 1) {
		return '00.0' + num.toString()
	} else if (num.toString().length == 2) {
		return '00.' + num.toString()
	} else if (num.toString().length == 3) {
		return '0' + num.toString()[0] + '.' + num.toString()[1] +
			num.toString()[2];
	} else if (num.toString().length >= 4) {
		return num.toString().slice(0, (num.toString().length - 2)) +
			'.' + num.toString()[(num.toString().length - 2)] +
			num.toString()[(num.toString().length - 1)];
	}
}

select_all_btn = function(e) {
	tableData['all_btn_state'] =  $(e).attr('data-state'); 
	$.post('{{route('franchise.location_price.toggle_all')}}',tableData).done(function(res) {
		franchiseTable.ajax.reload();
		$("#res").html(res);
	});
	$(e).attr('data-state', ($(e).attr('data-state') == 0 ? 1:0) ); 
	$(e).toggleClass('active_button_activated');
}

filter_price('#retail_price_normal_fk' , '#retail_price_normal')

function filter_price(target_field,buffer_in) {
	$(target_field).off();
	$(target_field).on( "keydown", function( event ) {
		event.preventDefault()
		if (event.keyCode == 8) {
			$(buffer_in).val('')
			$(target_field).val('')
			return null
		}	
		if (isNaN(event.key) ||
		$.inArray( event.keyCode, [13,38,40,37,39] ) !== -1 ||
		event.keyCode == 13) {
			if ($(buffer_in).val() != '') {
				$(target_field).val(atm_money(parseInt($(buffer_in).val())))
			} else {
				$(target_field).val('')
			}
			return null;
		}

		const input =  event.key;
		old_val = $(buffer_in).val()
		
		if (old_val === '0.00') {
			$(buffer_in).val('')
			$(target_field).val('')
			old_val = ''
		}
		
		$(buffer_in).val(''+old_val+input)
		$(target_field).val(atm_money(parseInt($(buffer_in).val())))
		
	});

	$(target_field).focusout(function (event) {
		valdate = validation_input_price(parseInt($(buffer_in).val()));
		console.log("Validate_result", valdate);
		if (valdate == false) {
			$(buffer_in).val(0)
			$(target_field).val(atm_money(0))
		}
	});
}

function validation_input_price(val) {
	//low_price = null;
	//high_price = null;
	
	if ( validation	== 'bypass') {
		return true;
	}

	if (low_price <= val && high_price >= val) {
		return true;
	}

	return false;
}

/////////////////////////////////////////////////////////////////////
function pumpSetFuelGrades(fuelgrades, ipaddr) {
	var ret = null;
	console.log('pumpSetFuelGrades: fuelgrades='+fuelgrades);

	$.ajax({
		url: "/set-fuel-grades-configuration",
		type:"POST",
		data: {
			'fuelgrades' : fuelgrades,
			'ipaddr' : ipaddr
		},
		dataType:"JSON",
		success: function(response) {
			console.log(JSON.stringify(response));
			ret = true;
		},
		error: function(response) {
			console.log(JSON.stringify(response));
			console.log('****** Error: set-fuel-grades-configuration ! *****');
			ret = false;
		}
	});
	return ret;
}
///////////////////////////////////////////////////////////////////////
var start_date_dialog = osmanli_calendar;

osmanli_calendar.MAX_DATE = new Date();
osmanli_calendar.DAYS_DISABLE_MAX = "on";

osmanli_calendar.MIN_DATE =	new Date("{{date("Y-m-d", strtotime($approvedAt))}}");
osmanli_calendar.DAYS_DISABLE_MIN = "on";

function date_picker(e) {
  
 from_input = $('#date').val();
  jQuery('#showDateModal').modal('show');

  if (from_input != ''){
	start_date_dialog.CURRENT_DATE = new Date(from_input)
    start_date_dialog.SELECT_DATE = new Date(from_input)
  } 
    

  $('.next-month').off()
  $('.prev-month').off()

  $('.prev-month').click(function () {start_date_dialog.pre_month()});
  $('.next-month').click(function () {start_date_dialog.next_month()});
  
  start_date_dialog.ON_SELECT_FUNC = selectDate
  start_date_dialog.init()
}

selectDate = function(selectedDate) {

 if (selectedDate == null) {
    return false;
  }

  const todaysDate = new Date();
  var selectedFinalDate = (selectedDate.getDate() < 10 ? '0' : '') + selectedDate.getDate();
  var selectedFullYear = selectedDate.getFullYear().toString();
  selectedFullYear = selectedFullYear.match(/\d{2}$/);
  
  $('#date_pick').html(selectedFinalDate + selectedDate.toLocaleString('en-us',
  {month: 'short'}) + selectedFullYear);
  $("#date").val(selectedDate);

  tableData['date'] =	$('#date_pick').html();
  franchiseTable.ajax.reload();
  jQuery('#showDateModal').modal('hide');
}

var english_month_name = function(dt){
	mlist = [ "January", "Febuary", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December" ];
	return mlist[dt.getMonth()];
};
</script>
@include('settings.buttonpermission')
@endsection
@endsection
