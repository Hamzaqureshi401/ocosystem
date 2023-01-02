<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @if(Request::is('platypos/mob_confirm_order') or Request::is('platypos/mob_comfirmed-order'))
        <title>@yield('title', 'Order Confirm Page' )</title>
        <link rel="stylesheet" href="{{asset('css/mob_order.css')}}">
    @elseif(Request::is('platypos/mob_confirm_order') or Request::is('platypos/mob_product_show'))
        <link rel="stylesheet" href="{{asset('css/mob_order.css')}}">
    @elseif(Request::is('analytics/companies') or Request::is('analytics/company/branch'))
        <title>@yield('title', 'Analytics' )</title>
        <link rel="stylesheet" href="{{asset('css/mob_order.css')}}">
    @elseif(Request::is('transactions') or Request::is('transactions/delivery-order'))
        <title>@yield('title', 'Transactions' )</title>
        <link rel="stylesheet" href="{{asset('css/mob_order.css')}}">
    @elseif(Request::is('platypos/mob_order'))
        <title>@yield('title', 'Order Page' )</title>
        <link rel="stylesheet" href="{{asset('css/mob_order.css')}}">
    @elseif(Request::is('scanner'))
        <title>@yield('title', 'Scanner' )</title>
        <link rel="stylesheet" href="{{asset('css/mob_order.css')}}">
    @elseif(Request::is('analytics/company/summary'))
        <title>@yield('title', 'Summary Page' )</title>
        <link rel="stylesheet" href="{{asset('css/mob_order.css')}}">
    @elseif(Request::is('analytics/'))
        <title>@yield('title', 'Analytics Page' )</title>
        <link rel="stylesheet" href="{{asset('css/mob_order.css')}}">
    @elseif(Request::is('mob/user/*'))
        <title>@yield('title', 'User Details' )</title>
        <link rel="stylesheet" href="{{asset('css/mob_order.css')}}">
    @elseif(Request::is('mob-inventory/stock-in') or Request::is('mob-inventory/stock-out'))
        <title>@yield('title', 'Inventory' )</title>
        <link rel="stylesheet" href="{{asset('css/mob_order.css')}}">
    @elseif(Request::is('mob_landing'))
        <title>@yield('title', 'Ocosystem Main Page' )</title>
        <link rel="stylesheet" href="{{asset('css/mob_order.css')}}">
    @elseif(Request::is('mob-inventory'))
        <title>@yield('title', 'Inventory' )</title>
    @elseif(Request::is('mob-member'))
        <title>@yield('title', 'Member' )</title>
        <link rel="stylesheet" href="{{asset('css/mob_order.css')}}">
    @elseif(Request::is('mob-ecommerce'))
        <title>@yield('title', 'Ecommerce' )</title>
        <link rel="stylesheet" href="{{asset('css/mob_order.css')}}">
    @elseif(Request::is('mob-inventory/product-inventory-form'))
        <title>@yield('title', 'Product Inventory' )</title>
        <link rel="stylesheet" href="{{asset('css/mob_order.css')}}">
    @elseif(Route::current()->getName() === 'mob.user.personal')
		<title>@yield('title', 'Personal Page' )</title>
	@elseif(Route::current()->getName() === 'mob.user.repair_and_maintenance')
		<title>@yield('title', 'Service and Maintenance' )</title>
	@elseif(Route::current()->getName() === 'mob.user.repair_and_maintenance.list')
		<title>@yield('title', 'Service and Maintenance List' )</title>
	@elseif(Route::current()->getName() === 'mob.user.repair_and_maintenance.form')
		<title>@yield('title', 'Service and Maintenance Form' )</title>
	@elseif(Route::current()->getName() === 'mob.user.repair_and_maintenance.form.add')
		<title>@yield('title', 'Service and maintenance Add' )</title>
    @else
        <title>@yield('title', 'PlatyPos Main Page' )</title>
    @endif
    <link rel="stylesheet" href="{{asset('css/styles.css')}}">
    <link rel="stylesheet" href="{{asset('css/materialize.css')}}">
    <link rel="stylesheet" href="{{asset('css/mob_landing.css')}}">
    <link rel="stylesheet" href="{{asset('css/bootstrap-3.4.1.css')}}">
    <link rel="stylesheet" href="{{asset('css/mob_platypos.css')}}">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    
    <script src="{{asset('js/mob_landing.js')}}"></script>
    <script src="{{asset('js/materialize.js')}}"></script>
    <script src="{{asset('js/jquery.min.js')}}"></script>
    <script src="{{asset('js/bootstrap.min.js')}}"></script>
	
    <script type="text/javascript" src="{{asset('js/instascan.min.js')}}"></script>

    <script src="{{asset('js/html5-qrcode.min.js') }}"></script>

    <!-- fonts -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="{{asset('fonts/Noteworthy-Lt.ttf')}}" rel="stylesheet">
    <style>
