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

}
</style>
{{-- modal remarks --}}
<div class="modal fade" id="remarks_qty" tabindex="-1"
  role="dialog" aria-labelledby="productcontModallabel" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered" role="document" >
  <div class="modal-content">

    <form  class="m-form  m-form--state m-form--label-align-right " >
      <div class="modal-body">
            <div class="m-form__content">
                <input type="hidden" id="modal-item_id" name="item_id" value="">
                <input type="hidden" id="modal-remark_type" name="remark_type" value="">
          <input  type="text" name="remark" id="modal-item_remark" class="form-control m-input" placeholder="Remarks">
        </div>
      </div>
    <!--end::Form-->
    </form>
  </div>
</div>
</div>
        <div class="row" style="padding-top:0">
        <div class="col-sm-4" style="align-self:center">
            <h2 style="margin-bottom:0px;padding-top: 0;">
				Product Ledger: Raw Material</h2>
        </div>
  <div class="col-sm-1" style="align-self:center">
      
  @if (!empty($product->thumbnail_1))
    <img src="{{ asset('images/product/'.$product->id.'/thumb/'.$product->thumbnail_1) }}"
      alt="Logo" width="70px" height="70px" alt="Logo"
      style="object-fit:contain;float:right;margin-left:0;margin-top:0;">
  @endif
   </div>
   <div class="col-sm-4" style="align-self:center;float:left;padding-left:0">
       <h4 style="margin-bottom:0px;padding-top: 0;line-height:1.5;">@if($product->name){{$product->name}} @else Product Name @endif</h4>
       <p style="font-size:18px;margin-bottom:0">{{$product->systemid}}</p>
   </div>
      <div class="col-sm-3" style="float: right;">
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
      </div>
  </div>
    <table class="table table-bordered" id="tableInventoryQty" style="width: 100%;">
        <thead class="thead-dark">
          <tr>
              <th class="text-center" style="width:5%;text-align: center;">No</th>
              <th class="text-center" style="width:13%;text-align: center;">Document&nbsp;No</th>
              <th class="text-center" style="width: 11%">Type</th>
              <th class="text-center" style="width: 10%" nowrap>Last&nbsp;Update</th>
              <th class="text-center" style="width: 10%;">Location</th>
              <th class="text-center" style="width: 5%">Qty</th>
              <th class="text-left" style="width: 36%">Remarks</th>

          </tr>
        </thead>
      <tbody>

    @foreach($opos_product as $key => $value)
      <tr>
        <td style="text-align: center;"></td>
        <td style="text-align: center;" @if($value->void == 1) background-color: red; @endif>
          @if($value->document_no)
            @if($value->stock)
              <p class="os-linkcolor" style="cursor: pointer; margin: 0; text-align: center;"><a class="os-linkcolor" href="{{ url('stockreport')}}/{{$value->document_no}}" target="_blank" style="text-decoration: none;">{{$value->document_no}}</a></p>
            @elseif($value->wastage)
              <a href="{{ url('wastagereport')}}/{{$value->document_no}}" target="_blank" style="cursor: pointer;">{{$value->document_no}}</a>
            @else 
              <a href="#" style="cursor: pointer;" onclick="show_receipt('{{$value->document_no}}')">{{$value->document_no}}</a>
            @endif
          @else
            - 
          @endif
        </td>
        <td style="text-align: center;" nowrap>@if($value->sales_type) {{$value->sales_type}} @else - @endif</td>
        <td style="text-align: center;" nowrap><?php echo date('dMy H:i:s',strtotime($value->last_update)); ?></td>
        <td style="text-align: center;" nowrap>@if($value->location) {{$value->location}} @else - @endif</td>
        <td style="text-align: center;">{{$value->quantity}}</td>
        <td style="text-align: left; padding-left: 15px;" class="remarks" id='remark_{{$value->item_detail_id}}'>
          @if($value->stock) 
              <a title="{{$value->item_remarks}}" href="#" data-toggle="modal" data-target="#remarks_qty" data-item_id="{{$value->item_detail_id}}" data-item_remark="{{$value->item_remarks}}" data-remark_type='stock'>
                @if($value->item_remarks)
                  {{$value->item_remarks}} 
                @else 
                  Remarks 
                @endif 
          @elseif($value->wastage)
            -
          @else
              <a title="{{$value->item_remarks}}" href="#" data-toggle="modal" data-target="#remarks_qty" data-item_id="{{$value->item_detail_id}}" data-item_remark="{{$value->item_remarks}}" data-remark_type='sales'>
                @if($value->item_remarks)
                  {{$value->item_remarks}} 
                @else 
                  Remarks 
                @endif 
              </a> 
          @endif
        </td>
       </tr>
      @endforeach
    </tbody>
  </table>
  <div id="productResponce"></div>
<div id="response"></div>

      @include('inventory.inventoryqtypdtlocation')
@section('scripts')

<script type="text/javascript">
  $(document).ready(function () {
    var tableinventory =  $('#tableInventoryQty').DataTable({
      // "order": [[ 3, "desc" ]]
    });
    tableinventory.on( 'order.dt search.dt', function () {
        tableinventory.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
            cell.innerHTML = i+1;
        } );
    } ).draw();
    var tableInventoryQtylocation =  $('#tableInventoryQtylocation').DataTable();
    $('#modal-item_remark').blur(function() {
      var item_remark = $.trim($('#modal-item_remark').val());
      var item_id = $('#modal-item_id').val();
      var remark_type = $('#modal-remark_type').val();

      if(item_remark!="" && item_id!=""){

        $.ajax({
          url: "{{route('inventory.update_remark')}}",
          type: "POST",
          data: {
              item_id: item_id,
              item_remark: item_remark,
              remark_type: remark_type,
          },
          cache: false,
          success: function(dataResult){
            var remark_html = '<a href="#" data-toggle="modal" data-target="#remarks_qty" data-item_id="'+item_id+'" data-item_remark="'+item_remark+'" data-remark_type="'+remark_type+'">'+item_remark+'</a>';
              $("#remarks_qty").modal('hide');
              $("#productResponce").html(dataResult);
              $("#remark_"+item_id).html(remark_html);
          }
        });
      }
    });

  });

    // data attributes to scan when populating modal values
    var ATTRIBUTES = ['item_id', 'item_remark', 'remark_type'];

    $('[data-toggle="modal"]').on('click', function (e) {
      var $target = $(e.target);
      var modalSelector = $target.data('target');
      
      ATTRIBUTES.forEach(function (attributeName) {
        var $modalAttribute = $(modalSelector + ' #modal-' + attributeName);
        var dataValue = $target.data(attributeName);  
        $modalAttribute.val(dataValue || '');
      });
    });

function show_receipt(id,voucher_ids) {
  $.ajax({
    url: "{{route('opossum.reciept')}}",
    type: 'post',
    data: {
      'reciept_id': id,
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
</script>
@include('settings.buttonpermission')
@endsection

</div>
@endsection
