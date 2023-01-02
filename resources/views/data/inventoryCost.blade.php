@extends('layouts.layout')
@section('content')
    <style type="text/css">

        #products-datatable tbody td{
            display: table-cell;
            vertical-align: inherit;
            padding-bottom: 2px !important;
            padding-top: 2px !important;
        }

        #products-datatable thead th {
            padding: 12px 18px 12px 18px;
        }

        .date_table >  tbody > tr > th {
            font-size:22px;
            color:white;
            background-color: rgba(255, 255,255, 0.5);
        }

        .date_table > tbody > tr > td {
            color:#fff;
            font-weight: 600;
            border:unset;
            font-size: 20px;
            cursor:pointer;
        }
        .disable-day {
            color: #a0a0a0 !important;
            cursor: not-allowed !important;
        }

        .decrease, .increase {
            border-radius: 15px;
        }
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        input.number {
            text-align: center;
            border: none;
            border: 1px solid #e2dddd;
            margin: 0px;
            width: 90px;
            border-radius: 5px;
            height: 38px;
            border-radius: 5px;
            background-color: #d4d3d36b !important;
            vertical-align: text-bottom;
        }
    </style>
    <link href="{{ asset('css/ionicons.min.css') }}" rel="stylesheet">

    <div id="landing-view">
        <div id="landing-content" style="width: 100%">

            <div class="row mb-2" style="padding-top:5px">
                <div class="col-sm-3" style="align-self:center">
                    <h2 style="margin-bottom:0px;padding-top: 0;">Inventory Cost</h2>
                </div>
                <div class="col-sm-5" style="align-self:center;float:left;padding-left:0">
                    <h4 style="margin-bottom:0px;padding-top: 0;line-height:1.5;">{{ $merchant_data['name'] }}</h4>
                    <p style="font-size:18px;margin-bottom:0">{{ $merchant_data['systemId'] }}</p>
                </div>
                <div class="col-sm-3 js-date-container" style="float: right;" >
                    <div class="row">
                        <div class="col-md-12 text-right mb-1" style="line-height: 0.5;margin-top:0;">
                            <input type="text" class="form-control" name="documentNo" placeholder="Document No" />
                        </div>
                    </div>
                    <div class="row">
                        <dov class="col-md-12">
                            <div style="right:190px;display:inline;padding-left:0; margin-bottom:20px">
                                <input class="form_date form-control"
                                       style="display:inline;
									cursor:pointer;"
                                       onclick="show_dialog4()"
                                       id="date_from"  name="froms" placeholder="Dated"/>
                                <input type="hidden" id='startDate' name="startDate">
                            </div>

                        </dov>
                    </div>
                </div>
                <div class="col-sm-1">
                    <button class="btn btn-success btn-log bg-confirm sellerbutton float-right" id="confirm-btn" style="width:70px !important;height:70px !important;margin-right: 0px;margin-bottom:0">
                        <span>Confirm</span>
                    </button>
                </div>
            </div>



            <table class="table table-bordered" id="products-datatable" style="width:100%;">
                <thead class="thead-dark">
                <tr>
                    <th style="width:30px;">No</th>
                    <th style="width:150px;">Product ID</th>
                    <th>Product Name</th>
                    <th style="width:80px;">Product</th>
                    <th style="width:90px;">Cost (MYR)</th>
                    <th style="width:135px;text-align: center;" nowrap>Qty</th>
                    <th style="width:60px;">
                        Amount&nbsp;(MYR)
                    </th>
                </tr>
                </thead>
                <tbody>
                @foreach($inventory_data as $key => $in_product)
                    <tr>
                        <td></td>
                        <td>{{$in_product->systemid}}</td>
                        <td><img src="{{ asset('images/product/'.$in_product->id.'/thumb/'.$in_product->thumbnail_1) }}" data-field='inven_pro_name' style=' width: 40px;height: 40px;display: inline-block;margin-right: 8px;object-fit:contain;'>@if(!empty($in_product->name)) {{$in_product->name}} @endif</td>

                        <td class="text-center">
                            @if($in_product->ptype == 'inventory')
                                Inventory
                            @elseif($in_product->ptype == 'rawmaterial')
                                Raw Material
                            @endif
                        </td>

                        <td>
                            <p class="os-linkcolor loyaltyOutput js-product-cost" data-id="{{ $in_product->id  }}" style="cursor: pointer; margin: 0; text-align: center;">0.00</p>
                        </td>
                        <td>
                            <div class="value-button increase" id="increase_{{$in_product->id}}" onclick="increaseValue('{{$in_product->id}}')" value="Increase Value" style="margin-top:-25px;">
                                <ion-icon class="ion-ios-plus-outline" style="font-size: 24px;margin-right:5px;"></ion-icon>
                            </div>
                            <input type="number" id="number_{{$in_product->id}}"  class="number product_qty js-product-qty" value="0"  min="0" required>
                            <div class="value-button decrease" id="decrease_{{$in_product->id}}" onclick="decreaseValue('{{$in_product->id}}')" value="Decrease Value" style="margin-top:-25px;">
                                <ion-icon class="ion-ios-minus-outline" style="font-size: 24px;margin-left:5px;"></ion-icon>
                            </div>
                        </td>
                        <td>
							<span class="js-product-qty-cost">0.00</span>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <div class="modal" id="productCostModel">
                <div class="modal-dialog modal-sm modal-dialog-centered">
                    <div class="modal-content">
                        <!-- Modal body -->
                        <div class="modal-body">
                            <input type="text" id="productCost" name="productCost"  placeholder="0.00" class="form-control text-right"/>
                            <input type="hidden" id="buffer_main_price"  value="0.00">
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="msgModal"  tabindex="-1"
                 role="dialog" aria-labelledby="staffNameLabel"
                 aria-hidden="true" style="text-align: center;">

                <div class="modal-dialog modal-dialog-centered  mw-75 w-50"
                     role="document" style="display: inline-flex;">
                    <div class="modal-content modal-inside bg-greenlobster"
                         style="width: 100%;">
                        <div class="modal-body text-center">
                            <br/><br/>
                            <h5 class="modal-title text-white"
                                id="status-msg-element"></h5>
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

            <div class="modal fade" id="showDateModalFrom" tabindex="-1"
                 role="dialog" aria-labelledby="staffNameLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered  mw-75 w-50" role="document">
                    <div class="modal-content modal-inside bg-greenlobster" >
                        <div class="modal-body text-center" style="min-height: 450px;">
                            <div class="row">
                                <div class="col-md-2">
                                    <i class="prev-month fa fa-chevron-left fa-3x"
                                       style="cursor:pointer;display: inline-flex;"></i>
                                </div>
                                <div class=" col-md-8">
                                    <div class="month-year text-center text-white" ></div>
                                </div>
                                <div class="col-md-2">
                                    <i style="cursor:pointer"
                                       class="next-month fa fa-chevron-right fa-3x"></i>
                                </div>
                            </div>

                            <table class="table date_table">
                                <tr>
                                    <th>S</th> <th>M</th> <th>T</th><th>W</th>
                                    <th>T</th> <th>F</th> <th>S</th>
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

        </div>





        <style>
            .btn {color: #fff !Important;}

        </style>

        <style>
            .form-control:disabled, .form-control[readonly] {
                background-color: #e9ecef !important;
                opacity: 1;
            }
            a{
                text-decoration: none;
            }
        </style>

        @section('scripts')
            @include('settings.buttonpermission')

            <script type="text/javascript">
                var CURRENT_DATE = new Date();
                var d = new Date();

                var content = 'January February March April May June July August September October November December'.split(' ');
                var weekDayName = 'SUN MON TUES WED THURS FRI'.split(' ');
                var daysOfMonth = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
                var productTableRow;

                $(document).ready(function () {

                    var tablevocherproduct =  $('#products-datatable').DataTable({
                        "order": [],
                        "columnDefs": [
                            {
                                "targets": [3,4,5,6],
                                'orderable' : false
                            },
                            {
                                "className": "dt-center",
                                "targets": [0,1]
                            },
                            {
                                "className": "dt-body-nowrap num_td",
                                "targets": [2]
                            },
                            {
                                "className": 'text-left',
                                "targets": [3]
                            },
                            {
                                "className": "dt-body-nowrap num_td cost-td",
                                "targets": [4]
                            },
                            {
                                "className": "dt-center qty-td",
                                "targets": [5]
                            },
                            {
                                "className": "text-right",
                                "targets": [6]
                            }

                        ],
                        "autoWidth" : true
                    });
                    tablevocherproduct.on( 'order.dt search.dt', function () {
                        tablevocherproduct.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
                            cell.innerHTML = i+1;
                        } );
                    }).draw();

                    $("#confirm-btn").click(function(event) {

                        var products = [];
                        $("#products-datatable tbody tr").each(function(index, row) {
                            var productCost = $(row).find('.js-product-cost').text();
                            var qty = $(row).find('.js-product-qty').val();
                            var productId = $(row).find('.js-product-cost').attr('data-id');
                            if (qty != '0') {
                                products.push({
                                    productId: productId,
                                    productCost: productCost,
                                    qty: qty
                                });
                            }
                        });

                        var data = {
                            documentNo: $.trim($('[name="documentNo"]').val()),
                            dated: $('[name="startDate"]').val(),
                            products: products,
                            merchantId: '{{ $merchant_id  }}'
                        };


                        if (data.documentNo === '') {
                            displayStatusMsgPopup('Document No is required');
                            return false;
                        }

                        if (data.dated === '') {
                            displayStatusMsgPopup('Dated is required');
                            return false;
                        }

                        if (data.products.length === 0) {
                            displayStatusMsgPopup('Enter Quantity for at least one product');
                            return false;
                        }


                        $.ajax({
                            url: '{{route("data.ajax.saveInventoryCost")}}',
                            type: 'post',
                            data: data,
                            success: function (response) {

                                if (response.status == 'true') {

                                    $("#products-datatable tbody tr").each(function(index, row) {
                                        var productCost = $(row).find('.js-product-cost').text();
                                        var qty = $(row).find('.js-product-qty').val();
                                        if (qty == '0') {
                                            $(row).remove();
                                        }
                                    });

                                    $("#products-datatable tr:gt(0):visible").each(function(index, row){
                                        $(row).find('td:eq(0)').text(index+1);
                                    });

                                    $(".increase, .decrease").remove();
                                    $(".qty-td").css("width", "60px");
                                    $(".qty-td input").each(function(index, qtyInput){
                                        $(qtyInput).replaceWith($(qtyInput).val());
                                    });;

                                    $(".js-product-cost").css('cursor', 'not-allowed');
                                    $(".js-product-cost").css('text-align', 'right');
                                    $(".js-product-cost").removeClass('js-product-cost os-linkcolor');


                                    $( "#date_from" ).replaceWith('<h4>'+$("#date_from").val()+ '</h4>');

                                    $('[name="documentNo"]').replaceWith('<h4>'+$('[name="documentNo"]').val()+'</h4>');

                                    $('#confirm-btn').parent().remove();
                                    $(".js-date-container").addClass('offset-1 text-right');

                                    displayStatusMsgPopup(response.msg);
                                } else {
                                    displayStatusMsgPopup(response.msg);
                                }


                            }
                        });

                    });


                    $('#products-datatable').on('click', '.js-product-cost', function(event){
                        productTableRow = $(this).parents('tr');
                        $("#productCost").val($(this).text());
                        if ($(this).text() != '0.00') {
                            $("#buffer_main_price").val($(this).text());
                        } else {
                            $("#buffer_main_price").val('')
                        }

                        $("#productCostModel").modal('show');
                        $("#productCost").focus();
                    });


                    $('#showDateModalFrom').on('hidden.bs.modal', function (e) {
                        onDateSelect();
                    });

                    $('.prev-month').click(function() {
                        navigationHandler(-1);
                    });
                    $('.next-month').click(function() {
                        navigationHandler(1);
                    });
                    myCalendar();
                    shoot_event();
                });

                function displayStatusMsgPopup(msg) {
                    $("#status-msg-element").text(msg);
                    $("#msgModal").modal('show');
                    setTimeout(function() {
                        $("#msgModal").modal('hide');
                        $('.modal-backdrop').remove();
                    },3500);
                }

                function atm_money(num) {
                    if (num.toString().length == 1) {
                        return '00.0' + num.toString()
                    } else if (num.toString().length == 2) {
                        return '00.' + num.toString()
                    } else if (num.toString().length == 3) {
                        return '0' + num.toString()[0] + '.' + num.toString()[1] + num.toString()[2];
                    } else if (num.toString().length >= 4) {
                        return num.toString().slice(0, (num.toString().length - 2)) + '.' + num.toString()[(num.toString().length - 2)] + num.toString()[(num.toString().length - 1)];
                    }
                }

                filter_price("#productCost","#buffer_main_price");

                function filter_price(target_field,buffer_in) {
                    $(target_field).on( "keydown", function( event ) {
                        event.preventDefault();

                        if (event.keyCode == 8) {
                            $(buffer_in).val('');
                            $(target_field).val('');
                            $(productTableRow).find('.js-product-cost').text('0.00');
                            $(productTableRow).find('.js-product-qty-cost').text('0.00');
                            return null
                        }

                        if (isNaN(event.key) || $.inArray( event.keyCode, [13,38,40,37,39] ) !== -1 || event.keyCode == 13  ) {
                            if ($(buffer_in).val() != '') {
                                var totalPrice = atm_money(parseInt($(buffer_in).val()));
                                $(target_field).val(totalPrice);
                                $(productTableRow).find('.js-product-cost').text(totalPrice);

                                var qty = $(productTableRow).find('.js-product-qty').val();
                                $(productTableRow).find('.js-product-qty-cost').text(qty * totalPrice);
                            } else {
                                $(target_field).val('');
                                $(productTableRow).find('.js-product-cost').text('0.00');
                                $(productTableRow).find('.js-product-qty-cost').text('0');
                            }
                            return null;
                        }

                        const input =  event.key;
                        old_val = $(buffer_in).val()

                        if (old_val === '0.00') {
                            $(buffer_in).val('');
                            $(target_field).val('');
                            $(productTableRow).find('.js-product-cost').text('0.00');
                            $(productTableRow).find('.js-product-qty-cost').text('0.00');
                            old_val = ''
                        }

                        $(buffer_in).val(''+old_val+input)
                        var totalPrice = atm_money(parseInt($(buffer_in).val()));
                        $(target_field).val(number_format(totalPrice, 2));
                        $(productTableRow).find('.js-product-cost').text(number_format(totalPrice, 2));

                        var qty = $(productTableRow).find('.js-product-qty').val();
                        $(productTableRow).find('.js-product-qty-cost').text(qty == 0 ? '0.00' : number_format(qty * totalPrice, 2));

                    });
                }

                function number_format (number, decimals, decPoint, thousandsSep) {
                    number = (number + '').replace(/[^0-9+\-Ee.]/g, '')
                    var n = !isFinite(+number) ? 0 : +number
                    var prec = !isFinite(+decimals) ? 0 : Math.abs(decimals)
                    var sep = (typeof thousandsSep === 'undefined') ? ',' : thousandsSep
                    var dec = (typeof decPoint === 'undefined') ? '.' : decPoint
                    var s = ''

                    var toFixedFix = function (n, prec) {
                        if (('' + n).indexOf('e') === -1) {
                            return +(Math.round(n + 'e+' + prec) + 'e-' + prec)
                        } else {
                            var arr = ('' + n).split('e')
                            var sig = ''
                            if (+arr[1] + prec > 0) {
                                sig = '+'
                            }
                            return (+(Math.round(+arr[0] + 'e' + sig + (+arr[1] + prec)) + 'e-' + prec)).toFixed(prec)
                        }
                    }

                    // @todo: for IE parseFloat(0.55).toFixed(0) = 0;
                    s = (prec ? toFixedFix(n, prec).toString() : '' + Math.round(n)).split('.')
                    if (s[0].length > 3) {
                        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep)
                    }
                    if ((s[1] || '').length < prec) {
                        s[1] = s[1] || ''
                        s[1] += new Array(prec - s[1].length + 1).join('0')
                    }

                    return s.join(dec)
                }

                @yield('current_year')

                function navigationHandler(dir) {
                    d.setUTCMonth(d.getUTCMonth() + dir);
                    clearCalendar();
                    myCalendar();


                    var month = d.getUTCMonth();
                    var day = d.getUTCDay();
                    var date = d.getUTCDate();
                    var totalDaysOfMonth = daysOfMonth[month];
                    var year = d.getUTCFullYear();

                    var dateToHighlight = 0;

                    // Determine if Month && Year are current for Date Highlight
                    if (CURRENT_DATE.getUTCMonth() === month && CURRENT_DATE.getUTCFullYear() === year) {
                        dateToHighlight = date;
                    }

                    if (month === 1) {
                        if ((year % 100 !== 0) && (year % 4 === 0) || (year % 400 === 0)) {
                            totalDaysOfMonth = 29;
                        }
                    }

                    shoot_event();

                }

                function show_dialog4() {
                    $('#showDateModalFrom').modal('show');
                }

                function onDateSelect() {
                    const val = $('#startDate').val();

                    const selectedDate = new Date(val);
                    if (selectedDate == 'Invalid Date') {
                        return false;
                    }



                    const todaysDate = new Date();


                    $('#date_from').val(selectedDate.getDate()+''+selectedDate.toLocaleString('en-us',
                            { month: 'short' })+''+selectedDate.getFullYear().toString().substr(-2));

                    if (todaysDate.getFullYear() > selectedDate.getFullYear()) {
                        displayStatusMsgPopup('You can only select from this year!');
                        $('#startDate').val('');
                        return false;
                    }

                    $("#date_to").removeAttr("disabled");


                    var month = d.getUTCMonth();
                    var day = d.getUTCDay();
                    var date = d.getUTCDate();
                    var totalDaysOfMonth = daysOfMonth[month];
                    var year = d.getUTCFullYear();

                    var dateToHighlight = 0;

                    // Determine if Month && Year are current for Date Highlight
                    if (CURRENT_DATE.getUTCMonth() === month && CURRENT_DATE.getUTCFullYear() === year) {
                        dateToHighlight = date;
                    }

                    if (month === 1) {
                        if ((year % 100 !== 0) && (year % 4 === 0) || (year % 400 === 0)) {
                            totalDaysOfMonth = 29;
                        }
                    }

                    if ($('#startDate1').val() != '') {
                        var fromDate = new Date($('#startDate').val());
                        var toDate = new Date($('#startDate1').val());

                        if (fromDate === toDate || toDate.getTime() < fromDate.getTime()) {
                            $('#startDate1').val('');
                            $('#date_to').val('');
                        }
                    }
                }

                function myCalendar() {
                    var month = d.getUTCMonth();
                    var day = d.getUTCDay();
                    var year = d.getUTCFullYear();
                    var date = d.getUTCDate();
                    var totalDaysOfMonth = daysOfMonth[month];
                    var counter = 1;

                    var $h3 = $('<h3>');

                    $h3.text(content[month] + ' ' + year);
                    $h3.appendTo('.month-year');

                    var dateToHighlight = 0;

                    // Determine if Month && Year are current for Date Highlight
                    if (CURRENT_DATE.getUTCMonth() === month && CURRENT_DATE.getUTCFullYear() === year) {
                        dateToHighlight = date;
                    }

                    //Getting February Days Including The Leap Year
                    if (month === 1) {
                        if ((year % 100 !== 0) && (year % 4 === 0) || (year % 400 === 0)) {
                            totalDaysOfMonth = 29;
                        }
                    }

                    // Get Start Day
                    renderCalendar(getCalendarStart(day, date), totalDaysOfMonth, dateToHighlight);
                    //renderCalendar1(getCalendarStart(day, date), totalDaysOfMonth, dateToHighlight);
                }

                // Clear generated calendar
                function clearCalendar() {
                    $("#showDateModalFrom table").find('tr').not(':eq(0)').remove();
                    $('.month-year').empty();
                }

                // Generates table row used when rendering Calendar
                function getCalendarRow() {
                    var $table = $('table.date_table');
                    var $tr = $('<tr/>');
                    for (var i = 0, len = 7; i < len; i++) {
                        $tr.append($('<td/>'));
                    }
                    $table.append($tr);
                    return $tr;
                }

                function shoot_event () {
                    $('.date_table > tbody > tr > td').click(function(e) {
                        var target = e.target;

                        if ($(target).hasClass('disable-day') || $(target).html() == '') {
                            return false;
                        }
                        $('.date_table > tbody > tr > td').removeClass('selected_date');
                        $(target).addClass('selected_date');
                        var day = $(target).html();
                        var month  = $('.month-year > h3').html();
                        $('#startDate').val(day+' '+month);
                        jQuery('#showDateModalFrom').modal('hide');
                    });
                }

                // Render Calendar
                function renderCalendar(startDay, totalDays, currentDate) {
                    var currentRow = 1;
                    var currentDay = startDay;
                    var $table = $('table');
                    var $week = getCalendarRow();
                    var $day;
                    var i = 1;

                    var todayDate = new Date();

                    for (; i <= totalDays; i++) {
                        $day = $week.find('td').eq(currentDay);

                        var calendarDate = new Date(d.getFullYear()+'-'+(d.getMonth() + 1)+'-'+i);

                        if ((todayDate.getFullYear()+'-'+(todayDate.getMonth())+'-'+todayDate.getDate()) != (d.getFullYear()+'-'+(d.getMonth())+'-'+i)) {
                            if (calendarDate.getTime() > todayDate.getTime()) {
                                $day.addClass('disable-day');
                            }
                        }

                        $day.text(i);
                        if (i === currentDate) {
                            $day.addClass('today');
                        }

                        // +1 next day until Saturday (6), then reset to Sunday (0)
                        currentDay = ++currentDay % 7;

                        // Generate new row when day is Saturday, but only if there are
                        // additional days to render
                        if (currentDay === 0 && (i + 1 <= totalDays)) {
                            $week = getCalendarRow();
                            currentRow++;
                        }
                    }
                }

                // Returns the day of week which month starts (eg 0 for Sunday, 1 for Monday, etc.)
                function getCalendarStart(dayOfWeek, currentDate) {
                    var date = currentDate - 1;
                    var startOffset = (date % 7) - dayOfWeek;
                    if (startOffset > 0) {
                        startOffset -= 7;
                    }
                    return Math.abs(startOffset);
                }

                function increaseValue(id) {
                    var num_element = document.getElementById('number_'+id);
                    var value = parseInt(num_element.value, 10);
                    value = isNaN(value) ? 0 : value;
                    value++;
                    num_element.value = value;

                    var row = $('#number_'+id).parents('tr');
                    var cost = $(row).find('.js-product-cost').text();
                    if (cost == '0.00') {
                        cost = 0;
                    }
                    $(row).find('.js-product-qty-cost').text(number_format(value * cost, 2));

                    var price = $("#price_1").text();
                    var total  = price * value;
                    var totaldcmal = total.toFixed(2);
                    $("#total_1").html(totaldcmal);


                }

                function decreaseValue(id) {
                    var num_element = document.getElementById('number_'+id);

                    var value = parseInt(num_element.value, 10);
                    value = isNaN(value) ? 0 : value;
                    value < 1 ? value = 1 : '';
                    value--;
                    num_element.value = value;
                    // num_element.focus();

                    var row = $('#number_'+id).parents('tr');
                    var cost = $(row).find('.js-product-cost').text();
                    if (cost == '0.00' || value == 0) {
                        $(row).find('.js-product-qty-cost').text('0.00');
                    } else {
                        $(row).find('.js-product-qty-cost').text(number_format(value * cost, 2));
                    }


                    var price = $("#price_1").text();
                    var total  = price * value;
                    var totaldcmal = total.toFixed(2);
                    $("#total_1").html(totaldcmal);
                }


            </script>
            @include('settings.buttonpermission')


        @endsection
    </div>
    </div>
@endsection