.modal .modal-content {
    padding: 0 !important;
    overflow-y: hidden;
    height: 150px;
}
.modal-dialog {
    margin: 0 !important;
}
.d-flex {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
}

.icon-img {
	margin-left: 15px; 
	margin-top: 3px; 
	height: 80%; width: 80%;
	object-fit: contain;
}

a:link {
  text-decoration: none;
}

.fixed-top {
	position: fixed !important;
	left: 0 !important;
	top: 0 !important;
	width: 100% !important;
	height: 60px !important;
	padding: 10px 25px 10px 25px;
}

.bg-headerfooter {
	background-color: black;/*#4e2570;*/
}

.bg-blacklobster{color:white;border-color:rgba(0,0,0,0.5);background-color:rgba(0,0,0,0.5)}

/* .h-15 {
	height: 13%;
} */
.form-control {
	border:1px solid grey !important;
	border-radius: 5px !important;
	font-size: 20px !important;
}
.w-50 {
	width: 62%;
	padding: 0px;
}
.divider {
	border-top: 1px solid #474747;
	margin: 0px;
	padding: 0px;
}
.title-text {
	padding-top:1px; color: #474747; 
	font-size: 20px;
	margin-top:18px;
}
.mt-5 {
	margin-top:5px !important;
}
.p-m-0 {
	padding:0px; 
	margin:0px; 
}
.blank-div {
	width: 25px;
}
.font-default {
	font-size:20px;
	color: #474747
}

</style>

</head>
<body>   
@if(Request::is('mob_landing'))
	<div class="d-flex fixed-top bg-headerfooter">
		<div>
			<!-- Displaying a blank 50x50 tile -->
			<img style="width:25px;height:25px; object-fit: contain;
				vertical-align: middle;"
				src="/images/mob_landing/logo.png" alt="">
			<span style="font-size:17px;color:white;position:relative;top:3px">
				Ocosystem
			</span>
		</div>
		<div>
			<a href="#" data-toggle="modal" data-target=".bs-example-modal-sm">
				<div>
					<img style="width:25px;height:25px;"
						src="/images/mob_landing/times_50x50.png" alt="times">
				</div>
			</a>
		</div>
	</div>
	<br><br><br>
@elseif(Route::current()->getName() === 'mob.user.personal')
	<div class="d-flex fixed-top bg-headerfooter">
		<div>
			<a href="/mob_landing">
				<img style="width:25px;height:25px;"
					src="/images/mob_landing/white_ring1.png" alt="white-ring">
			</a>
		</div>
		<div>
			<h2 style="font-size:21px;padding:0;margin:0;">
				<a href="/mob_landing"
					style="font-family:Lato;font-weight:normal;color:white;">
					<b>Personal</b>
				</a>
			</h2>
		</div>
		<div>
			<img style="width:25px;height:25px;"
				src="/images/mob_landing/blank_50x50.png"
				alt="right-arrow">
			<!--
				src="/images/mob_landing/right_arrow_50x50.png"
				alt="right-arrow">
			-->
		</div>
	</div>
	<br><br><br>
