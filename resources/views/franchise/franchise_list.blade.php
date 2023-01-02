@extends('layouts.layout')
@section('content')
<div id="landing-view">
    <style>
        .modal-content1 {
            width: 40%  !important;
            left: 200px;
        }
        .btns{
            border: 1px solid #a0a0a0;
            color: #a0a0a0
        }
        .btns:hover{
            background-color: transparent !important;
            color: #34dabb !important;
            width: 100%
        }
        /*.btns:enabled{
            color: #34dabb;
        }*/
        #location-datatable.dataTable tbody tr {
            border-color: rgba(26, 188, 156, 0.7) !important;
            background-color: rgba(26, 188, 156, -0.3) !important;
        }
        .btn-link:hover {
            text-decoration: none;
        }
        .btn-link {
            text-decoration: none;
        }

        #location-datatable.dataTable thead th, table.dataTable thead td {
            padding:0px;
            border-bottom: 0px;
        }

        #location-datatable.dataTable tbody th, #location-datatable.dataTable tbody td{
            padding:0px !important;
        }

        #location-datatable.dataTable.no-footer {
            border-bottom: 0px !important;
        }

        #location-datatable.dataTable tbody tr.selected {
            /*background-color: white !important;*/
            color:green !important;
        }

        #location-datatable.dataTable tbody tr.selected h5{
            font-weight: 600;
        }

        .btn-franchise-list-active{
            color:#34dabb;
            border:1px solid #34dabb;
            font-weight:bold;
        }
        .btn-active {
            color:#34dabb;
            border:1px solid #34dabb;
            font-weight:bold;
        }
        .btn-merchant-list{
            color:#a0a0a0;
            border:1px solid;
        }
        .btn-inactive{
            color:#a0a0a0;
            border:1px solid;
        }
        .vertical-center{
            vertical-align:middle !important;
			padding-top:2px !important;
			padding-bottom:2px !important;
        }
        .highlight-off:focus{
            outline:0 !important;
            box-shadow:0;
        }
        .selected{
            color:green;
            font-weight:bold
        }

		button[disabled=disabled] {cursor:not-allowed !important}
    </style>

    <div id="default-content">


        <div class="row py-2"
			style="padding-top:0 !important; padding-bottom:0 !important">
            <div class="col align-self-center" style="width:80%">
                <h2 class="mb-0">Franchisee List</h2>
            </div>
            <div class="col col-auto align-self-center" style="display: flex;align-items: center;" >
				<div style="width:250px;margin: 0px 35px;">
					<h5 class="m-0">{{$franchise_detail->name}}</h5>
					<h5 class="m-0">{{$franchise_detail->systemid}}</h5>
				</div>
			<!--a href="{{ route('promo.bundle_List.FranchiseeLanding') }}?f_id={{$franchise_detail->id}}" target="_blank"-->
            <button class="btn btn-success btn-log bg-define_promo sellerbutton pl-0 pr-0"
				onclick="promo_btn()" disabled="disabled"
				style="float:none; margin-right: 0px;" id="btn_promo_newTab">
                <span>Define<br>Promo</span>
            </button>
            <!--/a-->

			</div>
        </div>


        <table class="table table-bordered" id="franchise" style="width:100%;">
            <thead class="thead-dark">
            <tr>
                <th style="width:30px; vertical-align:middle">No</th>
                <th style="width:110px; vertical-align:middle">Merchant ID</th>
                <th style="vertical-align:middle">Merchant Name</th>
                <th style="width:65px; vertical-align:middle">Royalty</th>
                <th style="width:65px; vertical-align:middle">Location</th>
                <th style="width:65px; vertical-align:middle">Terminal</th>
                <th style="width:80px; padding-left:0;padding-right:0; vertical-align:middle;">
                    <button type="button" class="active_product highlight-off btn btn12 btns" data-status="none" id="all" style="width:70px" >
                        All
                    </button>
                </th>
            </tr>
            </thead>
            <tbody>
            <th style="text-align: center"></th>
            <th style="text-align: center"></th>
            <th> </th>
            <th style="width:65px; vertical-align:middle">Terminal</th>
            <th style="width:100px; vertical-align:middle;">
                <button type="button" class="active_product btn btn12 btns" id="all" style="width:100px" >
                    All
                </button>
            </th>
            </tbody>
        </table>
    </div>

    <div  class="modal fade" id="franchiseRoyaltyModal"
          tabindex="-1" role="dialog"
          aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="width:250px" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <form action="#" method="post">
                        <input  type="text"
                                style="width:90%; border: 1px solid #ddd;text-align:center; padding: 1px 5px 1px 4px"
                                name="franchise_royalty_name_edit" id="franchise_royalty_name_edit" value="0">
                        <span style="width:10%;text-align:center">%</span>
                        <input  type="hidden"
                                style="width:100%; border: 1px solid #ddd; padding: 10px"
                                name="franchise_edit_id" id="franchise_edit_id">
						<input  type="hidden"
                                style="width:100%; border: 1px solid #ddd; padding: 10px"
                                name="franchise_merchant_edit_id" id="franchise_merchant_edit_id">		
                    </form>
                </div>
            </div>
        </div>
    </div>

	<input  type="hidden"
                                style="width:100%; border: 1px solid #ddd; padding: 10px"
                                name="franchisemerchant_edit_id" id="franchisemerchant_edit_id">	
								
	<input  type="hidden"
                                style="width:100%; border: 1px solid #ddd; padding: 10px"
                                name="franchisemerchant_edit_id" id="franchisemerchantterms">							

    <div  class="modal fade" id="location" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered " role="document">
            <div class="modal-content bg-greenmidlobster">
                <div class="modal-header">
                    <h3 class="modal-title">Franchise Location</h3>
                </div>
                <div class="modal-body"></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="merchant-location-modal" tabindex="-1" role="dialog" aria-hidden="true" >
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content bg-greenlobster">
                <div class="modal-header">
                    <h3 style="margin-bottom:4px">Franchise Location</h3>
                </div>
                <div class="modal-body">
                    <table id="location-datatable" style="width:100%;">
                        <thead>
                        <tr>
                            <th></th>
                        </tr>
                        </thead>
                    </table>
                </div>
				<input  type="hidden"
                                value="0"
                                name="terminals_no" id="terminals_no">
            </div>
        </div>
    </div>
    <div id="showEditInventoryModal"></div>

    <div class="modal fade" id="msgModal"  tabindex="-1"
         role="dialog" aria-labelledby="staffNameLabel"
         aria-hidden="true" style="text-align: center;">

        <div class="modal-dialog modal-dialog-centered  mw-75 w-50"
             role="document" style="display: inline-flex;">
            <div class="modal-content modal-inside bg-greenlobster"
                 style="width: 100%;">
                <div class="modal-header" style="border:0">&nbsp;</div>
                <div class="modal-body text-center">
                    <h5 class="mb-0 modal-title text-white"
                        id="status-msg-element"></h5>
                </div>
                <div class="modal-footer" style="border:0">&nbsp;</div>
            </div>
        </div>
    </div>
	<br><br>
