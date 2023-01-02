@include("common.mob_header")
	<div class="container features">
		<div style="padding-top:18px"></div>

	@if ($globalAuth->mobile_module('psn'))
		<div style="" class="row h-15" id="personalTab">
			<a href="{{route('mob.user.personal', $user->id)}}" id="personal"
				class="func-link"
				onmouseover="swapImg(this.id,'/images/mob_landing/personal_green.png')"
				onmouseleave="swapImg(this.id, '/images/mob_landing/personal.png')" {{ 
					$globalAuth->mobile_role('psn') ? '':'disabled=disabled' 
					}}>
				<div class="col w-30">
					<img src="/images/mob_landing/personal.png"
						style="margin-top:5px"
						class="icon-img" alt="Details" id="personal_image">
				</div>
				<div class="col w-50">
					<h1 class="title-text">Personal</h1>
				</div>
			</a>
		</div>
		<hr class="divider">
		@endif
		
		@if ($globalAuth->mobile_module('vcab'))
		<div style="" class="row h-15" id="virtualCabinetTab">
			<a href="{{ route('mobile.virtual_cabinet') }}" id="virtualcabinet"
				class="func-link"
				onmouseover="swapImg(this.id,'/images/mob_landing/virtualcabinet_green.png')"
				onmouseleave="swapImg(this.id, '/images/mob_landing/virtualcabinet.png')" {{ 
					$globalAuth->mobile_role('vcab') ? '':'disabled=disabled' 
					}}>
				<div class="col w-30">
					<img src="/images/mob_landing/virtualcabinet.png"
						style="margin-top:5px"
						class="icon-img" alt="Details" id="virtualcabinet_image">
				</div>
				<div class="col w-50">
					<h1 class="title-text">Virtual Cabinet</h1>
				</div>
			</a>
		</div>
		<hr class="divider">		
		@endif

		@if ($globalAuth->mobile_module('snm'))
		<div class="row h-15" id="repairMaintenanceTab">
			<a href="{{route('mob.user.repair_and_maintenance')}}"
				id="repair_maintenance"
				onmouseover="swapImg(this.id, '/images/mob_landing/repair_maintenance_green.png')"
				onmouseleave="swapImg(this.id, '/images/mob_landing/repair_maintenance.png')"
				class="func-link" {{ 
					$globalAuth->mobile_role('snm') ? '':'disabled=disabled' 
					}}>

				<div class="col w-30">
					<img style="margin-top:5px;object-fit:contain;width:50px;
						margin-left:22px"
						id="repair_maintenance_image"
						src="/images/mob_landing/repair_maintenance.png"
						class="icon-img" alt="Service & Maintenance"/>
				</div>
				<div class="col w-50">
					<h1 class="title-text">Service & Maintenance</h1>
				</div>
			</a>
		</div>
		<hr class="divider">		
		@endif

		@if ($globalAuth->mobile_module('fnb'))
		<div class="row h-15" id="floorManagementTab" >
			<a href=""
				id="floor_management"
				onmouseover="swapImg(this.id, '/images/mob_landing/floor_management_green.png')"
				onmouseleave="swapImg(this.id, '/images/mob_landing/floor_management.png')"
				class="func-link" {{ 
					$globalAuth->mobile_role('fnb') ? '':'disabled=disabled' 
					}}>

				<div class="col w-30">
					<img style="margin-top:7px;margin-left:20px;width:60px;
						object-fit:contain;"
						id="floor_management_image"
						src="/images/mob_landing/floor_management.png"
						class="icon-img" alt="Floor Management"/>
				</div>
				<div class="col w-50">
					<h1 style="margin-top:10px" class="title-text" >
					Food & Beverage<br>Floor Management
					</h1>
				</div>
			</a>
		</div>
		<hr class="divider">		
		@endif

		@if ($globalAuth->mobile_module('pos'))
		<div class="row h-15" id="poscupineTab">
			<a href=""
				id="poscupine"
				onmouseover="swapImg(this.id, '/images/mob_landing/poscupine_green.png')"
				onmouseleave="swapImg(this.id, '/images/mob_landing/poscupine.png')"
				class="func-link" {{ 
					$globalAuth->mobile_role('pos') ? '':'disabled=disabled' 
					}}>

				<div class="col w-30">
					<img style="margin-top:9px;margin-left:20px;width:55px;
						object-fit:contain;"
						id="poscupine_image"
						src="/images/mob_landing/poscupine.png"
						class="icon-img" alt="POScupine"/>
				</div>
				<div class="col w-50">
					<h1 style="margin-top:10px"
					class="title-text">POSCUPINE<br>
					Point of Sales
					</h1>
				</div>
			</a>
		</div>
		<hr class="divider">		
		@endif
		
		@if ($globalAuth->mobile_module('eln'))
		<div class="row h-15" id="eLearningTab">
			<a href=""
				id="elearning"
				onmouseover="swapImg(this.id, '/images/mob_landing/elearning_green.png')"
				onmouseleave="swapImg(this.id, '/images/mob_landing/elearning.png')"
				class="func-link" {{ 
					$globalAuth->mobile_role('eln') ? '':'disabled=disabled' 
					}}>

				<div class="col w-30">
					<img style="margin-top:6px;margin-left:20px;width:55px;
						object-fit:contain;"
						id="elearning_image"
						src="/images/mob_landing/elearning.png"
						class="icon-img" alt="E-Learning"/>
				</div>
				<div class="col w-50">
					<h1 style="vertical-align: middle;"
						class="title-text">E-Learning
					</h1>
				</div>
			</a>
		</div>
		<hr class="divider">		
		@endif


		@if ($globalAuth->mobile_module('ana'))
		<div class="row h-15" id="analyticsTab">
			<a href=""
				id="analytics"
				onmouseover="swapImg(this.id, '/images/mob_landing/analytics_green.png')"
				onmouseleave="swapImg(this.id, '/images/mob_landing/analytics.png')"
				class="func-link" {{ 
					$globalAuth->mobile_role('ana') ? '':'disabled=disabled' 
					}}>

				<div class="col w-30">
					<img style="margin-top:6px;margin-left:20px;width:55px;
						object-fit:contain;"
						id="analytics_image"
						src="/images/mob_landing/analytics.png"
						class="icon-img" alt="Analytics"/>
				</div>
				<div class="col w-50">
					<h1 style="vertical-align: middle;"
						class="title-text">Analytics
					</h1>
				</div>
			</a>
		</div>
		<hr class="divider">		
		@endif

		@if ($globalAuth->mobile_module('ecom'))
		<div class="row h-15" id="eCommerceTab">
			<a href=""
				id="ecommerce"
				onmouseover="swapImg(this.id, '/images/mob_landing/ecommerce_green.png')"
				onmouseleave="swapImg(this.id, '/images/mob_landing/ecommerce.png')"
				class="func-link" {{ 
					$globalAuth->mobile_role('ecom') ? '':'disabled=disabled' 
					}}>

				<div class="col w-30">
					<img style="margin-top:6px;margin-left:20px;width:55px;
						object-fit:contain;"
						id="ecommerce_image"
						src="/images/mob_landing/ecommerce.png"
						class="icon-img" alt="eCommerce"/>
				</div>
				<div class="col w-50">
					<h1 style="vertical-align: middle;"
						class="title-text">E-Commerce
					</h1>
				</div>
			</a>
		</div>
		<hr class="divider">		
		@endif

		@if ($globalAuth->mobile_module('log'))
		<div class="row h-15" id="logisticsTab">
			<a href=""
				id="logistics"
				onmouseover="swapImg(this.id, '/images/mob_landing/logistics_green.png')"
				onmouseleave="swapImg(this.id, '/images/mob_landing/logistics.png')"
				class="func-link" {{ 
					$globalAuth->mobile_role('log') ? '':'disabled=disabled' 
					}}>

				<div class="col w-30">
					<img style="margin-top:6px;margin-left:25px;width:50px;
						object-fit:contain;"
						id="logistics_image"
						src="/images/mob_landing/logistics.png"
						class="icon-img" alt="logistics"/>
				</div>
				<div class="col w-50">
					<h1 style="vertical-align: middle;"
						class="title-text">Logistics
					</h1>
				</div>
			</a>
		</div>
		<hr class="divider">		
		@endif
		
		@if ($globalAuth->mobile_module('sales'))
		<div class="row h-15" id="salesmgmtTab">
			<a href=""
				id="salesmgmt"
				onmouseover="swapImg(this.id, '/images/mob_landing/salesmgmt_green.png')"
				onmouseleave="swapImg(this.id, '/images/mob_landing/salesmgmt.png')"
				class="func-link" {{ 
					$globalAuth->mobile_role('sales') ? '':'disabled=disabled' 
					}}>

				<div class="col w-30">
					<img style="margin-top:4px;margin-left:25px;width:50px;
						object-fit:contain;"
						id="salesmgmt_image"
						src="/images/mob_landing/salesmgmt.png"
						class="icon-img" alt="salesmgmt"/>
				</div>
				<div class="col w-50">
					<h1 style="vertical-align: middle;"
						class="title-text">Sales Management
					</h1>
				</div>
			</a>
		</div>
		<hr class="divider">		
		@endif


		@if ($globalAuth->mobile_module('humn'))
		<div class="row h-15" id="HumancapTab">
			<a href="" id="humancap"
				class="func-link"
				onmouseover="swapImg(this.id,'/images/mob_landing/humancap_green.png')"
				onmouseleave="swapImg(this.id, '/images/mob_landing/humancap.png')" {{ 
					$globalAuth->mobile_role('humn') ? '':'disabled=disabled' 
					}}>
				<div class="col w-30">
					<img src="/images/mob_landing/humancap.png"
						style="margin-top:5px"
						class="icon-img" alt="Details" id="humancap_image">
				</div>
				<div class="col w-50">
					<h1 class="title-text">HumanCap</h1>
				</div>
			</a>
		</div>

		<hr class="divider">		
		@endif

		@if ($globalAuth->mobile_module('inv'))
		<div style="" class="row h-15 pt-0" id="inventoryTab">
			<a href="" id="inventory"
				class="func-link"
				onmouseover="swapImg(this.id,'/images/mob_landing/inventory_green.png')"
				onmouseleave="swapImg(this.id, '/images/mob_landing/inventory.png')" {{ 
					$globalAuth->mobile_role('inv') ? '':'disabled=disabled' 
					}}>
				<div class="col w-30">
					<img src="/images/mob_landing/inventory.png"
						style="margin-top:5px"
						class="icon-img" alt="Details" id="inventory_image">
				</div>
				<div class="col w-50">
					<h1 style="vertical-align:middle;"
						class="title-text">Inventory
					</h1>
				</div>
			</a>
		</div>

		<hr class="divider">
		@endif
		<!--
		<div class="row h-15">
			<div class="col w-30">
				<img onmouseover="swapImg(this.id, '/images/mob_landing/inventory_green.png')"
					onmouseleave="swapImg(this.id, '/images/mob_landing/inventory.png')"
					style="margin-top:5px"
					class="icon-img" alt="Inventory">
			</div>
			<div class="col w-50">
				<h1 style="vertical-align: middle;"
					class="title-text">Inventory
				</h1>
			</div>
		</div>
		-->
    <br>
    <br>
    <br>
    <br>		
    </div>