@elseif(Route::current()->getName() === 'mob.user.scanner')
	<div class="d-flex fixed-top bg-headerfooter">
		<div>
			<a href="/mob_landing">
				<img style="width:25px;height:25px;"
					src="/images/mob_landing/white_ring1.png" alt="white-ring">
			</a>
		</div>
		<div>
			<h2 style="font-size:21px;padding:0;margin:0;">
				<a href="#!"
					style="font-family:Lato;font-weight:normal;color:white;">
					<b>Scanner</b>
				</a>
			</h2>
		</div>
		<div>
			<img style="width:25px;height:25px;"
				src="/images/mob_landing/blank_50x50.png"
				alt="right-arrow">
			<!--
				src="/images/mob_landing/right_arrow_50x50.png"
				alt="right-arrow">
			-->
		</div>
	</div>
	<br><br><br>
@elseif(Route::current()->getName() === 'mob.user.repair_and_maintenance')
	<div class="d-flex fixed-top bg-headerfooter">
		<div>
			<a href="/mob_landing">
				<img style="width:25px;height:25px;"
					src="/images/mob_landing/white_ring1.png"
					alt="white-ring">
			</a>
		</div>
		<div>
			<h2 style="font-size:21px;padding:0;margin:0;">
				<a href="/mob/repair-maintenance"
					style="font-family:Lato;font-weight:normal;color:white;">
					<b>Service & Maintenance</b>
				</a>
			</h2>
		</div>
		<div>
			<a style="cursor:pointer" class="repair-main-funnel">
				<img style="width:25px;height:25px;" 
					src="/images/mob_landing/funnel_50x50.png" alt="funnel">
			</a>
		</div>
	</div>

@elseif(Route::current()->getName() === 'mob.user.repair_and_maintenance.list')
	<div class="d-flex fixed-top bg-headerfooter">
		<div>
			<a href="/mob_landing">
				<img style="width:25px;height:25px;"
					src="/images/mob_landing/white_ring1.png"
					alt="white_ring">
			</a>
		</div>
		<div>
			<h2 style="font-size:21px;padding:0;margin:0;">
				<a href="/mob/repair-maintenance"
					style="font-family:Lato;font-weight:normal;color:white;">
					<b>Service & Maintenance</b>
				</a>
			</h2>
		</div>
		<div>
			<a style="cursor:pointer" class="repair-main-funnel">
				<img style="width:25px;height:25px;" 
					src="/images/mob_landing/funnel_50x50.png" alt="funnel">
			</a>
		</div>
	</div>
@elseif(Route::current()->getName() === 'mob.user.repair_and_maintenance.form')
	<div class="d-flex fixed-top bg-headerfooter">
		<div>
			<a href="/mob_landing">
				<img style="width:25px;height:25px;"
					src="/images/mob_landing/white_ring1.png"
					alt="white_ring">
			</a>
		</div>
		<div>
			<h2 style="font-size:21px;padding:0;margin:0;">
				<a href="/mob/repair-maintenance"
					style="font-family:Lato;font-weight:normal;color:white;">
					<b>Service & Maintenance</b>
				</a>
			</h2>
		</div>
		<div class="blank_div">
			<a href="{{route('mob.user.repair_and_maintenance.form.add', $cmrForm->id)}}">
			<img src="/images/mob_landing/plus_50x50.png"
				style="width:25px;height:25px;"></a>
		</div>
	</div>
@elseif(Route::current()->getName() === 'mob.user.repair_and_maintenance.form.add')
	<div class="d-flex fixed-top bg-headerfooter">
		<div>
			<a href="/mob_landing">
				<img style="width:25px;height:25px;"
					src="/images/mob_landing/white_ring1.png" alt="white_ring">
			</a>
		</div>
		<div>
			<h2 style="font-size:21px;padding:0;margin:0;">
				<a href="/mob/repair-maintenance"
					style="font-family:Lato;font-weight:normal;color:white;">
					<b>Service & Maintenance</b>
				</a>
			</h2>
		</div>
		<div class="blank_div">
		</div>
	</div>
