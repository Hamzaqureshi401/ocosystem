 <style type="text/css">
 .upload-area {
    width: 70%;
    border: 2px solid lightgray;
    border-radius: 3px;
    margin: 0 auto;
    text-align: center;
    overflow: auto;
}
#file {
    display: none;
}
.upload-area h1 {
    text-align: center;
    font-weight: normal;
    font-family: sans-serif;
    line-height: 50px;
    color: darkslategray;
}
#uploadfile > button > i {
    color: #fff;
}

.pl_selected{
    color: green !important;
}

</style>
<input  type="hidden" value="0" id="product_selected">	
<input  type="hidden" value="0" id="totalclicks">
<div class="modal fade" id="connectedPlatfomrsModal" tabindex="-1" role="dialog" aria-hidden="true"></div>
<div class="modal fade" id="productsListModal" tabindex="-1" role="dialog" aria-hidden="true"></div>

<div class="modal fade" id="productdetailsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered  mw-70 w-50" role="document" 
    style="display: inline-flex;min-width: 40vw !important;">
    <div class="modal-content modal-inside bg-greenlobster"
        style="width: 100%;">
        <div class="modal-header" style="">
            <h3 class="text-white" id="statusModalLabel"
                style="margin-bottom:0">
                Product Details
            </h3>
        </div>
        <div class="modal-body text-center" style="">
            <form action="#" id="updateProspectFields" method="post"
                enctype="multipart/form-data"
                onsubmit="return false;" autocomplete="off">
            <div class="row" style="padding-top: unset;">
                <div class="col-md-6"
                    style="padding-right: unset;padding-top: 0px;">
                <div class="upload-area" id="uploadfile" style="border:
                unset;height:255px;background: white;display:block;
                    margin-left: 0.9%;overflow: hidden;width: 100%;">
            </div>
            </div>
            <div class="col-md-6">
                <p id="product_name" class="text-left"></p>
            </div>
            </div>
        </form>
        </div>
    </div>
</div>
</div>

<div class="row py-2" style="display:flex;height:83px!important;
	padding-top:8px !important;padding-bottom:0 !important">
    <div class="col align-self-center" style="width:80%">
        <h2>Product: E-Commerce</h2>
    </div>
</div>
<table class="table table-bordered" id="tableECommerce">
    <thead class="thead-dark">
    <tr>
        <th class="text-center"
			style="width:30px;">No</th>
        <th class="text-center"
			style="width:120px;">Product&nbsp;ID</th>
        <th>Product&nbsp;Name</th>
        <th class="text-center"
            style="width:80px;">Qty</th>
        <th class="text-center"
            style="width:160px;">Product&nbsp;Type</th>
        <th class="text-center"
			style="width:30px;background-image: unset !important "></th>
    </tr>
    </thead>
    <tbody>
    </tbody>
</table>
<br><br>

    <div class="modal fade" id="msgModal"  tabindex="-1"
         role="dialog" aria-labelledby="staffNameLabel"
         aria-hidden="true" style="text-align: center;">

        <div class="modal-dialog modal-dialog-centered  mw-75 w-50"
             role="document" style="display: inline-flex;">
            <div class="modal-content modal-inside bg-greenlobster"
                 style="width: 100%;">
                <div class="modal-header" style="border:0">&nbsp;</div>
                <div class="modal-body text-center">
                    <h5 class="modal-title text-white" id="status-msg-element"
					style="padding-bottom:0;display: none;">
						This product is authorized to connect to the external
						platform. Authorization has been updated successfully 
					</h5>
					<div id="modalconnecting" >
						<p align="center">
						<img src="{{ asset('images/connspinner.gif') }}"
							style="object-fit:contain;width:85px;height:85px"/></p>
							<p align="center">Connecting...</p>
					</div>		
                </div>
                <div class="modal-footer" style="border:0">&nbsp;</div>
            </div>
        </div>
    </div>
<script>
$(document).ready(function () {
    tableECommerce.draw();
 });
 
 $(document).click(function (e) {
    if ($(e.target).is('#platform')) {
        $('#connectedPlatfomrsModal').modal('hide');
	//	$('#connectedPlatfomrsModal').hide();
    }
});

 var tableECommerce = $('#tableECommerce').DataTable({
        "processing": true,
        "serverSide": true,
        "autoWidth": false,
        "ajax": {
            "url": "{{route('ecommerce.ajax.index')}}",
            "type": "POST",
              'headers': {
                  'X-CSRF-TOKEN': '{{ csrf_token() }}'
              },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'ecommerce_pro_id', name: 'ecommerce_pro_id'},
            {data: 'ecommerce_pro_name', name: 'ecommerce_pro_name'},
            {data: 'ecommerce_qty', name: 'ecommerce_qty'},
            {data: 'ecommerce_ptype', name: 'ecommerce_ptype'},
            {data: 'bluecrab', name: 'bluecrab'},
        ],
        "order": [],
        "columnDefs": [
            {"className": "dt-center", "targets": [0, 1, 3, 4, 5]},
        ],
    });

