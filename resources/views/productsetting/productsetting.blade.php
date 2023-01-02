<div style='margin-top:10px;margin-left: 0;margin-right: 16px;' class='row'>
	<div class="col-md-12" style='margin-top:10px;color:#27a98a;font-weight:bold;' >
        <h4 style="padding-bottom:5px;border-bottom:1px solid #e0e0e0;">Product Details</h4> 
		   
		<div class="col-md-12" style="padding-left: unset;padding-right: unset;">
		<div class="row">
		<div class="col-md-2 col-sm-6" style="padding-left: unset;padding-right: unset;">
			<label class="form-control" style="border:unset">Category</label>
		</div>
			<div class="col-md-10 col-sm-6" style="padding-left: unset;padding-right: unset;">
				<select class="form-control input-30" id='selectcategory' style="    margin-left: 2px;width: 31%;display: inline-flex;">
				<option class="form-control" value="cat">Select Category</option>
				@foreach ($product_category as $c)
				<option class="form-control" value="{{$c->id}}">{{$c->name}}</option>
				@endforeach
				</select>
				<button class="btn btn-sm btn-success introducernameButton btn-add" id='add_category' style="margin-left: 5px;    padding-top: 0.3em;display: inline-block;"><i class="fa fa-plus text-white"></i> Item</button>
				<button class="btn btn-sm btn-success introducernameButton btn-add" style="margin-left: 1px;padding-top: 0.3em;background-color:red;display: inline-block;border-color:red;" id="remove_category">
					<i class="fa fa-times text-white">
					</i> Item
				</button>
			</div>
		</div>
		</div>

		<div class="col-md-12" style="padding-left: unset;padding-right: unset;">
		<div class="row">
		<div class="col-md-2 col-sm-6" style="padding-left: unset;padding-right: unset;">
			<label class="form-control" style="border:unset">Subcategory</label>
		</div>
			<div class="col-md-10 col-sm-6" style="padding-left: unset;padding-right: unset;">
				<select class="form-control input-30" style="margin-left: 2px;width: 31%;display: inline-flex;" id="selectsubcategory" name="selectsubcategory">
				<option class="form-control" value="subcat">Select Subcategory</option>
				</select>
				<button class="btn btn-sm btn-success introducernameButton btn-add" id='add_subcategory' style="margin-left: 5px;    padding-top: 0.3em;display: inline-block;"><i class="fa fa-plus text-white"></i> Item</button>

			<button class="btn btn-sm btn-success introducernameButton btn-add" style="margin-left: 1px;padding-top: 0.3em;background-color:red;display: inline-block;border-color:red;" id="remove_subcategory">
					<i class="fa fa-times text-white">
					</i> Item
				</button>
			</div>
		</div>
		</div>

		<div class="col-md-12" style="padding-left: unset;padding-right: unset;">
			<div class="row">
            <div class="col-md-2 col-sm-6" style="padding-left: unset;padding-right: unset;">
				<label class="form-control"style="border:unset">Product</label>
			</div>
			<div class="col-md-10 col-sm-6" style="padding-left: unset;padding-right: unset;">
				<select class="form-control input-30" style="margin-left: 2px;width: 31%;display: inline-flex;" id='selectproduct'>
				<option class="form-control" value="select_Country">Select Product</option>
				</select>
				<button class="btn btn-sm btn-success introducernameButton btn-add" id='add_Product' style="margin-left: 5px;padding-top: 0.3em"><i class="fa fa-plus text-white" ></i> Item</button>
				
				<button class="btn btn-sm btn-success introducernameButton btn-add" style="margin-left: 1px;padding-top: 0.3em;background-color:red;display: inline-block;border-color:red;" id="remove_product">
					<i class="fa fa-times text-white">
					</i> Item
				</button>

			</div>
		</div>
	</div>
<br/>
	<div class="col-md-12" style="padding-left: unset;padding-right: unset;">
			<div class="row">
            <div class="col-md-2 col-sm-6" style="padding-left: unset;padding-right: unset;">
				<label class="form-control"style="border:unset">Brand</label>
			</div>
			<div class="col-md-10 col-sm-6" style="padding-left: unset;padding-right: unset;">
				<select class="form-control input-30" style="margin-left: 2px;width: 31%;display: inline-flex;" id='selectbrand'>
				<option class="form-control" value="select_brand">Brand Product</option>
				@foreach ($product_brand as $b)
				<option class="form-control" value="{{$b->id}}">{{$b->name}}</option>
				@endforeach
				</select>
				<button class="btn btn-sm btn-success introducernameButton btn-add" id='add_Brand' style="margin-left: 5px;padding-top: 0.3em"><i class="fa fa-plus text-white" ></i> Item</button>
				
				<button class="btn btn-sm btn-success introducernameButton btn-add" style="margin-left: 1px;padding-top: 0.3em;background-color:red;display: inline-block;border-color:red;" id="remove_brand">
					<i class="fa fa-times text-white">
					</i> Item
				</button>
			</div>
		</div>
	</div>

	<div class="clearfix"></div>
	<!-- Response inc from OH -->
	<!-- 	<div id='response'></div> -->
	</div>
</div>

<script type="text/javascript">
			
$("#add_Product").click(function(){
	addProd_data('add_product');
});


$("#add_category").click(function(){
	addProd_data('add_category');
});


$("#add_subcategory").click(function(){
	addProd_data('add_subcategory');
});