<div class="modal bs-example-modal-sm"  tabindex="-1"
    role="dialog" aria-labelledby="staffNameLabel"
    aria-hidden="true" style="text-align: center;  background: transparent;">

    <div class="modal-content content-centered modal-inside bg-blacklobster" >
		<div class="modal-header" style="border: none;">
			<h3>Do you really want to logout?</h3>
		</div>
		<div class="modal-footer" style="padding-bottom: 20px;">
			<a href="javascript:;" class="btn yes"
			onclick="LogoutWithClickForm()"
			style="width: 40%; height: 50px; font-size: 22px;
			border-radius: 10px; background: transparent;
			padding-top:8px;text-transform: capitalize;">Yes
			</a>&nbsp<a href="javascript:;" class="btn no"
			data-dismiss="modal"
			style="width: 40%; height: 50px; font-size: 22px;
			border-radius: 10px; background: transparent;
			padding-top:8px; text-transform: capitalize;">No
			</a>
		</div>
    </div>
</div>
	  <form id="logoutFormModalMovil" action="{{ route('logout') }}"
		method="POST" style="display: none;">
		@csrf
	</form>

@include("common.mob_footer")

</div>
<script>
	$('#personalTab').click(function() {
		console.log('clicked');
	});
	function swapImg(parent_id, id){
		var element_id = parent_id + '_image'
		var url = window.location.href.split('/')
		var base = url[0]+ '//' + url[2]
		var address = base + id
		$('#'+element_id).attr("src", address)
	}
