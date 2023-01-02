<nav class="butns navbar navbar-expand-lg navbar-light bg-light"
     style="margin-top:65px;padding-top:0">
     
@if ($globalAuth->web_module('stg'))
   <button class="btn btn-success btn-log bg-black sellerbutton"
     style="padding-left:0;padding-right:0" id="settingbutton">
     <span>Setting</span>
   </button>
@endif

@if ($globalAuth->web_module('loc'))
   <button class="btn btn-success btn-log bg-location sellerbutton"
     style="padding-left:0;padding-right:0" id="locationbutton">
     <span>Location</span>
   </button>
 @endif

 
@if ($globalAuth->web_module('data'))
   <button class="btn dropdownnn btn-success btn-log bg-data sellerbutton"
     type="button" style="width:100%;padding-left:0;padding-right:0"
	 id="databutton">
     <span>Data</span>
   </button>
@endif

@if ($globalAuth->web_module('prod'))
   <button class="btn dropdownnn btn-success btn-log bg-product sellerbutton"
     type="button" style="padding-left:0;padding-right:0" id="productbutton">
     <span>Product</span>
   </button>
@endif

@if ($globalAuth->web_module('ana'))
   <button class="btn dropdownnn btn-success btn-log bg-analytics sellerbutton"
     style="padding-left:0;padding-right:0" id="analyticsbutton">
     <span>Analytics</span>
   </button>
 @endif
 
@if ($globalAuth->web_module('rpt'))
   <button class="btn dropdownnn btn-success btn-log bg-ageing sellerbutton"
     style="padding-left:0;padding-right:0" id="reportbutton">
     <span>Report</span>
   </button>
@endif

@if ($globalAuth->web_module('vcab'))
   <button class="btn dropdownnn btn-success btn-log bg-virtualcabinet sellerbuttontwo"
     style="padding-left:0;padding-right:0" id="virtualcabinetbutton">
     <span>Virtual<br>Cabinet</span>
   </button>
@endif

@if ($globalAuth->web_module('ind'))
   <button class="btn dropdownnn btn-success btn-log bg-industry sellerbutton"
     style="padding-left:0;padding-right:0" id="industrybutton">
     <span>Industry</span>
   </button>
@endif

{{--
@if ($globalAuth->web_module('comm'))
   <button class="btn btn-success btn-log bg-partner sellerbuttonwide"
     style="padding-left:0;padding-right:0" id="partnerbutton">
     <span>Commission</span>
   </button>
@endif
--}}

@if ($globalAuth->web_module('snm'))
   <button class="btn dropdownnn btn-success btn-log bg-service sellerbuttonwide"
     style="padding-left:0;padding-right:0" id="servicebutton">
     <span>Service &<br>Maintenance</span>
   </button>
@endif

{{--
@if ($globalAuth->web_module('prdt'))
   <button class="btn dropdownnn btn-success btn-log bg-production sellerbuttonwide"
     style="padding-left:0;padding-right:0" id="productionbutton">
     <span>Production</span>
   </button>
@endif
--}}

@if ($globalAuth->web_module('dist'))
   <button class="btn dropdownnn btn-success btn-log bg-distribution sellerbuttonwide"
     style="padding-left:0;padding-right:0" id="distributionbutton">
     <span>Non-Fuel<br>Distribution</span>
   </button>
@endif

@if ($globalAuth->web_module('ret'))
   <button class="btn dropdownnn btn-success btn-log bg-retail sellerbutton"
     style="padding-left:0;padding-right:0" id="retailbutton">
     <span>Retail</span>
   </button>
@endif

@if ($globalAuth->web_module('crm'))
   <button class="btn btn-success btn-log bg-crm sellerbutton"
     style="padding-left:0;padding-right:0" id="btnEV">
     <span>EV<br>Charger</span>
   </button>
@endif

{{--
@if ($globalAuth->web_module('humn'))
   <button class="btn dropdownnn btn-success btn-log bg-humancap sellerbuttonwide"
     style="padding-left:0;padding-right:0;"
	 id="humancapbutton">
     <span>HumanCap</span>
   </button>
@endif
--}}

