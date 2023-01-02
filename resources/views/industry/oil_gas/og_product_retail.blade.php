<style>
    #tableOilGas {width:100% !important;}
</style>
<div style="padding-right:0" class="">
	<div class="row m-0 p-0" style="">
		<div class="col-md-6 p-0 d-flex align-items-center">
			<div class="" style="width: 100%;margin-top:0;">
				<div style="padding-left:0" class="col">
					<h2 class="mb-0">Product: Oil & Gas</h2>
				</div>
			</div>
		</div> 

		<div class="col-md-6 pr-0">
			<button class="btn btn-success sellerbutton"
				style="padding-left:0;padding-right:0;float:right;
				margin: 0px 0px 5px 5px;"
				id="addInvenProduct">+Product
			</button>

			<a href="javascript: openNewTabURL('{{route('get_industry_oil_gas_product_stockout_index_view')}}')">
				<button class="btn btn-success sellerbutton bg-stockout"
					style="padding-left:0;padding-right:0;float:right;
					margin: 0px 0px 5px 5px;"
					id="addStockOutProduct" >Stock Out
				</button>
			</a>
			<a href="javascript: openNewTabURL('{{route('get_industry_oil_gas_product_stockin_index_view')}}')">
				<button class="btn btn-success sellerbutton bg-stockin"
					style="padding-left:0;padding-right:0;float:right;
					margin: 0px 0px 5px 0px;"
					id="addStockInProduct">Stock In
				</button>
			</a>
		</div>
	</div>
</div>

<div style="padding-left:0;padding-right:0"
	class="col-sm-12">
    <table id="tableOilGas"
		class="table table-bordered" style="">
        <thead class="thead-dark">
            <tr>
                <th style="" >No</th>
                <th style="">Product&nbsp;ID</th>
                <th class="text-left">Product&nbsp;Name</th>
                <th style="">Litre&nbsp;(&ell;)</th>
                <th style="">Price/&ell;</th>
                <th style="">Loyalty</th>
                <th style=""></th>
                <th style=""></th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>
<div class="modal fade" id="UpdateMessagePopUp"
	tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered mw-75 w-50">

		<!-- Modal content-->
		<div class="modal-content  modal-inside bg-greenlobster">
			<div class="modal-header" style="border:none;">&nbsp;

			</div>
			<div class="modal-body text-center">
				<p style="font-size: 20px; margin-bottom:0">
					Loyalty point updated successfully
				</p>
			</div>
			<div class="modal-footer" style="border: none;">&nbsp;
			</div>
		</div>
	</div>
</div>

<div id="productResponce"></div>
<div id="showEditInventoryModal"></div>
<div id="showEditInputInventoryModal"></div>

<br/><br/><br/>

<script>
var oilGasTable = $('#tableOilGas').DataTable({
	"processing": false,
	"serverSide": true,
	"ajax": {
		"url": "{{route('industryoilgas.ajax.index')}}",
		"type": "POST",
		'headers': {
			'X-CSRF-TOKEN': '{{ csrf_token() }}'
		},
	},
	columns: [
		{data: 'DT_RowIndex', name: 'DT_RowIndex'},
		{data: 'og_product_id', name: 'og_product_id'},
		{data: 'og_product_name', name: 'og_product_name'},
		{data: 'og_litre', name: 'og_litre'},
		{data: 'og_price', name: 'og_price'},
		{data: 'og_loyalty', name: 'og_loyalty'},
		{data: 'og_color', name: 'og_color'},
		{data: 'deleted', name: 'deleted'},
	],
	"order": [0, 'desc'],
	"columnDefs": [
		{"width": "30px", "targets": 0},
		{"width": "150px", "targets": 1},
		{"width": "100px", "targets": 3},
		{"width": "100px", "targets": 4},
		{"width": "100px", "targets": 5},
		{"width": "66px", "targets": 6},
		{"width": "30px", "targets": 7},
		{"className": "dt-center p-0 m-0", "targets": [6]},
		{"className": "dt-center", "targets": [0, 1, 3, 4, 5, 7]},
		{"orderable": false, "targets": [6,7]},
	],
});