</script>
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


<style>
a[disabled=disabled] > div > img {opacity: 0.5;}
a[disabled=disabled] > div > h1 {opacity: 0.5;}
.h-15 {
	height: 13%;
}
.w-2 {
	widows: 2%;
}
.w-30 {
	width: 25%;
	height: 60px;
}
.w-50 {
	width: 62%;
	padding: 0px;
}
.icon-img {
	margin-left: 15px; 
	margin-top: 3px; 
	height: 80%; width: 80%;
	object-fit: contain;
}
.title-text {
	padding-top:1px; color: #474747; 
	font-size: 20px;
	margin-top:18px;
}
.divider {
	border-top: 1px solid #474747;
	margin: 0px;
	padding: 0px;
}
.right-icon {
	padding-top:0px; color: #474747; font-size: 20px;float: right;
}
.row {
	margin-top: 0px !important;
	margin-bottom: 0px !important;
	
}
.func-link {
	text-decoration: none;
}
.bg-greenlobster {
	color: white;
	border-color: rgba(26, 188, 156, 0.7);
	background-color: rgba(26, 188, 156, 0.7);
}

.bg-headerfooter {
	background-color: black;/*#4e2570;*/
}

hr {
    margin-top: 10px !important;
	margin-bottom: 10px !important;
}

@media(min-width: 300px) and (max-width: 330px){
.container {
	padding-right: 0px !important;
	padding-left: 0px !important;
	margin-right: 0px !important;
	margin-left: 0px !important;
	margin: 0px !important;
	max-width: 1280px;
	width: 98% !important;
}
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
</style>
</html>