@if ($globalAuth->web_module('rpt'))
   <!-- Report Second Tier Buttons -->
   <div class="sellerdropdown hide" id="reportdropdown">

{{--
	@if ($globalAuth->web_module('grp'))
     <a href="javascript:void(0)" id="groupreportbuttona" class="sellerab">
       <button class="btn btn-success btn-log bg-ageing sellerbuttonwide"
         style="padding-left:0;padding-right:0"
         id="grouprelationshipbutton">
         <span>Group&nbsp;Relationship</span>
       </button>
     </a>
	@endif
	@if ($globalAuth->web_module('conso'))
     <a href="" id="consolidatedbuttona" class="sellerab">
       <button class="btn btn-success btn-log bg-ageing sellerbuttonwide"
         style="padding-left:12px;"
         id="consolidatedutton">
         <span>Consolidated<br>Report</span>
       </button>
     </a>
	@endif
	@if ($globalAuth->web_module('crep'))
       <a href="javascript:void(0)" id="companyreportbuttona" class="sellerab">
       <button class="text-center btn btn-success btn-log bg-ageing sellerbuttontwo"
         style="font-size:13px;padding-left:6px"
         id="companyreportbutton">
         <span>Company<br>&nbsp;&nbsp;Report</span>
       </button>
     </a>
	@endif
--}}

	@if ($globalAuth->web_module('isr'))
       <a href="javascript:void(0)" id="isalesreportbuttona" class="sellerab">
       <button class="text-center btn btn-success btn-log bg-ageing sellerbuttonwide"
         style="font-size:13px;padding-left:0;padding-right:0"
         id="isalesreportbutton">
         <span>Itemized<br>Sales Report</span>
       </button>
     </a>
	@endif

	@if ($globalAuth->web_module('rvpy'))
       <a href="javascript:void(0)" id="ageingreportbuttona" class="sellerab">
       <button class="btn btn-success btn-log bg-ageing sellerbuttonwide"
         style="padding-left:0;padding-right:0;font-size:13px"
         id="ageingreportbutton">
         <span>Receivable<br>Payable</span>
       </button>
     </a>
	@endif
   </div>
@endif
 
@if ($globalAuth->web_module('dist'))
   <!-- Distribution Second Tier Buttons -->
	<div class="sellerdropdown hide" id="distributiondropdown" >
	@if ($globalAuth->web_module('cadm'))
     <a href="javascript:void(0)" id="centraladminbuttona" class="sellerab">
       <button class="btn btn-success btn-log bg-gator sellerbuttontwo"
         style="padding-left:12px;font-size:13px"
         id="centraladminbutton">
         <span>Central<br>Admin</span>
       </button>
     </a>
	@endif
	 <!--
     <a href="javascript:void(0)" id="logistcsbuttona" class="sellerab">
       <button class="btn btn-success btn-log bg-komodo sellerbutton"
         style="padding-left:9px;font-size:13px" id="logisticsbutton">
         <span>Logistics</span>
       </button>
     </a>
	 -->
	@if ($globalAuth->web_module('logs'))
      <a href="javascript:void(0)" id="warehousebuttona" class="sellerab">
       <button class="btn btn-success btn-log bg-komodo sellerbuttonwide"
         style="font-size:13px" id="warehousebutton">
         <span>Logistics & Warehouse</span>
       </button>
     </a>        
	@endif

	@if ($globalAuth->web_module('vhc'))
     <a href="javascript:void(0)" id="centraladminbuttona" class="sellerab">
       <button class="btn btn-success btn-log bg-vehicle sellerbuttontwo"
         style="padding-left:12px;font-size:13px"
         id="vehiclebutton">
         <span>Vehicle</span>
       </button>
     </a>
	@endif

	@if ($globalAuth->web_module('dlvr'))
     <a href="javascript:void(0)" id="drumbarrelbuttona" class="sellerab">
       <button class="btn btn-success btn-log bg-deliveryman sellerbuttonwide"
         style="padding-left:10px;font-size:13px" id="dilverymanbutton">
         <span>Deliveryman</span>
       </button>
     </a>
	@endif

	@if ($globalAuth->web_module('drum'))
	<a href="/drumbarrel-distribution-view" id="drumbarrelbuttona"
	 	class="sellerab">
		<button class="btn btn-success btn-log bg-drumbarrel sellerbuttonwide"
			 style="padding-left:10px;font-size:13px" id="drumbarrelbutton">
			 <span>Drum & Barrel</span>
		</button>
	</a>
	@endif

	</div>