$('#addInvenProduct').on('click', function () {
	addInvenProduct();
});

$('#loyaltyUpdateModal').on('blur', function(){
})

// $("#loyaltyUpdateModal").on('hidden.bs.modal', function (e) {
// 	// updateFuelPrice();
//     alert('here')
// });

//update loyalty function
function update_loyalty(fuel_product_id, id, current_loyalty_value){
	$('#fuel_product_loyalty'+id).focusout(function(){
		let new_loyalty_value = $('#fuel_product_loyalty'+id).val();
		 $.ajax({
		url: "{{route('industryoilgas-update-loyalty-point')}}",
		type: 'POST',
		'headers': {
			'X-CSRF-TOKEN': '{{ csrf_token() }}'
		},
		data: {
			"product_id": fuel_product_id,
			"new_loyalty_value":new_loyalty_value
		},
		success: function (response) {
			$('body').removeClass('modal-open')
			$('.modal-backdrop').removeClass('modal-backdrop')
			oilGasTable.ajax.reload();
			$("#UpdateMessagePopUp").modal('show');
			setTimeout(function(){
				$("#UpdateMessagePopUp").modal('hide');
				// $('.modal-backdrop').removeClass('modal-backdrop')
			}, 2500)
			//  $('.modal-backdrop').removeClass('modal-backdrop')
		},
		error: function (e) {
			console.log('error', e);
		}
	});
	})
	
	// }
}

var product = oilGasTable;
var prd = false;

function addInvenProduct() {

	if (prd == true) {
		return null
	}
	;
	prd = true;
	$.ajax({
		url: "{{route('industryoilgas.store')}}",
		type: "GET",
		enctype: 'multipart/form-data',
		processData: false,
		contentType: false,
		cache: false,
		data: '',
		success: function (response) {
			oilGasTable.ajax.reload();
			$("#showEditInventoryModal").html(response);
			//$("#msgModal").modal('show');
			prd = false;

		}, error: function (e) {
			console.log(e.message);
		}
	});
}


function details(product_id) {
	$.ajax({
		url: "{{route('product.details.dialog')}}",
		type: 'POST',
		'headers': {
			'X-CSRF-TOKEN': '{{ csrf_token() }}'
		},
		data: {
			"product_id": product_id
		},
		success: function (response) {
			$("#productResponce").html(response);
			$('#modal').modal('show')
		},
		error: function (e) {
			console.log('error', e);
		}
	});
}


$('#tableOilGas tbody').on('click', 'td', function () {
	const tableCell = oilGasTable.cell(this).data();
	const tableRow = oilGasTable.row($(this).closest('tr')).data();
	const element = $(tableCell).data("field");


	if (element == 'inven_pro_name') {
		return null
	}

	if (element != null) {
		console.log(element);

		$.ajax({
			url: "{{route('industryoilgas.edit.modal')}}",
			type: 'post',
			'headers': {
				'X-CSRF-TOKEN': '{{ csrf_token() }}'
			},
			data: {
				'id': tableRow['id'],
				'field_name': element
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

	// alert( 'You clicked on '+data[0]+'\'s row COmmmmits;
});


function myFunction() {
	var x = document.getElementById("myDIV");
	if (x.style.display === "none") {
		x.style.display = "block";
	} else {
		x.style.display = "none";
	}
	inventoryTable.draw();
}

$('body').on('click', '.docs_id', function() {
	var url = $(this).attr('url');
	window.open(url, '_blank');
});


function select_colorModal(id) {	
	$('.color_bar_sel').val(id);
	$("#colorSelectDialog").modal('show');
}


function update_color(value, product_id) {
	$.post("{{route('og.product.update.color')}}", {
		ogproduct_id: product_id,
		color:value
	}).done(function(res){
			oilGasTable.ajax.reload();
			$("#productResponce").html(res);
	});
}
</script>

