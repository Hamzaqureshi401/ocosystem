<style>
	th {
    background-color: black;
    color: white;
} 
.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter,
.dataTables_wrapper .dataTables_info,
.dataTables_wrapper .dataTables_processing,
.dataTables_wrapper .dataTables_paginate {
	color: black !important;
	font-weight: normal !important;
}

#receipt-table_length, #receipt-table_filter,
#receipt-table_info, .paginate_button {
	color: white !important;
}

#eodSummaryListModal-table_paginate, #eodSummaryListModal-table_previous,
#eodSummaryListModal-table_next, #eodSummaryListModal-table_length,
#eodSummaryListModal-table_filter, #eodSummaryListModal-table_info {
	color: white !important;
}

.paging_full_numbers a.paginate_button {
	color: #fff !important;
}

.paging_full_numbers a.paginate_active {
	color: #fff !important;
}
.alignleft {

    float: left;
}

.alignright {

    float: right;
}
table.dataTable th.dt-right, table.dataTable td.dt-right {
	text-align: right !important;
}
</style>
<div class="location">
<div class="row py-2 text-center"
	style="height:75px;padding-bottom:0 !important;padding-top:0 !important">
	<div class="col-md-6 text-center" style="display:flex;align-items:center" >
		<h2 class="mb-0 text-center">Itemized Sales Report: Location</h2>
	</div>
</div>
<div class="table-responsive" style="overflow-x: hidden;">
	<table class="table table-bordered " id="cstore_tbl">
	<thead class="thead-dark">
	<tr class="">
		<th class="text-center" style="width:30px">No</th>
		<th class="text-center" style="width:120px;">Location&nbsp;ID</th>
		<th class="text-left" style="width:auto;">Location&nbsp;</th>
	</tr>
	</thead>
	<tbody>
		@php
		$count = 0;
		@endphp
		@foreach ($locations as $location)
		@php
		$count = $count + 1;
		@endphp
		<tr>
			<td class="text-center">{{ $count }}</td>
			<td class="text-center">
			<a href="/"
				style="text-decoration:none"
				onclick="openitemizeddsalereport({{ $location->systemid }}); return false;"
				>{{ $location->systemid }}</a></td>
			<td class="text-left" id="branch-{{ $location->systemid }}"> {{ $location->branch }} </td>
			
		</tr>
		@endforeach
	</tbody>

	</table>
</div>
</div>



<div class="sales_pages d-none">
	<div id="landing-view">
	<!--div id="landing-content" style="width: 100%"-->
	<div class="container-fluid pl-0 pr-0">
		<div class="clearfix"></div>
		<div class="row py-2 align-items-center"
			style="display:flex;height:75px;margin-left:0; margin-right:0;
			padding-top:0;padding-bottom:0">
			<div class="col p-0" style="width:100%">
				<h2 class="alignleft mb-0">
					Itemized Sales Report
				</h2>
			</div>

			<div class="col-md-2 text-right">
				<h5 style="margin-bottom:0;">
				<span class="alignright text-center" id="branch"></span>
				<br>
				<span class="alignright systemid"></span>
				</h5>
			</div>
		</div>


		<div>
			<h5 class="mb-0">Convenience Store Sales</h5>
			<hr class="mt-0 mb-2" style="border-color:#c0c0c0">
			<div style="right:200px;display:inline;padding-left:0;margin-bottom:20px">
				<input class="to_date form-control btnremove"
				style="display:inline;margin-top:10px;padding-top:0px !important;
				position:relative;top:2px;
				padding-bottom: 0px; width:110px;padding-right:0;padding-left:0px;
				text-align: center;"
				value="{{ $date }}"
				onclick="show_date(1)"
				id="setDate-1" name="start_date" placeholder="Select" />
			</div>
			{{ csrf_field() }}
			To
			<div style="right:200px;display:inline;padding-left:0;
				margin-bottom:20px">
				<input class="to_date form-control btnremove"
				style="display:inline;margin-top:10px;padding-top:0px !important;
				position:relative;top:2px;
				padding-bottom: 0px; width:110px;padding-right:0;
				padding-left:0px; text-align: center;"
				value="{{ $date }}"
				onclick="show_date(2)"
				id="setDate-2" name="end_date" placeholder="Select" />
			</div>

			<div style="right:200px;display:inline;
				padding-left:40px;margin-bottom:20px">
				<button class="btn btn-success bg-download"
					style="height:70px;width:70px;border-radius:10px;
					outline:none;font-size: 14px" 
					onclick="pdfPrint(1 , 2 , 'C-Store')">PDF
				</button>
			</div>
		</div>