@endif

   
@if ($globalAuth->web_module('ret'))
   <!-- Retail Second Tier Buttons -->
   <div class="sellerdropdown hide" id="retaildropdown" >
    <!-- <a href="javascript:void(0)" id="voucherbuttona" class="sellerab">
       <button class="btn btn-success btn-log bg-voucher sellerbutton"
         style="padding-left:10px;font-size:13px" id="voucherbutton">
         <span>Voucher</span>
       </button>
     </a>-->

@if ($globalAuth->web_module('mbr'))
     <a href="javascript:void(0)" id="membershipbuttona" class="sellerab">
       <button class="btn btn-success btn-log bg-point sellerbuttonwide"
         style="padding-left:14px;font-size:13px" id="membershipbutton">
         <span>Membership</span>
       </button>
     </a>
@endif
@if ($globalAuth->web_module('opos'))
     <a id="opossumbuttona" class="sellerab">
       <button class="btn btn-success btn-log bg-opossum sellerbuttonwide"
         style="padding-left:5px" id="opossumbutton">
         <span>POS<br>OPOSsum</span>
       </button>
     </a>
@endif

{{--
@if ($globalAuth->web_module('indt'))
     <a  id="dingobuttona" class="sellerab"  target="_blank">
       <button class="btn pl-0 pr-0 btn-success bg-opossum sellerbutton"
         style="" id="dingobutton">
         <span>Demo</span>
       </button>
     </a>
@endif
--}}
	 <!--
     <a href="" id="ecommercebuttona" class="sellerab">
       <button class="btn btn-success btn-log bg-ecommerce sellerbuttontwo"
         style="padding-left:4px;font-size:12px" id="ecommercebutton">
         <span>&nbsp;Electronic<br>Commerce</span>
       </button>
     </a>
	 -->
   </div>
   @endif

   
@if ($globalAuth->web_module('humn'))
   <!-- HumanCap Second Tier Buttons -->
   <div class="sellerdropdown hide" id="humancapdropdown" >

@if ($globalAuth->web_module('stf'))
    <a href="javascript:void(0);" id="staffbuttona" class="sellerab">
      <button class="btn btn-success btn-log bg-staff sellerbutton"
        style="padding-left:10px;font-size:13px" id="staffbutton" >
        <span>Staff</span>
      </button>
    </a>
@endif
@if ($globalAuth->web_module('schd'))
     <a  id="schedulerbuttona" class="sellerab">
       <button class="btn btn-success btn-log bg-scheduler sellerbutton"
         style="padding-left:7px;font-size:12px" id="schedulerbutton">
         <span>Scheduler</span>
       </button>
     </a>
@endif
@if ($globalAuth->web_module('attd'))
     <a href="javascript:void(0);" id="attendancebuttona" class="sellerab">
       <button class="btn btn-success btn-log bg-attendance sellerbuttontwo"
         style="padding-left:4px;font-size:12px" id="attedancebutton">
         <span>Attendance<br>&nbsp;&nbsp;Report</span>
       </button>
     </a>
@endif
   </div>
@endif


@if ($globalAuth->web_module('snm'))
   <!-- Service & Maintenance Second Tier Buttons -->
   <div class="sellerdropdown hide" id="servicedropdown" >
@if ($globalAuth->web_module('ast'))
     <a href="javascript:void(0)" id="assetbuttona" class="sellerab">
       <button class="btn btn-success btn-log bg-tts sellerbutton"
         style="padding-left:10px;font-size:13px" id="assetbutton" onclick="">
         <span>Asset</span>
       </button>
     </a>
