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

</style>

  <div class="modal fade" id="productdetailsModal" tabindex="-1" role="dialog" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered  mw-75 w-50" role="document" 
		style="display: inline-flex;min-width: 43vw !important;">
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
                    unset;height:255px;background: grey;display:block;
                        margin-left: 0.9%;overflow: hidden;width: 100%;">
                    <input type="file" name="file" id="file" class="hidden">

				<!--
                <h1 id="upload_text" style="color:#fff;margin: 40px"></h1>
                <button class="btn btn-sm  btn-add"
					style="position: absolute;bottom: 10px;right: 10px;
                    font-size: 17px" id="uploadLogo" onclick="return false;">
                    <i class="fa fa-camera" id="logo_upload_cam"
						style="font-size: 40px">
					</i>
                </button>
				-->
                </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <input type="text" id="systemid" readonly class="form-control">
                    </div>
                    <div class="form-group">
                        <input type="text" id="product_name" readonly class="form-control">
                    </div>
                    <div class="form-group">
                        <input type="text" id="ptype" readonly class="form-control">
                    </div>
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
			style="width:30px;text-align: center;">No</th>
        <th class="text-center"
			style="width:100px;text-align: center;">Product&nbsp;ID</th>
        <th>Product&nbsp;Name</th>
        <th class="text-center"
            style="width:80px;">Qty</th>
        <th class="text-center"
            style="width:80px;">Product&nbsp;Type</th>
<<<<<<< .mine
	
		{{-- <th class="text-center" data-toggle="modal" data-target=".productdetailsModal"
            style="width:30px;">&nbsp;</th> --}}
||||||| .r6407
		<!--
		<th class="text-center"
            style="width:30px;">&nbsp;</th>
		-->
=======
		<th class="text-center"
            style="width:30px;">&nbsp;</th>
>>>>>>> .r6428
    </tr>
    </thead>
    <tbody>
<<<<<<< .mine
||||||| .r6407
        <tr>
            <td>1</td>
            <td>1231212</td>
            <td><span class="os-linkcolor" data-target=".productdetailsModal"
				style="cursor:pointer"
				data-toggle="modal">Product name</span></td>
            <td>Inventory</td>
			<!--
            <td>&nbsp;</td>
			-->
         </tr> 
=======
        <tr>
            <td>1</td>
            <td>1231212</td>
            <td><span class="os-linkcolor" data-target=".productdetailsModal"
				style="cursor:pointer"
				data-toggle="modal">Product name</span></td>
            <td>12</td>
            <td>Inventory</td>
            <td>
                <a href="javascript:void(0);" data-field="bluecrab"
					data-platform_id="samplebluecrab"
					style="display:flex;align-items:center;justify-content:center"
					class="btn-primary bg-bluecrab">O
				</a>
			</td>
         </tr> 
>>>>>>> .r6428
    </tbody>
</table>

 
<script>
$(document).ready(function () {
    tableECommerce.draw();
 });

 var tableECommerce = $('#tableECommerce').DataTable({
        "processing": true,
        "serverSide": true,
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
            {data: 'ecommerce_ptype', name: 'ecommerce_ptype'},
            
        ],
        "order": [],
        "columnDefs": [
<<<<<<< .mine
            {"className": "dt-center", "targets": [0,1,3]},
||||||| .r6407
            {"className": "dt-center", "targets": [0,1,3,4,5,6]},
=======
            {"className": "dt-center", "targets": [0,1,3,4,5,6,7]},
>>>>>>> .r6428
            {"targets": -1, 'orderable' : true}
        ],
        "autoWidth" : true,
    });

var product = tableECommerce;

$('#tableECommerce tbody').on('click', 'td', function () {
    
        const tableRow = tableECommerce.row($(this).closest('tr')).data();
        $('#product_name').val(tableRow.name);
        $('#systemid').val(tableRow.systemid);
        $('#ptype').val(tableRow.ptype);
        $('#photo').val(tableRow.photo_1);
        console.log(tableRow);

        $("#productdetailsModal").modal('show');
        // alert( 'You clicked on '+data[0]+'\'s row COmmmmits;
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

 
