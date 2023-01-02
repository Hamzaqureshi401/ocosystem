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
<div class="row py-2" style="padding-bottom:0px !important;margin-bottom:10px !important;margin-top:10px !important">
    <div class="col align-self-end" style="width:80%">
        <h2>Product Sales</h2>
        <a href="javascript:void(0)"
           class="btn selected-button fillter_btn"
           onclick="tr_type('all',this)"
           style="width:80px;margin-top:0px;padding-top:6px !important"
           id="allbutton" name="all" rel-type="all">All
        </a>
        <a href="javascript:void(0)"
           class="btn un-selected-button fillter_btn"
           onclick="tr_type('cash',this)"
           style="width:80px;margin-top:0px;padding-top:6px !important"
           id="cash-btn-filter" name="" rel-type="all">Cash
        </a>
        <button href="javascript:void(0)"
                class="btn un-selected-button fillter_btn"
                onclick="tr_type('credit',this)"
                style="width:80px;margin-top:0px;padding-top:6px !important;  color: white; cursor: pointer"
                id="creditbutton" name="" rel-type="credit">Credit
        </button>

	<!--
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
	-->

    <div style="right:190px;display:inline;padding-left:0; margin-bottom:20px">
        <input class="form_date form-control btnremove"
        style="display:inline;margin-top:10px;
		position:relative;top:2px;
        padding-top:0px !important;padding-left:0px;
        padding-right:0;padding-bottom: 0px; width:110px;text-align: center;"
		onclick="show_dialog4()"
		value="{{date('dMy')}}"
        id="date_from" name="froms" placeholder="Select"/>
    </div>
    To
    <div style="right:200px;display:inline;padding-left:0;margin-bottom:20px">
        <input class="to_date form-control btnremove"
        style="display:inline;margin-top:10px;padding-top:0px !important;
		position:relative;top:2px;
		padding-bottom: 0px; width:110px;padding-right:0;padding-left:0px;
		text-align: center;" 
		 value="{{date('dMy')}}"
		onclick="show_dialog5()"
        id="date_to" name="all" placeholder="Select" disabled="disabled"/>
    </div>

    <div id="branch_name" style="float:right;width:150px;margin-right:20px">
        <h5 class="os-linkcolor text-center"
		data-toggle="modal" data-target="#myAllLocations"
        style="cursor:pointer;margin-bottom:0;padding-top:0"
        id="location_modal">All<br>Location</h5>
    </div>
    <div id="segment_name" style="float:right;width:150px;margin-right:20px">
        <h5 class="os-linkcolor text-center"
		data-toggle="modal" data-target="#segmentModal"
        style="cursor:pointer;margin-bottom:0;padding-top:0"
        id="segment_modal" value="all" >All<br>Segment</h5>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="segmentModal" role="dialog" style="">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
      <!-- Modal content-->
      <div class="modal-content bg-greenlobster" style="padding: 9;">
        <div style="padding-top:10px;padding-bottom:10px"
             class="modal-header">
            <h3 style="margin-bottom:4px">Segment</h3>
        </div>
        <div class="modal-body segment_link">
            <h5 style="cursor:pointer" onclick="segment('all',this)" id="alls"
				class="active"
				name="All Segment" data-dismiss="modal">All Segment</h5>
            <h5 style="cursor:pointer" onclick="segment('direct',this)" id="direct"
				name="Direct Segment" data-dismiss="modal">Direct Segment</h5>
            <h5 style="cursor:pointer" onclick="segment('franchise',this)"
				id="franchise" name="Franchise Segment" data-dismiss="modal">
				Franchise Segment</h5>
            <h5 style="cursor:pointer" onclick="segment('food',this)"
				id="franchise" name="FoodCourt Segment" data-dismiss="modal">
				Food Court Segment</h5>
        </div>
    </div>
	</div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="myAllLocations" role="dialog" style="">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <!-- Modal content-->
            <div class="modal-content bg-greenlobster" style="padding: 9;">
                <div style="padding-top:10px;padding-bottom:10px"
                class="modal-header">
                <h3 style="margin-bottom:4px">Location</h3>
            </div>
            <div class="modal-body location_link">
		<h5 style="cursor:pointer" class="active"
			 onclick="display(this.id,this)" id="all"
					data-dismiss="modal">All Location</h5>

		@foreach ($branch_location as $key => $value)
                <h5 style="cursor: pointer;text-transform: capitalize"
					id="{{$value->id}}"
                    onclick="display(this.id,this)" name="{{$value->branch}}"
					date="{{$value->created_at}}"
		    class="location select_date_range loc_list_item 
				{{empty($value->direct) ? '':'direct_loc'}}
				{{$value->foodcourt == 1 ? 'foodcourt_loc':''}}
				{{empty($value->franchise)? '':'franchise_loc'}}" 
			data-dismiss="modal">
					{{$value->branch}}</h5>
         @endforeach
                </div>
            </div>
        </div>
    </div>
    
