@extends('layouts.layout')

@section('content')

<div id="landing-view">

<style type="text/css">
.block_1 {
	float: right;
	padding-left: 10px;
}

#decrease {
	border-radius: 15px;
}

#increase {
	border-radius: 15px;
}
    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { 
      -webkit-appearance: none; 
      margin: 0; 
    }
input#number {
	text-align: center;
	border: none;
	border-radius: 5px;
	background-color: #d4d3d36b !important;
	vertical-align: text-bottom;
}

.inside_qty {
	margin-top: -3px;
}

.minus_plus {
	cursor: pointer;
	font-size: 28px;
	font-weight: bold;
}

a:link {
	text-decoration: none !important;
}

#para_middle:first-letter {
	padding-left: 15%;
}
td > a.sellerbutton{
	float: none;
	margin-right: 0px;
}
</style>

<div class="modal fade" id="barcode_sku" tabindex="-1"
    role="dialog" aria-labelledby="productcontModallabel" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered" role="document" >
    <div class="modal-content">
        <form style="margin-bottom:0"
			class="m-form  m-form--state m-form--label-align-right " >
            <div class="modal-body">
                <div class="m-form__content">
                    <input type="hidden" id="is_main"/>
                <input type="hidden" id="modal-barcode_id" name="product_id"
					value="">
                    <input  type="text" name="sku" id="modal-sku"
						class="form-control m-input" placeholder="SKU">
                </div>
            </div>
        <!--end::Form-->
        </form>
    </div>
</div>
</div>

<div class="modal fade" id="barcode_name" tabindex="-1"
    role="dialog" aria-labelledby="productcontModallabel" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered" role="document" >
    <div class="modal-content">
        <form style="margin-bottom:0"
			class="m-form  m-form--state m-form--label-align-right " >
            <div class="modal-body">
                <div class="m-form__content">
                    <input type="hidden" id="is_main"/>
                <input type="hidden" id="modal-barcode_id" name="product_id"
					value="">
                    <input  type="text" name="name" id="modal-name"
						class="form-control m-input" placeholder="Barcode Name">
                </div>
            </div>
        <!--end::Form-->
        </form>
    </div>
</div>
</div>

<div class="row" style="padding-top:0;">
	<div class="col-md-1 align-self-center">

		@if(!empty($product->thumbnail_1))
			<div>
				<img src="/images/product/{{$product->id}}/thumb/{{$product->thumbnail_1}}"
					 style="object-fit:contain;width:60px;height: 60px;margin-right:0;"/>
			</div>
		@else
			<div>
				<img style="width:60px;height: 60px;margin-right:0; border: 1px solid #e0e0e0;border-radius: 5px">
			</div>
		@endif
	</div>
	<div class="col-md-7 align-self-center">
		<div class="row">
			<div style="margin-left: -25px">
				@if(!empty($product->name))
					<h4 style="margin-bottom:0px;margin-top:0;">{{$product->name}}</h4>
				@else
					<h4 style="margin-bottom:0px;margin-top:0;">Product Name</h4>
				@endif
				<p class="mb-0">{{$system_id}}</p>
			</div>
		</div>
	</div>
	<div class="col-md-4">
		<div style=" padding-left: 20px;">
			<div style="float: left;font-weight:bold;padding-left:60px;padding-top:10px;font-size:20px">
				<d3>Total Qty</d3>
				<br>
				<span class="ml-4">{{$product_qty}}</span>
			</div>
			<div style="float: right;">
                <a href="#" id="show_datt" data-toggle="modal" data-target="#showDateModal_1">
				<button class="btn btn-success sellerbutton"
						style="padding:0"
						style="padding-left:9px">
					<span>Expiry<br>Date</span>
				</button>
                </a>
				<a href="#" data-toggle="modal" data-target="#group_barcode_modal0">
					<button class="btn btn-success sellerbutton mr-0 mb-0"
							style="padding:0">
						<span>Group<br>Barcode</span>
					</button>
				</a>
				<a href="#" data-toggle="modal"
					data-target="#create_barcode_modal"
					class="btn btn-success sellerbutton mr-0 mb-0 ml-1"
						style="padding:0">
                    <button class="btn btn-success sellerbuttontwo mr-0 mb-0"
                            style="padding:0">
                        <span style="margin-left: -4px;">
						Serial<br>Barcode</span>
                    </button>
				</a>
			</div>
		</div>
	</div>
