<style>.btn-success.disabled, .btn-success:disabled {
    color: #fff;
    background-color: #a0a0a0;
    border-color: #a0a0a0;
}
</style>

<script>

$(document).ready(function(){
$('.navbar > button').attr("disabled", "on");

@if ($globalAuth->web_module('data'))

	@if ($globalAuth->web_role('data'))
	$("#databutton").removeAttr("disabled");

	@endif

	@if ($globalAuth->web_module('umgmt'))

		$( "#userbutton" ).click(function() {
			loadView("{{route('landing.ajax.user')}}")
		});
		
	@endif
	@if ($globalAuth->web_module('dmgmt'))

		$( "#datamanagementbutton" ).click(function() {
			loadView("{{route('landing.ajax.data')}}")
		});

	@endif
		
	@if ($globalAuth->web_module('fnch'))
		$('#franchisebuttona').click(function() {
			window.open(modified_url_fn("{{route('data.ajax.franchiseManagement')}}"));
		});
	
	@endif

	@if ($globalAuth->web_module('prcu'))
		$('#procurementbutton').click(function() {
			window.open(modified_url_fn("{{ url('inventoryautoprocurement')}}"));
		});
	
	@endif



	@if ($globalAuth->web_module('alli'))

		$( "#alliancebutton" ).click(function() {
			loadView("{{route('alliance.ajax.data')}}")
		});
	@endif
		
@endif
	
@if ($globalAuth->web_module('stg'))
	@if ($globalAuth->web_role('stg'))
		$('#settingbutton').click(function() {
			loadView("{{route('landing.ajax.Setting')}}");
		});

		$("#settingbutton").removeAttr("disabled");

		$('#partnerbutton').click(function() {
			loadView("{{route('landing.ajax.Commission')}}");
		});

	@endif	
@endif	

@if ($globalAuth->web_module('loc'))
	@if ($globalAuth->web_role('loc'))
		$('#locationbutton').click(function() {
			loadView("{{route('landing.ajax.location')}}");
		});

		$("#locationbutton"). removeAttr("disabled");

	@endif	
@endif	

@if ($globalAuth->web_module('prod'))
	@if ($globalAuth->web_role('prod'))
		
		$('#productbutton').click(function() {
			loadView("{{route('landing.ajax.product')}}");
		});
		
		$("#productbutton"). removeAttr("disabled");
	@endif	
@endif	
	
@if ($globalAuth->web_module('ana'))
	@if ($globalAuth->web_role('ana'))
		$("#analyticsbutton").removeAttr("disabled");
	@endif
	@if ($globalAuth->web_module('cash'))
		$("#cashbutton" ).click(function() {
			loadView("{{route('landing.ajax.cash')}}")
		});
	@endif
	@if ($globalAuth->web_module('stk'))
		$("#stocklevelbutton" ).click(function() {
			loadView("{{route('landing.ajax.stocklevel')}}")
		});
	@endif
	@if ($globalAuth->web_module('oper'))
		/*
		$("#creditbutton" ).click(function() {
			loadView("{{route('landing.ajax.credit')}}")
		});
		*/

		$("#operatorviewbutton" ).click(function() {
			loadView("{{route('landing.ajax.operatorview')}}")
		});
	@endif
	@if ($globalAuth->web_module('job'))

		$( "#jobdurationbutton" ).click(function() {
			loadView("{{route('job_duration_mgmt')}}")
		});
 
	@endif	
@endif	

@if ($globalAuth->web_module('rpt'))
	@if ($globalAuth->web_role('age'))
		$("#reportbutton").removeAttr("disabled");
	@endif

	@if ($globalAuth->web_module('rvpy'))
		$('#ageingreportbutton').click(function() {
			loadView("{{route('landing.ajax.ageing')}}");
		});
	@endif
	@if ($globalAuth->web_module('isr'))
		$("#isalesreportbutton" ).click(function() {
			loadView("{{route('landing.ajax.ItemizedSalesRepor')}}")
		});
	@endif
	
	{{--
	@if ($globalAuth->web_module('crep'))
		$("#companyreportbutton" ).click(function() {
			loadView("{{route('landing.ajax.company_report')}}")
		});
	@endif

	@if ($globalAuth->web_module('grp'))
		$("#grouprelationshipbutton" ).click(function(event) {
			loadView("{{route('show-group-relationship')}}")
		});
	@endif	
	--}}
@endif	

@if ($globalAuth->web_module('vcab'))
	@if ($globalAuth->web_role('vcab'))
		$("#virtualcabinetbutton").removeAttr("disabled");

	@endif
	@if ($globalAuth->web_module('man'))
		$('#vcmanualbutton').click(function() {
			loadView("{{route('landing.ajax.vcmanual')}}");
		});
	@endif
	@if ($globalAuth->web_module('auto'))
		$('#vcautobutton').click(function() {
			loadView("{{route('landing.ajax.vcauto')}}");
		});
	@endif
	@if ($globalAuth->web_module('pgn'))
		$('#pigeonbutton').click(function() {
			loadView("{{route('landing.ajax.pigeon')}}");
		});
	@endif
	 //new routes addrd
    @if ($globalAuth->web_module('cstore'))
        $('#vccstorebutton').click(function() {
            loadView("{{route('landing.ajax.cstore')}}");
        });
    @endif
    @if ($globalAuth->web_module('fuel'))
        $('#vcfuelbutton').click(function() {
            loadView("{{route('landing.ajax.fuel')}}");
        });
    @endif
    @if ($globalAuth->web_module('ev'))
        $('#vcelectricvehicalbutton').click(function() {
            loadView("{{route('landing.ajax.electricvehical')}}");
        });
    @endif
    @if ($globalAuth->web_module('h2'))
        $('#vchydrogenbutton').click(function() {
            loadView("{{route('landing.ajax.hydrogen')}}");
        });
    @endif

@endif

@if ($globalAuth->web_module('ind'))

	@if ($globalAuth->web_role('ind'))
		$("#industrybutton").removeAttr("disabled");
	@endif
	
	@if ($globalAuth->web_module('oilg'))
		$('#oilandgasbutton').click(function () {
			window.open(modified_url_fn('/industry/oil-gas'))
		});
	@endif
	
	@if ($globalAuth->web_module('mall'))
		$('#mallButtonDrp').click(function () {
			window.open(modified_url_fn('/industry/mall'))
		});
	@endif

	@if ($globalAuth->web_module('autm'))
		$('#autoMobileMotive_drp').click(function () {
			window.open(modified_url_fn('/industry/food_beverage'))
		});
	@endif

	@if ($globalAuth->web_module('ecom'))
		$('#ecommercebutton').click(function () {
			window.open(modified_url_fn('/industry/ecommerce'))
		});
	@endif

	@if ($globalAuth->web_module('insr'))
		$('#insurancebrokeragebutton').click(function () {
			window.open(modified_url_fn('/industry/insurance_brokerage'))
		});
	@endif


@endif
	
@if ($globalAuth->web_module('comm'))
	@if ($globalAuth->web_role('comm'))
		$("#partnerbutton").removeAttr("disabled");
	@endif
@endif

		
@if ($globalAuth->web_module('snm'))
	@if ($globalAuth->web_role('snm'))
		$("#servicebutton").removeAttr("disabled");
	@endif
	@if ($globalAuth->web_module('ast'))

		$( "#assetbutton" ).click(function() {
			loadView("{{route('asset_management_ajax_view')}}")
		});
	@endif
	@if ($globalAuth->web_module('tts'))
		$( "#ttsbutton" ).click(function() {
			loadView("{{route('service_tts_view')}}")
		});
	@endif
	@if ($globalAuth->web_module('repr'))
		$( "#repairbutton" ).click(function() {
			loadView("{{route('service_repair_view')}}")
		});
	@endif
	@if ($globalAuth->web_module('wrnt'))
		$( "#warrantybutton" ).click(function() {
			loadView("{{route('servicewarranty_view')}}")
		});
	@endif
	@if ($globalAuth->web_module('cmr'))
		$( "#cmrbutton" ).click(function() {
			loadView("{{route('cmr_view')}}")
		});
	@endif
	@if ($globalAuth->web_module('csr'))
		$( "#csrbutton" ).click(function() {
			loadView("{{route('csr_view')}}")
		});
	@endif
	@if ($globalAuth->web_module('cpcr'))
		$( "#cpcrbutton" ).click(function() {
			loadView("{{route('cpcr_view')}}")
		});
	@endif
@endif

	
@if ($globalAuth->web_module('prdt'))
	@if ($globalAuth->web_role('prdt'))

		$('#productionbutton').click(function() {
			loadView("{{route('landing.ajax.production')}}")
		});

		$("#productionbutton").removeAttr("disabled");
	@endif
@endif
		
@if ($globalAuth->web_module('dist'))
	@if ($globalAuth->web_role('dist'))
		$("#distributionbutton").removeAttr("disabled");

		//logistics is part of distribution
        //updated to central admin
		$('#centraladminbutton').click(function() {
			loadView("{{route('logistics.ajax.deliverycontrol')}}");
		});
        $('#logisticsbutton').click(function() {
            loadView("{{route('landing.ajax.logistics')}}");
        });
		$("#warehousebutton" ).click(function() {
			loadView("{{route('landing.ajax.warehouse')}}")
		});
	
		$("#dilverymanbutton" ).click(function() {
			loadView("{{route('warehouse.ajax.deliveryman')}}")
		});

		$("#vehiclebutton" ).click(function() {
			window.open(modified_url_fn("{{route('logistics.ajax.vehiclemanagement')}}"))
		});

	@endif
@endif

@if ($globalAuth->web_module('ret'))
	@if ($globalAuth->web_role('ret'))
		$("#retailbutton").removeAttr("disabled");
		$( "#membershipbutton" ).click(function() {
		  loadView("{{route('retail_membership_view')}}")
		});
		
		$("#voucherbuttona").click(function () {
			loadView("{{route('retailvoucher.ajax.index')}}")
		});
	     
		$('#opossumbuttona').click(function() {
			//loadView("{{route('landing.ajax.opossum')}}");
			window.open(modified_url_fn("{{route('landing.ajax.opossum')}}"));
		});
   	@endif
@endif

@if ($globalAuth->web_module('crm'))
	@if ($globalAuth->web_role('crm'))
		$("#btnEV").removeAttr("disabled");
		$('#btnEV').click(function() {
			loadView("{{route('landing.ajax.evcharger')}}");
		});
	@endif
@endif

@if ($globalAuth->web_module('humn'))		
	@if ($globalAuth->web_role('humn'))		
		$("#humancapbutton").removeAttr("disabled");
		$("#attendancebuttona" ).click(function() {
			loadView("{{route('hcapattendance.ajax.attendance')}}")
		});
		$("#staffbutton" ).click(function() {
			loadView("{{route('hcapstaff_ajax_view')}}")
		});
	@endif
@endif

	/* Disable cursor:pointer in all disabled buttons */
	$('.navbar > button').each(function(idx) {
		if ($(this).attr('disabled') == 'disabled') {
			$(this).css('cursor','auto');
		}
	})


	$('#superadminbutton').click(function() {
		window.location = "{{route('landing.ajax.superadmin')}}";
	});

	function loadView(route) {
		$.ajax({
			url: route,
			type: 'GET',
			dataType: "html",
			success: function (response) {
				$("#landing-view").html(response);
			},
			error: function (e) {
				console.log('error', e);
			}
		});
	}
});
</script>
