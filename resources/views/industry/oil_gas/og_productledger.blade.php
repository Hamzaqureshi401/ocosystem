@extends('layouts.layout')
@section('content')

<style>
.butns{
  display: none
}
th{
vertical-align: middle !important;
  text-align: center
}
td{
  vertical-align: middle !important;
}
.bg-primary:hover{
  color:white;
}
</style>
@include('industry.oil_gas.og_buttons')
<div id="landing-view">

<!--white abolone-->
<style media="screen">
a:link{
	text-decoration: none!important;
}
@media (min-width: 1025px) {
	#ogProductLeger{
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
<div class="modal fade" id="remarks_qty" tabindex="-1"
	role="dialog" aria-labelledby="productcontModallabel"
	aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document" >
		<div class="modal-content">
		<form  class="m-form  m-form-state m-form--label-align-right " >
			<div class="modal-body">
				<div class="m-form_content">
					<input type="hidden" id="modal-item_id"
						name="item_id" value="">
					<input type="hidden" id="modal-remark_type"
						name="remark_type" value="">
					<textarea id="modal-item_remark" name="remark"
						class="form-control m-input" rows="3"
						placeholder="Remarks"></textarea>
				</div>
			</div>
		</form>
		</div>
	</div>
</div>

<div class="row"
  style="padding-top:0;height:75px;margin-top:0px;margin-bottom:0">
  <div class="col-sm-4" style="align-self:center">
    <h2 style="margin-bottom:8px;padding-top: 0;">Product Ledger: Oil & Gas</h2>
  </div>

  <div class="col-sm-1" style="align-self:center">
  @if (!empty($product->thumbnail_1))
    <img src="{{ asset('images/product/'.$product->id.'/thumb/'.$product->thumbnail_1) }}"
      alt="Logo" width="70px" height="70px" alt="Logo"
      style="object-fit:contain;float:right;margin-left:0;margin-top:0;">
  @endif
   </div>

   <div class="col-sm-5" style="align-self:center;float:left;padding-left:0">
       <h4 style="margin-bottom:0px;padding-top: 0;line-height:1.5;">@if(isset($product) && $product->name){{$product->name}} @else Product Name @endif</h4>
       <p style="font-size:18px;margin-bottom:0"> @if(isset($product) && $product->systemid){{$product->systemid}}@endif</p>
   </div>
      <div class="col-sm-3" style="float: right;">
      </div>
  </div>
  <div class="table-responsive mb-5" style="overflow-x: hidden;">
  <table class="table table-bordered" id="ogProductLeger" style="width: 100%;">
    <thead class="thead-dark">
          <tr>
              <th class="text-center" style="width:30px;text-align: center;">No</th>
              <th class="text-center" style="width:150px;text-align: center;">Document&nbsp;No</th>
              <th class="text-center" style="width: 11%">Type</th>
              <th class="text-center" style="width: 120px" nowrap>Last&nbsp;Update</th>
              <th class="text-center" style="width: auto;">Location</th>
              <th class="text-center" style="width: 80px">Litre&nbsp;(&ell;)</th>
              <!--th class="text-left" style="width: 36%">Remarks</th-->

          </tr>
    </thead>
    <tbody>
        @inject('str', 'Illuminate\Support\Str')

        <!--
        Types of ledger data (table names):
        1. opos_receiptproduct
        2. stockreportproduct
        3. opos_refund
         -->
        @foreach($data as $value)
            @php
      $type="";
      if($value->table_name == 'opos_receiptproduct'){
        $lastUpdate = $value->updated_at;
        $location = $value->location ?? '-';
        $quantity =  $value->quantity ? '-'.number_format($value->quantity,  2) : 0;
        $remarks = $value->remarks ?? null;
        $remark_type = 'cash_sale_receipt';
        $item_id = $value->receipt->id;

      } else if($value->table_name == 'stockreportproduct'){
        $lastUpdate = $value->stock_report->updated_at;
        $location = $value->stock_report->location->branch ?? '-';
        $quantity = $value->quantity ? number_format($value->quantity,  2) : 0;
        $remarks = $value->stock_report->remark->remarks ?? null;
        $remark_type = 'stock';
        $item_id = $value->stock_report->id;
      }
  
      Log::debug('RF refund_type='.$value->refund_type);

      if($value->refund_type == "Cp"){
         $type= "Cash Sales";

      }else{
        if ($value->table_name == 'opos_receiptproduct' &&
          $value->void == 1){
          $type= "Void Sales";
        } elseif ($value->table_name == 'opos_receiptproduct'){
          $type= "Cash Sales";
        } elseif ($value->table_name == 'stockreportproduct'){
          if($value->stock_report->type){
            $type = $value->stock_report->type;

            if($str->is('stock*', $type)){
              $type = 'Stock'.' '.
                $str->ucfirst(explode('stock',$type)[1]);
            } else if ($value->stock_report->type == 'cforward') {
              $type = 'C/Forward';
            } else if ($value->stock_report->type == 'daily_variance') {
              $type = 'Daily Variance';
            } else{
              $type = $str->ucfirst($type);
            }
          }
        }
      }

          $filtertypes =['Voided','Transfer','Stocktake'];
            @endphp
            @if(!in_array($type , $filtertypes))
            <tr>
                <td style="text-align: center;"></td>
                <td style="text-align: center;@if($value->void == 1) background-color: red; @endif">
                    @if($value->table_name == 'opos_receiptproduct')
					<span onclick="show_receipt('{{$value->document_no}}')" class="os-linkcolor" 
						style="cursor: pointer; margin: 0;display:inline-block">{{$value->document_no}}</span>
                    @elseif($value->table_name == 'stockreportproduct')
                        {{$value->stock_report->systemid}}
                    @else
                    @endif
                </td>
                <td style="text-align: center;" nowrap>
                  @if($type == "Refundcp")
                    Refund Cp
                  @else
                    {{$type}}
                  @endif
                </td>
                <td style="text-align: center;" nowrap>{{ date('dMy H:i:s',strtotime($lastUpdate)) }}</td>
                <td style="text-align: center;" nowrap>{{$location}}</td>
				<td style="text-align: center;">
						@if ($type == 'Daily Variance')						
							{{ number_format($value->quantity/100 , 2) }}
						@elseif ($value->void == 1) 
								0.00 
						@elseif ($value->refund_type == "Cp")  
							-{{ number_format($value->quantity , 2) }} 
						@else
							{{$quantity}} 
						@endif
				</td>
                <!--td style="text-align: left; padding-left: 15px;" class="remarks" id='remark_{{$item_id}}'>
                    <a id="{{'remark_' . $item_id}}"
                       href="#"
                       data-toggle="modal"
                       data-target="#remarks_qty"
                       data-item_id="{{$item_id}}"
                       data-item_remark="{{$remarks ?? ''}}"
                       data-remark_type='{{$remark_type}}'>
                        {{$remarks ?? 'Remarks'}}
                    </a>
                </td-->
            </tr>
            @endif
			@endforeach
    </tbody>
  </table>
  </div>
{{--@include('inventory.inventoryqtypdtlocation')--}}
@section('scripts')
<div id="productResponce"></div>
<div id="response"></div>

<script type="text/javascript">
  $(document).ready(function () {
    var tableinventory =  $('#ogProductLeger').DataTable({
      // dd(tableinventory)
      // "order": [[ 3, "desc" ]]
    });

    tableinventory.on( 'order.dt search.dt', function () {
        tableinventory.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
            cell.innerHTML = i+1;
        } );
    } ).draw();
    var ogProductLegerlocation =  $('#ogProductLegerlocation').DataTable();

    $('#modal-item_remark').keyup(function(){
       var ir = $('#modal-item_remark').val();
        if(ir){
            $('#modal-item_remark').blur(function() {
              var item_remark = $.trim($('#modal-item_remark').val());
              var item_id = $('#modal-item_id').val();
              var remark_type = $('#modal-remark_type').val();

              console.log(item_remark + ' id : ' +item_id + ' type : ' + remark_type);

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
                      $('#modal-item_remark').val('');
                  }
                });
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
	if(attributeName == 'item_remark'){
	console.log('MODAL ATTRIB',$modalAttribute,'DATA VAL',dataValue);
          $modalAttribute.text(dataValue || '');
        } else {
          $modalAttribute.val(dataValue || '');
        }
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
{{-- @include('settings.buttonpermission') --}}
@endsection

</div>
@endsection
