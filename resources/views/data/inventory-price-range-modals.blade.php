<div class="modal" id="showMsgModal">
    <div class="modal-dialog modal-lg" style="width: 600px;">
      <div class="modal-content">
        <!-- Modal body -->
        <div class="modal-body">
            <form action="#" id="updateProspectFields" method="post" enctype="multipart/form-data" onsubmit="return false;" autocomplete="off">
                <div class="container">
                    <div class="row">
                        <div class="col-sm">
{{--                                <h4 class="font-weight-bold d-inline">Retail Price <span class="d-inline" style="font-weight: 300;font-size: 17px;">MYR</span> </h4> <input type="text" disabled name="price" id="main_price" class="pl-1 d-inline" style="width:150px; border: 1px solid #ddd;" value="{{empty($inventory->price) ? '0.00':number_format(($inventory->price/100),2)}}"/>--}}

{{--                                <input type="hidden" id="buffer_main_price" value="{{empty($inventory->price) ? '0.00':str_replace('.','',number_format(($inventory->price/100),2))}}">--}}
{{--                                <input type="hidden"  name="prd_inventory_id" value="{{$inventory->id}}"/>--}}

                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-sm-6"><h4 class="font-weight-bold">Wholesale Price</h4></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm">
                            <p id="wholsale_input_fu1" style="width:150px; display: inline-block;text-align: center;margin: unset; ">1</p><p class="d-inline">&nbsp;to&nbsp;</p>
                            <input type="hidden"  name="wholsale_input_fu1" value="1"/>
                            <input id="wholsale_input_tu1" name="wholsale_input_tu1" value="{{$product_whole_sale_price_and_range[0]['unit'] !== 0 ? $product_whole_sale_price_and_range[0]['unit'] : "" }}" type="text" disabled class="pl-1 d-inline" style="width:150px; border: 1px solid #ddd;"><p class=" d-inline">&nbsp;MYR&nbsp;</p>
                            <input type="text" disabled disabled id="wholsale_input_1" name="wholsale_input_1" value="{{$product_whole_sale_price_and_range[0]['price'] !== 0 ? $product_whole_sale_price_and_range[0]['price']/100 : "" }}" class="pl-1 d-inline" style="width:150px; border: 1px solid #ddd;" >
                            <input type="hidden" id="wholsale_input_buffer_1">
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm">
                            <input id="wholsale_input_fu2" name="wholsale_input_fu2" value="{{$product_whole_sale_price_and_range[1]['funit'] !== 0 ? $product_whole_sale_price_and_range[1]['funit'] : "" }}" type="text" disabled disabled class="pl-1 d-inline" style="width:150px; border: 1px solid #ddd;"><p class="d-inline">&nbsp;to&nbsp;</p>
                            <input id="wholsale_input_tu2" name="wholsale_input_tu2" value="{{$product_whole_sale_price_and_range[1]['unit'] !== 0 ? $product_whole_sale_price_and_range[1]['unit'] : "" }}"  type="text" disabled disabled class="pl-1 d-inline" style="width:150px; border: 1px solid #ddd;"><p class=" d-inline">&nbsp;MYR&nbsp;</p>
                            <input id="wholsale_input_2" name="wholsale_input_2" value="{{$product_whole_sale_price_and_range[1]['price'] !== 0 ? $product_whole_sale_price_and_range[1]['price']/100 : "" }}" type="text" disabled disabled class="pl-1 d-inline" style="width:150px; border: 1px solid #ddd;">
                            <input type="hidden" id="wholsale_input_buffer_2">
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm">
                            <input id="wholsale_input_fu3" name="wholsale_input_fu3" value="{{$product_whole_sale_price_and_range[2]['funit'] !== 0 ? $product_whole_sale_price_and_range[2]['funit'] : "" }}"  type="text" disabled disabled class="pl-1 d-inline" style="width:150px; border: 1px solid #ddd;"><p class="d-inline">&nbsp;to&nbsp;</p>
                            <input id="wholsale_input_tu3" name="wholsale_input_tu3" value="{{$product_whole_sale_price_and_range[2]['unit'] !== 0 ? $product_whole_sale_price_and_range[2]['unit'] : "" }}"  type="text" disabled disabled class="pl-1 d-inline" style="width:150px; border: 1px solid #ddd;"><p class=" d-inline">&nbsp;MYR&nbsp;</p>
                            <input id="wholsale_input_3" name="wholsale_input_3" value="{{$product_whole_sale_price_and_range[2]['price'] !== 0 ? $product_whole_sale_price_and_range[2]['price']/100 : "" }}"  type="text" disabled disabled class="pl-1 d-inline" style="width:150px; border: 1px solid #ddd;">
                            <input type="hidden" id="wholsale_input_buffer_3">
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm">
                            <input id="wholsale_input_fu4" name="wholsale_input_fu4" value="{{$product_whole_sale_price_and_range[3]['funit'] !== 0 ? $product_whole_sale_price_and_range[3]['funit'] : "" }}" type="text" disabled disabled class="pl-1 d-inline" style="width:150px; border: 1px solid #ddd;"><p class="d-inline">&nbsp;to&nbsp;</p>
                            <input id="wholsale_input_tu4" name="wholsale_input_tu4" value="{{$product_whole_sale_price_and_range[3]['unit'] !== 0 ? $product_whole_sale_price_and_range[3]['unit'] : "" }}" type="text" disabled disabled class="pl-1 d-inline" style="width:150px; border: 1px solid #ddd;"><p class=" d-inline">&nbsp;MYR&nbsp;</p>
                            <input id="wholsale_input_4" name="wholsale_input_4" value="{{$product_whole_sale_price_and_range[3]['price'] !== 0 ? $product_whole_sale_price_and_range[3]['price']/100 : "" }}"  type="text" disabled disabled class="pl-1 d-inline" style="width:150px; border: 1px solid #ddd;">
                            <input type="hidden" id="wholsale_input_buffer_4">
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm">
                            <input id="wholsale_input_fu5" name="wholsale_input_fu5" value="{{$product_whole_sale_price_and_range[4]['funit'] !== 0 ? $product_whole_sale_price_and_range[4]['funit'] : "" }}"  type="text" disabled disabled class="pl-1 d-inline" style="width:150px; border: 1px solid #ddd;"><p class="d-inline">&nbsp;to&nbsp;</p>
                            <input id="wholsale_input_tu5" name="wholsale_input_tu5" value="{{$product_whole_sale_price_and_range[4]['unit'] !== 0 ? $product_whole_sale_price_and_range[4]['unit'] : "" }}" type="text" disabled disabled class="pl-1 d-inline" style="width:150px; border: 1px solid #ddd;"><p class=" d-inline">&nbsp;MYR&nbsp;</p>
                            <input id="wholsale_input_5" name="wholsale_input_5" value="{{$product_whole_sale_price_and_range[4]['price'] !== 0 ? $product_whole_sale_price_and_range[4]['price']/100 : "" }}"  type="text" disabled disabled class="pl-1 d-inline" style="width:150px; border: 1px solid #ddd;">
                            <input type="hidden" id="wholsale_input_buffer_5">
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm">
                            <input id="wholsale_input_fu6" name="wholsale_input_fu6" value="{{$product_whole_sale_price_and_range[5]['funit'] !== 0 ? $product_whole_sale_price_and_range[5]['funit'] : "" }}"  type="text" disabled disabled class="pl-1 d-inline" style="width:150px; border: 1px solid #ddd;"><p class="d-inline">&nbsp;to&nbsp;</p>
                            <input id="wholsale_input_tu6" name="wholsale_input_tu6" value="{{$product_whole_sale_price_and_range[5]['unit'] !== 0 ? $product_whole_sale_price_and_range[5]['unit'] : "" }}"  type="text" disabled disabled class="pl-1 d-inline" style="width:150px; border: 1px solid #ddd;"><p class=" d-inline">&nbsp;MYR&nbsp;</p>
                            <input id="wholsale_input_6" name="wholsale_input_6" value="{{$product_whole_sale_price_and_range[5]['price'] !== 0 ? $product_whole_sale_price_and_range[5]['price']/100 : "" }}" type="text" disabled disabled class="pl-1 d-inline" style="width:150px; border: 1px solid #ddd;">
                            <input type="hidden" id="wholsale_input_buffer_6">
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm">
                            <input id="wholsale_input_fu7" name="wholsale_input_fu7" value="{{$product_whole_sale_price_and_range[6]['funit'] !== 0 ? $product_whole_sale_price_and_range[6]['funit'] : "" }}" type="text" disabled disabled class="pl-1 d-inline" style="width:150px; border: 1px solid #ddd;"><p class="d-inline">&nbsp;to&nbsp;</p>
                            <input id="wholsale_input_tu7" name="wholsale_input_tu7" value="{{$product_whole_sale_price_and_range[6]['unit'] !== 0 ? $product_whole_sale_price_and_range[6]['unit'] : "" }}"  type="text" disabled disabled class="pl-1 d-inline" style="width:150px; border: 1px solid #ddd;"><p class=" d-inline">&nbsp;MYR&nbsp;</p>
                            <input id="wholsale_input_7" name="wholsale_input_7" value="{{$product_whole_sale_price_and_range[6]['price'] !== 0 ? $product_whole_sale_price_and_range[6]['price']/100 : "" }}" type="text" disabled disabled class="pl-1 d-inline" style="width:150px; border: 1px solid #ddd;">
                            <input type="hidden" id="wholsale_input_buffer_7">
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm">
                            <input id="wholsale_input_fu8" name="wholsale_input_fu8" value="{{$product_whole_sale_price_and_range[7]['funit'] !== 0 ? $product_whole_sale_price_and_range[7]['funit'] : "" }}" type="text" disabled disabled class="pl-1 d-inline" style="width:150px; border: 1px solid #ddd;"><p class="d-inline">&nbsp;to&nbsp;</p>
                            <input id="wholsale_input_tu8" name="wholsale_input_tu8" value="{{$product_whole_sale_price_and_range[7]['unit'] !== 0 ? $product_whole_sale_price_and_range[7]['unit'] : "" }}" type="text" disabled disabled class="pl-1 d-inline" style="width:150px; border: 1px solid #ddd;"><p class=" d-inline">&nbsp;MYR&nbsp;</p>
                            <input id="wholsale_input_8" name="wholsale_input_8" value="{{$product_whole_sale_price_and_range[7]['price'] !== 0 ? $product_whole_sale_price_and_range[7]['price']/100 : "" }}" type="text" disabled disabled class="pl-1 d-inline" style="width:150px; border: 1px solid #ddd;">
                            <input type="hidden" id="wholsale_input_buffer_8">
                        </div>
                    </div>
                </div>
            </form>
        </div>
      </div>
    </div>