@elseif(Route::current()->getName() === 'mobile.virtual_cabinet')
	<div class="d-flex fixed-top bg-headerfooter">
		<div>
			<a href="/mob_landing">
				<img style="width:25px;height:25px;"
					src="/images/mob_landing/white_ring1.png"
					alt="white-ring">
			</a>
		</div>
		<div>
			<h2 style="font-size:21px;padding:0;margin:0;">
				<a href="#!"
					style="font-family:Lato;font-weight:normal;color:white;">
					<b>Branch</b>
				</a>
			</h2>
		</div>
		<div class="blank_div">
			<img src="/images/mob_landing/blank_50x50.png"
				style="width:25px;height:25px;">
		</div>
	</div>
@elseif(Route::current()->getName() === 'mobile.virtual_cabinet_eod')
	<div class="d-flex fixed-top bg-headerfooter">
		<div>
			<a href="/mob_landing">
				<img style="width:25px;height:25px;"
					src="/images/mob_landing/white_ring1.png"
					alt="white-ring">
			</a>
		</div>
		<div>
			<h2 style="font-size:21px;padding:0;margin:0;">
				<a href="#!"
					style="font-family:Lato;font-weight:normal;color:white;">
					<b>End of Day Summary</b>
				</a>
			</h2>
		</div>
		<div class="blank_div">
			<img src="/images/mob_landing/blank_50x50.png"
				style="width:25px;height:25px;">
		</div>
	</div>
@elseif(Route::current()->getName() === 'mobile.virtual_cabinet_eod_shift')
	<div class="d-flex fixed-top bg-headerfooter">
		<div>
			<a href="/mob_landing">
				<img style="width:25px;height:25px;"
					src="/images/mob_landing/white_ring1.png"
					alt="white-ring">
			</a>
		</div>
		<div>
			<h2 style="font-size:21px;padding:0;margin:0;">
				<a href="#!"
					style="font-family:Lato;font-weight:normal;color:white;">
					<b>End of Shift {{ $index }} Summary</b>
				</a>
			</h2>
		</div>
		<div class="blank_div">
			<img src="/images/mob_landing/blank_50x50.png"
				style="width:25px;height:25px;">
		</div>
	</div>
@elseif(Request::is('analytics/companies'))
	<div class="topnav fixed-top" style="height:50px; background-color:black;">
		<div class="col-3">
			<img style="margin-top:10px;margin-left:10px;width:30px;height:30px"
				src="/images/mob_landing/logo.png" alt="">
		</div>
		<div class="col-3">
			<h2 style="margin-top:12px;margin-bottom:10px" >
				<a href="/mob_landing">Company</a>
			</h2>
		</div>
		<div class="col-3">
			<a style="cursor:pointer" onclick="goBack()">
				<i style="top:10px;margin-right:-10px" 
				class="material-icons top_arroy_icon">keyboard_arrow_right</i>
			</a>
		</div>
	</div>
@elseif(Request::is('mob-inventory') or Request::is('mob-inventory/stock-in') 
									or Request::is('mob-inventory/stock-out'))
	<div class="topnav fixed-top" style="height:50px; background-color:black;">
		<div class="col-3">
			<img style="margin-top:10px;margin-left:10px;width:30px;height:30px"
				src="/images/mob_landing/logo.png" alt="">
		</div>
		<div class="col-3">
			<h2 style="margin-top:12px;margin-bottom:10px" >
				<a href="/mob_landing">Inventory</a>
			</h2>
		</div>
		<div class="col-3">
			<a style="cursor:pointer" onclick="goBack()">
				<i style="top:10px;margin-right:-10px" 
				class="material-icons top_arroy_icon">keyboard_arrow_right</i>
			</a>
		</div>
	</div>
@elseif(Request::is('analytics'))
	<div class="topnav fixed-top" style="height:50px; background-color:black;">
		<div class="col-3">
			<img style="margin-top:10px;margin-left:10px;width:30px;height:30px"
				src="/images/mob_landing/logo.png" alt="">
		</div>
		<div class="col-3">
			<h2 style="margin-top:12px;margin-bottom:10px" >
				<a href="/mob_landing">Analytics</a>
			</h2>
		</div>
		<div class="col-3">
			<a style="cursor:pointer" onclick="goBack()">
				<i style="top:10px;margin-right:-10px" 
				class="material-icons top_arroy_icon">keyboard_arrow_right</i>
			</a>
		</div>
	</div>