</div>
@section('js')

    <script type="text/javascript">

		$(document).ready(function() {
		  $(window).keydown(function(event){
			if(event.keyCode == 13) {
			  event.preventDefault();
			  return false;
			}
		  });
		});
			
	
        $("#newTab").click(function(){
            var go_to_url = 'franchise-terminal-list'
            window.open(go_to_url, '_blank');
        });

        var ownerUserId = '4';
        var merchantLinkId = '';
        var merchantLinkRelationId = '';
        var merchantUserId = '';
        var merchantId;
        var selectedUserId;
        var companyId;
        var franchiseId = {{$id}}
        var franchiseDataTable = $('#franchise').DataTable({
			pageLength: 10,
			bPaginate: true,
			info: false,
			ordering: true,
			responsive: true,
			processing: true,
			serverSide: true,
			bFilter: true,
			ajax: "{{route('data.ajax.FranchiseMerchants', $id )}}",
			initComplete: function(setting, json) {
			},
			columns: [
				{
					// no
					className: "text-center  vertical-center",
					mRender: function(data, type, full) {
						return full.indexNumber;
					}
				},
				{
					// system id
					className: "text-center  vertical-center",
					mRender: function(data, type, full) {
						return full.company_system_id == null ? '-' : full.company_system_id;
					}
				},

				{
					// company name
					className: "text-left  vertical-center",
					mRender: function(data, type, full) {
						return full.company_name;
					}

				},
				{
					// Royalty name
					className: "text-center  vertical-center",
					mRender: function(data, type, full) {
						t = full.merchant_royalty;
						if(full.merchant_royalty==null || full.merchant_royalty==""){
							t =  '0';
						}
						if(full.status=='active'){
							return '<a href="javascript:void(0)" style="text-decoration: none;" data-franchise-id="'+franchiseId+'" data-id="'+full.merchant_id+'" data-value="'+t+'" class="btn-link os-linkcolor js-royalty-location">'+t+'</a>';
						} else {
								return t;
						}

					}
				},
				{
					// location
					className: "text-center  vertical-center",
					mRender: function(data, type, full) {
						var template;
						t = full.franchise_merchant_locations.length;
						w = full.franchiseMerchantLocTermResult.length
						if(full.status=='active'){
							template = '<a href="javascript:void(0)" style="text-decoration: none;" data-row-type="'+
							full.row_type+'" data-merchantlink-relation-id="'+
							full.franchisemerchant_id+'" data-merchantlink-w="'+
							w+'"  data-merchant-id="'+
							full.merchant_id+'" data-locationterm-ids="'+
							full.franchiseMerchantLocTermResult+'" data-location-ids="'+
							full.franchise_merchant_locations+'" class="btn-link os-linkcolor js-merchant-location">'+t+'</a>';
						} else {
							template = t;
						}

						return template;
					}
				},
				{
					// terminal
					className: "text-center  vertical-center",
					mRender: function(data, type, full) {
						t = full.franchiseMerchantLocTermResult.length;
						
						var template;
						if(full.status=='active'){
							template = '<a href="{{URL::to("/")}}/dm/franchise-terminal-list?franchisemerchant_id='+full.franchisemerchant_id+'" target="_blank" style="text-decoration: none;" class="btn-link os-linkcolor">'+t+'</a>';
						} else {
							template = t;
						}
							if (t > 0) {
								enable_promo();
							}

						return template;
					}
				},
				{
					// All
					className: "text-center vertical-center",
					mRender: function(data, type, full) {
                        console.log("full", full);
                        if (full.franchise_has_transaction != 0) {
                            return '<button type="button" style="visibility: hidden;" id="'+full.company_id+'" class="btn btn-default btn-merchant-list" data-status="active" data-franchise-id="'+franchiseId+'" data-id="'+full.company_id+'">Active</button>';
                        }
						if(full.status=='active'){
                            console.log("pintaaaa",full.status);
                            return '<button type="button" style="width:70px" id="'+full.company_id+'" class="btn btn-default btn-franchise-list-active" data-status="active" data-franchise-id="'+franchiseId+'" data-id="'+full.company_id+'">Active</button>';
                        }else{
                            console.log("pintaaaa2",full.status);
                            return '<button type="button" style="width:70px" id="'+full.company_id+'" class="btn btn-default btn-merchant-list" data-status="inactive" data-franchise-id="'+franchiseId+'" data-id="'+full.company_id+'">Active</button>';
                        }
					}
				},

			],
			/*
			"order": [0, 'desc'],
			*/
			"columnDefs": [{'orderable' : false },
				{orderable: false, targets: [6]},
			]

		});

        
        $('#franchiseRoyaltyModal').on('hidden.bs.modal', function (e) {
			disable_promo();
			var merchantId = $("#franchise_merchant_edit_id").val();
			var franchisId = $("#franchise_edit_id").val();
			var royalty = $("#franchise_royalty_name_edit").val();
            $.ajax({
                url:'{{route('data.ajax.updateFranchiseMerchant')}}',
                type: 'POST',
                data: {
                    franchisId: franchisId,
                    merchantId: merchantId,
                    royalty: royalty
                },

                success: function(response) {
                    if (response.status === 'true') {
                        $("#franchise_royalty_name_edit").val(royalty);
						franchiseDataTable.ajax.reload();
                    }
                }
            });				
		});
		
        $("#franchise").on('click', '.btn-merchant-list, .btn-franchise-list-active',function(){
			disable_promo() 
            console.log('entroooo aquiii');
			$(this).toggleClass('btn-merchant-list btn-franchise-list-active');
            var status = $(this).attr("data-status");
			if(status == 'active'){
				status = 'inactive';
			} else {
				status = 'active';
			}
			var merchantId = $(this).attr("data-id");
			var franchisId = $(this).attr("data-franchise-id");
            change_status(this,status);
			$.ajax({
                url:'{{route('data.ajax.updateFranchiseMerchant')}}',
                type: 'POST',
                data: {
                    franchisId: franchisId,
                    merchantId: merchantId,
                    status: status
                },

                success: function(response) {
                    if (response.status === 'true') {
						franchiseDataTable.ajax.reload();
						$("#all").attr("data-status",'none');
                    }
                }
            });	
           
            
        })


        $("#all").click(function(){
            var status = $(this).attr("data-status");
            if(status == "none" || status == 'inactive') {
                $(this).attr("data-status",'active');
                $(this).addClass('btn-active');
                $(this).removeClass('btn-inactive');
                $('.btn-merchant-list, .btn-franchise-list-active').each(function(){
                    $(this).addClass('btn-franchise-list-active');
                    $(this).removeClass('btn-merchant-list');
                    change_status(this,'active');
                })

            } else {
                $(this).attr("data-status",'inactive');
                $(this).removeClass('btn-active');
                $(this).addClass('btn-inactive');
                $('.btn-merchant-list, .btn-franchise-list-active').each(function(){
                    $(this).removeClass('btn-franchise-list-active');
                    $(this).addClass('btn-merchant-list');
                    change_status(this,'inactive');
                })
            }
        })

        function change_status(elem,status) {
            console.log("HOLAAAAA",status);
            var id = $(elem).attr("data-id");
            $(elem).attr("data-status",status);
        }
		
		var terminals = 0;
        var setMerchantObj = "null";
		var current_locations = [];
        $("#franchise").on('click', '.js-merchant-location', function(){
            setMerchantObj = this;
            merchantId = $(this).data('merchant-id');
            current_locations = $(this).data('locationterm-ids');
			//console.log(current_locations);
            franchismerchantId = $(this).data('merchantlink-relation-id');
			terminals = $(this).data('merchant-id');
			w = $(this).data('merchantlink-w');
			console.log("IDDDDD");
			console.log(w);
			$("#franchisemerchant_edit_id").val(franchismerchantId);
			$("#franchisemerchantterms").val(current_locations);
			$("#terminals_no").val(w);
			console.log("TERMINALS", w);
            locationIds = $(this).data('location-ids').toString().split(',');
            $('#location-datatable tbody tr').removeClass('selected');
            for(var i = 0; i < locationIds.length; i++){
                if (locationIds[i] != '') {
                    $('#location-datatable').find('[data-id="'+locationIds[i]+'"]').parents('tr').addClass('selected');
                }
            }
            $("#merchant-location-modal").modal('show');
        });

        function changeCount(obj){
			/*	if(setMerchantObj != "null"){
					addNum = $(obj).parent().parent().hasClass('selected') ? -1 : 1;
					counter = parseInt($('#location-datatable tbody tr.selected').length) + parseInt(addNum);
					$(setMerchantObj).text(counter);
				}*/
        }
		
        $("#franchise").on('click', '.js-royalty-location', function(){
            $("#franchiseRoyaltyModal").modal('show');
            console.log($(this));
			var merchantId = $(this).attr("data-id");
			var franchisId = $(this).attr("data-franchise-id");
			var royalty = $(this).attr("data-value");
			$("#franchise_edit_id").val(franchisId);
			$("#franchise_merchant_edit_id").val(merchantId);
			$("#franchise_royalty_name_edit").val(royalty);
            $.ajax({
                url:'{{route('data.ajax.getFranchiseRoyalty')}}',
                type: 'POST',
                data: {
                    franchisId: franchisId,
                    merchantId: merchantId,
                    royalty: royalty
                },

                success: function(response) {
                    if (response.status === 'true') {
                        $("#franchiseRoyaltyModal").modal('show');	
					//	var royalty = $(this).attr("data-value");						
                    }
                }
            });
        });
		locationDatatable = undefined;
        locationDatatable = $('#location-datatable').DataTable({
            "paging":   false,
            "ordering": false,
            "info":     false,
            "searching": false,
            "ajax": "{{route('data.ajax.getLocations')}}",
            "initComplete": function(setting, json) {
				else_ = false;
	
				if (locationDatatable != undefined) {
					if ( ! locationDatatable.data().any() ) {
						locationDatatable.destroy();
					}	else {
						else_ = true;
					}
				} else {
					else_ = true;
				}

			   	if (else_ = true)	{
					$('#location-datatable tbody').on( 'click', 'tr', function () {
						/*// $('#location-datatable tbody tr').removeClass('selected');
						var ww = $("#terminals_no").val();
						//console.log("Terminal!!!!");
						//console.log(ww);
						current_locations
						if(ww == 0){
							$(this).toggleClass('selected');
						}*/
						var current_locations = $("#franchisemerchantterms").val();
						
						var current_locations = current_locations.split(",");
					//	console.log(current_locations[1]);
					//	console.log($(this).find('td h5[data-id="'+current_locations[1]+'"]').length);
						var inarr = true;
						for(var q = 0; q < current_locations.length; q++){	
							if ($(this).find('td h5[data-id="'+current_locations[q]+'"]').length > 0) {
								inarr = false;
							}
						}
						if(inarr){
							$(this).toggleClass('selected');
						}
					//	console.log();
						
					});
				}

            },
            columns: [
                {
                    // branch
                    mRender: function(data, type, full) {
						//var ww = $("#terminal_no").val();
						//console.log(full);
                        var branch = full.branch == null ? 'Branch' : full.branch;
                        return '<h5 style="margin-bottom:5px;margin-top:5px;cursor:pointer;" data-id="'+full.id+'" onclick="changeCount(this)">'+branch+'</h5>';
                    }
                },

            ]
        });


        $('#merchant-location-modal').on('hidden.bs.modal', function (e) {
            var selectedLocationIds = [];

            locationDatatable.rows('.selected').data().each(function(location, index){
                selectedLocationIds.push(location.id);
            });
			var merchantId = $("#franchisemerchant_edit_id").val();
			console.log(merchantId);
            $.ajax({
                url:'{{route('data.ajax.saveFranchiseMerchantLocation')}}',
                type: 'post',
                data: {
                    locationIds: selectedLocationIds,
                    merchantId: merchantId,
                },
                dataType:'json',
                success: function(response) {
					var terminals = $("#terminals_no").val();
					console.log(terminals);
						if (response.status === 'true') {
							displayStatusMsgPopup('Franchise locations assigned successfully');
							if ($.fn.DataTable.isDataTable('#franchise') ) {
								$("#franchise").DataTable().ajax.reload(null, false );
							}
						}
						else{
							displayStatusMsgPopup('Something went wrong, try again');
							if ($.fn.DataTable.isDataTable('#franchise') ) {
								$("#franchise").DataTable().ajax.reload(null, false );
							}
						}
                }
            });

        });

        function displayStatusMsgPopup(msg) {
            $("#status-msg-element").text(msg);
            $("#msgModal").modal('show');
            setTimeout(function() {
                $("#msgModal").modal('hide');
                $('.modal-backdrop').remove();
            },3500);
        }
		function promo_btn() {
			url  = modified_url_fn("{{ route('promo.bundle_List.FranchiseeLanding') }}?f_id={{$franchise_detail->id}}");
			window.open(url, '_blank');
		}
		function disable_promo() {
			$("#btn_promo_newTab").attr('disabled',true);
		}

		function enable_promo() {
			$("#btn_promo_newTab").removeAttr('disabled');
			console.log("Promo Enable");
		}
		/*setInterval(function() {
			//console.log("HELLO");
			 franchiseDataTable.ajax.reload();
		},3500);*/
    </script>
    
@include('settings.buttonpermission')
@endsection
@endsection