@endif
@if ($globalAuth->web_module('tts'))
     <a href="javascript:void(0)" id="ttsbuttona" class="sellerab">
       <button class="btn btn-success btn-log bg-tts sellerbutton"
         style="padding-left:10px;font-size:13px" id="ttsbutton">
         <span>TTS</span>
       </button>
     </a>
@endif
{{--
@if ($globalAuth->web_module('repr'))
     <a href="javascript:void(0)" id="repairbuttona" class="sellerab">
       <button class="btn btn-success btn-log bg-repair sellerbutton"
         style="padding-left:10px;font-size:13px" id="repairbutton">
         <span>Repair</span>
       </button>
     </a>
@endif
@if ($globalAuth->web_module('wrnt'))
     <a href="javascript:void(0)" id="warrantybuttona" class="sellerab">
       <button class="btn btn-success btn-log bg-warranty sellerbutton"
         style="padding-left:8px;font-size:13px" id="warrantybutton">
         <span>Warranty</span>
       </button>
     </a>
@endif
--}}
@if ($globalAuth->web_module('cmr'))
     <a href="javascript:void(0)" id="cmrbuttona" class="sellerab">
       <button class="btn btn-success btn-log bg-warranty sellerbuttonwide"
         style="padding-left:0;padding-right:0;padding-top:7px;padding-bottom:7px;
		 	font-size:13px" id="cmrbutton">
         <span>Corrective<br>Maintenance Report</span>
       </button>
     </a>
@endif
{{--
@if ($globalAuth->web_module('csr'))
     <a href="javascript:void(0)" id="csrbuttona" class="sellerab">
       <button class="btn btn-success btn-log bg-warranty sellerbuttonwide"
         style="padding-left:0;padding-right:0;padding-top:7px;padding-bottom:7px;
		 	font-size:13px" id="csrbutton">
         <span>Crane<br>Service Report</span>
       </button>
	</a>
@endif
@if ($globalAuth->web_module('cpcr'))
     <a href="javascript:void(0)" id="cpcrbuttona" class="sellerab">
       <button class="btn btn-success btn-log bg-warranty sellerbuttonwide"
         style="padding-left:0;padding-right:0;padding-top:7px;padding-bottom:7px;
		 	font-size:13px" id="cpcrbutton">
         <span>Crane Preventive<br>Check Report</span>
       </button>
     </a>
@endif
--}}
   </div>
@endif


@if ($globalAuth->web_module('prdt'))
   <!-- Production Second Tier Buttons -->
   <div class="sellerdropdown hide" id="productiondropdown" >
     <a href="javascript:void(0)" id="projectmgmtbuttona" class="sellerab">
       <button class="btn btn-success btn-log bg-production sellerbuttonwide"
         style="padding-left:10px;font-size:13px" id="projectmgmtbutton">
         <span>Project<br>Management</span>
       </button>
     </a>
   </div>
@endif

 
@if ($globalAuth->web_module('data'))
   <!-- Data Second Tier Buttons -->
   <div class="sellerdropdown hide" id="datadropdown">
	@if ($globalAuth->web_module('dmgmt'))
     <a href="javascript:void(0)" id="datamanagementbuttona" class="sellerab">
       <button class="btn dropdownnn btn-success btn-log bg-data sellerbuttonwide"
         style="padding-left:10px;font-size:13px" id="datamanagementbutton">
         <span>Data Management</span>
       </button>
     </a>
	@endif
{{--
	@if ($globalAuth->web_module('alli'))
     <a href="javascript:void(0)" id="alliancebuttona" class="sellerab">
       <button class="btn btn-success btn-log bg-data sellerbutton"
         style="padding-left:12px;font-size:13px" id="alliancebutton">
         <span>Alliance</span>
       </button>
     </a>
	@endif
--}}
	@if ($globalAuth->web_module('umgmt'))
     <a href="javascript:void(0)" id="userbuttona" class="sellerab">
       <button class="btn btn-success btn-log bg-data sellerbutton"
         style="padding-left:10px;font-size:13px" id="userbutton">
         <span>User</span>
       </button>
     </a>
	@endif
	@if ($globalAuth->web_module('fnch'))
     <a href="javascript:void(0)" id="franchisebuttona" class="sellerab">
       <button class="btn btn-success btn-log bg-data sellerbutton"
         style="padding-left:0;padding-right:0;font-size:13px"
		 id="franchisebutton">
         <span>Franchise</span>
       </button>
     </a>
	@endif
	@if ($globalAuth->web_module('prcu'))
		<button class="btn btn-success btn-log bg-data sellerbuttonwide"
			style="padding-left:0;padding-right:0;font-size:13px"
			id="procurementbutton">
			<span>Procurement</span>
		</button>
	@endif
	@if ($globalAuth->web_module('csgn'))
     <a href="javascript:void(0)" id="mallbuttona" class="sellerab">
       <button class="btn btn-success btn-log bg-data sellerbuttonwide"
         style="padding-left:0;padding-right:0;font-size:13px"
		 id="mallbutton">
         <span>Consignment</span>
       </button>
     </a>
	@endif
   </div>
