{{--
@if(Route::current()->getName() === 'mob.user.personal')
    <div class="fixed-bottom d-flex bg-headerfooter">
@else
    <div class="bottomnav platy_footer scan_footer"
		style="background-color:rgb(0, 0, 0); margin-top: auto !important;">
@endif
--}}

<div class="fixed-bottom d-flex bg-headerfooter">
@if(Request::is('platypos/mob_confirm_order'))
	<div class="col-3">
		<span class="left_text">Beverage</span>
	</div>
	<div class="col-3">
		<span class="right_text">Tables 6</span>
	</div>
	<div class="col-3">
		<button class="btn btn-danger remove top_arroy_icon green deleteMode">
			<i class="material-icons remove-icon">close</i>
		</button>
	</div>
	<div class="col-3">
		<button class="btn btn-danger remove top_arroy_icon orderMode">
			<i class="material-icons remove-icon">close</i>
		</button>
	</div>
@elseif(Request::is('mob_landing'))
	<div style="width: 100%;">
		
	<center>
	<a href="/mob_landing"
		style="font-family:Lato;color:white; font-size: 21px;">
		<b>Function</b>
	</a>
	</center>
	</div>
@elseif(Route::current()->getName() === 'mob.user.personal')
	<div>
		<a style="cursor:pointer" onclick="goBack()">
			<img src="/images/mob_landing/blank_50x50.png"
				style="width:25px;height:25px;">
		<!--
			<img src="/images/mob_landing/left_arrow_50x50.png"
				style="width:25px;height:25px;">
		-->
		</a>
	</div>
	<div>
		<a style="cursor:pointer" href="/mob/scanner">
			<img src="/images/mob_landing/scanner_white.png"
				style="width:25px;height:25px;">
		</a>
	</div> 
@elseif(Route::current()->getName() === 'mob.user.scanner')
	<div>
		<a style="cursor:pointer" onclick="goBack()">
			<img src="/images/mob_landing/left_arrow_50x50.png"
				style="width:25px;height:25px;">
		</a>
	</div>
@elseif(Route::current()->getName() === 'mob.user.repair_and_maintenance')
	<div>
		<a style="cursor:pointer" onclick="goBack()">
			<img src="/images/mob_landing/left_arrow_50x50.png"
				style="width:25px;height:25px;">
		</a>
	</div>
	<div class="blank_div">
	</div>
@elseif(Route::current()->getName() === 'mob.user.repair_and_maintenance.list')
	<div>
		<a style="cursor:pointer" onclick="goBack()">
			<img src="/images/mob_landing/left_arrow_50x50.png"
				style="width:25px;height:25px;">
		</a>
	</div>
	<div>
		<p style="padding: auto; margin: auto; color: white;">
		<b>Corrective Maintenance Report</b>
		</p>
	</div>

	<div class="blank_div">
		<img src="/images/blank_50x50.png"
			style="width:25px;height:25px"/>
	</div>
	<!-- <div>
		<div style="display: flex;">
			<a href="#" style="margin-left:20px; width: 30px; height:30px; background: #0067ff; border-radius: 19px;"></a>
			<a href="#" style="margin-left:20px; width: 30px; height:30px; background: #00ff09; border-radius: 19px;"></a>
			<a href="#" style="margin-left:20px; width: 30px; height:30px; background: #ee2206; border-radius: 19px;"></a>
			<a href="#" style="margin-left:20px; width: 30px; height:30px; background: #ff6250; border-radius: 19px;"></a>
		</div>
	</div>  -->
@elseif(Route::current()->getName() === 'mob.user.repair_and_maintenance.form')
	<div>
		<a style="cursor:pointer" onclick="goBack()">
			<img src="/images/mob_landing/left_arrow_50x50.png"
				style="width:25px;height:25px;">
		</a>
	</div>
	<div>
		<center>
		<h4 style="color: white;">
			<b>{{ $cmr->systemid }}</b>
		</h4>
		</center>
	</div>
	<div>
		<a href="#!" style="cursor:pointer; " onclick="saveCMRForm()">
			<img src="/images/mob_landing/tick.png"
				style="width:25px;height:25px;">
		</a>
	</div>
@elseif(Route::current()->getName() === 'mob.user.repair_and_maintenance.form.add')
	<div style="width: 33%;">
		<a style="cursor:pointer" href="/mob/repair-maintenance-form/{{ $cmrForm->cmrmgmt_id }}">
			<img src="/images/mob_landing/left_arrow_50x50.png"
				style="width:auto;height:25px;">
		</a>
	</div>
	<div style="width: 33%;">
		<h2 style="font-size:25px;margin-bottom:0;margin-top:0"> 
			<a href="/mob_landing" 
				style="font-family:Lato;font-weight:normal;color:white;">
				Parts
			</a>
		</h2>
	</div>
	<div class="blank_div" style="width: 18%">
	</div>