</div>
<input type="hidden" name="my_system" id="my_system" value="{{ $system_id }}">
<input type="hidden" name="my_product" id="my_product" value="{{ $product_id }}">
@if(!$barcodematrix)
	<table class="table table-bordered " id="tableinventorybarcode_default" style="width:100%;">
		<thead class="thead-dark">
		<tr>
			<th style="width:30px;text-align: center;">No.</th>
			<th style="width:100px;text-align: center;">Barcode</th>
			<th style="width:100px;text-align: center;">QR Code</th>
			<th style="width:50px;text-align: center;">Colour</th>
			<th style="text-align: center;">Matrix</th>
			<th style="text-align: center;">Notes</th>
			<th style="width:30px;text-align: center;">Qty</th>
			<th style="width:50px;text-align: center;"></th>
			<th style="width:10px;text-align: center;"></th>
		</tr>
		</thead>
		<tbody>
		</tbody>
	</table>
	<div style="min-height: 70px"></div>
@endif
@if($barcodematrix)
	<table class="table table-bordered align-content-center" id="tableinventorybarcode" style="width:100%;">
		<thead class="thead-dark">
		<tr>
			<th style="width:30px;text-align: center;">No</th>
			<th style="width:100px;text-align: center;">Barcode</th>
			<th style="width:100px;text-align: center;">QR Code</th>
			<th style="width:50px;text-align: center;">Colour</th>
			<th style="text-align: center;">Matrix</th>
			<th style="text-align: center;">Notes</th>
			<th style="width:30px;text-align: center;">Qty</th>
			<th style="width:50px;text-align: center;"></th>
			<th style="width:10px;text-align: center;"></th>
		</tr>
		</thead>
		<tbody>


		</tbody>
	</table>
@endif
<div id="productResponce"></div>
{{-- modal for Group Barcode--}}

    <div class="modal fade" id="create_barcode_modal" tabindex="-1" role="dialog"
         aria-labelledby="productcontModallabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:600px;">

            <div class="modal-content bg-greenlobster">
                <div class="modal-header clearfix">
                    <h3 class="modal-title pull-left">Serial Barcode</h3>
                </div>
                <div class="modal-body">
                    <div class="input-group">
                        <input type="number" id="barcode_from" class="form-control" name="barcode_from" placeholder=""/>
                        <span class="input-group-addon" style="padding: 10px;">TO</span>
                        <input type="number" class="form-control" id="barcode_to" name="barcode_to" placeholder=""/>
                    </div>
                    <div style="float: left; width: 80%;">                        <br>
                        <input type="text" class="form-control" id="barcode_notes" name="barcode_notes" placeholder="Notes"/>
                    </div>
                    <div class="clearfix mt-2" style="float: right;">
                        <button id="create_barcode_btn" class="btn sellerbutton btn-primary float-right">Submit</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

<div class="modal fade" id="group_barcode_modal0" tabindex="-1" role="dialog"
	 aria-labelledby="productcontModallabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document"
		style="max-width:600px;">

	<div class="modal-content bg-greenlobster">
		<div class="modal-header">
			<h3 class="modal-title">Group Barcode</h3>
		</div>
		<div class="modal-body">
		<div class="row">
			<div class="col-md-4" style="padding-right:0">
				<h5>Note:</h5>
				Enter or scan barcodes. Separate with senicolon(;) or Enter
				<div class="col-md-12 d-flex justify-content-center"
					style="align-items:end;padding-top:80px;
					padding-left:0;padding-right:0">
					<button class="btn btn-primary save-barcode sellerbutton"
					style="padding-left:9px">
					<span>Submit</span>
					</button>
				</div>
			</div>
			<div class="col-md-8" style="padding-top:10px">
			<textarea style="width:95%;" name="group_barcode"
				rows="10" cols="45"
				placeholder="&nbsp;&nbsp;&nbsp;Please Enter/Scan Barcode"></textarea>
			</div>
		</div>
		</div>
	</div>
	</div>