@endif



@if ($globalAuth->web_module('prod'))
   <!-- Product Second Tier Buttons -->
	<div class="sellerdropdown hide" id="productdropdown">

	@if ($globalAuth->web_module('pfuel'))
     <a href="javascript:void(0)" id="pfuelbuttona" class="sellerab">
       <button class="btn btn-success btn-log bg-product sellerbutton"
         style="padding-left:12px;font-size:13px" id="pfuelbutton">
         <span>Fuel</span>
       </button>
     </a>
	@endif

   </div>
@endif


@if ($globalAuth->web_module('ana'))
	<!-- Analytics Second Tier Buttons -->
	<div class="sellerdropdown hide" id="analyticsdropdown">
	@if ($globalAuth->web_module('cash'))
     <a href="javascript:void(0)" id="cashbuttona" class="sellerab">
       <button class="btn dropdownnn btn-success btn-log bg-analytics sellerbutton"
         style="padding-left:12px;font-size:13px" id="cashbutton">
         <span>General</span>
       </button>
     </a>
	@endif
	 <!--
     <a href="javascript:void(0)" id="creditbuttona" class="sellerab">
       <button class="btn dropdownnn btn-success btn-log bg-analytics sellerbuttontwo"
         style="padding-left:10px;font-size:13px" id="creditbutton">
         <span>Credit<br>Term</span>
       </button>
	</a>
	-->

	@if ($globalAuth->web_module('stk'))
     <a href="javascript:void(0)" id="stocklevelbuttona" class="sellerab">
       <button class="btn dropdownnn btn-success btn-log bg-analytics sellerbuttontwo"
         style="padding-left:10px;font-size:13px" id="stocklevelbutton">
         <span>Stock<br>Level</span>
       </button>
     </a> 
	@endif
	@if ($globalAuth->web_module('oper'))
     <a href="javascript:void(0)" id="operatorviewbuttona" class="sellerab">
       <button class="btn dropdownnn btn-success btn-log bg-analytics sellerbuttontwo"
         style="padding-left:8px;font-size:13px" id="operatorviewbutton">
         <span>Operator<br>View</span>
       </button>
     </a>    
	@endif
	@if ($globalAuth->web_module('job'))
     <a href="javascript:void(0)" id="jobdurationbuttona" class="sellerab">
       <button class="btn dropdownnn btn-success bg-analytics sellerbuttonwide"
         style="padding-left:8px;font-size:13px" id="jobdurationbutton">
         <span>Job<br>Management</span>
       </button>
     </a>    
	@endif
	</div>
@endif

 
@if ($globalAuth->web_module('vcab'))

   <!-- Virtual Cabinet Second Tier Buttons -->
   <div class="sellerdropdown hide" id="virtualcabinetdropdown">
@if ($globalAuth->web_module('auto'))
     <a href="javascript:void(0)" id="vcautobuttona" class="sellerab">
       <button class="btn dropdownnn btn-success btn-log bg-virtualcabinet sellerbutton"
         style="padding-left:10px;font-size:13px" id="vcautobutton">
         <span>Auto</span>
       </button>
     </a>
