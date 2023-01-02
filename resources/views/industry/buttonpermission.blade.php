<style>.btn-success.disabled, .btn-success:disabled {
    color: #fff;
    background-color: #a0a0a0;
    border-color: #a0a0a0;
}
</style>

<script>
$(document).ready(function(){
	
	$('.navbar > button').attr("disabled", "on");

	@if ($globalAuth->web_role('data'))
		$("#databutton").removeAttr("disabled");

		$( "#userbutton" ).click(function() {
			loadView("{{route('landing.ajax.user')}}")
		});

		$( "#datamanagementbutton" ).click(function() {
			loadView("{{route('landing.ajax.data')}}")
		});

		$( "#alliancebutton" ).click(function() {
			loadView("{{route('alliance.ajax.data')}}")
		});

	@elseif ($globalAuth->web_role('stg'))
		$('#settingbutton').click(function() {
			loadView("{{route('landing.ajax.Setting')}}");
		});

		$("#settingbutton").removeAttr("disabled");

	@elseif ($globalAuth->web_role('loc'))
		$('#locationbutton').click(function() {
			loadView("{{route('landing.ajax.location')}}");
		});

		$("#locationbutton"). removeAttr("disabled");

	@elseif ($globalAuth->web_role('prod'))
		$('#productbutton').click(function() {
			loadView("{{route('landing.ajax.product')}}");
		});
		$("#productbutton"). removeAttr("disabled");

	@elseif ($globalAuth->web_role( 'ana'))
		$("#analyticsbutton").removeAttr("disabled");
		$("#cashbutton" ).click(function() {
			loadView("{{route('landing.ajax.cash')}}")
		});
		$("#cashbutton" ).click(function() {
			loadView("{{route('landing.ajax.stocklevel')}}")
		});

		$("#cashbutton" ).click(function() {
			loadView("{{route('landing.ajax.credit')}}")
		});

		$("#operatorviewbutton" ).click(function() {
			loadView("{{route('landing.ajax.operatorview')}}")
		});
 
	@elseif ($globalAuth->web_role('age'))
		$('#reportbutton').click(function() {
			loadView("{{route('landing.ajax.ageing')}}");
		});

		$("#companyreportbutton" ).click(function() {
			loadView("{{route('landing.ajax.company_report')}}")
		});

		$("#isalesreportbutton" ).click(function() {
			loadView("{{route('landing.ajax.ItemizedSalesRepor')}}")
		});

		$("#reportbutton").removeAttr("disabled");

	@elseif ($globalAuth->web_role('vcab'))
		$("#virtualcabinetbutton").removeAttr("disabled");

		$('#vcmanualbutton').click(function() {
			loadView("{{route('landing.ajax.vcmanual')}}");
		});

		$('#vcautobutton').click(function() {
			loadView("{{route('landing.ajax.vcauto')}}");
		});

		$('#pigeonbutton').click(function() {
			loadView("{{route('landing.ajax.pigeon')}}");
		});
		// new route added 
		$('#vccstorebutton').click(function() {
			loadView("{{route('landing.ajax.cstore')}}");
		});
	
		$('#vcfuelbutton').click(function() {
			loadView("{{route('landing.ajax.fuel')}}");
		});
	
		$('#vcelectricvehicalbutton').click(function() {
			loadView("{{route('landing.ajax.electricvehical')}}");
		});
	
		$('#vchydrogenbutton').click(function() {
			loadView("{{route('landing.ajax.hydrogen')}}");
		});

	@elseif ($globalAuth->web_role('web'))
		$("#webbutton").removeAttr("disabled");

	@elseif ($globalAuth->web_role('prt'))
		$("#partnerbutton").removeAttr("disabled");

	@elseif ($globalAuth->web_role('snm'))
		$("#servicebutton").removeAttr("disabled");

		$( "#warrantybutton" ).click(function() {
			loadView("{{route('servicewarranty_view')}}")
		});

		$( "#ttsbutton" ).click(function() {
			loadView("{{route('service_tts_view')}}")
		});

		$( "#repairbutton" ).click(function() {
			loadView("{{route('service_repair_view')}}")
		});

	@elseif ($globalAuth->web_role('prdt'))
		$('#productionbutton').click(function() {
			loadView("{{route('landing.ajax.production')}}")
		});

		$("#productionbutton").removeAttr("disabled");

	@elseif ($globalAuth->web_role('dist'))
		$("#distributionbutton").removeAttr("disabled");

		//logistics is part of distribution
        //updated to central admin
		$('#centraladminbutton').click(function() {
			loadView("{{route('logistics.ajax.deliverycontrol')}}");
		});
        $('#logisticsbutton').click(function() {
            loadView("{{route('landing.ajax.logistics')}}");
        });

	@elseif ($globalAuth->web_role('ret'))
		$("#retailbutton").removeAttr("disabled");
		$( "#membershipbutton" ).click(function() {
		  loadView("{{route('retail_membership_view')}}")
		});
		
		$("#voucherbuttona").click(function () {
			loadView("{{route('retailvoucher.ajax.index')}}")
		});
	     
		$('#opossumbuttona').click(function() {
			//loadView("{{route('landing.ajax.opossum')}}");
			window.open("{{route('landing.ajax.opossum')}}");
		});
   

	@elseif ($globalAuth->web_role('crm'))
		$("#btnCRM").removeAttr("disabled");
		$('#btnCRM').click(function() {
			loadView("{{route('landing.ajax.crm')}}");
		});

	@elseif ($globalAuth->web_role('humn'))
		$("#humancapbutton").removeAttr("disabled");
		$("#attendancebuttona" ).click(function() {
			loadView("{{route('hcapattendance.ajax.attendance')}}")
		});
		$("#staffbutton" ).click(function() {
			loadView("{{route('hcapstaff_ajax_view')}}")
		});

	@endif

/* For King's Account: All buttons are accessible */
@if($is_king == true)
	//All rules above should be copied here
	$('#companyreportbutton').click(function() {
		loadView("{{route('report.ajax.company.directory')}}");
	});
	$('#ageingreportbutton').click(function() {
		loadView("{{route('landing.ajax.ageing')}}");
	});

	$('#vcmanualbutton').click(function() {
		loadView("{{route('landing.ajax.vcmanual')}}");
	});
	
	$("#isalesreportbutton" ).click(function() {
			loadView("{{route('landing.ajax.ItemizedSalesRepor')}}")
	});

	$('#vcautobutton').click(function() {
		loadView("{{route('landing.ajax.vcauto')}}");
	});
	// new route added 
	$('#vccstorebutton').click(function() {
		loadView("{{route('landing.ajax.cstore')}}");
	});
	
	$('#vcfuelbutton').click(function() {
		loadView("{{route('landing.ajax.fuel')}}");
	});
	
	$('#vcelectricvehicalbutton').click(function() {
		loadView("{{route('landing.ajax.electricvehical')}}");
	});
	
	$('#vchydrogenbutton').click(function() {
		loadView("{{route('landing.ajax.hydrogen')}}");
	});

	$( "#userbutton" ).click(function() {
		loadView("{{route('landing.ajax.user')}}")
	});

	$('#locationbutton').click(function() {
		loadView("{{route('landing.ajax.location')}}");
	});

	$('#opossumbuttona').click(function() {
		//loadView("{{route('landing.ajax.opossum')}}");
		window.open("{{route('landing.ajax.opossum')}}");
	});

    $('#franchisebuttona').click(function() {
        window.open("{{route('data.ajax.franchiseManagement')}}");
	});

	$('#mallbuttona').click(function() {
        window.open("{{route('data.ajax.showConsignment')}}");
    });


	$('#productbutton').click(function() {
		loadView("{{route('landing.ajax.product')}}");
	});

	$('#productionbutton').click(function() {
		loadView("{{route('landing.ajax.production')}}")
	});

	$('#centraladminbutton').click(function() {
        loadView("{{route('logistics.ajax.deliverycontrol')}}");
	});

	$('#logisticsbutton').click(function() {
		loadView("{{route('landing.ajax.logistics')}}");
	});

	$( "#datamanagementbutton" ).click(function() {
		loadView("{{route('landing.ajax.data')}}")
	});

	$('#settingbutton').click(function() {
		loadView("{{route('landing.ajax.Setting')}}");
	});

	$('#btnCRM').click(function() {
		loadView("{{route('landing.ajax.crm')}}");
	});

	$( "#warrantybutton" ).click(function() {
		loadView("{{route('servicewarranty_view')}}")
	});

	$( "#ttsbutton" ).click(function() {
		loadView("{{route('service_tts_view')}}")
	});

	$( "#repairbutton" ).click(function() {
		loadView("{{route('service_repair_view')}}")
	});

	$( "#membershipbutton" ).click(function() {
		loadView("{{route('retail_membership_view')}}")
	});

	$( "#membershipbutton" ).click(function() {
		loadView("{{route('retail_membership_view')}}")
	});

	$("#staffbutton" ).click(function() {
		loadView("{{route('hcapstaff_ajax_view')}}")
	});

	$( "#alliancebutton" ).click(function() {
		loadView("{{route('alliance.ajax.data')}}")
	});

	$("#voucherbuttona").click(function () {
		loadView("{{route('retailvoucher.ajax.index')}}")
	});
        
    $("#attendancebuttona" ).click(function() {
        loadView("{{route('hcapattendance.ajax.attendance')}}")
    });

	$("#schedulerbutton" ).click(function() {
		loadView("{{route('hcapscheduler.ajax.scheduler')}}")
	});

	$("#cashbutton" ).click(function() {
		loadView("{{route('landing.ajax.cash')}}")
	});

	$("#stocklevelbutton").click(function() {
		loadView("{{route('landing.ajax.stocklevel')}}")
	});

	$("#creditbutton" ).click(function() {
		loadView("{{route('landing.ajax.credit')}}")
	});

	$("#operatorviewbutton" ).click(function() {
		loadView("{{route('landing.ajax.operatorview')}}")
	});

 	$("#companyreportbutton" ).click(function() {
		loadView("{{route('landing.ajax.company_report')}}")
	});

 	$("#warehousebutton" ).click(function() {
		loadView("{{route('landing.ajax.warehouse')}}")
	});

	$('#pigeonbutton').click(function() {
		loadView("{{route('landing.ajax.pigeon')}}");
	});

	$(".navbar > button"). removeAttr("disabled");
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