var product = tableECommerce;

$('#connectedPlatfomrsModal').on('hidden.bs.modal', function (e) {
	
	e.preventDefault();
	var totalclicks = parseInt($("#totalclicks").val());
	console.log('HIDEEEEE', totalclicks);
	if(totalclicks > 0){	
		$("#msgModal").modal('show');
		var all = $(".pl_selected").map(function() {
					return $(this).data("id");
				}).get();
				
		var noall = $(".pl_notselected").map(function() {
					return $(this).data("id");
				}).get();				
		var product_id = $("#product_selected").val();
		$.ajax({
			beforeSend: function(jqXHR, settings) {
			  $("#msgModal").modal('show'); // Assuming you have some kind of spinner object.
			},
            url: "{{route('ecommerce.connected.upsertplatforms')}}",
            type: "POST",
            enctype: 'multipart/form-data',
            cache: false,
            data: {'product_id': product_id, 'platforms': all , 'noplatforms': noall},
            success: function (response) {
				console.log(response);
				$("#status-msg-element").show();
				$("#modalconnecting").hide();
				setTimeout(function() {
					$("#msgModal").modal('hide');
					$("#status-msg-element").hide();
					$("#modalconnecting").show();
					$('.modal-backdrop').remove();
					$("#totalclicks").val(0);
				},3500);
				/*$('#connectedPlatfomrsModal').html(response);
                $('#connectedPlatfomrsModal').modal('show');
				$("#product_selected").val(tableRow.id);*/
            }, error: function (e) {
                console.log(e.message);
            }
        });	
	}
	
});


function selectplatform(id){
	//console.log(id);
	var totalclicks = parseInt($("#totalclicks").val());
	totalclicks++;
	$("#totalclicks").val(totalclicks);	
	if($("#platform" + id).hasClass("pl_selected")){
		$("#platform" + id).removeClass("pl_selected");
		$("#platform" + id).addClass("pl_notselected");
	} else {
		$("#platform" + id).addClass("pl_selected");
		$("#platform" + id).removeClass("pl_notselected");
	}
}

$('#tableECommerce tbody').on('click', '.ecommerce_pro_name', function(e) {
    const tableRow = tableECommerce.row($(this).closest('tr')).data();
    $('#product_name').text(tableRow.name);
    $('#uploadfile').html('<img style="width:100%;height:100%" src="'+$(this).attr('data')+'">')
    $("#productdetailsModal").modal('show');
});

$('#tableECommerce tbody').on('click', '#bluecrab', function() {
    const tableRow = tableECommerce.row($(this).closest('tr')).data();
	//console.log(tableRow);
    $.ajax({
            url: "{{route('ecommerce.connected.platforms')}}",
            type: "POST",
            enctype: 'multipart/form-data',
            cache: false,
            data: {'systemid': tableRow.systemid, 'id': tableRow.id},
            success: function (response) {
				$('#connectedPlatfomrsModal').html(response);
                $('#connectedPlatfomrsModal').modal('show');
				$("#product_selected").val(tableRow.id);
            }, error: function (e) {
                console.log(e.message);
            }
        });
});

$('#tableECommerce tbody').on('click', '.ecommerce_inventory_qty', function() {
    // for api call
    const tableRow = tableECommerce.row($(this).closest('tr')).data();
    $.ajax({
            url: "{{route('ecommerce.product.ledger')}}",
            type: "POST",
            enctype: 'multipart/form-data',
            cache: false,
            data: {'productid': tableRow.id},
            success: function (response) {
				$('#response_data_product').html(response);
                // $('#productsListModal').modal('show');
            }, error: function (e) {
                console.log(e.message);
            }
        });
});
</script>



<style>
    .modal-add-style {
        text-decoration: underline blue;
        cursor: pointer;
    }
    .table td {
        vertical-align: middle;
    }

	.productdetailsModal > .modal-dialog {width: 250px;}

    #rsPriceInput, #rsCogsInput {text-align: right !important;}

	#rsLoyaltyInput  {text-align: center !important;}
</style>

 
