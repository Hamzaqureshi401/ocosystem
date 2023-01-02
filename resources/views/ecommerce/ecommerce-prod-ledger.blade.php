@extends('layouts.layout')

@section('content')
<div id="landing-view">
<!--white abolone-->
<style media="screen">
a:link{
text-decoration: none!important;
}
@media (min-width: 1025px) {
	#tableInventoryQty{
		table-layout: fixed;
	}
	.remarks {
		white-space: nowrap;
		overflow-x: hidden;
		text-overflow: ellipsis;
	}
}

#void_stamp{
	font-size:100px;
	color:red;
	position:absolute;
	z-index:2;
	font-weight:500;
	margin-top:130px;
	margin-left:15%;
	transform:rotate(45deg);
	display:none;
}
</style>
{{-- modal remarks --}}
<div class="row" style="height:83px;padding-top:0;padding-bottom:5px">
        <div class="col-sm-4" style="align-self:center">
            <h2 style="margin-bottom:0;padding-top: 0;">Product Ledger: E-Commerce</h2>
        </div>
	<div class="col-sm-1" style="margin-left:-80px;align-self:center">
      
	@if (!empty($product->thumbnail_1))
		<img src="{{ asset('images/product/'.$product->id.'/thumb/'.$product->thumbnail_1) }}"
			alt="Logo" width="70px" height="70px" alt="Logo"
			style="object-fit:contain;float:right;margin-left:0;margin-top:0;">
	@endif
   </div>
   <div class="col-sm-5" style="align-self:center;float:left;padding-left:0">
       <h4 style="margin-bottom:0px;padding-top: 0;line-height:1.5;">@if($product->name){{$product->name}} @else Product Name @endif</h4>
       <p style="font-size:18px;margin-bottom:0">{{$product->systemid}}</p>
   </div>
      {{-- <div class="col-sm-3" style="float: right;">
          <div style="float: right;">
            <a href="/consignmentlocation" target="_blank">
              <button class="btn btn-success sellerbuttonwide bg-product"
                  style="padding-left:9px">
                  <span>Consignment</span>
              </button>
            </a>
            <a href="{{ url('/landing/inventoryqtydamage') }}" target="_blank">
              <button class="btn btn-success sellerbuttontwo bg-product"
                  style="padding-left:9px">
                  <span>Wastage<br>Damage</span>
              </button>
            </a>
            <a href="#" data-toggle="modal" data-target="#product_location_modal">
              <button class="btn btn-success sellerbutton bg-product mr-0 mb-0"
                  style="padding-left:9px">
                  <span>Product Location</span>
              </button>
            </a>
          </div>
      </div> --}}
	</div>
  <div class="table-responsive" style="overflow-x: hidden;">
	<table class="table table-bordered" id="tableECommerce"
		style="width: 100%;">
	    <thead class="thead-dark">
            <tr>
                <th class="text-center"
					style="width:30px;text-align: center;">No</th>
                <th class="text-center"
					style="width:100px;text-align: center;">
					Receipt&nbsp;ID</th>
                <th class="text-center" style="width:100px">Type</th>
                <th class="text-center" style="width:150px" nowrap>Last&nbsp;Update</th>
                <th class="text-center" style="width: 20%;">Location</th>
                <th class="text-center" style="width:50px">Qty</th>
                <th class="text-left" style="width: auto">Remarks</th>
            </tr>
        </thead>
		<tbody>
		 @for($tt = 0; $tt < sizeof($defdata); $tt++)
			  <tr>
				<td class="text-center">{{$tt + 1}}</td>
				<td class="text-center"><p class="os-linkcolor" data-field="receipt_id" style="margin: 0; cursor:pointer;" onclick="show_receipt({{$defdata[$tt]->recid}}, '{{$defdata[$tt]->platform->url}}')">{{$defdata[$tt]->systemid}}</p></td>
				<td class="text-center">E-Commerce</td>
				<td class="text-center"><?php echo date('dMy H:i:s',strtotime($defdata[$tt]->created_at)); ?></td>
				<td class="text-center">{{$defdata[$tt]->platform->platform}}</td>
				<td class="text-center">{{$defdata[$tt]->quantity * -1}}</td>
				<td>
					<a title="{{$defdata[$tt]->recremark}}" 
						id="remark_{{$defdata[$tt]->recid}}"
					   href="#" 
					   data-toggle="modal" 
					   data-target="#remarks_rec" 
					   data-item_id="{{$defdata[$tt]->recid}}"
					   data-item_url="{{$defdata[$tt]->platform->url}}"
					   data-item_remark="{{$defdata[$tt]->recremark}}">
						@if($defdata[$tt]->recremark)
						  {{$defdata[$tt]->recremark}} 
						@else 
						  Remarks 
						@endif 
					 </a>
				</td>
			  </tr>
		  @endfor
		</tbody>
    </table>
  </div>

