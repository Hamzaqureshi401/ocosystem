@extends('industry.oil_gas.og_oilgas')

@section('content')
<style>
.butns{
	display: none
}
th,td{
	vertical-align: middle !important;	
	text-align: center
}
</style>
<style>
.dataTable > thead > tr > th[class*="sort"]:after{
	content: "" !important;
}
.dataTable > thead > tr > th[class*="sort"]:before{
	content: "" !important;
}

li{
	list-style: none
}
.modal-add-style {
	text-decoration: underline blue;
	cursor: pointer;
}
.table td {
	vertical-align: middle;
}

#inventoryCogsModal >  .modal-dialog, #inventoryCostModal > .modal-dialog, #inventoryLoyaltyModal > .modal-dialog {width: 250px;}

#inventoryCostInput, #inventoryCogsInput {text-align: right !important;}

#inventoryLoyaltyInput {text-align: center !important;}

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

 .selected_date {
 	color:#008000 !important;
 	font-weight: bold !important;
 }

 #Datepickk .d-table {
     display: -webkit-flex !important;
     display: -ms-flexbox !important;
     display: flex !important;
 }
 .date_table > tbody > tr > th {
    font-size: 22px;
    color: white;
    background-color: rgba(255, 255,255, 0.5);
}
.btn.bg-primary.primary-button:hover {
    color:  white;
}
</style>