</div>
<div class="col col-auto align-self-center">
{{--
    <a href="{{ route('showCashProductSalesViewDownloadPDF') }}" class="getDownload" target="_blank">
        <button class="btn btn-success bg-download sellerbutton"
        style="padding-left:0;padding-right:0;padding-top:7px;float:right;margin-bottom: -35px; margin-right:0px"
        data-toggle="modal"
        data-target="#stockinmodal">
        <span>PDF</span>
    </button>
</a>
--}}
<a href="{{ url('show-cash-productsalesqty-view')}}" target="_blank">
    <button class="btn btn-success bg-product sellerbutton mr-0"
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
<thead style="background-color: #007bff;color: #fff">
    <tr>
        <th class="text-left" scope="col" style="border-bottom-width:0">MYR</th>
        <!--th class="text-left" scope="col" style="border-bottom-width:0">Amount</th-->
    </tr>
</thead>
    <tbody id="shows">
    </tbody>
</table>

<script src="{{asset('/js/osmanli_calendar.js')}}?version={{date("hmis")}}"></script>
<input type="hidden" id='startDate'>
<input type="hidden" id='createdDate'>
<input type="hidden" id='startDate1'>
<input type="hidden" name="overide" id="overide" value="false"/>
<input type="hidden" name="_token" value="<?php echo csrf_token(); ?>"/>
<script type="text/javascript">

var start_date_dialog = osmanli_calendar;
var completion_date_dialog = osmanli_calendar;

osmanli_calendar.MAX_DATE = new Date();
osmanli_calendar.DAYS_DISABLE_MAX = "on";

function show_dialog4(e) {
  from_input = $('#date_from').val();
  jQuery('#showDateModalFrom').modal('show');
  if (from_input != ''){
	start_date_dialog.CURRENT_DATE = new Date(from_input)
    start_date_dialog.SELECT_DATE = new Date(from_input)
  } 
    
  start_date_dialog.MIN_DATE =  new Date( '{{$userApprovedDate}}');
  start_date_dialog.DAYS_DISABLE_MIN = 'On'

  $('.next-month').off()
  $('.prev-month').off()

  $('.prev-month').click(function () {start_date_dialog.pre_month()});
  $('.next-month').click(function () {start_date_dialog.next_month()});
  
  start_date_dialog.ON_SELECT_FUNC = onDateSelect_from
  start_date_dialog.init()
}

function show_dialog5(e) {
        
  jQuery('#showDateModalFrom').modal('show');
  date = $('#date_from').val();
  to_input  = $('#date_to').val();

  if (to_input != '') {
    completion_date_dialog.CURRENT_DATE = new Date(to_input)
    completion_date_dialog.SELECT_DATE =  new Date(to_input)
  }

  completion_date_dialog.DAYS_DISABLE_MIN = 'On'
  completion_date_dialog.MIN_DATE =  new Date(date)
  
  $('.next-month').off()
  $('.prev-month').off()
  $('.prev-month').click(function () {completion_date_dialog.pre_month()});
  $('.next-month').click(function () {completion_date_dialog.next_month()});

  completion_date_dialog.ON_SELECT_FUNC = onDateSelect_to
  completion_date_dialog.init()

}