<div>
			<h5 class="mb-0">Fuel Sales</h5>
			<hr class="mt-0 mb-2" style="border-color:#c0c0c0">
			<div style="right:200px;display:inline;padding-left:0;margin-bottom:20px">
				<input class="to_date form-control btnremove"
				style="display:inline;margin-top:10px;padding-top:0px !important;
				position:relative;top:2px;
				padding-bottom: 0px; width:110px;padding-right:0;padding-left:0px;
				text-align: center;"
				value="{{ $date }}"
				onclick="show_date(3)"
				id="setDate-3" name="start_date" placeholder="Select" />
			</div>
			{{ csrf_field() }}
			To
			<div style="right:200px;display:inline;padding-left:0;
				margin-bottom:20px">
				<input class="to_date form-control btnremove"
				style="display:inline;margin-top:10px;padding-top:0px !important;
				position:relative;top:2px;
				padding-bottom: 0px; width:110px;padding-right:0;
				padding-left:0px; text-align: center;"
				value="{{ $date }}"
				onclick="show_date(4)"
				id="setDate-4" name="end_date" placeholder="Select" />
			</div>

			<div style="right:200px;display:inline;
				padding-left:40px;margin-bottom:20px">
				<button class="btn btn-success bg-download"
					style="height:70px;width:70px;border-radius:10px;
					outline:none;font-size: 14px" 
					onclick="pdfPrint(3 , 4 , 'Fuel')">PDF
				</button>
			</div>
		</div>
		<div>
			<h5 class="mb-0">Electric Vehicle Charger Sales</h5>
			<hr class="mt-0 mb-2" style="border-color:#c0c0c0">
			<div style="right:200px;display:inline;padding-left:0;margin-bottom:20px">
				<input class="to_date form-control btnremove"
				style="display:inline;margin-top:10px;padding-top:0px !important;
				position:relative;top:2px;
				padding-bottom: 0px; width:110px;padding-right:0;padding-left:0px;
				text-align: center;"
				value="{{ $date }}"
				onclick="show_date(5)"
				id="setDate-5" name="start_date" placeholder="Select" />
			</div>
			{{ csrf_field() }}
			To
			<div style="right:200px;display:inline;padding-left:0;
				margin-bottom:20px">
				<input class="to_date form-control btnremove"
				style="display:inline;margin-top:10px;padding-top:0px !important;
				position:relative;top:2px;
				padding-bottom: 0px; width:110px;padding-right:0;
				padding-left:0px; text-align: center;"
				value="{{ $date }}"
				onclick="show_date(6)"
				id="setDate-6" name="end_date" placeholder="Select" />
			</div>

			<div style="right:200px;display:inline;
				padding-left:40px;margin-bottom:20px">
				<button class="btn btn-success bg-download"
					style="height:70px;width:70px;border-radius:10px;
					outline:none;font-size: 14px" 
					onclick="pdfPrint(5 , 6 , 'EV')">PDF
				</button>
			</div>
		</div>
		<div>
			<h5 class="mb-0">Hydrogen Sales</h5>
			<hr class="mt-0 mb-2" style="border-color:#c0c0c0">
			<div style="right:200px;display:inline;padding-left:0;margin-bottom:20px">
				<input class="to_date form-control btnremove"
				style="display:inline;margin-top:10px;padding-top:0px !important;
				position:relative;top:2px;
				padding-bottom: 0px; width:110px;padding-right:0;padding-left:0px;
				text-align: center;"
				value="{{ $date }}"
				onclick="show_date(7)"
				id="setDate-7" name="start_date" placeholder="Select" />
			</div>
			{{ csrf_field() }}
			To
			<div style="right:200px;display:inline;padding-left:0;
				margin-bottom:20px">
				<input class="to_date form-control btnremove"
				style="display:inline;margin-top:10px;padding-top:0px !important;
				position:relative;top:2px;
				padding-bottom: 0px; width:110px;padding-right:0;
				padding-left:0px; text-align: center;"
				value="{{ $date }}"
				onclick="show_date(8)"
				id="setDate-8" name="end_date" placeholder="Select" />
			</div>

			<div style="right:200px;display:inline;
				padding-left:40px;margin-bottom:20px">
				<button class="btn btn-success bg-download"
					style="height:70px;width:70px;border-radius:10px;
					outline:none;font-size: 14px" 
					onclick="pdfPrint(7 , 8 , 'hydrogen')">PDF
				</button>
			</div>
		</div>

		<div>
			<h5 class="mb-0">Outdoor e-Wallet Sales</h5>
			<hr class="mt-0 mb-2" style="border-color:#c0c0c0">
			<div style="right:200px;display:inline;padding-left:0;margin-bottom:20px">
				<input class="to_date form-control btnremove"
				style="display:inline;margin-top:10px;padding-top:0px !important;
				position:relative;top:2px;
				padding-bottom: 0px; width:110px;padding-right:0;padding-left:0px;
				text-align: center;"
				value="{{ $date }}"
				onclick="show_date(9)"
				id="setDate-9" name="start_date" placeholder="Select" />
			</div>
			{{ csrf_field() }}
			To
			<div style="right:200px;display:inline;padding-left:0;
				margin-bottom:20px">
				<input class="to_date form-control btnremove"
				style="display:inline;margin-top:10px;padding-top:0px !important;
				position:relative;top:2px;
				padding-bottom: 0px; width:110px;padding-right:0;
				padding-left:0px; text-align: center;"
				value="{{ $date }}"
				onclick="show_date(10)"
				id="setDate-10" name="end_date" placeholder="Select" />
			</div>

			<div style="right:200px;display:inline;
				padding-left:40px;margin-bottom:20px">
				<button class="btn btn-success bg-download"
					style="height:70px;width:70px;border-radius:10px;
					outline:none;font-size: 14px"
					onclick="pdfPrint(9 , 10 , 'e-Wallet')">PDF
				</button>
			</div>
		</div>

		<div>
			<h5 class="mb-0">Outdoor Payment Terminal Sales</h5>
			<hr class="mt-0 mb-2" style="border-color:#c0c0c0">
			<div style="right:200px;display:inline;padding-left:0;margin-bottom:20px">
				<input class="to_date form-control btnremove"
				style="display:inline;margin-top:10px;padding-top:0px !important;
				position:relative;top:2px;
				padding-bottom: 0px; width:110px;padding-right:0;padding-left:0px;
				text-align: center;"
				value="{{ $date }}"
				onclick="show_date(11)"
				id="setDate-11" name="start_date" placeholder="Select" />
			</div>
			{{ csrf_field() }}
			To
			<div style="right:200px;display:inline;padding-left:0;
				margin-bottom:20px">
				<input class="to_date form-control btnremove"
				style="display:inline;margin-top:10px;padding-top:0px !important;
				position:relative;top:2px;
				padding-bottom: 0px; width:110px;padding-right:0;
				padding-left:0px; text-align: center;"
				value="{{ $date }}"
				onclick="show_date(12)"
				id="setDate-12" name="end_date" placeholder="Select" />
			</div>

			<div style="right:200px;display:inline;
				padding-left:40px;margin-bottom:20px">
				<button class="btn btn-success bg-download"
					style="height:70px;width:70px;border-radius:10px;
					outline:none;font-size: 14px"
					onclick="pdfPrint(11 , 12 , 'payment_terminal')">PDF
				</button>
			</div>
		</div>

		<div>
			<h5 class="mb-0">Open Item Sales</h5>
			<hr class="mt-0 mb-2" style="border-color:#c0c0c0">
			<div style="right:200px;display:inline;padding-left:0;margin-bottom:20px">
				<input class="to_date form-control btnremove"
				style="display:inline;margin-top:10px;padding-top:0px !important;
				position:relative;top:2px;
				padding-bottom: 0px; width:110px;padding-right:0;padding-left:0px;
				text-align: center;"
				value="{{ $date }}"
				onclick="show_date(13)"
				id="setDate-13" name="start_date" placeholder="Select" />
			</div>
			{{ csrf_field() }}
			To
			<div style="right:200px;display:inline;padding-left:0;
				margin-bottom:20px">
				<input class="to_date form-control btnremove"
				style="display:inline;margin-top:10px;padding-top:0px !important;
				position:relative;top:2px;
				padding-bottom: 0px; width:110px;padding-right:0;
				padding-left:0px; text-align: center;"
				value="{{ $date }}"
				onclick="show_date(14)"
				id="setDate-14" name="end_date" placeholder="Select" />
			</div>

			<div style="right:200px;display:inline;
				padding-left:40px;margin-bottom:20px">
				<button class="btn btn-success bg-download"
					style="height:70px;width:70px;border-radius:10px;
					outline:none;font-size: 14px"
					onclick="pdfPrint(13 , 14 , 'open-item')">PDF
				</button>
			</div>
		</div>
	</div>