@elseif(Route::current()->getName() === 'mobile.virtual_cabinet')
	<div>
		<a style="cursor:pointer" onclick="goBack()">
			<img src="/images/mob_landing/left_arrow_50x50.png"
				style="width:25px;height:25px;">
		</a>
	</div>
	<div>
		<h4
			class="text-primary" 
			id="dateholder" 
			onclick="callDateModal()">{{ \Carbon\Carbon::now()->format('dMy') }}</h4>
	</div>
	<div class="blank_div">
		<img src="/images/mob_landing/blank_50x50.png"
				style="width:25px;height:25px;">
	</div>
@elseif(Route::current()->getName() === 'mobile.virtual_cabinet_eod')
	<div>
		<a style="cursor:pointer" onclick="goBack()">
			<img src="/images/mob_landing/left_arrow_50x50.png"
				style="width:25px;height:25px;">
		</a>
	</div>
	<div>
		<h4 style="color: white;">{{ \Carbon\Carbon::parse($eod_detail->startdate)->format('dMy') }}</h4>
	</div>
	<div class="blank_div">
		<img src="/images/mob_landing/blank_50x50.png"
				style="width:25px;height:25px;">
	</div>
@elseif(Route::current()->getName() === 'mobile.virtual_cabinet_eod_shift')
	<div>
		<a style="cursor:pointer" onclick="goBack()">
			<img src="/images/mob_landing/left_arrow_50x50.png"
				style="width:25px;height:25px;">
		</a>
	</div>
	<div>
		<h4 style="color: white;">{{ \Carbon\Carbon::parse($opos_shiftdetail->startdate)->format('dMy') }}</h4>
	</div>
	<div class="blank_div">
		<img src="/images/mob_landing/blank_50x50.png"
				style="width:25px;height:25px;">
	</div>
@elseif(Request::is('platypos/mob_menu_cancel'))
	<div class="col-3">
		<span class="left_text">Menu</span>
	</div>
	<div class="col-3">
		<span class="right_text">Tables 3+4</span>
	</div>
@elseif(Request::is('platypos/mob_menu_split'))
	<div class="col-3">
		<span class="left_text">Menu</span>
	</div>
	<div class="col-3">
		<span class="right_text">Tables 6</span>
	</div>
@elseif(Request::is('platypos/mob_comfirmed-order'))
	<div class="col-3">
		<span class="left_text">Menu</span>
	</div>
	<div class="col-3">
		<span class="right_text">Tables 6</span>
	</div>
	<div class="col-3">
		<button class="btn btn-danger top_arroy_icon">
			<i class="material-icons">close</i>
		</button>
	</div>
@elseif(Request::is('platypos/mob_order'))
	<div class="col-3">
		<span class="left_text">Beverage</span>
	</div>
	<div class="col-3">
		<span class="right_text" style='position: relative;left: 70px;' >Tables 6</span>
	</div>
	<div class="col-3">
		<a href="/platypos/mob_confirm_order" style="cursor:pointer">
			<i style="top:10px;margin-right:-10px;" 
			class="material-icons top_arroy_icon arrow_order_right">keyboard_arrow_right</i>
		</a>
	</div>
@elseif(Request::is('platypos/mob_product_show'))
	<div class="col-3">
			<span class="left_text">Special</span>
	</div>
	
	<div class="col-3"><a href="#" data-toggle="modal"style="cursor:pointer"data-target="#logoutModal">
		<i style="top: 12px; margin-right: -10px;font-size: 2.5rem !important;" class="material-icons top_arroy_icon">close</i></a>
	</div>
@elseif(Request::is('analytics/companies'))
	<div class="col-3 sumary_footer">
		<span class="left_text">Cash Sales</span>
	</div>
	
	<div class="col-3">
		<span class=" date_time">12Dec19</span>
	</div>
@elseif(Request::is('analytics/company/branch'))
	<div class="col-3 sumary_footer">
		<span class="left_text">Black Star Pte Ltd</span>
	</div>
	
	<div class="col-3 ">
		<span class=" date_time">12Dec19</span>
	</div>
@elseif(Request::is('transactions'))
	<div class="col-3">
		<span class="left_text">Pending</span>
	</div>
	<div class="order_quantity_button">
		<div class="btn btn-danger green "></div>
	</div>
	<div class="order_quantity_button" style="border: none !important;">
		<div class="btn btn-danger red " style="position: relative;left: -50px;"></div>
	</div>
	<div class="col-3">
		<div class="selected-date left_text"></div>
	</div>
