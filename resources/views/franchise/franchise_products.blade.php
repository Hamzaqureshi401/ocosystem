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
		style="padding-top:15px !important;padding-bottom: 15px !important">
		<div class="col col-auto align-self-center" style="width:80%">
			<h2 class="mb-0">Franchise Product</h2>
		</div>
		<div class="col col-auto align-self-center mr-8" >
			<h5 class="m-0">{{$franchise_details->name}}</h5>
			<h5 class="m-0">{{$franchise_details->systemid}}</h5>
		</div>
	</div>


	<table class="table table-bordered datatable" style="width:100%" id="franchise">
		<thead class="thead-dark">
		<tr>
			<th style="width:30px;text-align: center;">No</th>
			<th style="width:100px;text-align: center;">Product ID</th>
			<th>Product Name</th>
			<th class="text-center">Type</th>
			<th class="text-center">Price</th>
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


<div class="modal fade" id="inventoryPriceModal"  tabindex="-1"
	role="dialog" aria-labelledby="staffNameLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered  mw- 75 w- 50" role="document">
			<div class="modal-content modal---inside bg-greenlobster" >
		<div class="modal-header" >
			<h3 class="modal-title text-white"  id="statusModalLabel">Price Range</h3>
				</div>
		<div class="modal-body">
			<div class="row">
				<div class='col-4 text-center'>
						<label>Minimum</label>
						<input type="text" id='model_price_min_fk' style="text-align:right" class="form-control" placeholder='0.00'/>
						<input type="hidden" id='model_price_min' />
				</div>

				<div class='col-4 text-center'>
						<label>Retail</label>
						<input type="text" id="model_price_retail_fk" style="text-align:right" class="form-control" placeholder='0.00'/>
						<input type="hidden" id='model_price_retail' />
				</div>

				<div class='col-4 text-center'>
						<label>Maximum</label>
						<input type="text" id="model_price_max_fk" style="text-align:right" class="form-control" placeholder='0.00'/>
						<input type="hidden" id='model_price_max' />
				</div>
			</div>
		</div>
		</ul>
				<!-- div class="modal-footer" style="border:0;">
				</div --->
			</div>
		</div>

	</div>

<div class="modal fade" id="normalPriceModal"  tabindex="-1"
	role="dialog" aria-labelledby="staffNameLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered  mw- 75 w- 50" role="document">
			<div class="modal-content modal---inside bg-greenlobster" >
		<div class="modal-header" >
			<h3 class="modal-title text-white"  id="statusModalLabel">Price Range</h3>
				</div>
		<div class="modal-body">
				<div class='text-center col-8' style="margin:auto">
						<label>Price</label>
						<input type="text" id="retail_price_normal_fk"style="text-align:right" class="form-control" placeholder='0.00'/>
						<input type="hidden" id='retail_price_normal' />
				</div>
		</div>
		</ul>
				<!-- div class="modal-footer" style="border:0;">
				</div --->
			</div>
		</div>

	</div>


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
<div id="res"></div>
@section('js')
 <div id="showEditInventoryModal"></div>
<script type="text/javascript">

var franchiseid = '{{$franchiseid}}';
var productid = 	null;
$(document).ready(function() {
	$(window).keydown(function(event){
		if(event.keyCode == 13) {
			event.preventDefault();
			return false;
		}
	});
});

function inventory_modal(price) {
	$("#inventoryPriceModal").modal('show');
}

$('#inventoryPriceModal').on('hidden.bs.modal', function (e) {
	update_price('inventory');
});

$('#normalPriceModal').on('hidden.bs.modal', function (e) {
	update_price('normal');
});

disable_click = false
async function update_price(type) {
	productid_ = productid;
	productid  = null;
	if (disable_click == true) {
		return;
	}
	disable_click = true;
	model_price_min 	= $("#model_price_min").val();
	model_price_max		= $("#model_price_max").val();

	if (type == 'inventory') {
		model_price_retail	= $("#model_price_retail").val();
	} else {
		model_price_retail  = $("#retail_price_normal").val();
	}

	await $.post('{{route("franchise.set_product_price")}}',{

		min_price: 		model_price_min,
		max_price:		model_price_max,
		retail_price: 	model_price_retail,
		franchiseid: 	franchiseid,
		product_id:		productid_

	}).done(  function(res) {
		
		$("#model_price_min").val('');
		$("#model_price_retail").val('');
		$("#model_price_max").val('');
		$("#retail_price_normal").val('');
		franchiseTable.ajax.reload();
		$("#res").html(res);
		disable_click = false;
	}).fail(function(res) {
		$("#model_price_min").val('');
		$("#model_price_retail").val('');
		$("#model_price_max").val('');
		$("#retail_price_normal").val('');
		$("#showEditInventoryModal").html(res);
		disable_click = false;
	});

}