@include('industry.oil_gas.og_header')
@include('industry.oil_gas.og_buttons')

	<div class="d-flex" style="width: 100%">
		<div style="padding-left:0" class="align-self-center col-sm-5">
			<h2 style="">Fuel Price/Litre</h2>
		</div>
		<div class="col-sm-4 d-flex">
			<div class="col-sm-2 align-self-center">
			@if(!empty($oilgas->product_name->thumbnail_1))
				<img class="thumbnail align-self-center"
					style="width:50px;height:50px;object-fit:contain"
					src="{{'/images/product/'.$oilgas->product_name->id.'/thumb/'.$oilgas->product_name->thumbnail_1}}"/>
			@endif
			</div>
			<div class="col-sm-8 align-self-center"
				style="margin-bottom:5px">
				<ul style="padding-left:0;margin-bottom:0; list-style:none">
					<li><h4 style="margin-bottom:0">{{$oilgas->product_name->name}}</h4></li>
					<li>{{$oilgas->product_name->systemid}}</li>
				</ul>
			</div>
		</div>  
		<div style="padding-right:0" class="col-sm-3">
			<button class="btn btn-success sellerbutton"
				style="float: right; margin: 0px 0px 5px 0px;"
				id="addFuelPrice">
				+Price
			</button>
		</div>  

	</div>
	</div>

	<div class="col-sm-12" style="padding-left:20px;padding-right:20px">
		<table id="tableFuelPrice" class="table table-bordered">
			<thead class="thead-dark">
				<tr>
					<th style="width: 30px">No</th>
					<th style="width: 200px">Start</th>
					<th style="text-align:center; width: 200px">Price/&ell;</th>
					<th class="text-left">User</th>
					<th style="text-align:center; width: 200px">User Date</th>
					<th class="text-center"
					style="width:5px;text-align: center;background-image: unset !important "></th>
				</tr>
			</thead>
			<tbody id="shows">
			</tbody>
		</table>
	</div>

	<div class="modal" id="inventoryCogsModal">
		<div class="modal-dialog modal-dialog-centered">
		  <div class="modal-content">
			<!-- Modal body -->
			<div class="modal-body">
				<input id="inventoryCogsInput" type="text" class="pl-1"  style="width: 100%; border: 1px solid #ddd;">
				<input id="ogFuelPriceId" value="" type="hidden" class="pl-1">
				<input type="hidden" id="buffer_main_price" value="0.00">
			</div>
		  </div>
		</div>
	</div>

	<div class="modal fade" id="showDateModal" tabindex="-1" role="dialog"
         aria-labelledby="staffNameLabel" aria-hidden="true">
		 <div class="modal-dialog modal-dialog-centered  mw-75 w-50"
			 role="document">
			 <div class="modal-content modal-inside bg-greenlobster" >
				 <div class="modal-body text-center" style="min-height: 518px;">
					 
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

					<table class="table date_table picker">
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

	<input type="hidden" id='startDate'>

	<div id="productResponce"></div>
	<div id="showEditInventoryModal"></div>
	<div id="showEditInputInventoryModal"></div>
	@endsection

    @section('scripts')
	<script>
	loadFuelPrice();
	function dateToYMD(date) {
		var strArray=['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
		var d = date.getDate();
		var m = strArray[date.getMonth()];
		var y = date.getFullYear().toString().substr(-2);
		var currentHours = date.getHours();
		return '' + (d <= 9 ? '0' + d : d) + '' + m + '' + y + ' ' + ("0" + currentHours).slice(-2) +':'+ (date.getMinutes()<10?'0':'') + date.getMinutes() +':'+ (date.getSeconds()<10?'0':'') + date.getSeconds();
	}

	function dateToYMDEmpty(date) {
		var strArray=['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
		var d = date.getDate();
		var m = strArray[date.getMonth()];
		var y = date.getFullYear().toString().substr(-2);
		var currentHours = date.getHours();
		return '' + (d <= 9 ? '0' + d : d) + '' + m + '' + y + ' ' + "00:00:01";
	}

	function loadFuelPrice(){
	$.ajax({
		type: "POST",
		url: "{{route('industryoilgas.ajax.showFuelPrices')}}",
		'headers': {
			  'X-CSRF-TOKEN': '{{ csrf_token() }}'
			},
		data:"ogFuelId="+{{$id}},
		dataType: 'json',
		success: function (data) {
			//console.log(data);
			var fuelPrice_data = '';

			var counter = 1;
			data.forEach(function (value) {

				if(value.price != null){
					price = value.price;
				}else{
					price = '0.00';
				}
				
				start = dateToYMD(new Date(value.start));
				created_at = dateToYMD(new Date(value.created_at));
				
				if(counter == 1){
					var currentDate = new Date();
					var startTime = new Date(value.start);
					  
					if (value.start == null || value.price == null ||
						(currentDate.getTime() < startTime.getTime())) {
						
						$("#addFuelPrice").attr("disabled", true);
						$("#addFuelPrice").css({"cursor":"not-allowed", "background-color":"#808080","border-color":"#808080"});
						
					} else {
						$("#addFuelPrice").attr("disabled", false);
						$("#addFuelPrice").css({"cursor":"pointer", "background-color":"#ddd;","border-color":"#ddd;"});
					}
					
					if(value.start == null || value.price == null){
						
						if(value.start == null){
							emptystartFormate = dateToYMDEmpty(new Date(value.created_at));

						} else {
							emptystartFormate = start;
						}
						
						fuelPrice_data += '<tr>';
						fuelPrice_data += '<td>'+counter+'</td>';
						fuelPrice_data += '<td class="os-linkcolor" data-field="og_start" style="cursor: pointer; margin: 0; text-align: center;" onclick="show_dialog2('+value.id+')" data-date="'+emptystartFormate+'">'+emptystartFormate+'</td>';
						fuelPrice_data += '<td class="os-linkcolor" data-field="og_price" onclick="showogFuelPriceModel('+value.id+')"  data-toggle="modal"  style="cursor: pointer; margin: 0; text-align:center;"><span class="og_price">'+ (price/100).toFixed(2) +'</span></td>';
						fuelPrice_data += '<td class="buyOutput" data-field="og_user" style="margin: 0; text-align:left;">'+value.user_name.name+'</td>';
						fuelPrice_data += '<td class="getOutput" style="text-align: center;margin: 0;" data-field="og_created_at">'+created_at+'</td>';
						fuelPrice_data += '<td><div data-field="delete" \
							onclick="removeogFuelPriceModel('+value.id+')" class="remove"> \
							<img class="" src="/images/redcrab_50x50.png" \
							style="width:25px;height:25px;cursor:pointer"/> \
							</div></td>';

						fuelPrice_data += '</tr>';

					} else if (startTime.getTime() > currentDate.getTime()){
						
						fuelPrice_data += '<tr>';
						fuelPrice_data += '<td>'+counter+'</td>';
						fuelPrice_data += '<td class="os-linkcolor" data-field="og_start" style="cursor: pointer; margin: 0; text-align: center;" onclick="show_dialog2('+value.id+')">'+start+'</td>';
						fuelPrice_data += '<td class="os-linkcolor" data-field="og_price" onclick="showogFuelPriceModel('+value.id+')"  data-toggle="modal"  style="cursor: pointer; margin: 0; text-align:center;"><span class="og_price">'+ (price/100).toFixed(2) +'</span></td>';
						fuelPrice_data += '<td class="buyOutput" data-field="og_user" style="margin: 0; text-align:left;">'+value.user_name.name+'</td>';
						fuelPrice_data += '<td class="getOutput" style="text-align: center;margin: 0;" data-field="og_created_at">'+created_at+'</td>';

						/*
						fuelPrice_data += '<td><p data-field="delete" onclick="removeogFuelPriceModel('+value.id+')"  style="background-color:red;border-radius:5px;margin:auto;width:25px;height:25px;display:block;cursor: pointer;"class="text-danger remove"><i class="fas fa-times text-white"  style="color:white;opacity:1.0;padding-top:4px;-webkit-text-stroke: 1px red;"></i></p></td>';
						*/

						fuelPrice_data += '<td><div data-field="delete" \
							onclick="removeogFuelPriceModel('+value.id+')" class="remove"> \
							<img class="" src="/images/redcrab_50x50.png" \
							style="width:25px;height:25px;cursor:pointer"/> \
							</div></td>';

						fuelPrice_data += '</tr>';

					} else {
						
						fuelPrice_data += '<tr>';
						fuelPrice_data += '<td>'+counter+'</td>';
						fuelPrice_data += '<td class="" data-field="" style="margin: 0; text-align: center;">'+start+'</td>';
						fuelPrice_data += '<td class="" data-field="og_price" style="margin: 0; text-align:center;">'+ (price/100).toFixed(2) +'</td>';
						fuelPrice_data += '<td class="buyOutput" data-field="og_user" style="margin: 0; text-align:left;">'+value.user_name.name+'</td>';
						fuelPrice_data += '<td class="getOutput" style="text-align: center;margin: 0;" data-field="og_created_at">'+created_at+'</td>';
						/*
						fuelPrice_data += '<td><p style="background-color:#ddd;border-radius:5px;margin:auto;width:25px;height:25px;display:block;cursor:not-allowed;" class="text-secondary"><i class="fas fa-times text-white" style="color:white;opacity:1.0;padding-top:4px;-webkit-text-stroke: 1px #ccc;"></i></p></td>';
						*/

						fuelPrice_data += '<td><div><img src="/images/redcrab_50x50.png" \
							style="width:25px;height:25px;cursor:not-allowed; \
							filter:grayscale(100%) brightness(200%)"/> \
							</div></td>';

						fuelPrice_data += '</tr>';
					}
					
				}else{
					
					fuelPrice_data += '<tr>';
					fuelPrice_data += '<td>'+counter+'</td>';
					fuelPrice_data += '<td class="" data-field="" style=" margin: 0; text-align: center;">'+start+'</td>';
					fuelPrice_data += '<td class="" data-field="og_price" style="margin: 0; text-align:center;">'+ (price/100).toFixed(2); +'</td>';
					fuelPrice_data += '<td class="buyOutput" data-field="og_user" style="margin: 0; text-align:left;">'+value.user_name.name+'</td>';
					fuelPrice_data += '<td class="getOutput" style="text-align: center;margin: 0;" data-field="og_created_at">'+created_at+'</td>';
					fuelPrice_data += '<td><div><img src="/images/redcrab_50x50.png" \
						style="width:25px;height:25px;cursor:not-allowed; \
						filter:grayscale(100%) brightness(200%)"/> \
						</div></td>';

					fuelPrice_data += '</tr>';
				}
				counter++;
			});

			$('#tableFuelPrice').DataTable().clear().destroy();
			$("#shows").append(fuelPrice_data);
			FuelPricetable = $('#tableFuelPrice').DataTable({
				"autoWidth": false,
				"columnDefs": [
					{"width": "30px", "targets": 0},
					{"width": "5px", "targets": 5},
					{"className": "dt-center",
						"targets": [0, 1, 4, 5]},
				],
			});
			var product = FuelPricetable;

		},
		error: function () {
			console.log('fall');
			$('#tableFuelPrice').DataTable().clear().draw();
		}
	});
	}

	$('#addFuelPrice').on('click',function(){
		addFuelPrice();
	});
	
	var prd = false;


	function addFuelPrice() {
		if (prd == true) {return null};prd = true;
		$.ajax({
			url: "{{route('industryfuelprice.store')}}",
			type: "GET",
			enctype: 'multipart/form-data',
			processData: false,
			contentType: false,
			cache: false,
			data:"ogFuelPriceId=" + {{$id}},
			success:   function (response) {
				loadFuelPrice();
				$("#showEditInventoryModal").html(response);
				//$("#msgModal").modal('show');
				prd = false;

			}, error: function (e) {
				console.log(e.message);
			}
		});
	}
		
	$('table').on('click','tr span.og_price',function(e){
		e.preventDefault();
	 
		document.getElementById("inventoryCogsInput").value = atm_money(parseInt($(this).text() * 100));
		document.getElementById("buffer_main_price").value = (parseInt($(this).text() * 100));
		$('#inventoryCogsInput').val(atm_money(parseInt($(this).text() * 100)));
    });

	function atm_money(num) {
		if(num == 0){
			return '0.00';
		}
		if (num.toString().length == 1) {
			return '00.0' + num.toString()

		} else if (num.toString().length == 2) {
			return '00.' + num.toString()

		} else if (num.toString().length == 3) {
			return '0' + num.toString()[0] +'.'+ num.toString()[1] + 
				num.toString()[2];

		} else if (num.toString().length >= 4) {
			return num.toString().slice(0,(num.toString().length - 2)) +
				'.'+ num.toString()[(num.toString().length - 2)] + 
				num.toString()[(num.toString().length - 1)];
		}
	}

	
	$("#inventoryCogsInput").on( "keydown", function( event ) {
		event.preventDefault()

		if (event.keyCode == 8) {
			$("#buffer_main_price").val('')
			$("#inventoryCogsInput").val('')
			return null
		}
	  
		if (isNaN(event.key) || $.inArray( event.keyCode, [13,38,40,37,39] ) !== -1 || event.keyCode == 13  ) {
			if ($("#buffer_main_price").val() != '') {
			$("#inventoryCogsInput").val(atm_money(parseInt($("#buffer_main_price").val())))
			} else {
			  $("#inventoryCogsInput").val('')
			}
			return null;
		}

	   const input =  event.key;
	   old_val = $("#buffer_main_price").val()
		
	   if (old_val === '0.00') {
			$("#buffer_main_price").val('')
			$("#inventoryCogsInput").val('')
			old_val = ''
	   }

	   $("#buffer_main_price").val(''+old_val+input)
	   $("#inventoryCogsInput").val(atm_money(parseInt($("#buffer_main_price").val())))
	});
  
	$('#inventoryCogsModal').on('hidden.bs.modal', function (e) {
		updateFuelPrice();
	});
	
	function showogFuelPriceModel(ogFuelId){
		jQuery('#inventoryCogsModal').modal('show');
		$("#ogFuelPriceId").val(ogFuelId);
	}
	
	function updateFuelPrice(){
		$.ajax({
			url: "{{route('industryfuelprice.update.price')}}",
			type: 'POST',
			'headers': {
			  'X-CSRF-TOKEN': '{{ csrf_token() }}'
			},
			data: {
				"ogFuelPriceId":$("#ogFuelPriceId").val(),
				"ogFuelPrice":$("#inventoryCogsInput").val()
			},
			success: function (response) {
				loadFuelPrice();
				$("#productResponce").html(response);
				$('#modal').modal('show')
			},
			error: function (e) {
				console.log('error', e);
			}
		});
	}
	
    </script>

<script type="text/javascript">

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
		$('#startDate').attr('min',minDate);
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
// var contentYear = '2020 2021 2022 2023 2024 2025 2026 2027 2028 2029 2030 2031 2032 2033 2034 2035 2036 2037 2038 2039 2040 2041 2042 2043 2044 2045 2046 2047 2048 2049 2050 2051'.split(' ');
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


function navigationHandler(dir) {
	d.setUTCMonth(d.getUTCMonth() + dir);
	clearCalendar();
	myCalendar();
	shoot_event();
}


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
	
	if(currentMOnth ==  CURRENT_DATE.getMonth()  && year <=CURRENT_DATE.getFullYear()){
		$('.prev-month').addClass('disabled');
		$('.prev-month').css("cursor", "not-allowed");
	} else{
		$('.prev-month').removeClass('disabled');
		$('.prev-month').css("cursor", "default");
	}

    $('.date_table > tbody > tr > td').click(function(e) {
		console.log("Date clicked");
		var target = e.target;
		$('.date_table > tbody > tr > td').removeClass('selected_date');
		
		$(target).addClass('selected_date');

    	var act = {"day" : $(this).text(), "month" : $('#currMonth').val() , "year" : year};
		sessionStorage.setItem('activeDate', JSON.stringify(act));

		let day = $(target).html();
		let month  = $('.month-year > h3').html();
		$('#startDate').val(day+' '+month);
		jQuery('#showDateModal').modal('hide');
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

			$(this).closest('td').addClass('disabled');
			// $(this).closest('tr').css("pointer-events", "none");
			$(this).closest('td').css("cursor", "not-allowed");
			$(this).closest('td').unbind('click');
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
		$('#startDate').val('');
		return false;

	} else {
		$.ajax({
			url: "{{route('industryfuelprice.update.startDate')}}",
			type: 'post',
			data: {
				'startDate':  $('#startDate').val(),
				"ogFuelPriceId":$("#ogFuelPriceId").val(),
			},
			success: function (response) {
				loadFuelPrice();
				$("#productResponce").html(response);
				jQuery('#modal').modal('show')
			},
			error: function (e) {
				console.log('error', e);
			}
		});
	}
}


function removeogFuelPriceModel(id)
{
    $.ajax({
        url: "{{route('industryfuelprice.edit.modal')}}",
        type: 'post',
        'headers': {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        data: {
            'id': id,
        },
        success: function (response) {
            $("#showEditInventoryModal").html(response);
            $("#showMsgModal").modal('show');
        },
        error: function (e) {
            console.log('error', e);
        }
    });
}

 
</script>

@endsection