$("#add_Brand").click(function(){
	addProd_data('add_Brand');
});

 function listen_prdstg_event() {
 	// $("#selectcategory").off();
$("#selectcategory").on("change paste keyup", function() {
	$('#selectproduct option:first').prop('selected',true);
	$('#selectsubcategory option:first').prop('selected',true);	

	if ($('#selectcategory').val() != 'cat') {
		enable_subcategories();
	} else {
		disable_all();
	}
});


$("#selectsubcategory").on("change paste keyup", function() {
	$('#selectproduct option:first').prop('selected',true);

	if ($('#selectsubcategory').val() != 'subcat') {
		 enable_products();
	} else {
		disable_products(); 
	}
});
}

listen_prdstg_event()

disable_all(); 

function disable_all() {
	$('#add_subcategory').attr("disabled", "on"); 
	$('#selectsubcategory').attr("disabled", "on");
	$('#add_Product').attr("disabled", "on");
	$('#selectproduct').attr("disabled", "on");
}

function enable_subcategories() {
	$('#add_subcategory').removeAttr("disabled"); 
	$('#selectsubcategory').removeAttr("disabled");
	get_dropDown('#selectsubcategory','subcat');	
}

function disable_products() {
	$('#add_Product').attr("disabled", "on");
	$('#selectproduct').attr("disabled", "on");
}

function enable_products() {
	$('#add_Product').removeAttr("disabled"); 
	$('#selectproduct').removeAttr("disabled");
	get_dropDown('#selectproduct','product');	
}

function get_dropDown(target,option) {

	var dropdown = $(target);

	dropdown.empty();

	if (option == 'subcat') {
		key = $('#selectcategory').val().toString();
		var firstIndex = '<option class="form-control" value="subcat">Select Subcategory</option>';
	} else if (option == 'product') {
		key = $('#selectsubcategory').val().toString();
		var firstIndex = '<option class="form-control" value="product">Select Product</option>';
	} 

	const url = "{{route('productsetting.get_dropDown',['OPTION'=>'OPTION','KEY'=>'KEY'])}}".replace('OPTION',option).replace('KEY',key);
	
	$.getJSON(url, function (data) {
	
		dropdown.empty();
		dropdown.append(firstIndex)
		dropdown.prop('selectedIndex', 0);

		$.each(data, function (key, entry) {
			dropdown.append($('<option class="form-control"></option>').attr('value', entry.id).text(entry.name));
		})
	});
}


function addProd_data(type) {
	var selectcategory = $('#selectcategory').val().toString();
	var selectsubcategory = $('#selectsubcategory').val().toString();
	$.ajax({
		url: "{{route('productsetting.addProd_data')}}",
		type: 'post',
		data: {
			'addType': type,
			'selectcategory': selectcategory,
			'selectsubcategory': selectsubcategory
		},
		success: function (response, textStatus, request) {
			$('#response').html(response);
			$("#addProd_data").modal('show');
		},
		error: function (e) {
			$('#response').html(e);
			$("#msgModal").modal('show');
		}
	});
}

$("#remove_category").click(function(){
	selectDeleteProduct('category');
});

$("#remove_subcategory").click(function(){
	selectDeleteProduct('subcategory');
});

$("#remove_product").click(function(){
	selectDeleteProduct('product');
});

$("#remove_brand").click(function(){
	selectDeleteProduct('brand');
});


function selectDeleteProduct(type) {

$('#selectcategory').removeClass('focusDel');
$('#selectcategory').off()
$('#selectsubcategory').removeClass('focusDel');
$('#selectsubcategory').off()
$('#selectproduct').removeClass('focusDel');
$('#selectproduct').off()
$('#selectbrand').removeClass('focusDel');
$('#selectbrand').off()

	if (type == 'category') {
		var data = $('#selectcategory').addClass('focusDel');
		$('.focusDel').on('change', function() {
			deleteProductData('category');
		});
	} else if (type == 'subcategory') {
		var data = $('#selectsubcategory').addClass('focusDel');
		$('.focusDel').on('change', function() {
			deleteProductData('subcategory');
		});	
	} else if (type == 'product') {
		var data = $('#selectproduct').addClass('focusDel');
		$('.focusDel').on('change', function() {
			deleteProductData('product');
		});
	} else if (type == 'brand') {
		var data = $('#selectbrand').addClass('focusDel');
		$('.focusDel').on('change', function() {
			deleteProductData('brand');
		});
	}

	listen_prdstg_event();
}


function deleteProductData(type) {
	
	if (type == 'category') {

		var id = $('#selectcategory').val().toString();
		$('#selectcategory').removeClass('focusDel');
		$('#selectcategory').off();

	} else if (type == 'subcategory') {

		var id = $('#selectsubcategory').val().toString();
		$('#selectsubcategory').removeClass('focusDel');
		$('#selectsubcategory').off();

	} else if (type == 'product') {

		var id = $('#selectproduct').val().toString();
		$('#selectproduct').removeClass('focusDel');
		$('#selectproduct').off();
	} else if (type == 'brand') {
		var id = $('#selectbrand').val().toString();
		$('#selectbrand').removeClass('focusDel');
		$('#selectproduct').off();
	}

	$.ajax({
		url: "{{route('productsetting.addProd_data')}}",
		type: 'post',
		data: {
			'addType': 'deleted',
			'deleteType': type,
			'id': id,
		},
		success: function (response, textStatus, request) {
			$('#response').html(response);
			$("#deleteProd_data").modal('show');
			listen_prdstg_event()
		},
		error: function (e) {
			$('#response').html(e);
			$("#msgModal").modal('show');
			listen_prdstg_event()
		}
	});
}

</script>