@elseif(Request::is('transactions'))
	<div class="topnav fixed-top" style="height:50px; background-color:black;">
		<div class="col-3">
			<img style="margin-top:10px;margin-left:10px;width:30px;height:30px"
				src="/images/mob_landing/logo.png" alt="">
		</div>
		<div class="col-3">
			<h2 style="margin-top:12px;margin-bottom:10px" >
				<a href="/mob_landing">Transaction</a>
			</h2>
		</div>
		<div class="col-3">
			<a style="cursor:pointer" onclick="goBack()">
				<i style="top:10px;margin-right:-10px" 
				class="material-icons top_arroy_icon">keyboard_arrow_right</i>
			</a>
		</div>
	</div>
@elseif(Request::is('scanner'))
	<div class="topnav fixed-top" style="height:50px; background-color:black;">
		<div class="col-3">
			<img style="margin-top:10px;margin-left:10px;width:30px;height:30px"
				src="/images/mob_landing/logo.png" alt="">
		</div>
		<div class="col-3">
			<h2 style="margin-top:12px;margin-bottom:10px" >
				<a href="/mob_landing">Scanner</a>
			</h2>
		</div>
		<div class="col-3">
			<a href="/mob_landing" style="cursor:pointer">
			<i style="top:10px;margin-right:-10px" 
			class="material-icons top_arroy_icon">keyboard_arrow_right</i></a>
		</div>
	</div>
@elseif(Request::is('transactions/delivery-order'))
	<div class="topnav fixed-top" style="height:50px; background-color:black;">
		<div class="col-3">
			<img style="margin-top:10px;margin-left:10px;width:30px;height:30px"
				src="/images/mob_landing/logo.png" alt="">
		</div>
		<div class="col-3">
			<h2 style="margin-top:12px;margin-bottom:10px" >
				<a href="/mob_landing"style="margin-top: 4px;font-size: 18px;">Delivery Order</a>
			</h2>
		</div>
		<div class="col-3">
			<a style="cursor:pointer" onclick="goBack()">
				<i style="top:10px;margin-right:-10px" 
				class="material-icons top_arroy_icon">keyboard_arrow_right</i>
			</a>
		</div>
	</div>
@elseif(Request::is('analytics/company/branch'))
	<div class="topnav fixed-top" style="height:50px; background-color:black;">
		<div class="col-3">
			<img style="margin-top:10px;margin-left:10px;width:30px;height:30px"
				src="/images/mob_landing/logo.png" alt="">
		</div>
		<div class="col-3">
			<h2 style="margin-top:12px;margin-bottom:10px" >
				<a href="/mob_landing">Branch</a>
			</h2>
		</div>
		<div class="col-3">
			<a style="cursor:pointer" onclick="goBack()">
				<i style="top:10px;margin-right:-10px" 
				class="material-icons top_arroy_icon">keyboard_arrow_right</i>
			</a>
		</div>
	</div>
@elseif(Request::is('analytics/company/summary'))
	<div class="topnav fixed-top" style="height:50px; background-color:black;">
		<div class="col-3">
				<img style="margin-top:10px;margin-left:10px;width:30px;height:30px"
				src="/images/mob_landing/logo.png" alt="">
		</div>
		<div class="col-3">
			<h2 style="margin-top:12px;margin-bottom:10px" >
				<a href="/mob_landing">EOD</a>
			</h2>
		</div>
		<div class="col-3">
			<a style="cursor:pointer" onclick="goBack()">
				<i style="top:10px;margin-right:-10px" 
				class="material-icons top_arroy_icon">keyboard_arrow_right</i>
			</a>
		</div>
	</div>
@elseif(Request::is('mob-ecommerce'))
	<div class="topnav fixed-top" style="height:50px; background-color:black;">
		<div class="col-3">
			<img style="margin-top:10px;margin-left:10px;width:30px;height:30px"
				src="/images/mob_landing/logo.png" alt="">
		</div>
		<div class="col-3">
			<h2 style="margin-top:12px;margin-bottom:10px" >
				<a href="/mob_landing">Ecommerce</a>
			</h2>
		</div>
		<div class="col-3">
			<a style="cursor:pointer" onclick="goBack()">
				<i style="top:10px;margin-right:-10px" 
				class="material-icons top_arroy_icon">keyboard_arrow_right</i>
			</a>
		</div>
	</div>