function onDateSelect_to(selectedDate) {
  
  if (selectedDate == null) {
    return false;
  }

  const todaysDate = new Date();
  var selectedFinalDate = (selectedDate.getDate() < 10 ? '0' : '') + selectedDate.getDate();
  var selectedFullYear = selectedDate.getFullYear().toString();
  selectedFullYear = selectedFullYear.match(/\d{2}$/);
  $('#date_to').val(selectedFinalDate + selectedDate.toLocaleString('en-us',
  {month: 'short'}) + selectedFullYear);
  jQuery('#showDateModalFrom').modal('hide');
  date_filter();
}
      
function onDateSelect_from(selectedDate) {
  if (selectedDate == null) {
    return false;
  }
  const todaysDate = new Date();
  var selectedFinalDate = (selectedDate.getDate() < 10 ? '0' : '') + selectedDate.getDate();
  var selectedFullYear = selectedDate.getFullYear().toString();
  selectedFullYear = selectedFullYear.match(/\d{2}$/);
  $('#date_from').val(selectedFinalDate + selectedDate.toLocaleString('en-us',
  {month: 'short'}) + selectedFullYear);
  
  if ($("#startDate").val() != "") {
    $("#date_to").removeAttr("disabled");
  }

  $('#date_to').val('');
  jQuery('#showDateModalFrom').modal('hide');
  //date_filter();
}


//#########################################################
var button_filter = 'all';
function tr_type(filter,target) {
	$('.fillter_btn').addClass('un-selected-button');
	$('.fillter_btn').removeClass('selected-button');
	$(target).removeClass("un-selected-button");
	$(target).addClass("selected-button");
	button_filter = filter;
	date_filter();	
}
function date_filter() {

	var	formData = {};

	if ($("#date_from").val().trim() != '') {
		var from = $("#date_from").val().trim() + " " + "00:00:00";	
		formData['from_date_all'] = from;
	}

	if ($("#date_to").val().trim() != '') {
		var to = $("#date_to").val().trim() + " " + "23:59:59";
		formData['to_date_all'] = to;
	}

	segment_ = $("#segment_modal").val();

	if (segment != 'all') {
		formData['segment'] = segment_;
	}
 

	var loc_name = $(".form_date").attr("name");
	if (loc_name != "froms" && loc_name != 'all' && segment_ == 'all'){
      	l_id = $(".form_date").attr("name");
		formData['loc_id'] = l_id;
	}
	
	formData['button_filter'] = button_filter;
	
	send_ajax(formData);
}

function display(l_id,target) {	
	
	if ($(target).hasClass('disabled')) {
		return null;
	}

	var loc_name = $("#" + l_id).attr("name");
	var loc_date = $("#" + l_id).attr("date");
	
	$(".location_link > h5").removeClass("active");
	$(target).addClass("active");

	if (typeof loc_name == "undefined"){
		$("#location_modal").html("All <br> Location").css('padding-top','0px');
	} else {			
		$("#location_modal").html(loc_name).css('padding-top','10px');
	}

	var	formData = {};
	var from = $("#date_from").val().trim() + " " + "00:00:00";
	var to = $("#date_to").val().trim() + " " + "23:59:59";
			
	if ($("#date_to").val().trim() != '' && $("#date_from").val().trim() != '' ) {
		formData['from_date_all'] = from;
		formData['to_date_all'] = to;
	}

	segment_ = $("#segment_modal").val();

	if (segment != 'all') {
		formData['segment'] = segment_;
	}

/*
	$("#segment_modal").html('All<br/>Segment');
	$("#segment_modal").val('all');
	
	$(".segment_link > h5").removeClass("active");
	$("#alls").addClass('active');
 */

	formData['loc_id'] = l_id;
	

      	$(".form_date").attr("name", l_id);
     	$(".to_date").attr("name", l_id);

	send_ajax(formData);
}