</div>

<div class="modal fade" id="remarks_rec" tabindex="-1"
	role="dialog" aria-labelledby="productcontModallabel" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered" role="document" >
	<div class="modal-content">

		<form  class="m-form  m-form--state m-form--label-align-right " >
			<div class="modal-body">
        		<div class="m-form__content">
					<input type="hidden" id="modal-item_id" name="item_id" value="">
					<input type="hidden" id="modal-item_url" name="item_url" value="">
					<textarea id="modal-item_remark" name="remark"
						class="form-control m-input" rows="3" placeholder="Remarks"></textarea>
				</div>
			</div>
		<!--end::Form-->
		</form>
	</div>
</div>
</div>
<div id="remarkResponse"></div>
<div id="response"></div>
@section('scripts')
<script>
    $(document).ready(function () {
		var tableECommerce = $('#tableECommerce').DataTable({
            "order": [],
            "columnDefs": [
                {"className": "dt-center", "targets": [0, 1, 3, 4, 5]},
            ],
            "autoWidth" : true,
        });
		
		$('#modal-item_remark').blur(function() {
		  var item_remark = $.trim($('#modal-item_remark').val());
		  var item_id = $('#modal-item_id').val();
		  var item_url = $('#modal-item_url').val();
		//  var remark_type = $('#modal-remark_type').val();
		  if(item_remark!="" || item_id!=""){
			$.ajax({
			  url: "{{route('ecommerce.ajax.remark')}}",
			  type: "POST",
			  data: {
				  receipt_id: item_id,
				  receipt_remark: item_remark,
				  url: item_url,
			  },
			  cache: false,
			  success: function(dataResult){
				console.log(dataResult);
				var remark_html = '<a href="#" title="'+item_remark+'"  data-toggle="modal" data-target="#remarks_rec" data-item_id="'+item_id+'" data-item_remark="'+item_remark+'">'+item_remark+'</a>';
				  $("#remarks_rec").modal('hide');
				  $("#remarkResponse").html(dataResult);
				  $("#remark_"+item_id).html(remark_html);
			  }
			});
		  }
		});		
     });
	 
    // data attributes to scan when populating modal values
    var ATTRIBUTES = ['item_id', 'item_remark', 'item_url'];

    $(document).on('click', '[data-toggle="modal"]', function (e) {
      e.preventDefault();
      var $target = $(e.target);
      var modalSelector = $target.data('target');
      ATTRIBUTES.forEach(function (attributeName) {
        var $modalAttribute = $(modalSelector + ' #modal-' + attributeName);
        var dataValue = $target.data(attributeName);
        if(attributeName == 'item_remark'){
          $modalAttribute.val('');
          $modalAttribute.val(dataValue || '');
          $modalAttribute.text(dataValue || '');
        } else {
          $modalAttribute.val(dataValue || '');
        }
      });
    });	 
  
	function show_receipt(id, url) {
		
	  $.ajax({
		url: "{{route('ecommerce.ajax.receipt')}}",
		type: 'post',
		data: {
		  'receipt_id': id,
		  'url': url,
		  // 'voucher_ids':voucher_ids
		},
		success: function (response) {
		  $('#response').html(response);
		  $('#receiptModal').modal('show');
		},
		error: function (e) {
		  $('#response').html(e);
		  $("#msgModal").modal('show');
		}
	  });

	}	
    
    var product = tableECommerce;
</script>

@include('settings.buttonpermission')
@endsection
@endsection