@elseif(Request::is('platyPOS'))
	<div class="topnav fixed-top" style="height:50px; background-color:black;">
		<div class="col-3">
			<img style="margin-top:10px;margin-left:10px;width:30px;height:30px"
				src="/images/mob_landing/logo.png" alt="">
		</div>
		<div class="col-3">
			<h2 style="margin-top:12px;margin-bottom:10px" >
			<a href="/mob_landing">PlatyPos</a>
			</h2>
		</div>
		<div class="col-3">
			<a href="/mob_landing" style="cursor:pointer">
			<i style="top:10px;margin-right:-10px" 
			class="material-icons top_arroy_icon">keyboard_arrow_right</i></a>
		</div>
	</div>
@elseif(Request::is('mob-member'))
	<div class="topnav fixed-top" style="height:50px; background-color:black;">
		<div class="col-3">
			<img style="margin-top:10px;margin-left:10px;width:30px;height:30px"
				src="/images/mob_landing/logo.png" alt="">
		</div>
		<div class="col-3">
			<h2 style="margin-top:12px;margin-bottom:10px" >
				<a href="/mob_landing">Member</a>
			</h2>
		</div>
		<div class="col-3">
			<a style="cursor:pointer" onclick="goBack()">
				<i style="top:10px;margin-right:-10px" 
				class="material-icons top_arroy_icon">keyboard_arrow_right</i>
			</a>
		</div>
	</div>
@elseif(Request::is('platypos/mob_order') or Request::is('platypos/mob_confirm_order'))
	<div class="topnav fixed-top" style="height:50px; background-color:black;">
		<div class="col-3">
			<img style="margin-top:10px;margin-left:10px;width:30px;height:30px"
				src="/images/mob_landing/logo.png" alt="">
		</div>
		<div class="col-3">
			<h2 style="margin-top:12px;" >
				<a href="/mob_landing"  >PlatyPos</a>
			</h2>
		</div>
		<div class="col-3">
			<a style="cursor:pointer" onclick="goBack()">
				<i style="top:10px;margin-right:-10px" 
				class="material-icons top_arroy_icon">keyboard_arrow_right</i>
			</a>
		</div>
	</div>
@elseif(Request::is('mob-inventory/product-inventory-form'))
 <div class="topnav fixed-top" style="height:50px; background-color:black;">
		<div class="col-3">
			<img style="margin-top:10px;margin-left:10px;width:30px;height:30px"
				src="/images/mob_landing/logo.png" alt="">
		</div>
		<div class="col-3">
			<h2 style="margin-top:12px;margin-bottom:10px" >
				<a href="/mob_landing" style="font-size: 20px;" >Product Inventory</a>
			</h2>
		</div>
		<div class="col-3">
			<a style="cursor:pointer" onclick="goBack()">
				<i style="top:10px;margin-right:-10px" 
				class="material-icons top_arroy_icon">keyboard_arrow_right</i>
			</a>
		</div>
	</div>
@else
	<div class="topnav fixed-top" style="height:50px; background-color:black;">
		<div class="col-3">
			<img style="margin-top:10px;margin-left:10px;width:30px;height:30px"
				src="/images/mob_landing/logo.png" alt="">
		</div>
		<div class="col-3">
			<h2 style="margin-top:12px;margin-bottom:10px" >
				<a href="/mob_landing"></a>
			</h2>
		</div>
		<div class="col-3">
			<a style="cursor:pointer" onclick="goBack()">
				<i style="top:10px;margin-right:-10px" 
				class="material-icons top_arroy_icon">keyboard_arrow_right</i>
			</a>
		</div>
	</div>
@endif
  
<script>
    function LogoutWithClickForm(){
        $('#logoutFormModalMovil').submit();
    }
</script>
