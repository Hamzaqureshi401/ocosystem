<style>.btn-success.disabled, .btn-success:disabled {
    color: #fff;
    background-color: #a0a0a0;
    border-color: #a0a0a0;
}
</style>

<script>

$(document).ready(function(){
$('.navbar > button').attr("disabled", "on");

<?php if($globalAuth->web_module('data')): ?>

	<?php if($globalAuth->web_role('data')): ?>
	$("#databutton").removeAttr("disabled");

	<?php endif; ?>

	<?php if($globalAuth->web_module('umgmt')): ?>

		$( "#userbutton" ).click(function() {
			loadView("<?php echo e(route('landing.ajax.user')); ?>")
		});
		
	<?php endif; ?>
	<?php if($globalAuth->web_module('dmgmt')): ?>

		$( "#datamanagementbutton" ).click(function() {
			loadView("<?php echo e(route('landing.ajax.data')); ?>")
		});

	<?php endif; ?>
		
	<?php if($globalAuth->web_module('fnch')): ?>
		$('#franchisebuttona').click(function() {
			window.open(modified_url_fn("<?php echo e(route('data.ajax.franchiseManagement')); ?>"));
		});
	
	<?php endif; ?>

	<?php if($globalAuth->web_module('prcu')): ?>
		$('#procurementbutton').click(function() {
			window.open(modified_url_fn("<?php echo e(url('inventoryautoprocurement')); ?>"));
		});
	
	<?php endif; ?>



	<?php if($globalAuth->web_module('alli')): ?>

		$( "#alliancebutton" ).click(function() {
			loadView("<?php echo e(route('alliance.ajax.data')); ?>")
		});
	<?php endif; ?>
		
<?php endif; ?>
	
<?php if($globalAuth->web_module('stg')): ?>
	<?php if($globalAuth->web_role('stg')): ?>
		$('#settingbutton').click(function() {
			loadView("<?php echo e(route('landing.ajax.Setting')); ?>");
		});

		$("#settingbutton").removeAttr("disabled");

		$('#partnerbutton').click(function() {
			loadView("<?php echo e(route('landing.ajax.Commission')); ?>");
		});

	<?php endif; ?>	
<?php endif; ?>	

<?php if($globalAuth->web_module('loc')): ?>
	<?php if($globalAuth->web_role('loc')): ?>
		$('#locationbutton').click(function() {
			loadView("<?php echo e(route('landing.ajax.location')); ?>");
		});

		$("#locationbutton"). removeAttr("disabled");

	<?php endif; ?>	
<?php endif; ?>	

<?php if($globalAuth->web_module('prod')): ?>
	<?php if($globalAuth->web_role('prod')): ?>
		
		$('#productbutton').click(function() {
			loadView("<?php echo e(route('landing.ajax.product')); ?>");
		});
		
		$("#productbutton"). removeAttr("disabled");
	<?php endif; ?>	
<?php endif; ?>	
	
<?php if($globalAuth->web_module('ana')): ?>
	<?php if($globalAuth->web_role('ana')): ?>
		$("#analyticsbutton").removeAttr("disabled");
	<?php endif; ?>
	<?php if($globalAuth->web_module('cash')): ?>
		$("#cashbutton" ).click(function() {
			loadView("<?php echo e(route('landing.ajax.cash')); ?>")
		});
	<?php endif; ?>
	<?php if($globalAuth->web_module('stk')): ?>
		$("#stocklevelbutton" ).click(function() {
			loadView("<?php echo e(route('landing.ajax.stocklevel')); ?>")
		});
	<?php endif; ?>
	<?php if($globalAuth->web_module('oper')): ?>
		/*
		$("#creditbutton" ).click(function() {
			loadView("<?php echo e(route('landing.ajax.credit')); ?>")
		});
		*/

		$("#operatorviewbutton" ).click(function() {
			loadView("<?php echo e(route('landing.ajax.operatorview')); ?>")
		});
	<?php endif; ?>
	<?php if($globalAuth->web_module('job')): ?>

		$( "#jobdurationbutton" ).click(function() {
			loadView("<?php echo e(route('job_duration_mgmt')); ?>")
		});
 
	<?php endif; ?>	
<?php endif; ?>	

<?php if($globalAuth->web_module('rpt')): ?>
	<?php if($globalAuth->web_role('age')): ?>
		$("#reportbutton").removeAttr("disabled");
	<?php endif; ?>

	<?php if($globalAuth->web_module('rvpy')): ?>
		$('#ageingreportbutton').click(function() {
			loadView("<?php echo e(route('landing.ajax.ageing')); ?>");
		});
	<?php endif; ?>
	<?php if($globalAuth->web_module('isr')): ?>
		$("#isalesreportbutton" ).click(function() {
			loadView("<?php echo e(route('landing.ajax.ItemizedSalesRepor')); ?>")
		});
	<?php endif; ?>
	
	
<?php endif; ?>	

<?php if($globalAuth->web_module('vcab')): ?>
	<?php if($globalAuth->web_role('vcab')): ?>
		$("#virtualcabinetbutton").removeAttr("disabled");

	<?php endif; ?>
	<?php if($globalAuth->web_module('man')): ?>
		$('#vcmanualbutton').click(function() {
			loadView("<?php echo e(route('landing.ajax.vcmanual')); ?>");
		});
	<?php endif; ?>
	<?php if($globalAuth->web_module('auto')): ?>
		$('#vcautobutton').click(function() {
			loadView("<?php echo e(route('landing.ajax.vcauto')); ?>");
		});
	<?php endif; ?>
	<?php if($globalAuth->web_module('pgn')): ?>
		$('#pigeonbutton').click(function() {
			loadView("<?php echo e(route('landing.ajax.pigeon')); ?>");
		});
	<?php endif; ?>
	 //new routes addrd
    <?php if($globalAuth->web_module('cstore')): ?>
        $('#vccstorebutton').click(function() {
            loadView("<?php echo e(route('landing.ajax.cstore')); ?>");
        });
    <?php endif; ?>
    <?php if($globalAuth->web_module('fuel')): ?>
        $('#vcfuelbutton').click(function() {
            loadView("<?php echo e(route('landing.ajax.fuel')); ?>");
        });
    <?php endif; ?>
    <?php if($globalAuth->web_module('ev')): ?>
        $('#vcelectricvehicalbutton').click(function() {
            loadView("<?php echo e(route('landing.ajax.electricvehical')); ?>");
        });
    <?php endif; ?>
    <?php if($globalAuth->web_module('h2')): ?>
        $('#vchydrogenbutton').click(function() {
            loadView("<?php echo e(route('landing.ajax.hydrogen')); ?>");
        });
    <?php endif; ?>

<?php endif; ?>

<?php if($globalAuth->web_module('ind')): ?>

	<?php if($globalAuth->web_role('ind')): ?>
		$("#industrybutton").removeAttr("disabled");
	<?php endif; ?>
	
	<?php if($globalAuth->web_module('oilg')): ?>
		$('#oilandgasbutton').click(function () {
			window.open(modified_url_fn('/industry/oil-gas'))
		});
	<?php endif; ?>
	
	<?php if($globalAuth->web_module('mall')): ?>
		$('#mallButtonDrp').click(function () {
			window.open(modified_url_fn('/industry/mall'))
		});
	<?php endif; ?>

	<?php if($globalAuth->web_module('autm')): ?>
		$('#autoMobileMotive_drp').click(function () {
			window.open(modified_url_fn('/industry/food_beverage'))
		});
	<?php endif; ?>

	<?php if($globalAuth->web_module('ecom')): ?>
		$('#ecommercebutton').click(function () {
			window.open(modified_url_fn('/industry/ecommerce'))
		});
	<?php endif; ?>

	<?php if($globalAuth->web_module('insr')): ?>
		$('#insurancebrokeragebutton').click(function () {
			window.open(modified_url_fn('/industry/insurance_brokerage'))
		});
	<?php endif; ?>


<?php endif; ?>
	
<?php if($globalAuth->web_module('comm')): ?>
	<?php if($globalAuth->web_role('comm')): ?>
		$("#partnerbutton").removeAttr("disabled");
	<?php endif; ?>
<?php endif; ?>

		
<?php if($globalAuth->web_module('snm')): ?>
	<?php if($globalAuth->web_role('snm')): ?>
		$("#servicebutton").removeAttr("disabled");
	<?php endif; ?>
	<?php if($globalAuth->web_module('ast')): ?>

		$( "#assetbutton" ).click(function() {
			loadView("<?php echo e(route('asset_management_ajax_view')); ?>")
		});
	<?php endif; ?>
	<?php if($globalAuth->web_module('tts')): ?>
		$( "#ttsbutton" ).click(function() {
			loadView("<?php echo e(route('service_tts_view')); ?>")
		});
	<?php endif; ?>
	<?php if($globalAuth->web_module('repr')): ?>
		$( "#repairbutton" ).click(function() {
			loadView("<?php echo e(route('service_repair_view')); ?>")
		});
	<?php endif; ?>
	<?php if($globalAuth->web_module('wrnt')): ?>
		$( "#warrantybutton" ).click(function() {
			loadView("<?php echo e(route('servicewarranty_view')); ?>")
		});
	<?php endif; ?>
	<?php if($globalAuth->web_module('cmr')): ?>
		$( "#cmrbutton" ).click(function() {
			loadView("<?php echo e(route('cmr_view')); ?>")
		});
	<?php endif; ?>
	<?php if($globalAuth->web_module('csr')): ?>
		$( "#csrbutton" ).click(function() {
			loadView("<?php echo e(route('csr_view')); ?>")
		});
	<?php endif; ?>
	<?php if($globalAuth->web_module('cpcr')): ?>
		$( "#cpcrbutton" ).click(function() {
			loadView("<?php echo e(route('cpcr_view')); ?>")
		});
	<?php endif; ?>
<?php endif; ?>

	
<?php if($globalAuth->web_module('prdt')): ?>
	<?php if($globalAuth->web_role('prdt')): ?>

		$('#productionbutton').click(function() {
			loadView("<?php echo e(route('landing.ajax.production')); ?>")
		});

		$("#productionbutton").removeAttr("disabled");
	<?php endif; ?>
<?php endif; ?>
		
<?php if($globalAuth->web_module('dist')): ?>
	<?php if($globalAuth->web_role('dist')): ?>
		$("#distributionbutton").removeAttr("disabled");

		//logistics is part of distribution
        //updated to central admin
		$('#centraladminbutton').click(function() {
			loadView("<?php echo e(route('logistics.ajax.deliverycontrol')); ?>");
		});
        $('#logisticsbutton').click(function() {
            loadView("<?php echo e(route('landing.ajax.logistics')); ?>");
        });
		$("#warehousebutton" ).click(function() {
			loadView("<?php echo e(route('landing.ajax.warehouse')); ?>")
		});
	
		$("#dilverymanbutton" ).click(function() {
			loadView("<?php echo e(route('warehouse.ajax.deliveryman')); ?>")
		});

		$("#vehiclebutton" ).click(function() {
			window.open(modified_url_fn("<?php echo e(route('logistics.ajax.vehiclemanagement')); ?>"))
		});

	<?php endif; ?>
<?php endif; ?>

<?php if($globalAuth->web_module('ret')): ?>
	<?php if($globalAuth->web_role('ret')): ?>
		$("#retailbutton").removeAttr("disabled");
		$( "#membershipbutton" ).click(function() {
		  loadView("<?php echo e(route('retail_membership_view')); ?>")
		});
		
		$("#voucherbuttona").click(function () {
			loadView("<?php echo e(route('retailvoucher.ajax.index')); ?>")
		});
	     
		$('#opossumbuttona').click(function() {
			//loadView("<?php echo e(route('landing.ajax.opossum')); ?>");
			window.open(modified_url_fn("<?php echo e(route('landing.ajax.opossum')); ?>"));
		});
   	<?php endif; ?>
<?php endif; ?>

<?php if($globalAuth->web_module('crm')): ?>
	<?php if($globalAuth->web_role('crm')): ?>
		$("#btnEV").removeAttr("disabled");
		$('#btnEV').click(function() {
			loadView("<?php echo e(route('landing.ajax.evcharger')); ?>");
		});
	<?php endif; ?>
<?php endif; ?>

<?php if($globalAuth->web_module('humn')): ?>		
	<?php if($globalAuth->web_role('humn')): ?>		
		$("#humancapbutton").removeAttr("disabled");
		$("#attendancebuttona" ).click(function() {
			loadView("<?php echo e(route('hcapattendance.ajax.attendance')); ?>")
		});
		$("#staffbutton" ).click(function() {
			loadView("<?php echo e(route('hcapstaff_ajax_view')); ?>")
		});
	<?php endif; ?>
<?php endif; ?>

	/* Disable cursor:pointer in all disabled buttons */
	$('.navbar > button').each(function(idx) {
		if ($(this).attr('disabled') == 'disabled') {
			$(this).css('cursor','auto');
		}
	})


	$('#superadminbutton').click(function() {
		window.location = "<?php echo e(route('landing.ajax.superadmin')); ?>";
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
<?php /**PATH E:\ocosystem\resources\views/settings/buttonpermission.blade.php ENDPATH**/ ?>