@elseif(Request::is('scanner'))
	<a href="" class="scan_button">
		<div class="scan_btn green "></div>
	</a>
@elseif(Request::is('platyPOS'))
	<div class="col-3">
		<span class="left_text">Table</span>
	</div>
   
	<div class="col-3">
		<span class="left_text"></span>
	</div>
@elseif(Request::is('mob-inventory/confirm-inventory'))
	<div class="col-3">
		<span class="left_text">Stock In</span>
	</div>
   
	<div class="col-3">
		<span class="left_text">Kenny Hill</span>
	</div>
@else
	<div class="col-3">
		<span class="left_text"></span>
	</div>
   
	<div class="col-3">
		<span class="left_text"></span>
	</div>
@endif
</div>

<style>
.col-3 a:visited{
	color: #fff;
}
.d-flex {
	display: flex !important;
	align-items: center !important;
	justify-content: space-between !important;
}
.fixed-bottom {
	position: fixed !important;
	left: 0 !important;
	bottom: 0 !important;
	width: 100% !important;
	height: 60px !important;
	padding: 10px 25px 10px 25px;
}
.bg-headerfooter {
	background-color: black;/*#4e2570;*/
}

.yes{
	border: 1px solid white;
}
.no{
	border: 1px solid white;
}
.yes:active{
	border: 2px solid #007bff;
	color: #007bff;
	font-weight: bold;
}
.no:active{
	border: 2px solid #dc3545;
	color: #dc3545;
	font-weight: bold;
}
.modal-backdrop {
   background-color: rgba(0,0,0,0.5);
}
#part_number_value{
	color: white !important;
} 
.modal{
	height: 100% !important;
	width: 100% !important;
	border-radius: 10px !important;
     /*will-change: top, opacity; */
    background: transparent !important;
  background-color: transparent !important;
  max-height: 100% !important;
  width: 100% !important;
}
.modal .modal-content{
	height: 100%;
	overflow-y: scroll;
}
</style>


<div class="modal"  id="modalMessage"  tabindex="-1"
    role="dialog" aria-labelledby="staffNameLabel"
    aria-hidden="true" style="text-align: center;">

    <div class="bg-blacklobster"
		style="border-radius:10px;margin-top:180px;height:150px;">
        <div class="modal-header" style="border:0"></div>
        <div class="modal-body text-center" style="padding-top:27px">
            <h3 class="mb-0 modal-title text-white text-center"
				style="padding:0"
                id="statusModalLabelMsg">
            </h3>
        </div>
        <div class="modal-footer" style="border:0"></div>
    </div>
</div>

<div class="modal"  id="modalPartNumber"  tabindex="-1"
    role="dialog" aria-labelledby="staffNameLabel"
    aria-hidden="true" style="background: transparent;">

    <div class="bg-blacklobster"
		style="border-radius:10px;height: 150px;margin-top:180px">
        <div class="modal-body text-center">
        	<input type="hidden" id="part_number_id">
        	<input type="hidden" id="part_number_element_id">
            <input type="text" placeholder="Add Part No."
			id="part_number_value"
			style="font-size:24px;border-radius:5px;margin-top: 32px;
				width: 98%; padding-left: 2%; height: 50px;
				border: 1px solid white; color: white;">
        </div>
    </div>
</div>

<div class="modal" id="deletePart" tabindex="-1"
    role="dialog" aria-labelledby="staffNameLabel"
    aria-hidden="true" style="text-align: center;">

    <div class="bg-blacklobster"
		style="border-radius:10px;height: 200px;margin-top:180px" >
		<div class="modal-header"
			style="padding-top:35px;border: none; margin-top: 18px;">
			<h3>Do you want to permanently delete this part?</h3>
		</div>
		<div class="modal-footer">
        	<input type="hidden" id="delete_part_id">
			<a href="javascript:;" class="btn yes"  data-dismiss="modal"
			onclick="deletePart()"
			style="width: 40%; height: 40px; font-size: 22px;
			padding-top:3px; border-radius: 10px; background: transparent;
			text-transform: capitalize; vertical-align: middle;">Yes
			</a>&nbsp<a href="javascript:;" class="btn no"
			data-dismiss="modal"
			style="width: 40%; height: 40px; font-size: 22px;
			padding-top:3px; border-radius: 10px; background: transparent;
			text-transform: capitalize; vertical-align: middle;">No
			</a>
		</div>
    </div>
</div>
<script type="text/javascript">
	var current_height = $('.content-centered').height();
	var window_height = $(window).height();
	var margin = 0;
	if(window_height > current_height){
		margin = (window_height - current_height)/2
	}
	$('.content-centered').css("margin-top", margin + "px")
</script>
</body>
</html>