@endif
@if ($globalAuth->web_module('man'))
     <a href="javascript:void(0)" id="vcmanualbuttona" class="sellerab">
       <button class="btn btn-success btn-log bg-virtualcabinet sellerbutton"
         style="padding-left:12px;font-size:13px" id="vcmanualbutton">
         <span>Manual</span>
       </button>
     </a>
@endif
@if ($globalAuth->web_module('pgn'))
     <a href="javascript:void(0)" id="pigeonbuttona" class="sellerab">
       <button class="btn btn-success btn-log bg-virtualcabinet sellerbutton"
         style="padding-left:12px;font-size:13px" id="pigeonbutton">
         <span>Pigeon</span>
       </button>
     </a> 
@endif
<!-- need to set below $globalAuth->web_module -->
@if ($globalAuth->web_module('cstore'))
     <a href="javascript:void(0)" id="vccstorebuttona" class="sellerab">
       <button class="btn dropdownnn btn-success btn-log bg-virtualcabinet sellerbutton"
         style="padding-left:10px;font-size:13px" id="vccstorebutton">
         <span>C-Store</span>
       </button>
     </a>
@endif
@if ($globalAuth->web_module('fuel'))
     <a href="javascript:void(0)" id="vcfuelbuttona" class="sellerab">
       <button class="btn dropdownnn btn-success btn-log bg-virtualcabinet sellerbutton"
         style="padding-left:10px;font-size:13px" id="vcfuelbutton">
         <span>Fuel</span>
       </button>
     </a>
@endif
@if ($globalAuth->web_module('ev'))
     <a href="javascript:void(0)" id="vcelectricvehicalbuttona" class="sellerab">
       <button class="btn dropdownnn btn-success btn-log bg-virtualcabinet sellerbutton"
         style="padding-left:10px;font-size:13px" id="vcelectricvehicalbutton">
         <span>Electric Vehicle</span>
       </button>
     </a>
@endif
@if ($globalAuth->web_module('h2'))
     <a href="javascript:void(0)" id="vchydrogenbuttona" class="sellerab">
       <button class="btn dropdownnn btn-success btn-log bg-virtualcabinet sellerbutton"
         style="padding-left:10px;font-size:13px" id="vchydrogenbutton">
         <span>Hydrogen</span>
       </button>
     </a>
@endif
   </div>
@endif

@if ($globalAuth->web_module('ind'))
   <!-- Industry Second Tier Buttons -->
   <div class="sellerdropdown hide" id="industrydropdown">

@if ($globalAuth->web_module('oilg'))
       <button class="btn btn-success btn-log bg-industry sellerbuttonwide"
         style="padding-left:10px;font-size:13px" id="oilandgasbutton">
         <span>Oil & Gas</span>
       </button>
@endif
@if ($globalAuth->web_module('mall'))
       <button class="btn btn-success btn-log bg-industry sellerbutton"
         style="padding-left:10px;font-size:13px" id="mallButtonDrp">
         <span>Mall</span>
       </button>
@endif
{{--
@if ($globalAuth->web_module('autm'))
       <button class="btn btn-success btn-log bg-industry sellerbuttonwide"
         style="padding-left:10px;font-size:13px" id="autoMobileMotive_drp">
         <span>Automobile &<br>Automotive</span>
       </button>
@endif
--}}
@if ($globalAuth->web_module('ecom'))
       <button class="btn btn-success btn-log bg-industry sellerbuttonwide"
         style="padding-left:10px;font-size:13px" id="ecommercebutton">
         <span>Electronic<br>Commerce</span>
       </button>
@endif
{{--
@if ($globalAuth->web_module('insr'))
       <button class="btn btn-success btn-log bg-industry sellerbuttonwide"
         style="padding-left:10px;font-size:13px" id="insurancebrokeragebutton">
         <span>Insurance &<br>Brokerage</span>
       </button>
@endif
--}}

   </div>
@endif
</nav>