</div>

<div id="showBarcodeModal"></div>

<div class="modal fade" id="message_popup" tabindex="-1" role="dialog" aria-labelledby="message_popup"
     aria-hidden="true">
</div>

<div class="modal fade" id="showConfirm" tabindex="-1" role="dialog" aria-labelledby="showMsgModal" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered  mw-75 w-50" role="document">
		<div class="modal-content modal-inside bg-greenlobster">
			<div style="border-width:0" class="modal-header text-center"></div>
			<div class="modal-body text-center">
				<h5 class="modal-title text-white"
				id="statusModalLabel">Do you want to permanently delete this
				barcode?</h5>
			</div>
			<div class="modal-footer"
				style="border-top:0 none; padding-left: 0px; padding-right: 0px;">
				<div class="row"
					style="width: 100%; padding-left: 0px; padding-right: 0px;">
					<div class="col col-m-12 text-center">
						<input type="hidden" id="my_code"/>
						<button type="button"
						class="btn bg-primary primary-button"
						onclick="delete_code()"
						data-dismiss="modal" style="color: white">Yes</button>
						<button type="button"
						class="btn btn-danger primary-button"
						data-dismiss="modal">No</button>
					</div>
				</div>

				<form id="status-form" action="{{ route('logout') }}"
					method="POST" style="display: none;">
					@csrf
				</form>
			</div>
		</div>
	</div>
</div>

{{-- modal for barcode and qr--}}
<div class="modal fade" id="barcode" tabindex="-1"
	 role="dialog" aria-labelledby="productcontModallabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">

		<div class="modal-content">
			<form class="m-form  m-form--state m-form--label-align-right ">
				<div class="modal-body">
					<div class="m-form__content">
						<input type="text" name=""
							   class="form-control m-input"
							   placeholder="Dummy"/>
					</div>
				</div>
				<!--end::Form-->
			</form>
		</div>
	</div>
</div>

<div class="modal fade" id="qrcode" tabindex="-1"
	 role="dialog" aria-labelledby="productcontModallabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">

		<div class="modal-content">
			<form class="m-form  m-form--state m-form--label-align-right ">
				<div class="modal-body">
					<div class="m-form__content">
						<input type="text" name=""
							   class="form-control m-input"
							   placeholder="SKU">
					</div>
				</div>
				<!--end::Form-->
			</form>
		</div>
	</div>
</div>

<div class="modal fade" id="qrcode_name" tabindex="-1"
	 role="dialog" aria-labelledby="productcontModallabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">

		<div class="modal-content">
			<form class="m-form  m-form--state m-form--label-align-right ">
				<div class="modal-body">
					<div class="m-form__content">
						<input type="text" name=""
							   class="form-control m-input"
							   placeholder="Name">
					</div>
				</div>
				<!--end::Form-->
			</form>
		</div>
	</div>
</div>

@section('scripts')
@include('settings.buttonpermission')