function normal_price_modal(price) {
	$("#normalPriceModal").modal('show');
}
function activate_product(product_id,e) {
$.post("{{route('franchise.toggle_product_active')}}",{
	product_id:		product_id,
	franchiseid:	franchiseid
}).done(function(res) {
		$(e).toggleClass('active_button_activated');	
		franchiseTable.ajax.reload();
		$("#showEditInventoryModal").html(res);
}).fail(function(res){
		franchiseTable.ajax.reload();
		$("#showEditInventoryModal").html(res);
});
}

var franchiseTable = $('#franchise').DataTable({
	"processing": false,
	"serverSide": true,
	"ajax": {
	"url": "{{route('franchise.get_product')}}",
		"type": "POST",
		"data": {
			franchiseid: franchiseid
		},
		'headers': {
		'X-CSRF-TOKEN': '{{ csrf_token() }}'
		},
	},
	columns: [
		{data: 'DT_RowIndex', name: 'DT_RowIndex'},
		{data: 'product_systemid', name: 'systemid'},
		{data: 'product_name', name: 'product_name'},
		{data: 'product_type', name: 'product_type'},
		{data: 'product_cost', name: 'product_cost'},
		{data: 'active', name: 'active'},
		],
	"order": [0, 'desc'],
	"columnDefs": [
		{"width": "30px", "targets": 0},
		{"width": "120px", "targets": 1},
		{"width": "170px", "targets": 3},
		{"width": "60px", "targets": 4},
		{"width": "50px", "targets": 5},
		{"className": "dt-right vt_middle", "targets": [4]},
		{"className": "dt-center vt_middle", "targets": [0, 1, 3]},
		{"className": "vt_middle", "targets":[2]},
		{"className": "slim_cell", "targets":[5]},
		{orderable: false, targets: [-1]},
	],
});

$('#franchise tbody').on('click', 'td', function () {
	const tableRow = franchiseTable.row($(this).closest('tr')).data();
	productid = tableRow['id'];
	
	const tableCell = franchiseTable.cell(this).data();
	const element = $(tableCell).data("field");
	
	if (element == 'product_cost' && tableRow['ptype'] == 'inventory') {	
		custom_price_handler("#model_price_max", tableRow['max_price']);

		custom_price_handler("#model_price_min", tableRow['min_price']);

		custom_price_handler("#model_price_retail", tableRow['retail_price']);
	}
	
	if (element == 'product_cost' && tableRow['ptype'] != 'inventory') {	
		custom_price_handler("#retail_price_normal", tableRow['retail_price']);
	}

});

function custom_price_handler(selector, value) {
		$(selector+"_fk").val((value/100).toFixed(2));
		$(selector).val((value));
		filter_price(selector+"_fk",selector);
}

function atm_money(num) {
		if (num.toString().length == 1) {
			return '0.0' + num.toString()
		} else if (num.toString().length == 2) {
			return '0.' + num.toString()
		} else if (num.toString().length == 3) {
			return num.toString()[0] + '.' + num.toString()[1] +
				num.toString()[2];
		} else if (num.toString().length >= 4) {
			return num.toString().slice(0, (num.toString().length - 2)) +
				'.' + num.toString()[(num.toString().length - 2)] +
				num.toString()[(num.toString().length - 1)];
		}
}

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
	
		valdate = validation_input_price($(buffer_in).attr('id'));
		console.log("Validate_result", valdate);
		if (valdate == false) {
			$(buffer_in).val(0)
			$(target_field).val(atm_money(0))
		}
	});
}

function validation_input_price(id) {
	
	model_price_min 	= parseFloat($("#model_price_min").val());
	model_price_max		= parseFloat($("#model_price_max").val());
	model_price_retail	= parseFloat($("#model_price_retail").val());
	
	if (id == 'model_price_min') {
		
		if (model_price_min > model_price_max && model_price_max != 0 ) {
			return false;
		}

	} else if (id == "model_price_max") {
		if (model_price_min > model_price_max && model_price_min != 0 ) {
			return false;
		}

	} else if (id == "model_price_retail") {
	
		if (model_price_retail > model_price_max) {
			return false;
		}

		
		if (model_price_retail < model_price_min) {
			return false;
		}

	} else {
		return false;
	}
	
	return true;
}

</script>

@include('settings.buttonpermission')
@endsection
@endsection