</div>


<style>
.btn {
	color: #fff !Important;
}
.form-control:disabled, .form-control[readonly] {
	background-color: #e9ecef !important;
	opacity: 1;
}

#void_stamp {
	font-size: 100px;
	color: red;
	position: absolute;
	z-index: 2;
	font-weight: 500;
	margin-top: 130px;
	margin-left: 10%;
	transform: rotate(45deg);
	display: none;
}
</style>



<div class="clearfix"></div>
<br><br>

<div class="modal fade" id="showDateModalFrom" tabindex="-1"
  role="dialog" aria-labelledby="staffNameLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered  mw-75 w-50" role="document">
    <div class="modal-content modal-inside bg-purplelobster">
      <div class="modal-body text-center" style="min-height: 485px;max-height:485px">
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


<!-- blue crab modal pop up ends here-->
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
	color: #008000 !important;
	font-weight: bold !important;
}

.selected_date1 {
	color: #008000 !important;
	font-weight: bold !important;
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
	color:white;
	border-color:rgba(0,0,255,0.5);
	background-color:rgba(0,0,255,0.5)
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
.selected-button {
	background-color: green;;
	color: #fff;
}

.selected-button:hover {
	color: #fff !important;
}

.un-selected-button {
	background-color: #007bff;
	color: #fff;
}

.un-selected-button:hover {
	background: green;;
	color: white;
}

.disabled {
	color: gray!important;
   cursor: not-allowed !important;
}
.active {
	color:darkgreen;
	font-weight:700;
}
</style>
<style>
table.dataTable thead th, table.dataTable thead td { border: none !important}
</style>
<script>
$(document).ready(function() {
    $('#cstore_tbl').dataTable({
        "aLengthMenu": [[10, 50, 75, -1], [10, 25, 50, 100]],
        "iDisplayLength": 10,
        'aoColumnDefs': [{
        'bSortable': false,
        'aTargets': ['nosort']
    }]
    });
} );

var system_id = "";
function openitemizeddsalereport(systemid){

	$('.location').addClass('d-none');
	$('.sales_pages').removeClass('d-none');
	$('.systemid').text(systemid);
	system_id = systemid;
	var branch = $('#branch-'+systemid).text();
	$('#branch').text(branch);
	
	
}
</script>
<script src="{{asset('/js/osmanli_calendar.js')}}?version={{date("hmis")}}"></script>

<script>

	// store_date = dateToYMDEmpty(new Date());
	// $('#setDate-'+id_pass).val(store_date);


 $(function() {
        $(document).ready(function () {
            var todaysDate = new Date(); // Gets today's date
            // Max date attribute is in "YYYY-MM-DD".
            // Need to format today's date accordingly
            var year = todaysDate.getFullYear(); // YYYY
            var month = ("01");  // MM
            var day = ("01");           // DD
            var minDate = (year +"-"+ month );
            //  +"-"+ display Results in "YYYY-MM" for today's date
            // Now to set the max date value for the calendar to be today's date
            $('#setdate').attr('min',minDate);
        });
    });

var id_pass = 0;
function show_date(id_passed){

	id_pass = id_passed;
	console.log("this" , id_pass);
	 var date = osmanli_calendar.SELECT_DATE;
           
	var start_date = dateToYMDEmpty(date);
	          $('#setDate-'+id_pass).val(start_date);
      
	jQuery('#showDateModalFrom').modal('show');
	var EndDate = new Date();
}
function dateToYMDEmpty(date) {
	var strArray=['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
	var d = date.getDate();
	var m = strArray[date.getMonth()];
	var y = date.getFullYear().toString().substr(-2);
	var currentHours = date.getHours();
	return '' + (d <= 9 ? '0' + d : d) + '' + m + '' + y ;
}
    $(function() {
        $(document).ready(function () {
            var todaysDate = new Date(); // Gets today's date
            // Max date attribute is in "YYYY-MM-DD".
            // Need to format today's date accordingly
            var year = todaysDate.getFullYear(); // YYYY
            var month = ("01");  // MM
            var day = ("01");           // DD
            var minDate = (year +"-"+ month );
            //  +"-"+ display Results in "YYYY-MM" for today's date
            // Now to set the max date value for the calendar to be today's date
            $('#setDate').attr('min',minDate);
        });
    });

    @yield('current_year')

    function show_dialog2(ogFuelId) {
        jQuery('#showDateModal').modal('show');
        $("#ogFuelPriceId").val(ogFuelId);
    }

    $('#showDateModal').on('hidden.bs.modal', function (e) {
        onDateSelect();
    });


    var CURRENT_DATE = new Date();
    var d = new Date();

    var content = 'January February March April May June July August September October November December'.split(' ');
    var contentMonth = '1 2 3 4 5 6 7 8 9 10 11 12'.split(' ');
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


    // Clear generated calendar
    var ACTIVE_DATE  = [];


    function clearCalendar() {
        if($('td.selected_date').length){
             ACTIVE_DATE  = [];
            ACTIVE_DATE.push($('td.selected_date').text());
            ACTIVE_DATE.push($('#currMonth').val());
            // console.log(ACTIVE_DATE);
        }
        var $trs = $('.picker tr').not(':eq(0)');
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


    function myCalendar() {
        var month = d.getUTCMonth();
        var day = d.getUTCDay();
        var year = d.getUTCFullYear();
        var date = d.getUTCDate();
        var totalDaysOfMonth = daysOfMonth[month];
        var counter = 1;

        var $h3 = $('<h3>');

        $h3.html(content[month] + ' ' + year );
        $h3.appendTo('.month-year');
        var $div = $('<div>');
        $div.html('<input type="hidden" id="currMonth" name="currMonth" value="'+contentMonth[month]+'">');
        $div.appendTo('.month-year');

        var dateToHighlight = 0;

        // Determine if Month && Year are current for Date Highlight
        if (CURRENT_DATE.getUTCMonth() === month &&
            CURRENT_DATE.getUTCFullYear() === year) {

            dateToHighlight = date;
        }

        //Getting February Days Including The Leap Year
        if (month === 1) {
            if ((year % 100 !== 0) && (year % 4 === 0) || (year % 400 === 0)) {
                totalDaysOfMonth = 29;
            }
        }

        // Get Start Day
        renderCalendar(getCalendarStart(day, date), totalDaysOfMonth,
            dateToHighlight);
    };

    $(document).ready(function() {
        // Bind Events
        $('.prev-month').click(function() {
            if($(this).hasClass('disabled')) return false;
            navigationHandler(-1);
        });
        $('.next-month').click(function() {
            navigationHandler(1);
        });
        // Generate Calendar
        myCalendar();
        shoot_event();
    });

    var CURRENT_DATE = new Date();
    function shoot_event () {
        var year = d.getUTCFullYear();
        var currentMOnth = parseInt($('#currMonth').val()) -1;

        // if(currentMOnth ==  CURRENT_DATE.getMonth()  && year <=CURRENT_DATE.getFullYear()){
        //     $('.prev-month').addClass('disabled');
        //     $('.prev-month').css("cursor", "not-allowed");
        // } else{
        //     $('.prev-month').removeClass('disabled');
        //     $('.prev-month').css("cursor", "default");
        // }

        $('.date_table > tbody > tr > td').click(function(e) {
            console.log("Date clicked");

            var target = e.target;
            $('.date_table > tbody > tr > td').removeClass('selected_date');

            $(target).addClass('selected_date');

            var act = {"day" : $(this).text(), "month" : $('#currMonth').val() , "year" : year};
            sessionStorage.setItem('activeDate', JSON.stringify(act));

            let day = $(target).html();
            let month  = $('.month-year > h3').html();

            var strFirstThree = month.substring(0,3);
            var last2 = month.slice(-2);

            var a = $('#setDate-'+id_pass).val(day+''+strFirstThree+''+last2);
            console.log(id_pass);

            jQuery('#showDateModalFrom').modal('hide');
        });

        $('.date_table tbody tr td').each(function () {
            if(ACTIVE_DATE.length){
                if($(this).text() == ACTIVE_DATE[0] &&
                    $('#currMonth').val() == ACTIVE_DATE[1] &&
                    year <=CURRENT_DATE.getFullYear()){

                    $(this).addClass('selected_date');
                }
            }
            var s = sessionStorage.getItem('activeDate');
            if(s != null || s != undefined){
                s = JSON.parse(s);
                //console.log(s);
                if(s.day == $(this).text() &&
                    s.month == $('#currMonth').val() && s.year == year) {
                    $(this).addClass('selected_date');
                }
            }

            // var currentMOnth = parseInt($('#currMonth').val()) -1;

            if ((parseInt($(this).text()) < CURRENT_DATE.getDate() &&
                currentMOnth <=  CURRENT_DATE.getMonth() &&
                year <=CURRENT_DATE.getFullYear())){

                // $(this).closest('td').addClass('disabled');
                // // $(this).closest('tr').css("pointer-events", "none");
                // $(this).closest('td').css("cursor", "not-allowed");
                // $(this).closest('td').unbind('click');
            }
        });
    }


    function overideFY() {
        $('#overide').val('true');
        //onDateSelect();
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
		$('#date_from').val(selectedDate.getDate()+selectedDate.toLocaleString('en-us',
			{ month: 'short' })+selectedDate.getFullYear().toString().substr(2,2));

		//  $('#from_year').html(selectedDate.getFullYear()+ ' from');

		if (todaysDate.getFullYear() > selectedDate.getFullYear()) {
			alert('Error: You can only select from this year!');
			$('#setDate').val('');
			return false;

		} else {
			console.log("date")
		}
	}

    $(".modal-body div:first").on("click" , function(){
		var change_month_year = $(".modal-body div:first .col-md-8 .month-year h3").html()
		var select_moth_year = sessionStorage.getItem("select_moth_year");
		var date = sessionStorage.getItem("date_check");

		if(date == 1){
			if(change_month_year  == select_moth_year ){
			var table_data =  $(".date_table tbody tr").eq(1)
			table_data.children('td').each(function(){
				var data = $(this).html();
					if(data== 1){
						$(this).addClass("selected_date")   

					}
				})

			}else{
				$(".selected_date").removeClass("selected_date")

			}
		}
	})

   function pdfPrint(start_date , end_date , view){


        var startDate = $('#setDate-'+start_date).val();
        var endDate = $('#setDate-'+end_date).val();
        
		   
   //     var data = {
			// "startDate":startDate,
			// "endDate":endDate,
			// "view":view,
			// "systemid":system_id,
   //      };


        var url = "{{ route('download.pdf') }}/"+startDate+"/"+endDate+"/"+view+"/"+system_id;
        window.open(url, '_blank');
       console.log(url);

  //       $.ajax({
		// 	url: "{{route('download.pdf')}}",
  //           type: "get",
  //           'headers': {
		// 	  'X-CSRF-TOKEN': '{{ csrf_token() }}'
		// 	},
  //           data:data,

		// }).done(downloadFile);       
   
    }
     function downloadFile(response) {
        var blob = new Blob([response], {type: 'application/pdf'})
        var url = URL.createObjectURL(blob);
        location.assign(url);
    }


</script>