function segment(type, target) {

	disable_loc = false;
	
	$(".loc_list_item").attr('disabled');
	$(".loc_list_item").addClass('disabled');

	if (type == 'direct') {
		$("#segment_modal").html('Direct<br/>Segment');
		
		$(".direct_loc").removeAttr('disabled');
		$(".direct_loc").removeClass('disabled');

	} else if (type == 'franchise') {
		$("#segment_modal").html('Franchise<br/>Segment');
		$(".franchise_loc").removeAttr('disabled');
		$(".franchise_loc").removeClass('disabled');
	} else if (type == 'food') {	
		$("#segment_modal").html('Food Court<br/>Segment');
		$(".foodcourt_loc").removeAttr('disabled');
		$(".foodcourt_loc").removeClass('disabled');
	} else {
		$("#segment_modal").html('All<br/>Segment');
		$(".loc_list_item").removeAttr('disabled');
		$(".loc_list_item").removeClass('disabled');
	}

	$(".segment_link > h5").removeClass("active");
	$(target).addClass("active");


	$("#segment_modal").val(type);
			
	var	formData = {};

	var from = $("#date_from").val().trim() + " " + "00:00:00";
	var to = $("#date_to").val().trim() + " " + "23:59:59";
			
	if ($("#date_to").val().trim() != '' && $("#date_from").val().trim() != '' ) {
		formData['from_date_all'] = from;
		formData['to_date_all'] = to;
	}

	var id = $("#date_to").attr("name",'all');
	formData['segment'] = type;

	$("#location_modal").html("All <br> Location").css('padding-top','0px');
    	$(".form_date").attr("name", 'all');
	$(".to_date").attr("name", 'all');
	$(".location_link > h5").removeClass("active");
	$('#all').addClass("active");


	send_ajax(formData);
}

function send_ajax(formData) {
	$("#shows").empty();
        $.ajax({

            type: "GET",
            url: "{{route('analytics.ajax.cash.productsales')}}",
            data: formData,
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

                    var max_percentage = (max_value / total) * 100;
                    var url = '/images/product/' + value.id + '/thumb/' + value.thumbnail_1;
                    branch_data += '<tr><td>';
	
					if (value.promo_id == undefined) {
						branch_data += '<img src={{URL::to('/')}}/images/product/';
						branch_data += value.id + '/thumb/' + value.thumbnail_1;
					} else {
					
						branch_data += '<img src={{URL::to('/')}}/images/opos_promo/';
						branch_data += value.id + '/thumb/thumb_' + value.thumbnail_1;
					}
	
					branch_data += '  alt="" width="50px" height="50px" style="object-fit:contain" /> ';
                    if (value.T_amount == max_value) {
                        branch_data += '<img class="greenshade" style="width:85%;"/>';
                    } else {
                        var each_percentage = (value.T_amount / total) * 100;
                        var new_percentage = (85 / max_percentage) * each_percentage;
                        branch_data += '<img class="greenshade" style="width:' + new_percentage + '%;"/>';
                    }

					branch_data += '<span style="color:red;display:inline"><b> MYR ' 
					branch_data += formatNumber(value.T_amount / 100) 
					branch_data += '</b></span><br><span style="padding-left:55px;">' 
					branch_data += value.name + '</span></td>';
                    branch_data += '</tr>';

                });
				reinit(true)
                $("#shows").append(branch_data);
				reinit()
            },
            error: function () {
                console.log('fall');

            }
        });

}

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

function reinit(destory) {
	if (destory == true) {
		$('#product_sales_pdt_table').DataTable().clear().destroy();
	} else {
		dt = $('#product_sales_pdt_table').DataTable({
			order:[]
		});
	}
	}

date_filter()
	
</script>


<div class="clearfix"></div>
<br><br>

<div class="modal fade" id="showDateModalFrom" tabindex="-1"
  role="dialog" aria-labelledby="staffNameLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered  mw-75 w-50" role="document">
    <div class="modal-content modal-inside bg-greenlobster">
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


@if(empty($approved_merchant))
<style type="text/css">
    .btndownload{
        pointer-events: none !important; 
    }
</style>
@endif
