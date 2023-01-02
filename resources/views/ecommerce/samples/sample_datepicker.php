<style type="text/css">
    .date_table > tbody > tr > th {
        font-size: 22px;
        color: white;
        background-color: rgba(255, 255, 255, 0.5);
    }

    .date_table > tbody > tr > td {
        color: #fff;
        font-weight: 600;
        border: unset;
        font-size: 20px;
        cursor: pointer;
    }

    table.dataTable tbody td{
        border-left: 1px solid #dee2e6;
        border-right: 1px solid #dee2e6;
        border-top: none;
        border-bottom: none;
    }

    .btn-green {
        background-color: green !important;
        color: #fff !important;
        box-shadow: none !important;
        border: 0px !important;
    }

    .btn-green:focus {
        background-color: green !important;
        color: #fff !important;
        box-shadow: none !important;
        border: 0px !important;
    }

    .bg-blue {
        background-color: #007bff;
        color: #fff;
    }

    .date_table1 > tbody > tr > th {
        font-size: 22px;
        color: white;
        background-color: rgba(255, 255, 255, 0.5);
    }

    .date_table1 > tbody > tr > td {
        color: #fff;
        font-weight: 600;
        border: unset;
        font-size: 20px;
        cursor: pointer;
    }

    .selected_date {
        color: #fff !important;
        background: #008000;
        font-weight: 600 !important;
    }

    .selected_date1 {
        color: #fff !important;
        background: #008000;
        font-weight: 600 !important;
    }

    #Datepick .d-table {
        display: -webkit-flex !important;
        display: -ms-flexbox !important;
        display: flex !important;
    }

    .dataTables_filter input {
        width: 300px;
    }

    .greenshade {
        height: 30px;
        background-color: green; /* For browsers that do not support gradients */
        background-image: linear-gradient(-90deg, green, white); /* Standard syntax (must be last) */
    }
    .dt-button{
        display: none;
    }

    .bg-purplelobster{
        background-color: rgba(26, 188, 156, 0.7);
        border-color: rgba(26, 188, 156, 0.7);
    }

    /*//for calender short day*/
    .shortDay ul{
        llist-style: none;
        background-color: rgba(255, 255, 255, 0.5);
        position: relative;
        left: -75px;
        width: 124%;
        height: 55px;
        line-height: 42px;

     }
    .shortDay ul > li{
      font-size: 22px;
      color: white;
      font-weight: 700 !important;
      /* background-color: #2b1f1f; */
      padding: 5px 24px;
      text-align: left !important;
     }
      .list-inline-item:not(:last-child){
        margin-right: 0 !important;
    }
    .modal-content{
        overflow: hidden;
    }
    .modal-inside .row {
        margin: 0px;
        color: #fff;
        margin-top: 15px;
        padding: 0px !important;
    }
</style>
</style>
<div class="row py-2" style="padding-bottom:0px !important;margin-bottom:10px !important;margin-top:10px !important">
    <div class="col align-self-end" style="width:80%">
        <h2>Product Sales</h2>
        <a href="javascript:void(0)" class="since_ytd btn bg-blue bg-sales sellerbutton1 btndownload"
        onclick="filterSinceajax()"
        style="width:80px;margin-top:0px;padding-top:6px !important"
        id="graph-merchant-since"
        from="<?= date("2000-01-01"); ?>"
        to="<?php $date = new DateTime(date("Y-m-d"));$date->modify('+1 day');
        echo $date->format('Y-m-d'); ?>"
        name=""
        rel-type="since">Since
    </a>
    <a href="javascript:void(0)"
    class="ytd_btn btn bg-blue bg-sales sellerbutton1 btndownload"
    style="width:80px;margin-top:0px;padding-top:6px !important"
    onclick="filterYTDajax()"
    id="graph-merchant-ytd"
    name=""

    from="<?= date("Y-01-01"); ?>"

    to="<?php $date = new DateTime(date("Y-m-d"));$date->modify('+1 day');
    echo $date->format('Y-m-d'); ?>"

    rel-type="ytd">YTD</a>

    <a href="javascript:void(0)"
    class="mtd_btn btn bg-blue bg-sales sellerbutton1 btndownload"
    style="width:80px;margin-top:0px;padding-top:6px !important"
    onclick="filterMTDajax()"
    id="graph-merchant-mtd"
    name=""

    from="<?= date("Y-m-01"); ?>"

    to="<?php $date = new DateTime(date("Y-m-d"));$date->modify('+1 day');
    echo $date->format('Y-m-d'); ?>"

    rel-type="mtd">MTD</a>

    <a href="javascript:void(0)"
    class="wtd_btn btn bg-blue bg-sales sellerbutton1 btndownload"
    style="width:80px;margin-top:0px;padding-top:6px !important"
    onclick="filterWTDajax()"
    id="graph-merchant-wtd"
    name=""
    from="<?php $date = new DateTime(date("Y-m-d"));$date->modify('-7 day');
    echo $date->format('Y-m-d'); ?>"

    to="<?php $date = new DateTime(date("Y-m-d"));$date->modify('+1 day');
    echo $date->format('Y-m-d'); ?>"

    rel-type="wtd">WTD</a>

    <a href="javascript:void(0)"
    class="today_btn btn bg-blue bg-sales sellerbutton1 btndownload"
    style="width:80px;margin-top:0px;padding-top:6px !important"
    onclick="filtertodayajax()"
    id="graph-merchant-td"
    name=""
    from="<?= date("Y-m-d"); ?>"
    to="<?php $date = new DateTime(date("Y-m-d"));$date->modify('+1 day');
    echo $date->format('Y-m-d'); ?>"
    rel-type="TODAY">TODAY</a>


    <div style="right:190px;display:inline;padding-left:0; margin-bottom:20px">
        <input class="form_date form-control btndownload btnremove"
        style="display:inline;margin-top:10px;
        padding-top:0px !important;padding-left:0px;
        padding-right:0;padding-bottom: 0px; width:110px;text-align: center;" onclick="show_dialog4()"
        id="date_from" name="froms" placeholder="Select"/>

    </div>
    To
    <div style="right:200px;display:inline;padding-left:0;margin-bottom:20px">
        <input class="to_date form-control btndownload btnremove"
        style="display:inline;margin-top:10px;padding-top:0px !important;padding-bottom: 0px;
        width:110px;padding-right:0;padding-left:0px;text-align: center;" onclick="show_dialog5()"
        id="date_to" name="all" placeholder="Select" disabled="disabled"/>
    </div>

    <div id="branch_name" style="float:right">
        <h5 class="os-linkcolor" data-toggle="modal" data-target="#myModal"
        style="cursor:pointer;margin-bottom:0;padding-top:5px"
        id="location_modal">All</h5>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="myModal" role="dialog" style="">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <!-- Modal content-->
            <div class="modal-content bg-greenlobster" style="padding: 9;">
                <div style="padding-top:10px;padding-bottom:10px"
                class="modal-header">
                <h3 style="margin-bottom:4px">Location</h3>
            </div>
            <div class="modal-body">
                <h5 style="cursor:pointer" onclick="filterSinceajax()" id="all" data-dismiss="modal">All </h5>

                @foreach ($branch_location as $key => $value)
                <h5 style="cursor: pointer;text-transform: capitalize" id="{{$value->id}}"
                    onclick="display(this.id)" name="{{$value->branch}}" date="{{$value->created_at}}"
                    class="location select_date_range" data-dismiss="modal">{{$value->branch}}</h5>
                    @endforeach

                </div>
            </div>
        </div>
    </div>
</div>
<div class="col col-auto align-self-center">
    <a href="#" class="getDownload">
        <button class="btn btn-success bg-download sellerbutton"
        style="padding-left:0;padding-right:0;padding-top:7px;float:right;margin-bottom: -35px; margin-right:0px"
        data-toggle="modal"
        data-target="#stockinmodal" onclick="downloadAsPdf()">
        <span>PDF</span>
    </button>
</a>
<a href="{{ url('show-cash-productsalesqty-view')}}" target="_blank">
    <button class="btn btn-success bg-product sellerbutton"
    style="padding-left:12px;padding-top:7px;float:right;margin-bottom: -35px;"
    id="productsalesqty">
    <span>Qty</span>
</button>
</a>
</div>
</div>


<!-- //this modal for alert message -->
<div class="modal fade" id="msgModal"  tabindex="-1"
role="dialog" aria-labelledby="staffNameLabel"
aria-hidden="true" style="text-align: center;">

<div class="modal-dialog modal-dialog-centered  mw-75 w-50"
role="document" style="display: inline-flex;">
<div class="modal-content modal-inside bg-purplelobster" style="width: 100%;">
    <div class="modal-body text-center">
        <br/><br/>
        <h5 class="modal-title text-white" id="statusModalLabel"></h5>
    </div>
    <div class="modal-footer"
    style="border-top:0 none; padding-left: 0px; padding-right: 0px;">
    <div class="row" style="width: 100%; padding-left: 0px; padding-right: 0px;">
    </div>
