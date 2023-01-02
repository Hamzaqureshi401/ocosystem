<div style='margin-top:30px' class='row'>

<div class="row" style="width: 100%;border-bottom:1px solid #e0e0e0;margin-left: 0px ">
    <div class="col-md-11 align-self-end" style="color:#27a98a;font-weight:bold;">
		<h4 style="margin-bottom: 5px;">Financial Year</h4>
    </div>
    <div class="col-md-1 col-auto align-self-center">
        <button class="btn btn-primary btn-lg" style="width:120px;float:right;margin-bottom:5px;" onclick="show_dialog2()" >Set</button>
    </div>
</div>
<div class="clearfix"></div>

    
@foreach ($FYData as $data)
    
<div class="col-md-12" style="padding-top: 5px;">
    <p style="font-size: 15px;">Financial Year from {{$data->start_financial_year->format('dMy')}} - {{(\Carbon\Carbon::create($data->start_financial_year->toDateTimeString())->add(1,'year')->add(-1,'day')->format('dMy'))}}</p>
</div>

@endforeach

<div class="clearfix"></div>

<div id='FinancialModalData'></div>
    <div class="modal fade" id="showDateModal" tabindex="-1" role="dialog"
        aria-labelledby="staffNameLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered  mw-75 w-50"
            role="document">
            <div class="modal-content modal-inside bg-greenlobster" >
                <div class="modal-body text-center" style="min-height: 518px;">
                    <h5 class="modal-title text-white"
						id="statusModalLabel">Financial Year</h5>
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

<style type="text/css">
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
	color:rgba(0, 0, 255, 0.5)!important;
	background: #fff;
	font-weight: 600 !important;
}

#Datepickk .d-table {
    display: -webkit-flex !important;
    display: -ms-flexbox !important;
    display: flex !important;
}
</style>

<input type="hidden" id='startDate'>
<input type="hidden" name="overide" id="overide" value="false"/>

<script>
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

function show_dialog2() {
	jQuery('#showDateModal').modal('show');
}

$('#showDateModal').on('hidden.bs.modal', function (e) {
	onDateSelect();
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
function clearCalendar() {
	var $trs = $('tr').not(':eq(0)');
	$trs.remove();
	$('.month-year').empty();
}

// Generates table row used when rendering Calendar
function getCalendarRow() {
	var $table = $('table');
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
		navigationHandler(-1);
	});
	$('.next-month').click(function() {
		navigationHandler(1);
	});
	// Generate Calendar
	myCalendar();
	shoot_event();
});


function shoot_event () {
    $('.date_table > tbody > tr > td').click(function(e) {
		console.log("Date clicked");
		var target = e.target;
		$('.date_table > tbody > tr > td').removeClass('selected_date');
		$(target).addClass('selected_date');
		let day = $(target).html();
		let month  = $('.month-year > h3').html();
		$('#startDate').val(day+' '+month);
		jQuery('#showDateModal').modal('hide');
	});
}


function overideFY() {
	$('#overide').val('true');
	onDateSelect();
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
	$('#date_from').html(selectedDate.getDate()+selectedDate.toLocaleString('en-us',
		{ month: 'short' })+selectedDate.getFullYear().toString().substr(2,2));

	//  $('#from_year').html(selectedDate.getFullYear()+ ' from');

	if (todaysDate.getFullYear() > selectedDate.getFullYear()) {
		alert('Error: You can only select from this year!');
		$('#startDate').val('');
		return false;

	} else {
		$.ajax({
			url: "{{route('FinancialYear.confirm.modal')}}",
			type: 'post',
			data: {
				'startingDate':  $('#startDate').val(),
				'confirmation': $('#confirmation').val(),
				'overide':$('#overide').val(),
			},
			success: function (response, textStatus, request) {
				const contentType = request.getResponseHeader('content-type');
				if (contentType && contentType.indexOf("application/json") !== -1) {
					//   $("#FinancialModalData").html(response);
					// jQuery("#showFinancialModal").modal('show');
					reset_dialog();
					// return false;
				}

				$("#FinancialModalData").html(response);
				jQuery("#showFinancialModal").modal('show');
			},
			error: function (e) {
				console.log('error', e);
			}
		});
	}
}
</script>