<script type="text/javascript">
	$(document).ready(function () {

		if (('{{$barcodematrix}}')) {
			console.log('here');
			var system_id = '{{$system_id}}';
			var tableinventorybarcode = $('#tableinventorybarcode').DataTable({
				"processing": false,
				"serverSide": true,

				"ajax": {
					"url": "{{route('inventory.ajax.barcode')}}",
					"type": "POST",
					'data': {
						'system_id': system_id
					},
					'headers': {
						'X-CSRF-TOKEN': '{{ csrf_token() }}'
					},
				},
				columns: [
					{data: 'id', name: 'id'},
					{
						className: 'dt-body-center',
						data: 'bar', name: 'barcode'
					},
					{
						"className": '',
						"orderable": false,
						"data": null,
						"defaultContent": "<img src='{{asset('images/qrcode.jpg')}}' width='100px' height='100px' alt=''>"
					},
					{	data: 'color', name: 'colour'},
					{
						className: 'dt-body-center',
						data: 'size', name: 'size'
					},
					{
						className: 'dt-body-center',
						data: 'created_at', name: 'note'
					},
					{
						className: 'dt-body-center',
						data: 'id', name: 'qty'
					},
					{
						"className": '',
						"orderable": false,
						"data": null,
						"defaultContent": "<a href=\"#\" class=\"btn btn-success btn-log bg-web sellerbutton\"\n" +
							"style=\"color:red;\">XPrint</a>"
					},
				],

				"aoColumnDefs": [
					{
						"aTargets": [3],
						"fnCreatedCell": function (nTd, sData, oData, iRow, iCol, mData) {
							if (sData != 'NULL') {
								$(nTd).css('border', '1px solid #ccc')
								$(nTd).css('border-radius', '5px')
								$(nTd).css('padding', '1px 2px 1px 5px')
								$(nTd).css('background-color', $(nTd).html())
								$(nTd).css('margin-left', '-5px')
								$(nTd).css('margin-top', '2px')
								$(nTd).css('margin-bottom', '-2px')
								$(nTd).css('color', $(nTd).html())
							}
						},
					},
				],
				"order": [],
				"columnDefs": [
					{"className": "dt-center", "targets": [0, 1, 3, 4, 5]},
				],
			});

			tableinventorybarcode.on('order.dt search.dt', function () {
				tableinventorybarcode.column(0, {
					search: 'applied',
					order: 'applied'
				}).nodes().each(function (cell, i) {
					cell.innerHTML = i + 1;
				});
			}).draw();


		} else {
			console.log('no matrix');
			// var tableinventorybarcode = $('#tableinventorybarcode_default').DataTable({
			//     "order": [],
			//     "columnDefs": [
			//         {"bSortable": false, "aTargets": [7, 8]},
			//         {"targets": -1, 'orderable': true}
			//     ],
			//     "autoWidth": true,
			// });
		}


		$('table').on('click', 'tr p.remove', function (e) {
			e.preventDefault();
			$(this).closest('tr').remove();
		});
	});


	$('#modal-sku').change(function() {
      var sku = $.trim($('#modal-sku').val());
      var barcode_id = $('#modal-barcode_id').val();
      var is_main = $('#is_main').val();
      if(barcode_id!=""){

        $.ajax({
          url: "{{route('inventory.update_barcode_sku')}}",
          type: "POST",
          data: {
              sku: sku,
              barcode_id: barcode_id,
              is_main : is_main
          },
          cache: false,
          success: function(dataResult){
            var sku_html = '<a href="#" data-barcode_id="'+barcode_id+'" data-sku="'+sku+'">'+sku+'</a>';
              $("#barcode_sku").modal('hide');
              $("#productResponce").html(dataResult);
              $("#barcodesku_"+barcode_id).html(sku_html);
              $('#tableinventorybarcode_default').DataTable().ajax.reload(null, true);
          }
        });
      }
    });


	$('#modal-name').change(function() {
      var name = $.trim($('#modal-name').val());
      var barcode_id = $('#modal-barcode_id').val();
      var is_main = $('#is_main').val();
      if(barcode_id!=""){

        $.ajax({
          url: "{{route('inventory.update_barcode_name')}}",
          type: "POST",
          data: {
              name: name,
              barcode_id: barcode_id,
              is_main : is_main
          },
          cache: false,
          success: function(dataResult){
            var name_html = '<a href="#" data-barcode_id="'+barcode_id+'" data-name="'+name+'">'+name+'</a>';
              $("#barcode_name").modal('hide');
              $("#productResponce").html(dataResult);
              $("#barcodename_"+barcode_id).html(name_html);
              $('#tableinventorybarcode_default').DataTable().ajax.reload(null, true);
          }
        });
      }
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

	function show_dialog2() {
		jQuery('#showDateModal_1').modal('show');
	}

	$('#showDateModal_1').on('hidden.bs.modal', function (e) {
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
	function renderCalendar(startDay, totalDays, currentDate,month) {
		var currentRow = 1;
		var currentDay = startDay;
		var $table = $('table');
		var $week = getCalendarRow();
		var $day;
		var i = 1;

		var currentMonth = CURRENT_DATE.getUTCMonth();
		var currentYear = CURRENT_DATE.getUTCFullYear();

		for (; i <= totalDays; i++) {
			$day = $week.find('td').eq(currentDay);
			$day.text(i);
			if (i === currentDate) {
				$day.addClass('today');
			}
			console.log(currentMonth + " = " + month);
			var year = d.getUTCFullYear();
			if((month < currentMonth && year <= currentYear) || year < currentYear){
				$day.css('cursor','not-allowed');
				// $day.css('color','gray');
				$day.addClass('disabled');
			}
			if(month == currentMonth && i < currentDate){
				$day.css('cursor','not-allowed');
				// $day.css('color','gray');
				$day.addClass('disabled');
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
		renderCalendar(getCalendarStart(day, date), totalDaysOfMonth, dateToHighlight,month);
	};


	function navigationHandler(dir) {
		d.setUTCMonth(d.getUTCMonth() + dir);
		clearCalendar();
		$('#tableinventorybarcode_default').DataTable().ajax.reload(null, true);
		myCalendar();
		shoot_event();
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
	});
	
	$("#show_datt").on("contextmenu", function(e) {
		e.preventDefault();
		var targetModal = $(this).data('target');
		$(targetModal).modal("show");
	})

	function shoot_event() {
		$('.date_table > tbody > tr > td').click(function (e) {
			var target = e.target;
			if($(target).hasClass('disabled'))
				return false;
			save_expiry(d.getUTCMonth() , $(target).html() , d.getUTCFullYear());
			console.log("Date clicked");
			$('.date_table > tbody > tr > td').removeClass('selected_date');
			$(target).addClass('selected_date');
			let day = $(target).html();
			let month = $('.month-year > h3').html();
			$('#startDate').val(day + ' ' + month);

		});
	}

	function save_expiry(month , day , year){
		$("#msgModal").remove();
		var system_id = $('#my_system').val();
		var product_id = $('#my_product').val();
		$.ajax({
			url: "{{route('save.expiry')}}",
				data: {
				'system_id' : system_id,
				'product_id' : product_id,
				'month' : month ,
				'day' : day ,
				'year' : year
			},
			type: 'POST',
			dataType: "json",
			success: function (response) {
				jQuery('#showDateModal_1').modal('hide');
				$("#showBarcodeModal").html(response);
				 $('#tableinventorybarcode_default').DataTable().ajax.reload(null, true);
			},
			error: function (e) {
				jQuery('#showDateModal_1').modal('hide');
				$("#showBarcodeModal").html(e.responseText);
				$('#tableinventorybarcode_default').DataTable().ajax.reload(null, true);
			}
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
		$('#date_from').val(selectedDate.getDate() + selectedDate.toLocaleString('en-us',
			{month: 'short'}) + selectedDate.getFullYear().toString().substr(2, 2));

		//  $('#from_year').html(selectedDate.getFullYear()+ ' from');

		if (todaysDate.getFullYear() > selectedDate.getFullYear()) {
			alert('Error: You can only select from this year!');
			$('#startDate').val('');
			return false;

		} else {

/*$.ajax({
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

			reset_dialog();

		}

		$("#FinancialModalData").html(response);
		jQuery("#showFinancialModal").modal('show');
	},
	error: function (e) {
		console.log('error', e);
	}
});*/
                    }
                }
 // data attributes to scan when populating modal values
    var ATTRIBUTES = ['product_id', 'sku'];

    $('[data-toggle="modal"]').on('click', function (e) {
      var $target = $(e.target);
      var modalSelector = $target.data('target');

      ATTRIBUTES.forEach(function (attributeName) {
        var $modalAttribute = $(modalSelector + ' #modal-' + attributeName);
        var dataValue = $target.data(attributeName);
        $modalAttribute.val(dataValue || '');
      });
    });

</script>
@endsection


<div class="clearfix"></div>
	<div id='FinancialModalData'></div>
        <div class="modal fade" id="showDateModal_1" tabindex="-1" role="dialog"
             aria-labelledby="staffNameLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered  mw-75 w-50"
                 role="document">
                <div class="modal-content modal-inside bg-greenlobster">
                    <div class="modal-body text-center" style="min-height: 450px;">
                        <h5 class="modal-title text-white"
                            id="statusModalLabel">Expiry Date</h5>
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

                        <table class="table date_table">
                            <tr>
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
    </div>

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

        .selected_date {
            color: rgba(0, 0, 255, 0.5) !important;
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
    </div>
@endsection


<script type="text/javascript">

window.onload = function(){

$('#tableinventorybarcode_default').DataTable({
        "processing": true,
        "serverSide": true,
        autoFill: false,
        "ajax": {
            "url": "{{route('barcode.table.show')}}",
            "dataType": "json",
            "type": "POST",
            "data": {
                'id' : $('#my_product').val()
            },
            initComplete: function() {
				$(this.api().table().container()).
					find('input[type="search"]').
					parent().wrap('<form>').
					parent().attr('autocomplete','off').
					css('overflow','hidden').
					css('margin','auto');
            }
        },
        "columns": [{
			"data": "no" ,"orderable": false ,
			createdCell: function (td, cellData, rowData, row, col) {
			$(td).css('text-align', 'center');
			$(td).css('padding-top', '45px');
        }},
		{
			"data": "barcode" ,"orderable": false,
			createdCell: function (td, cellData, rowData, row, col) {
			$(td).css('text-align', 'center');
			$(td).css('padding-top', '5px');
        }},
		{
			"data": "qr_code","orderable": false,
			createdCell: function (td, cellData, rowData, row, col) {
			$(td).css('text-align', 'center');
			$(td).css('padding-top', '10px');
        }},
		{
			"data": "color" ,"orderable": false ,
			createdCell: function (td, cellData, rowData, row, col) {
			if(row == 0) {
				$(td).css('background-color', 'black');
				$(td).css('padding-top', '40px');
		}
        }},
		{
			"data": "matrix" ,"orderable": false,
			createdCell: function (td, cellData, rowData, row, col) {
				$(td).css('text-align', 'left');
				$(td).css('vertical-align', 'middle');
        }},
		{
			"data": "notes" ,"orderable": false,
			createdCell: function (td, cellData, rowData, row, col) {
				$(td).css('text-align', 'left');
				$(td).css('vertical-align', 'middle');
        }},
		{
			"data": "qty" ,"orderable": false ,
			createdCell: function (td, cellData, rowData, row, col) {
				$(td).css('text-align', 'center');
				$(td).css('vertical-align', 'middle');
        }},
		{
			"data": "options" ,"orderable": false,
			createdCell: function (td, cellData, rowData, row, col) {
				$(td).css('text-align', 'center');
				$(td).css('vertical-align', 'middle');
				$(td).css('margin-bottom', '0');
				//$(td).css('text-align', 'center');
				//$(td).css('padding-top', '20px');
        }},
            { "data": "actions" ,"orderable": false}

        ],
			'columnDefs': [{
			'targets': [0,1,2,3,4,5,6,7,8], // column index (start from 0)
			'orderable': false, // set orderable false for selected columns
		}]
    });


    $('.save-barcode').click(function(){
        $("#msgModal").remove();
        var barcodes = $('textarea[name=group_barcode]').val();
        $.ajax({
            url: "{{route('barcode.save')}}",
            data: {
                   'barcodes' : barcodes,
                   'id' : $('#my_product').val()
                  },
            type: 'POST',
            dataType: "json",
            success: function (response) {
                if(response != 0){
                    $("#showBarcodeModal").html(response);
                }
                $('#tableinventorybarcode_default').DataTable().ajax.reload(null, true);
                $('textarea[name=group_barcode]').val("");
                $('#group_barcode_modal0').modal('hide');
            },
            error: function (e) {
                if(e.responseText != 0){
                    $("#showBarcodeModal").html(e.responseText);
                }
                $('#tableinventorybarcode_default').DataTable().ajax.reload(null, true);
                $('textarea[name=group_barcode]').val("");
                 $('#group_barcode_modal0').modal('hide');
            }
        });
    });


    $('#create_barcode_btn').click(function(){

        var barcode_from = $('input[name=barcode_from]').val();
        var barcode_to = $('input[name=barcode_to]').val();
        var product_id = $('input[name=my_product]').val();
        var barcode_notes = $('input[name=barcode_notes]').val();

        $.ajax({
            url: "{{route('barcode.create.from_input_range')}}",
            data: {
                barcode_from : barcode_from,
                barcode_to : barcode_to,
                product_id : product_id,
                barcode_notes : barcode_notes,
            },
            type: 'POST',
            dataType: "json",
            success: function (response) {
                if (!$.isEmptyObject(response.status) && response.status === 'success') {
                    $('#tableinventorybarcode_default').DataTable().ajax.reload(null, true);
                    $('input[name=barcode_from]').val("");
                    $('input[name=barcode_to]').val("");

                    showMessagePopup(response.message,'');
                }
                if (!$.isEmptyObject(response.status) && response.status === 'error') {
                    showMessagePopup('Found Error. '+response.message, '');
                }
                $('#barcode_notes').val('');
                $('#create_barcode_modal').modal('hide');
            },
            error: function (response) {
                if (response.status === 422)  {
                    var filterResponse = $.parseJSON(response.responseText);
                    var errors = createMarkupListFromArray(filterResponse.errors);
                    $('#create_barcode_modal').modal('hide');
                    showMessagePopup('Validation Error', errors);
                }
            }
        });
    });

    function createMarkupListFromArray(errors) {
        var errorList = '<ul class="text-left">';
        $.each(errors, function(key, value){
            errorList += '<li>'+value+'</li>';
        });
        errorList += '</ul>';
        return errorList;
    }

    function showMessagePopup(title, messages) {
        var modalContent = '<div class="modal-dialog modal-dialog-centered  mw-75 w-50" role="document">\n' +
            '\n' +
            '        <div class="modal-content modal-inside bg-greenlobster">\n' +
            '    <div class="modal-body text-center" style="padding: 30px;">\n' +
            '        <h5 class="modal-title text-white mt-3 mb-3">' + title + '</h5>\n' +
            '\n' +
            '        <div class="message-section-footer mt-3 mb-3 text-white" style="font-size: 15px;">' + messages +
            '\n' +
            '        </div>\n' +
            '        </div>\n' +
            '        </div>\n' +
            '        </div>';

        $('#message_popup').html(modalContent);
        $("#message_popup").modal("show");
    }

    $(document).on("click", "a.sku" , function() {
		$("#modal-barcode_id").val($(this).attr('data-barcode_id'));
		if($(this).html() != 'SKU')
			$("#modal-sku").val($(this).html());
		else
			$("#modal-sku").val('');
		$("#is_main").val($(this).attr('data-is_main'));
		$("#barcode_sku").modal("show");
	});

    $(document).on("click", "a.name" , function() {
		$("#modal-barcode_id").val($(this).attr('data-barcode_id'));
		if($(this).html() != 'Barcode Name')
			$("#modal-name").val($(this).html());
		else
			$("#modal-name").val('');
		$("#is_main").val($(this).attr('data-is_main'));
		$("#barcode_name").modal("show");
	});

    $(document).on("click", "p.remove-barcode" , function() {
		var barcode_id = $(this).prev().val();
        $("#my_code").val(barcode_id);
        $("#showConfirm").modal("show");
        });

};



function delete_code(){
    $("#msgModal").remove();
    var barcode_id = $("#my_code").val();
    $.ajax({
		url: "{{route('barcode.delete')}}",
		data: {
			   'barcode_id' : barcode_id
			  },
		type: 'POST',
		dataType: "json",
		success: function (response) {
            $('#tableinventorybarcode_default').DataTable().ajax.reload(null, true);
            $(".modal-backdrop").remove();
            setTimeout(function(){
                $("#showBarcodeModal").html(response);
            }, 1000);



		},
		error: function (e) {
            setTimeout(function(){
                $("#showBarcodeModal").html(e.responseText);
            }, 1000);
			$('#tableinventorybarcode_default').DataTable().ajax.reload(null, true);
		}
	});
}
</script>