</div>
</div>
</div>
</div>

{{-- DUMMMY DATA IN TABLE --}}


<table style="width: 100%;" id="product_sales_pdt_table"
class="table skutable ">
<thead style="background-color: #0069d9;color: #fff">
    <tr>
        <th class="text-left" scope="col" style="border-bottom-width:0">MYR</th>
        <th class="text-left" scope="col" style="border-bottom-width:0">Amount</th>
    </tr>
</thead>
{{-- <tbody id="skutable-body"> --}}

    <tbody id="shows">

        @foreach ($each_Product_amount as $key => $value)

        @php
        $pro_total[] = $value->T_amount;

        @endphp

        @endforeach




        @foreach ($each_Product_amount as $key => $valuen)

        @php
        Log::debug($valuen->thumbnail_1);

        $pro_total_amount = array_sum($pro_total);

        $total_product_sales_amount = $valuen->T_amount;

        $progress_bar_percentage = number_format((($total_product_sales_amount/$pro_total_amount)*100),2);

        $all_prc_value[] = $progress_bar_percentage;

        $max_value = max($all_prc_value);
        @endphp

        <tr>
            <td id="image">
                @php
                $link = '/images/product/'.$valuen->id.'/thumb/'.$valuen->thumbnail_1;
                @endphp

                <img src="{{asset($link)}}" style="object-fit:contain"
                alt="" width="50px" height="50px"/>

                @if($progress_bar_percentage == $max_value)

                <img class="greenshade" style="width:85%; "/>

                @else

                @php
                $new_percentage_value = (85/$max_value)*$progress_bar_percentage;
                @endphp


                <img class="greenshade" style="width:{{$new_percentage_value}}%"/>

                @endif

                <span style="color:red;display:inline">
                    <b>MYR {{ number_format($valuen->T_amount/100,2) }}</b>
                </span>

                <br>
                <span style="padding-left: 55px;">
                    {{$valuen->name}}
                </span>

            </td>
            <td>
                {{$valuen->T_amount}}
            </td>
        </tr>


        @endforeach

    </tbody>
</table>