</div>

<script>
    //$('#rsPriceInput').on('change',function() {
//        var textVal = $('#rsPriceInput').val();
  //      var regex = /^(\$|)([1-9]\d{0,2}(\,\d{3})*|([1-9]\d*))(\.\d{2})?/;
    //    var passed = textVal.match(regex);
      //  if (passed == null) {
        //        alert("Enter price only. For example: 523.36");
         //    $('#rsPriceInput').val('0.00');
        //}
    //});

    //filter_price(target_field,buffer_in)
    filter_price("#main_price","#buffer_main_price")
    filter_price("#wholsale_input_1","#wholsale_input_buffer_1")
    filter_price("#wholsale_input_2","#wholsale_input_buffer_2")
    filter_price("#wholsale_input_3","#wholsale_input_buffer_3")
    filter_price("#wholsale_input_4","#wholsale_input_buffer_4")
    filter_price("#wholsale_input_5","#wholsale_input_buffer_5")
    filter_price("#wholsale_input_6","#wholsale_input_buffer_6")
    filter_price("#wholsale_input_7","#wholsale_input_buffer_7")
    filter_price("#wholsale_input_8","#wholsale_input_buffer_8")
    $('#showMsgModal').on('hidden.bs.modal', function (e) {
        updateInventory();
    });

    function updateInventory() {
        const form = $('#updateProspectFields')[0];
        console.log(form);
        const formData = new FormData(form);
        $.ajax({
            url: "{{route('inventory.update')}}",
            type: "POST",
            enctype: 'multipart/form-data',
            processData: false,  // Important!
            contentType: false,
            cache: false,
            data: formData,
            success: function (response) {
                inventoryTable.ajax.reload();
                 $("#modal").modal('hide');
                $("#showEditInventoryModal").html(response);

            }, error: function (e) {
                console.log(e.message)
            }
        });
    }
</script>