<input type="hidden" id='startDate'>
<input type="hidden" id='createdDate'>
<input type="hidden" id='startDate1'>
<input type="hidden" name="overide" id="overide" value="false"/>
<input type="hidden" name="_token" value="<?php echo csrf_token(); ?>"/>
<script type="text/javascript">

    $('#graph-merchant-mtd').hover(
        function btnchange() {
            $('.mtd_btn').removeClass('bg-blue').addClass('btn-success');
        },
        function btnchange() {
            $('.mtd_btn').removeClass(' btn-success').addClass('bg-blue');
        }
        );

    $('#graph-merchant-since').hover(
        function btnchange() {
            $('.since_ytd').removeClass('bg-blue').addClass('btn-success');
        },
        function btnchange() {
            $('.since_ytd').removeClass('btn-success').addClass('bg-blue');
        }
        );

    $('#graph-merchant-ytd').hover(
        function btnchange() {
            $('.ytd_btn').removeClass('bg-blue').addClass('btn-success');
        },
        function btnchange() {
            $('.ytd_btn').removeClass('btn-success').addClass('bg-blue');
        }
        );


    $('#graph-merchant-wtd').hover(
        function btnchange() {
            $('.wtd_btn').removeClass('bg-blue').addClass('btn-success');
        },
        function btnchange() {
            $('.wtd_btn').removeClass('btn-success').addClass('bg-blue');
        }
        );


    $('#graph-merchant-td').hover(
        function btnchange() {
            $('.today_btn').removeClass('bg-blue').addClass('btn-success');
        },
        function btnchange() {
            $('.today_btn').removeClass('btn-success').addClass('bg-blue');
        }
        );

    $(document).ready(function () {
        var tableinventory = $('#product_sales_pdt_table').DataTable({
            "order": [[1, 'desc']],
            "retrieve": true,
            dom: 'lfBrtip',
            buttons: [
            { extend: 'excel', text: 'Download' ,"className": 'btn btn-success bg-download sellerbutton btn-md' }
            ],
            "columnDefs": [
            {
                "orderData": [1],
                "targets": [0],
            },
            {
                "targets": [1],
                "visible": false,
                "searchable": false,
            },
            ],
            "autoWidth": true,
        });
        $(document).on('click', '#btnDownload', function(){
            $(".buttons-excel")[0].click(); //trigger the click event
        });
    });
    $(function () {
        $(document).ready(function () {
            var todaysDate = new Date(); // Gets today's date
            // Max date attribute is in "YYYY-MM-DD".
            // Need to format today's date accordingly
            var year = todaysDate.getFullYear(); // YYYY
            var month = ("01");  // MM
            var day = ("01");           // DD
            var minDate = (year + "-" + month);
            //  +"-"+ display Results in "YYYY-MM" for today's date
            // Now to set the max date value for the calendar to be today's date
            $('#startDate').attr('min', minDate);
        });
    });

    @yield('current_year')

    function show_dialog4() {
        jQuery('#showDateModalFrom').modal('show');
    }

    $('#showDateModalFrom').on('hidden.bs.modal', function (e) {
        onDateSelect();
    });

    function show_dialog5() {

        jQuery('#showDateModalTo').modal('show');
    }

    $('#showDateModalTo').on('hidden.bs.modal', function (e) {
        onDateSelect1();
    });

    var CURRENT_DATE = new Date();
    var d = new Date();

    var content = 'January February March April May June July August September October November December'.split(' ');
    var weekDayName = 'SUN MON TUES WED THURS FRI'.split(' ');
    var daysOfMonth = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

    // Returns the day of week which month starts (eg 0 for Sunday, 1 for Monday, etc.)
    function getCalendarStart(dayOfWeek, currentDate) {
        var date = currentDate - 1;
        var startOffset = (date % 7) - dayOfWeek;
        if (startOffset > 0) {
            startOffset -= 7;
        }
        return Math.abs(startOffset);
    }

    // Render Calendar
    function renderCalendar(startDay, totalDays, currentDate) {
        // console.log("Current Date: ", currentDate);
        var currentRow = 1;
        var currentDay = startDay;
        var $table = $('table');
        var $week = getCalendarRow();
        var $day;
        var i = 1;

        for (; i <= totalDays; i++) {
            $day = $week.find('td').eq(currentDay);
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

    function renderCalendar1(startDay, totalDays, currentDate) {
        // console.log("Current Date1 : ", currentDate);

        var currentRow = 1;
        var currentDay = startDay;
        var $table = $('table');
        var $week = getCalendarRow1();
        var $day;
        var i = 1;

        for (; i <= totalDays; i++) {
            $day = $week.find('td').eq(currentDay);
            $day.text(i);
            if (i === currentDate) {
                $day.addClass('today');
            }

            // +1 next day until Saturday (6), then reset to Sunday (0)
            currentDay = ++currentDay % 7;

            // Generate new row when day is Saturday, but only if there are
            // additional days to render
            if (currentDay === 0 && (i + 1 <= totalDays)) {
                $week = getCalendarRow1();
                currentRow++;
            }
        }
    }

    // Clear generated calendar
    function clearCalendar() {
        var $trs = $('tr').not(':eq(0)');
        $trs.remove();
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

    function getCalendarRow1() {
        var $table1 = $('table.date_table1');
        var $tr1 = $('<tr/>');
        for (var i = 0, len = 7; i < len; i++) {
            $tr1.append($('<td/>'));
        }
        $table1.append($tr1);
        return $tr1;
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
        renderCalendar1(getCalendarStart(day, date), totalDaysOfMonth, dateToHighlight);

    };


    function navigationHandler(dir) {
        d.setUTCMonth(d.getUTCMonth() + dir);
        clearCalendar();
        myCalendar();
        shoot_event();
        shoot_event1();
    }


    $(document).ready(function () {
        // Bind Events
        $('.prev-month').click(function () {
            navigationHandler(-1);
        });
        $('.next-month').click(function () {
            navigationHandler(1);
        });
        // Generate Calendar
        myCalendar();
        shoot_event();
        shoot_event1();
    });


    function shoot_event() {
        var month = d.getUTCMonth();
        var year = d.getUTCFullYear();

        const val = $('#startDate').val();
        const createdAt = $('#createdDate').val();
        const selectedDate = new Date(val);
        const createdDate = new Date(createdAt);

        var CURRENT_DATE = new Date();

        console.log("Location Current Date:", CURRENT_DATE.getUTCFullYear(), year);
        if (CURRENT_DATE && CURRENT_DATE.getMonth() < month && CURRENT_DATE.getUTCFullYear() === year || CURRENT_DATE.getUTCFullYear() < year) {
            $('.date_table tbody tr td').each(function () {
                $(this).closest('td').addClass('disabled');
                // $(this).closest('tr').css("pointer-events", "none");
                $(this).closest('td').css("cursor", "not-allowed");
                $(this).closest('td').unbind('click');

            });
            $('.date_table1 tbody tr td').each(function () {
                $(this).closest('td').addClass('disabled');
                // $(this).closest('tr').css("pointer-events", "none");
                $(this).closest('td').css("cursor", "not-allowed");
                $(this).closest('td').unbind('click');

            });
        }

        if (CURRENT_DATE && CURRENT_DATE.getMonth() === month && CURRENT_DATE.getUTCFullYear() === year) {

            $('.date_table tbody tr td').each(function () {
                console.log(selectedDate.getDate());

                if ($(this).text() > CURRENT_DATE.getDate()) {
                    $(this).closest('td').addClass('disabled');
                    // $(this).closest('tr').css("pointer-events", "none");
                    $(this).closest('td').css("cursor", "not-allowed");
                    $(this).closest('td').unbind('click');
                }

            });
            $('.date_table1 tbody tr td').each(function () {
                console.log(selectedDate.getDate());

                if ($(this).text() > CURRENT_DATE.getDate()) {
                    $(this).closest('td').addClass('disabled');
                    // $(this).closest('tr').css("pointer-events", "none");
                    $(this).closest('td').css("cursor", "not-allowed");
                    $(this).closest('td').unbind('click');
                }

            });
        }
        if (createdDate && createdDate.getMonth() > month) {
            $('.date_table tbody tr td').each(function () {
                $(this).closest('td').addClass('disabled');
                // $(this).closest('tr').css("pointer-events", "none");
                $(this).closest('td').css("cursor", "not-allowed");
                $(this).closest('td').unbind('click');

            });
        }

        if (createdDate && createdDate.getMonth() === month && createdDate.getUTCFullYear() === year) {

            $('.date_table tbody tr td').each(function () {
                console.log(selectedDate.getDate());

                if ($(this).text() == createdDate.getDate()) {
                    return false;
                }
                $(this).closest('td').addClass('disabled');
                // $(this).closest('tr').css("pointer-events", "none");
                $(this).closest('td').css("cursor", "not-allowed");
                $(this).closest('td').unbind('click');


            });
        }

        if (selectedDate && selectedDate.getMonth() > month) {
            $('.date_table1 tbody tr td').each(function () {

                $(this).closest('td').addClass('disabled');
                // $(this).closest('tr').css("pointer-events", "none");
                $(this).closest('td').css("cursor", "not-allowed");
                $(this).closest('td').unbind('click');

            });
        }
        if (selectedDate && selectedDate.getMonth() < month) {
            $('.date_table1 tbody tr td').each(function () {

                $(this).closest('td').addClass('disabled');
                // $(this).closest('tr').css("pointer-events", "none");
                $(this).closest('td').css("cursor", "not-allowed");
                $(this).closest('td').unbind('click');

            });
        }
        if (selectedDate && selectedDate.getMonth() === month && selectedDate.getUTCFullYear() === year) {
            $('.date_table tbody tr td').each(function () {

                if ($(this).text() == selectedDate.getDate()) {
                    $(this).closest('td').addClass('selected_date');
                }

            });

            $('.date_table1 tbody tr td').each(function () {
                console.log(selectedDate.getDate());

                if ($(this).text() == selectedDate.getDate()) {
                    return false;
                }
                $(this).closest('td').addClass('disabled');
                // $(this).closest('tr').css("pointer-events", "none");
                $(this).closest('td').css("cursor", "not-allowed");
                $(this).closest('td').unbind('click');


            });
        }
        if (selectedDate && selectedDate.getMonth() === month && selectedDate.getUTCFullYear() != year ) {
            $('.date_table1 tbody tr td').each(function () {

                $(this).closest('td').addClass('disabled');
                // $(this).closest('tr').css("pointer-events", "none");
                $(this).closest('td').css("cursor", "not-allowed");
                $(this).closest('td').unbind('click');


            });
        }
        $('.date_table > tbody > tr > td').click(function (e) {
            console.log("Date clicked");

            var target = e.target;
            console.log("Target", e.target.innerText);
            $('.date_table > tbody > tr > td').removeClass('selected_date');
            if ($(target).hasClass('disabled')) {
                return false;
            } else {
                $(target).addClass('selected_date');
            }
            $('.date_table1 tbody tr td').each(function () {

                if ($(this).text() == e.target.innerText) {
                    $(this).closest('td').addClass('from_selected_date');
                }
            });
            $('.date_table1 tbody tr td').each(function () {

                if ($(this).closest('td').hasClass('from_selected_date')) {
                    return false;
                }

                $(this).closest('td').addClass('disabled');
                // $(this).closest('tr').css("pointer-events", "none");
                $(this).closest('td').css("cursor", "not-allowed");
                $(this).closest('td').unbind('click');


            });
            let day = $(target).html();
            let month = $('.month-year > h3').html();

            console.log(day, month);
            $('#startDate').val(day + ' ' + month);

            if ($("#startDate").val() != "") {
                $("#date_to").removeAttr("disabled");
            }

            $('#date_to').val('');
            $('#startDate1').val('');
            jQuery('#showDateModalFrom').modal('hide');

            navigationHandler(1);
            navigationHandler(-1);
        });

    }

    function shoot_event1() {
        // navigationHandler(-1);

        $('.date_table1 > tbody > tr > td').click(function (e) {
            console.log("Date clicked To");

            var target = e.target;
            $('.date_table1 > tbody > tr > td').removeClass('selected_date1');
            if ($(target).hasClass('disabled')) {
                return false;
            } else {
                $(target).addClass('selected_date1');
            }

            let day1 = $(target).html();
            let month1 = $('.month-year > h3').html();
            $('#startDate1').val(day1 + ' ' + month1);

            jQuery('#showDateModalTo').modal('hide');

        });
    }


    function overideFY() {
        $('#overide').val('true');
        onDateSelect();
        onDateSelect1();
    }

    function reset_dialog() {
        $('#confirmation').val('false');
        $('#overide').val('false');
    }


    function onDateSelect() {
        const val = $('#startDate').val();

        const selectedDate = new Date(val);
        if (selectedDate == 'Invalid Date') {
            return false;
        }

        const todaysDate = new Date();
        var selectedFinalDate = (selectedDate.getDate() < 10 ? '0' : '') + selectedDate.getDate();
        var selectedFullYear = selectedDate.getFullYear().toString();
        selectedFullYear = selectedFullYear.match(/\d{2}$/);

        $('#date_from').val(selectedFinalDate + selectedDate.toLocaleString('en-us',
            {month: 'short'}) + selectedFullYear);

    }

    function onDateSelect1() {
        console.log("bugga");
        const val1 = $('#startDate1').val();
        const selectedDate = new Date(val1);
        if (selectedDate == 'Invalid Date') {
            return false;
        }

        const todaysDate = new Date();
        var selectedFinalDate = (selectedDate.getDate() < 10 ? '0' : '') + selectedDate.getDate();
        var selectedFullYear = selectedDate.getFullYear().toString();
        selectedFullYear = selectedFullYear.match(/\d{2}$/);

        $('#date_to').val(selectedFinalDate + selectedDate.toLocaleString('en-us',
            {month: 'short'}) + selectedFullYear);


// date range filter


        //from date to date

        var datefrom = $("#date_from").val();
        var newfrom = new Date(datefrom);
        var dayfrom = newfrom.getDate();
        var monthfrom = newfrom.getMonth() + 1;
        var yearfrom = newfrom.getFullYear(); // output is -2019 so create 2019
        var yearfrom1 = String(yearfrom);
        var yearfrom2 = yearfrom1.slice(0);

        var from = yearfrom2 + "-" + monthfrom + "-" + dayfrom;

        //to date

        var dateto = $("#date_to").val();
        var newto = new Date(dateto);
        var dayto = newto.getDate();
        var monthto = newto.getMonth() + 1;
        var yearto = newto.getFullYear(); // output is -2019 so create 2019
        var yearto1 = String(yearto);
        var yearto2 = yearto1.slice(0);

        var to = yearto2 + "-" + monthto + "-" + dayto;

        // pass date variable

        var from = from + " " + "00:00:00";
        var to = to + " " + "23:59:59";
        var id = $("#date_to").attr("name");


        if (id === "all") {
            dateRangeForAll(from, to)
        } else {

            dateRange(from, to, id);
        }


    }


    //

    // $("#date_from").click(function(){
    //  $("#date_to").removeAttr("disabled");
    // });

    $("#all").click(function () {
        $("#location_modal").text("All");

    });


    $("#location_modal").click(function () {
        $("#date_from").val("");
        $("#date_to").val("");
        $("#date_to").attr("disabled", "disabled");
    })

    if ($("#startDate").val() == "") {
        $("#date_to").attr("disabled", "disabled");
    }


    // since all location

    function filterSinceajax() {
        $('#graph-merchant-td').removeClass('btn-green').addClass('bg-blue');
        $('#graph-merchant-mtd').removeClass('btn-green').addClass('bg-blue');
        $('#graph-merchant-ytd').removeClass('btn-green').addClass('bg-blue');
        $('#graph-merchant-wtd').removeClass('btn-green').addClass('bg-blue');
        $('#graph-merchant-since').removeClass('bg-blue').addClass('btn-green');

        $("#date_from").val("");
        $("#date_to").val("");

        $("#date_to").attr("name", "all");

        $("#shows").empty();
        console.log("success ytd_btn ajax");

        var from = $(".since_ytd").attr("from");
        var to = $(".since_ytd").attr("to");


        $.ajax({

            type: "GET",
            url: "{{route('analytics.ajax.cash.productsales')}}",
            data: {
                from_date_all: from,
                to_date_all: to

            },
            dataType: 'json',
            success: function (data) {
                console.log('success');


                var branch_data = '';

                var allvalue = [];
                data.forEach(function (value) {

                    allvalue.push(value.T_amount);
                });

                var total = sum(allvalue);
                var max_value = Math.max.apply(Math, allvalue);
                ;


                data.forEach(function (value) {

                    var max_percentage = (max_value / total) * 100;

                    var url = '/images/product/' + value.id + '/thumb/' + value.thumbnail_1;


                    branch_data += '<tr>';
                    branch_data += '<td><img src={{URL::to('/')}}/images/product/' + value.id + '/thumb/' + value.thumbnail_1 + '  alt="" width="50px" height="50px" style="object-fit:contain" /> ';
                    if (value.T_amount == max_value) {

                        branch_data += '<img class="greenshade" style="width:85%;"/>';

                    } else {

                        var each_percentage = (value.T_amount / total) * 100;
                        var new_percentage = (85 / max_percentage) * each_percentage;

                        branch_data += '<img class="greenshade" style="width:' + new_percentage + '%;"/>';

                    }


                    branch_data += '<span style="color:red;display:inline"><b> MYR ' + formatNumber(value.T_amount / 100) + '</b></span><br>    <span style="padding-left:55px;">' + value.name + '</span></td>';
                    branch_data += '<td>' + value.T_amount + '</td></tr>';

                });
                $('#product_sales_pdt_table').DataTable().clear().destroy();
                $("#shows").append(branch_data);
                $('#product_sales_pdt_table').DataTable({
                    "order": [[1, 'desc']],
                    "columnDefs": [
                    {
                        "orderData": [1],
                        "targets": [0],
                    },
                    {
                        "targets": [1],
                        "visible": false,
                        "searchable": false,
                    },
                    ],
                    "autoWidth": true,
                });

            },
            error: function () {
                console.log('fall');
                $('#product_sales_pdt_table').DataTable().clear().draw();

            }
        });


        function sum(input) {
            var total = 0;
            for (var i = 0; i < input.length; i++) {
                total += Number(input[i]);
            }
            return total;
        }

        function formatNumber(num) {
            return num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
        }
    }


    // ytd all location

    function filterYTDajax() {
        $('#graph-merchant-td').removeClass('btn-green').addClass('bg-blue');
        $('#graph-merchant-mtd').removeClass('btn-green').addClass('bg-blue');
        $('#graph-merchant-since').removeClass('btn-green').addClass('bg-blue');
        $('#graph-merchant-wtd').removeClass('btn-green').addClass('bg-blue');
        $('#graph-merchant-ytd').removeClass('bg-blue').addClass('btn-green');
        $("#date_from").val("");
        $("#date_to").val("");


        $("#shows").empty();
        console.log("success ytd_btn ajax");

        var from = $(".ytd_btn").attr("from");
        var to = $(".ytd_btn").attr("to");


        $.ajax({

            type: "GET",
            url: "{{route('analytics.ajax.cash.productsales')}}",
            data: {
                from_date_all: from,
                to_date_all: to

            },
            dataType: 'json',
            success: function (data) {
                console.log('success');


                var branch_data = '';

                var allvalue = [];
                data.forEach(function (value) {

                    allvalue.push(value.T_amount);
                });

                var total = sum(allvalue);
                var max_value = Math.max.apply(Math, allvalue);
                ;


                data.forEach(function (value) {

                    var max_percentage = (max_value / total) * 100;

                    var url = '/images/product/' + value.id + '/thumb/' + value.thumbnail_1;


                    branch_data += '<tr>';
                    branch_data += '<td><img src={{URL::to('/')}}/images/product/' + value.id + '/thumb/' + value.thumbnail_1 + '  alt="" width="50px" height="50px" style="object-fit:contain" /> ';
                    if (value.T_amount == max_value) {

                        branch_data += '<img class="greenshade" style="width:85%;"/>';

                    } else {

                        var each_percentage = (value.T_amount / total) * 100;
                        var new_percentage = (85 / max_percentage) * each_percentage;

                        branch_data += '<img class="greenshade" style="width:' + new_percentage + '%;"/>';

                    }


                    branch_data += '<span style="color:red;display:inline"><b> MYR ' + formatNumber(value.T_amount / 100) + '</b></span><br>    <span style="padding-left:55px;">' + value.name + '</span></td>';
                    branch_data += '<td>' + value.T_amount + '</td></tr>';

                });
                $('#product_sales_pdt_table').DataTable().clear().destroy();
                $("#shows").append(branch_data);
                $('#product_sales_pdt_table').DataTable({
                    "order": [[1, 'desc']],
                    "columnDefs": [
                    {
                        "orderData": [1],
                        "targets": [0],
                    },
                    {
                        "targets": [1],
                        "visible": false,
                        "searchable": false,
                    },
                    ],
                    "autoWidth": true,
                });

            },
            error: function () {
                console.log('fall');
                $('#product_sales_pdt_table').DataTable().clear().draw();

            }
        });


        function sum(input) {
            var total = 0;
            for (var i = 0; i < input.length; i++) {
                total += Number(input[i]);
            }
            return total;
        }

        function formatNumber(num) {
            return num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
        }
    }


    // mtd all location

    function filterMTDajax() {
        $('#graph-merchant-td').removeClass('btn-green').addClass('bg-blue');
        $('#graph-merchant-since').removeClass('btn-green').addClass('bg-blue');
        $('#graph-merchant-ytd').removeClass('btn-green').addClass('bg-blue');
        $('#graph-merchant-wtd').removeClass('btn-green').addClass('bg-blue');
        $('#graph-merchant-mtd').removeClass('bg-blue').addClass('btn-green');
        $("#date_from").val("");
        $("#date_to").val("");

        $("#shows").empty();
        console.log("success mtd ajax");

        var from = $(".mtd_btn").attr("from");
        var to = $(".mtd_btn").attr("to");


        $.ajax({

            type: "GET",
            url: "{{route('analytics.ajax.cash.productsales')}}",
            data: {
                from_date_all: from,
                to_date_all: to

            },
            dataType: 'json',
            success: function (data) {
                console.log('success');


                var branch_data = '';

                var allvalue = [];
                data.forEach(function (value) {

                    allvalue.push(value.T_amount);
                });

                var total = sum(allvalue);
                var max_value = Math.max.apply(Math, allvalue);
                ;


                data.forEach(function (value) {

                    var max_percentage = (max_value / total) * 100;

                    var url = '/images/product/' + value.id + '/thumb/' + value.thumbnail_1;


                    branch_data += '<tr>';
                    branch_data += '<td><img src={{URL::to('/')}}/images/product/' + value.id + '/thumb/' + value.thumbnail_1 + '  alt="" width="50px" height="50px" style="object-fit:contain" /> ';
                    if (value.T_amount == max_value) {

                        branch_data += '<img class="greenshade" style="width:85%;"/>';

                    } else {

                        var each_percentage = (value.T_amount / total) * 100;
                        var new_percentage = (85 / max_percentage) * each_percentage;

                        branch_data += '<img class="greenshade" style="width:' + new_percentage + '%;"/>';

                    }


                    branch_data += '<span style="color:red;display:inline"><b> MYR ' + formatNumber(value.T_amount / 100) + '</b></span><br>    <span style="padding-left:55px;">' + value.name + '</span></td>';
                    branch_data += '<td>' + value.T_amount + '</td></tr>';

                });
                $('#product_sales_pdt_table').DataTable().clear().destroy();
                $("#shows").append(branch_data);
                $('#product_sales_pdt_table').DataTable({
                    "order": [[1, 'desc']],
                    "columnDefs": [
                    {
                        "orderData": [1],
                        "targets": [0],
                    },
                    {
                        "targets": [1],
                        "visible": false,
                        "searchable": false,
                    },
                    ],
                    "autoWidth": true,
                });

            },
            error: function () {
                console.log('fall');
                $('#product_sales_pdt_table').DataTable().clear().draw();

            }
        });


        function sum(input) {
            var total = 0;
            for (var i = 0; i < input.length; i++) {
                total += Number(input[i]);
            }
            return total;
        }

        function formatNumber(num) {
            return num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
        }
    }


    // wtd all location

    function filterWTDajax() {
        $('#graph-merchant-td').removeClass('btn-green').addClass('bg-blue');
        $('#graph-merchant-mtd').removeClass('btn-green').addClass('bg-blue');
        $('#graph-merchant-ytd').removeClass('btn-green').addClass('bg-blue');
        $('#graph-merchant-since').removeClass('btn-green').addClass('bg-blue');
        $('#graph-merchant-wtd').removeClass('bg-blue').addClass('btn-green');
        $("#date_from").val("");
        $("#date_to").val("");

        $("#shows").empty();
        console.log("success wtd_btn ajax");

        var from = $(".wtd_btn").attr("from");
        var to = $(".wtd_btn").attr("to");


        $.ajax({

            type: "GET",
            url: "{{route('analytics.ajax.cash.productsales')}}",
            data: {
                from_date_all: from,
                to_date_all: to

            },
            dataType: 'json',
            success: function (data) {
                console.log('success');


                var branch_data = '';

                var allvalue = [];
                data.forEach(function (value) {

                    allvalue.push(value.T_amount);
                });

                var total = sum(allvalue);
                var max_value = Math.max.apply(Math, allvalue);
                ;


                data.forEach(function (value) {

                    var max_percentage = (max_value / total) * 100;

                    var url = '/images/product/' + value.id + '/thumb/' + value.thumbnail_1;


                    branch_data += '<tr>';
                    branch_data += '<td><img src={{URL::to('/')}}/images/product/' + value.id + '/thumb/' + value.thumbnail_1 + '  alt="" width="50px" height="50px" style="object-fit:contain" /> ';
                    if (value.T_amount == max_value) {

                        branch_data += '<img class="greenshade" style="width:85%;"/>';

                    } else {

                        var each_percentage = (value.T_amount / total) * 100;
                        var new_percentage = (85 / max_percentage) * each_percentage;

                        branch_data += '<img class="greenshade" style="width:' + new_percentage + '%;"/>';

                    }


                    branch_data += '<span style="color:red;display:inline"><b> MYR ' + formatNumber(value.T_amount / 100) + '</b></span><br>    <span style="padding-left:55px;">' + value.name + '</span></td>';
                    branch_data += '<td>' + value.T_amount + '</td></tr>';

                });
                $('#product_sales_pdt_table').DataTable().clear().destroy();
                $("#shows").append(branch_data);
                $('#product_sales_pdt_table').DataTable({
                    "order": [[1, 'desc']],
                    "columnDefs": [
                    {
                        "orderData": [1],
                        "targets": [0],
                    },
                    {
                        "targets": [1],
                        "visible": false,
                        "searchable": false,
                    },
                    ],
                    "autoWidth": true,
                });

            },
            error: function () {
                console.log('fall');
                $('#product_sales_pdt_table').DataTable().clear().draw();

            }
        });


        function sum(input) {
            var total = 0;
            for (var i = 0; i < input.length; i++) {
                total += Number(input[i]);
            }
            return total;
        }

        function formatNumber(num) {
            return num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
        }
    }


    // today all location

    function filtertodayajax() {
        $('#graph-merchant-since').removeClass('btn-green').addClass('bg-blue');
        $('#graph-merchant-mtd').removeClass('btn-green').addClass('bg-blue');
        $('#graph-merchant-ytd').removeClass('btn-green').addClass('bg-blue');
        $('#graph-merchant-wtd').removeClass('btn-green').addClass('bg-blue');
        $('#graph-merchant-td').removeClass('bg-blue').addClass('btn-green');
        $("#date_from").val("");
        $("#date_to").val("");

        $("#shows").empty();
        console.log("success today ajax");

        var from = $(".today_btn").attr("from");
        var to = $(".today_btn").attr("to");


        $.ajax({

            type: "GET",
            url: "{{route('analytics.ajax.cash.productsales')}}",
            data: {
                from_date_all: from,
                to_date_all: to

            },
            dataType: 'json',
            success: function (data) {
                console.log('success');


                var branch_data = '';

                var allvalue = [];
                data.forEach(function (value) {

                    allvalue.push(value.T_amount);
                });

                var total = sum(allvalue);
                var max_value = Math.max.apply(Math, allvalue);
                ;


                data.forEach(function (value) {

                    var max_percentage = (max_value / total) * 100;

                    var url = '/images/product/' + value.id + '/thumb/' + value.thumbnail_1;


                    branch_data += '<tr>';
                    branch_data += '<td><img src={{URL::to('/')}}/images/product/' + value.id + '/thumb/' + value.thumbnail_1 + '  alt="" width="50px" height="50px" style="object-fit:contain" /> ';
                    if (value.T_amount == max_value) {

                        branch_data += '<img class="greenshade" style="width:85%;"/>';

                    } else {

                        var each_percentage = (value.T_amount / total) * 100;
                        var new_percentage = (85 / max_percentage) * each_percentage;

                        branch_data += '<img class="greenshade" style="width:' + new_percentage + '%;"/>';

                    }


                    branch_data += '<span style="color:red;display:inline"><b> MYR ' + formatNumber(value.T_amount / 100) + '</b></span><br>    <span style="padding-left:55px;">' + value.name + '</span></td>';
                    branch_data += '<td>' + value.T_amount + '</td></tr>';

                });
                $('#product_sales_pdt_table').DataTable().clear().destroy();
                $("#shows").append(branch_data);
                $('#product_sales_pdt_table').DataTable({
                    "order": [[1, 'desc']],
                    "columnDefs": [
                    {
                        "orderData": [1],
                        "targets": [0],
                    },
                    {
                        "targets": [1],
                        "visible": false,
                        "searchable": false,
                    },
                    ],
                    "autoWidth": true,
                });

            },
            error: function () {
                console.log('fall');
                $('#product_sales_pdt_table').DataTable().clear().draw();

            }
        });


        function sum(input) {
            var total = 0;
            for (var i = 0; i < input.length; i++) {
                total += Number(input[i]);
            }
            return total;
        }

        function formatNumber(num) {
            return num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
        }
    }


    //branch location  start

    function display(id) {


        console.log("Value: ", id);
        $("#shows").empty();
        var l_id = id;
        console.log(l_id);

        //change location name

        var loc_name = $("#" + l_id).attr("name");
        var loc_date = $("#" + l_id).attr("date");


        $("#location_modal").html(loc_name);
        $("#createdDate").val(loc_date);


        //change since,ytd,mtd,wtd,today function name and id
        $(".since_ytd").attr("onclick", "since_location(this.id)");
        $(".since_ytd").attr("id", l_id);
        $(".since_ytd").attr("name", loc_name);

        $(".ytd_btn").attr("onclick", "ytd_location(this.id)");
        $(".ytd_btn").attr("id", l_id);
        $(".ytd_btn").attr("name", loc_name);

        $(".mtd_btn").attr("onclick", "mtd_location(this.id)");
        $(".mtd_btn").attr("id", l_id);
        $(".mtd_btn").attr("name", loc_name);

        $(".wtd_btn").attr("onclick", "wtd_location(this.id)");
        $(".wtd_btn").attr("id", l_id);
        $(".wtd_btn").attr("name", loc_name);

        $(".today_btn").attr("onclick", "today_location(this.id)");
        $(".today_btn").attr("id", l_id);
        $(".today_btn").attr("name", loc_name);


        //change custome daterand id and


        $(".form_date").attr("name", l_id);
        $(".to_date").attr("name", l_id);


        $.ajax({

            type: "GET",
            url: "{{route('analytics.ajax.cash.productsales')}}",
            data: {
                branch_id: l_id
            },
            dataType: 'json',
            success: function (data) {
                console.log('success');


                var branch_data = '';

                var allvalue = [];
                data.forEach(function (value) {

                    allvalue.push(value.T_amount);
                });

                var total = sum(allvalue);
                var max_value = Math.max.apply(Math, allvalue);
                ;


                data.forEach(function (value) {

                    var max_percentage = (max_value / total) * 100;

                    var url = '/images/product/' + value.id + '/thumb/' + value.thumbnail_1;


                    branch_data += '<tr>';
                    branch_data += '<td><img src={{URL::to('/')}}/images/product/' + value.id + '/thumb/' + value.thumbnail_1 + '  alt="" width="50px" height="50px" style="object-fit:contain" /> ';
                    if (value.T_amount == max_value) {

                        branch_data += '<img class="greenshade" style="width:85%;"/>';

                    } else {

                        var each_percentage = (value.T_amount / total) * 100;
                        var new_percentage = (85 / max_percentage) * each_percentage;

                        branch_data += '<img class="greenshade" style="width:' + new_percentage + '%;"/>';

                    }


                    branch_data += '<span style="color:red;display:inline"><b> MYR ' + formatNumber(value.T_amount / 100) + '</b></span><br>    <span style="padding-left:55px;">' + value.name + '</span></td>';
                    branch_data += '<td>' + value.T_amount + '</td></tr>';

                });
                $('#product_sales_pdt_table').DataTable().clear().destroy();
                $("#shows").append(branch_data);
                $('#product_sales_pdt_table').DataTable({
                    "order": [[1, 'desc']],
                    "columnDefs": [
                    {
                        "orderData": [1],
                        "targets": [0],
                    },
                    {
                        "targets": [1],
                        "visible": false,
                        "searchable": false,
                    },
                    ],
                    "autoWidth": true,
                });

            },
            error: function () {
                console.log('fall');
                $('#product_sales_pdt_table').DataTable().clear().draw();

            }
        });


        function sum(input) {
            var total = 0;
            for (var i = 0; i < input.length; i++) {
                total += Number(input[i]);
            }
            return total;
        }

        function formatNumber(num) {
            return num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
        }


    }

    //since location branch


    function since_location(id) {

        $('.ytd_btn').removeClass('btn-green').addClass('bg-blue');
        $('.wtd_btn').removeClass('btn-green').addClass('bg-blue');
        $('.mtd_btn').removeClass('btn-green').addClass('bg-blue');
        $('.today_btn').removeClass('btn-green').addClass('bg-blue');
        $('.since_ytd').removeClass('bg-blue').addClass('btn-green');


        $("#date_from").val("");
        $("#date_to").val("");


        $("#shows").empty();
        console.log("ytd_location " + id);

        var from_date = $(".since_ytd").attr("from");
        var to_date = $(".since_ytd").attr("to");

        $.ajax({

            type: "GET",
            url: "{{route('analytics.ajax.cash.productsales')}}",
            data: {
                from_date: from_date,
                to_date: to_date,
                loc_id: id
            },
            dataType: 'json',
            success: function (data) {
                console.log('success');


                var branch_data = '';

                var allvalue = [];
                data.forEach(function (value) {

                    allvalue.push(value.T_amount);
                });

                var total = sum(allvalue);
                var max_value = Math.max.apply(Math, allvalue);
                ;


                data.forEach(function (value) {

                    var max_percentage = (max_value / total) * 100;

                    var url = '/images/product/' + value.id + '/thumb/' + value.thumbnail_1;


                    branch_data += '<tr>';
                    branch_data += '<td><img src={{URL::to('/')}}/images/product/' + value.id + '/thumb/' + value.thumbnail_1 + '  alt="" width="50px" height="50px" style="object-fit:contain" /> ';
                    if (value.T_amount == max_value) {

                        branch_data += '<img class="greenshade" style="width:85%;"/>';

                    } else {

                        var each_percentage = (value.T_amount / total) * 100;
                        var new_percentage = (85 / max_percentage) * each_percentage;

                        branch_data += '<img class="greenshade" style="width:' + new_percentage + '%;"/>';

                    }


                    branch_data += '<span style="color:red;display:inline"><b> MYR ' + formatNumber(value.T_amount / 100) + '</b></span><br>    <span style="padding-left:55px;">' + value.name + '</span></td>';
                    branch_data += '<td>' + value.T_amount + '</td></tr>';

                });
                $('#product_sales_pdt_table').DataTable().clear().destroy();
                $("#shows").append(branch_data);
                $('#product_sales_pdt_table').DataTable({
                    "order": [[1, 'desc']],
                    "columnDefs": [
                    {
                        "orderData": [1],
                        "targets": [0],
                    },
                    {
                        "targets": [1],
                        "visible": false,
                        "searchable": false,
                    },
                    ],
                    "autoWidth": true,
                });

            },
            error: function () {
                console.log('fall');
                $('#product_sales_pdt_table').DataTable().clear().draw();

            }
        });


        function sum(input) {
            var total = 0;
            for (var i = 0; i < input.length; i++) {
                total += Number(input[i]);
            }
            return total;
        }

        function formatNumber(num) {
            return num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
        }

    }


    //location ytd_location

    function ytd_location(id) {

        $('.since_ytd').removeClass('btn-green').addClass('bg-blue');
        $('.wtd_btn').removeClass('btn-green').addClass('bg-blue');
        $('.mtd_btn').removeClass('btn-green').addClass('bg-blue');
        $('.today_btn').removeClass('btn-green').addClass('bg-blue');
        $('.ytd_btn').removeClass('bg-blue').addClass('btn-green');

        $("#date_from").val("");
        $("#date_to").val("");


        $("#shows").empty();
        console.log("ytd_location " + id);

        var from_date = $(".ytd_btn").attr("from");
        var to_date = $(".ytd_btn").attr("to");

        $.ajax({

            type: "GET",
            url: "{{route('analytics.ajax.cash.productsales')}}",
            data: {
                from_date: from_date,
                to_date: to_date,
                loc_id: id
            },
            dataType: 'json',
            success: function (data) {
                console.log('success');


                var branch_data = '';

                var allvalue = [];
                data.forEach(function (value) {

                    allvalue.push(value.T_amount);
                });

                var total = sum(allvalue);
                var max_value = Math.max.apply(Math, allvalue);
                ;


                data.forEach(function (value) {

                    var max_percentage = (max_value / total) * 100;

                    var url = '/images/product/' + value.id + '/thumb/' + value.thumbnail_1;


                    branch_data += '<tr>';
                    branch_data += '<td><img src={{URL::to('/')}}/images/product/' + value.id + '/thumb/' + value.thumbnail_1 + '  alt="" width="50px" height="50px" style="object-fit:contain" /> ';
                    if (value.T_amount == max_value) {

                        branch_data += '<img class="greenshade" style="width:85%;"/>';

                    } else {

                        var each_percentage = (value.T_amount / total) * 100;
                        var new_percentage = (85 / max_percentage) * each_percentage;

                        branch_data += '<img class="greenshade" style="width:' + new_percentage + '%;"/>';

                    }


                    branch_data += '<span style="color:red;display:inline"><b> MYR ' + formatNumber(value.T_amount / 100) + '</b></span><br>    <span style="padding-left:55px;">' + value.name + '</span></td>';
                    branch_data += '<td>' + value.T_amount + '</td></tr>';

                });
                $('#product_sales_pdt_table').DataTable().clear().destroy();
                $("#shows").append(branch_data);
                $('#product_sales_pdt_table').DataTable({
                    "order": [[1, 'desc']],
                    "columnDefs": [
                    {
                        "orderData": [1],
                        "targets": [0],
                    },
                    {
                        "targets": [1],
                        "visible": false,
                        "searchable": false,
                    },
                    ],
                    "autoWidth": true,
                });

            },
            error: function () {
                console.log('fall');
                $('#product_sales_pdt_table').DataTable()
                .clear()
                .draw();
            }
        });


        function sum(input) {
            var total = 0;
            for (var i = 0; i < input.length; i++) {
                total += Number(input[i]);
            }
            return total;
        }

        function formatNumber(num) {
            return num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
        }

    }

    // location mtd_location

    function mtd_location(id) {

        $('.since_ytd').removeClass('btn-green').addClass('bg-blue');
        $('.wtd_btn').removeClass('btn-green').addClass('bg-blue');
        $('.ytd_btn').removeClass('btn-green').addClass('bg-blue');
        $('.today_btn').removeClass('btn-green').addClass('bg-blue');
        $('.mtd_btn').removeClass('bg-blue').addClass('btn-green');

        $("#date_from").val("");
        $("#date_to").val("");


        $("#shows").empty();
        console.log("mtd_location " + id);

        var from_date = $(".mtd_btn").attr("from");
        var to_date = $(".mtd_btn").attr("to");

        $.ajax({

            type: "GET",
            url: "{{route('analytics.ajax.cash.productsales')}}",
            data: {
                from_date: from_date,
                to_date: to_date,
                loc_id: id
            },
            dataType: 'json',
            success: function (data) {
                console.log('success');


                var branch_data = '';

                var allvalue = [];
                data.forEach(function (value) {

                    allvalue.push(value.T_amount);
                });

                var total = sum(allvalue);
                var max_value = Math.max.apply(Math, allvalue);
                ;


                data.forEach(function (value) {

                    var max_percentage = (max_value / total) * 100;

                    var url = '/images/product/' + value.id + '/thumb/' + value.thumbnail_1;


                    branch_data += '<tr>';
                    branch_data += '<td><img src={{URL::to('/')}}/images/product/' + value.id + '/thumb/' + value.thumbnail_1 + '  alt="" width="50px" height="50px" style="object-fit:contain" /> ';
                    if (value.T_amount == max_value) {

                        branch_data += '<img class="greenshade" style="width:85%;"/>';

                    } else {

                        var each_percentage = (value.T_amount / total) * 100;
                        var new_percentage = (85 / max_percentage) * each_percentage;

                        branch_data += '<img class="greenshade" style="width:' + new_percentage + '%;"/>';

                    }


                    branch_data += '<span style="color:red;display:inline"><b> MYR ' + formatNumber(value.T_amount / 100) + '</b></span><br>    <span style="padding-left:55px;">' + value.name + '</span></td>';
                    branch_data += '<td>' + value.T_amount + '</td></tr>';

                });
                $('#product_sales_pdt_table').DataTable().clear().destroy();
                $("#shows").append(branch_data);
                $('#product_sales_pdt_table').DataTable({
                    "order": [[1, 'desc']],
                    "columnDefs": [
                    {
                        "orderData": [1],
                        "targets": [0],
                    },
                    {
                        "targets": [1],
                        "visible": false,
                        "searchable": false,
                    },
                    ],
                    "autoWidth": true,
                });

            },
            error: function () {
                console.log('fall');
                $('#product_sales_pdt_table').DataTable()
                .clear()
                .draw();
            }
        });


        function sum(input) {
            var total = 0;
            for (var i = 0; i < input.length; i++) {
                total += Number(input[i]);
            }
            return total;
        }

        function formatNumber(num) {
            return num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
        }

    }

    // location wtd_location

    function wtd_location(id) {

        $('.since_ytd').removeClass('btn-green').addClass('bg-blue');
        $('.mtd_btn').removeClass('btn-green').addClass('bg-blue');
        $('.ytd_btn').removeClass('btn-green').addClass('bg-blue');
        $('.today_btn').removeClass('btn-green').addClass('bg-blue');
        $('.wtd_btn').removeClass('bg-blue').addClass('btn-green');

        $("#date_from").val("");
        $("#date_to").val("");

        $("#shows").empty();
        console.log("wtd_location " + id);

        var from_date = $(".wtd_btn").attr("from");
        var to_date = $(".wtd_btn").attr("to");

        $.ajax({

            type: "GET",
            url: "{{route('analytics.ajax.cash.productsales')}}",
            data: {
                from_date: from_date,
                to_date: to_date,
                loc_id: id
            },
            dataType: 'json',
            success: function (data) {
                console.log('success');


                var branch_data = '';

                var allvalue = [];
                data.forEach(function (value) {

                    allvalue.push(value.T_amount);
                });

                var total = sum(allvalue);
                var max_value = Math.max.apply(Math, allvalue);
                ;


                data.forEach(function (value) {

                    var max_percentage = (max_value / total) * 100;

                    var url = '/images/product/' + value.id + '/thumb/' + value.thumbnail_1;


                    branch_data += '<tr>';
                    branch_data += '<td><img src={{URL::to('/')}}/images/product/' + value.id + '/thumb/' + value.thumbnail_1 + '  alt="" width="50px" height="50px" style="object-fit:contain" /> ';
                    if (value.T_amount == max_value) {

                        branch_data += '<img class="greenshade" style="width:85%;"/>';

                    } else {

                        var each_percentage = (value.T_amount / total) * 100;
                        var new_percentage = (85 / max_percentage) * each_percentage;

                        branch_data += '<img class="greenshade" style="width:' + new_percentage + '%;"/>';

                    }


                    branch_data += '<span style="color:red;display:inline"><b> MYR ' + formatNumber(value.T_amount / 100) + '</b></span><br>    <span style="padding-left:55px;">' + value.name + '</span></td>';
                    branch_data += '<td>' + value.T_amount + '</td></tr>';

                });
                $('#product_sales_pdt_table').DataTable().clear().destroy();
                $("#shows").append(branch_data);
                $('#product_sales_pdt_table').DataTable({
                    "order": [[1, 'desc']],
                    "columnDefs": [
                    {
                        "orderData": [1],
                        "targets": [0],
                    },
                    {
                        "targets": [1],
                        "visible": false,
                        "searchable": false,
                    },
                    ],
                    "autoWidth": true,
                });
            },
            error: function () {
                console.log('fall');
                $('#product_sales_pdt_table').DataTable()
                .clear()
                .draw();
            }
        });


        function sum(input) {
            var total = 0;
            for (var i = 0; i < input.length; i++) {
                total += Number(input[i]);
            }
            return total;
        }

        function formatNumber(num) {
            return num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
        }

    }

    //location today_location

    function today_location(id) {

        $('.since_ytd').removeClass('btn-green').addClass('bg-blue');
        $('.mtd_btn').removeClass('btn-green').addClass('bg-blue');
        $('.ytd_btn').removeClass('btn-green').addClass('bg-blue');
        $('.wtd_btn').removeClass('btn-green').addClass('bg-blue');
        $('.today_btn').removeClass('bg-blue').addClass('btn-green');

        $("#date_from").val("");
        $("#date_to").val("");


        $("#shows").empty();
        console.log("today_location " + id);

        var from_date = $(".today_btn").attr("from");
        var to_date = $(".today_btn").attr("to");

        $.ajax({

            type: "GET",
            url: "{{route('analytics.ajax.cash.productsales')}}",
            data: {
                from_date: from_date,
                to_date: to_date,
                loc_id: id
            },
            dataType: 'json',
            success: function (data) {
                console.log('success');


                var branch_data = '';

                var allvalue = [];
                data.forEach(function (value) {

                    allvalue.push(value.T_amount);
                });

                var total = sum(allvalue);
                var max_value = Math.max.apply(Math, allvalue);


                data.forEach(function (value) {
                    console.log("Value: ", value);

                    var max_percentage = (max_value / total) * 100;

                    var url = '/images/product/' + value.id + '/thumb/' + value.thumbnail_1;


                    branch_data += '<tr>';
                    branch_data += '<td><img src={{URL::to('/')}}/images/product/' + value.id + '/thumb/' + value.thumbnail_1 + '  alt="" width="50px" height="50px" style="object-fit:contain" /> ';
                    if (value.T_amount == max_value) {

                        branch_data += '<img class="greenshade" style="width:85%;"/>';

                    } else {

                        var each_percentage = (value.T_amount / total) * 100;
                        var new_percentage = (85 / max_percentage) * each_percentage;

                        branch_data += '<img class="greenshade" style="width:' + new_percentage + '%;"/>';

                    }


                    branch_data += '<span style="color:red;display:inline"><b> MYR ' + formatNumber(value.T_amount / 100) + '</b></span><br>    <span style="padding-left:55px;">' + value.name + '</span></td>';
                    branch_data += '<td>' + value.T_amount + '</td></tr>';

                });
                $('#product_sales_pdt_table').DataTable().clear().destroy();
                $("#shows").append(branch_data);
                $('#product_sales_pdt_table').DataTable({
                    "order": [[1, 'desc']],
                    "columnDefs": [
                    {
                        "orderData": [1],
                        "targets": [0],
                    },
                    {
                        "targets": [1],
                        "visible": false,
                        "searchable": false,
                    },
                    ],
                    "autoWidth": true,
                });

            },
            error: function () {
                console.log('fall');
                $('#product_sales_pdt_table').DataTable().clear().draw();
            }
        });


        function sum(input) {
            var total = 0;
            for (var i = 0; i < input.length; i++) {
                total += Number(input[i]);
            }
            return total;
        }

        function formatNumber(num) {
            return num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
        }

    }

    //custom date range for location


    function dateRange(from_date, to_date, id) {

        $("#shows").empty();
        console.log("daterande success " + id + " " + from_date + " " + to_date);


        $.ajax({

            type: "GET",
            url: "{{route('analytics.ajax.cash.productsales')}}",
            data: {
                from_date: from_date,
                to_date: to_date,
                loc_id: id
            },
            dataType: 'json',
            success: function (data) {
                console.log('success');


                var branch_data = '';

                var allvalue = [];
                data.forEach(function (value) {

                    allvalue.push(value.T_amount);
                });

                var total = sum(allvalue);
                var max_value = Math.max.apply(Math, allvalue);
                ;


                data.forEach(function (value) {

                    var max_percentage = (max_value / total) * 100;

                    var url = '/images/product/' + value.id + '/thumb/' + value.thumbnail_1;


                    branch_data += '<tr>';
                    branch_data += '<td><img src={{URL::to('/')}}/images/product/' + value.id + '/thumb/' + value.thumbnail_1 + '  alt="" width="50px" height="50px" style="object-fit:contain" /> ';
                    if (value.T_amount == max_value) {

                        branch_data += '<img class="greenshade" style="width:85%;"/>';

                    } else {

                        var each_percentage = (value.T_amount / total) * 100;
                        var new_percentage = (85 / max_percentage) * each_percentage;

                        branch_data += '<img class="greenshade" style="width:' + new_percentage + '%;"/>';

                    }


                    branch_data += '<span style="color:red;display:inline"><b> MYR ' + formatNumber(value.T_amount / 100) + '</b></span><br>    <span style="padding-left:55px;">' + value.name + '</span></td>';
                    branch_data += '</tr>';

                });

                $("#shows").append(branch_data);

            },
            error: function () {
                console.log('fall');

            }
        });


        function sum(input) {
            var total = 0;
            for (var i = 0; i < input.length; i++) {
                total += Number(input[i]);
            }
            return total;
        }

        function formatNumber(num) {
            return num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
        }


    }


    //custom date range for all


    function dateRangeForAll(from_date, to_date) {

        $("#shows").empty();
        console.log("daterande success  " + from_date + " " + to_date);


        $.ajax({

            type: "GET",
            url: "{{route('analytics.ajax.cash.productsales')}}",
            data: {
                from_date_all: from_date,
                to_date_all: to_date

            },
            dataType: 'json',
            success: function (data) {
                console.log('success');
                console.log('Bro');
                console.log('Data: ', data);


                var branch_data = '';

                var allvalue = [];
                data.forEach(function (value) {

                    allvalue.push(value.T_amount);
                });

                var total = sum(allvalue);
                var max_value = Math.max.apply(Math, allvalue);
                ;


                data.forEach(function (value) {

                    var max_percentage = (max_value / total) * 100;

                    var url = '/images/product/' + value.id + '/thumb/' + value.thumbnail_1;


                    branch_data += '<tr>';
                    branch_data += '<td><img src={{URL::to('/')}}/images/product/' + value.id + '/thumb/' + value.thumbnail_1 + '  alt="" width="50px" height="50px" style="object-fit:contain" /> ';
                    if (value.T_amount == max_value) {

                        branch_data += '<img class="greenshade" style="width:85%;"/>';

                    } else {

                        var each_percentage = (value.T_amount / total) * 100;
                        var new_percentage = (85 / max_percentage) * each_percentage;

                        branch_data += '<img class="greenshade" style="width:' + new_percentage + '%;"/>';

                    }


                    branch_data += '<span style="color:red;display:inline"><b> MYR ' + formatNumber(value.T_amount / 100) + '</b></span><br>    <span style="padding-left:55px;">' + value.name + '</span></td>';
                    branch_data += '</tr>';

                });

                $("#shows").append(branch_data);

            },
            error: function () {
                console.log('fall');

            }
        });


        function sum(input) {
            var total = 0;
            for (var i = 0; i < input.length; i++) {
                total += Number(input[i]);
            }
            return total;
        }

        function formatNumber(num) {
            return num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
        }


    }


    //on click get value from date range.... 
    $(".btndownload").on('click',function(e){
        $(".btndownload").removeClass('getValueForDownload');
        $(e.target).addClass('getValueForDownload');
        var id = $('.getValueForDownload').hasClass('btnremove');
        if (id == true) {
            $(".btndownload").removeClass('btn-green');
        } 
    });

    //download pdf generate and ajax send request ...
    function downloadAsPdf(){

        var title_name = 'Product Sales';
        var branch_name = $('#location_modal').html();
        var branch_id = $('.getValueForDownload').attr('id');
        var range_name = $('.getValueForDownload').attr('rel-type');
        
        if (isNaN(branch_id)) {
            branch_id = -1;
        }


        if (typeof range_name == 'undefined') {       
        //from date to date
        var datefrom = $("#date_from").val();
        var newfrom = new Date(datefrom);
        var dayfrom = newfrom.getDate();
        var monthfrom = newfrom.getMonth()+1;
        var yearfrom = newfrom.getFullYear(); // output is -2019 so create 2019
        var yearfrom1 = String(yearfrom);
        var yearfrom2 = yearfrom1.slice(0);
        var from = yearfrom2+"-"+monthfrom+"-"+dayfrom;

        //to date
        var dateto = $("#date_to").val();
        var newto = new Date(dateto);
        var dayto = newto.getDate();
        var monthto = newto.getMonth()+1;
        var yearto = newto.getFullYear(); // output is -2019 so create 2019
        var yearto1 = String(yearto);
        var yearto2 = yearto1.slice(0);
        var to = yearto2+"-"+monthto+"-"+dayto;

    } else {
         //from date range button.....
         var from = $('.getValueForDownload').attr('from'); 
         var to = $('.getValueForDownload').attr('to');     
     }

        //check undefined value ....
        if (from == "NaN-NaN-NaN" && to == "NaN-NaN-NaN") {

          var msg = '<?php echo json_encode(App\Http\Controllers\ApiMessageController::alertMessage("Please select date range.")); ?>';

          var x = JSON.parse(msg);
          $("#statusModalLabel").html(x.message);            
          $("#msgModal").modal('show');

          setTimeout(function() {
            $("#msgModal").modal('hide');
            $('.modal-backdrop').remove();
        },1500)
          return false;
      }
      var token = $('input[name="_token"]').val();
        //if selected any date than it will be work
        if (from != "NaN-NaN-NaN" && to != "NaN-NaN-NaN") {
            $.ajax({
                type:"POST",
                url:"{{route('analytics.ajax.cash.pdf.productsales')}}",
                data:{
                    from_date:from,
                    to_date:to,
                    range_name:range_name,
                    title_name: title_name,
                    branch_name: branch_name,
                    branch_id: branch_id,
                    _token:token

                },
                dataType:'json',
                success: function(response){
                    var base_url = "{{ asset('') }}";
                    var url = base_url+ 'pdf/analytics/' + response.file_name;
                    $(".getDownload").attr("href", url);
                    $(".getDownload").attr("download", response.file_name);
                },
                error:function(){
                    console.log('fail download');
                }
            });
        }else{
            console.log('error from date_from and date_to');
        }
    }

    $('.btnremove').on('click',function() {
     $(".sellerbutton1").addClass('bg-blue');
 });

    $(function(){
        $('#graph-merchant-td').click();
    });

    $(".select_date_range").on('click',function(){
     var id = $('.select_date_range').attr('id');
     if (Number(id)) {
         $('.today_btn').click();
     }
 });

</script>


<div class="clearfix"></div>
<div class="modal fade" id="showDateModalFrom" tabindex="-1"
role="dialog" aria-labelledby="staffNameLabel" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered  mw-75 w-50" role="document">
    <div class="modal-content modal-inside bg-greenlobster">
        <div class="modal-body text-center" style="min-height: 450px;">
            <div class="row">
                <div class="col-md-2">
                    <i class="prev-month fa fa-chevron-left fa-3x"
                    style="cursor:pointer;display: inline-flex;"></i>
                </div>
                <div class=" col-md-8">
                    <div class="month-year text-center text-white"></div>
                </div>
                <div class="col-md-2">
                    <i style="cursor:pointer"
                    class="next-month fa fa-chevron-right fa-3x"></i>
                </div>
            </div>
            <div class="row">
                <div class="shortDay">
                    <ul>
                        <li class="list-inline-item">S</li>
                        <li class="list-inline-item">M</li>
                        <li class="list-inline-item">T</li>
                        <li class="list-inline-item">W</li>
                        <li class="list-inline-item">T</li>
                        <li class="list-inline-item">F</li>
                        <li class="list-inline-item">S</li>
                    </ul>
                </div>
            </div>
            <table class="table date_table">
                <tr style="display: none;">
                    <th>S</th>
                    <th>M</th>
                    <th>T</th>
                    <th>W</th>
                    <th>T</th>
                    <th>F</th>
                    <th>S</th>
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

<div class="modal fade" id="showDateModalTo" tabindex="-1"
role="dialog" aria-labelledby="staffNameLabel" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered  mw-75 w-50" role="document">
    <div class="modal-content modal-inside bg-greenlobster">
        <div class="modal-body text-center" style="min-height: 450px;">
            <div class="row">
                <div class="col-md-2">
                    <i class="prev-month fa fa-chevron-left fa-3x"
                    style="cursor:pointer;display: inline-flex;"></i>
                </div>
                <div class=" col-md-8">
                    <div class="month-year text-center text-white"></div>
                </div>
                <div class="col-md-2">
                    <i style="cursor:pointer"
                    class="next-month fa fa-chevron-right fa-3x"></i>
                </div>
            </div>
            <div class="row">
                <div class="shortDay">
                    <ul>
                        <li class="list-inline-item">S</li>
                        <li class="list-inline-item">M</li>
                        <li class="list-inline-item">T</li>
                        <li class="list-inline-item">W</li>
                        <li class="list-inline-item">T</li>
                        <li class="list-inline-item">F</li>
                        <li class="list-inline-item">S</li>
                    </ul>
                </div>
            </div>
            <table class="table date_table1">
                <tr style="display: none;">
                    <th>S</th>
                    <th>M</th>
                    <th>T</th>
                    <th>W</th>
                    <th>T</th>
                    <th>F</th>
                    <th>S</th>
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
<br><br>


@if(empty($approved_merchant))
<style type="text/css">
    .btndownload{
        pointer-events: none !important; 
    }
</style>
@endif
