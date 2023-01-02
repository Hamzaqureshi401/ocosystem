<style>
	.btn-gray {
		color: #fff !important;
		background-color: #a0a0a0 !important;
		border-color: #a0a0a0 !important;
	}
</style>

<div class="row"  style="margin-bottom:0;">
	<div class="col-md-6" style="display:flex;align-items:center" >
		<h2 class="mb-0">Dealer</h2>
	</div>
	@include('data.databuttons')
</div>
<div class="table-responsive" style="overflow-x: hidden;">
	<table class="table table-bordered " id="dealer_tbl">
	<thead>
	<tr class="bg-data">
		<th class="text-center" style="width:25px">No</th>
		<th class="text-center" style="width:25px;">Merchant&nbsp;ID</th>
		<th class="text-center" style="width:25px;">Business&nbsp;Reg.&nbsp;No.</th>
		<th class="text-left"   style="width:auto">Company Name</th>
		<th class="text-center" style="width:25px;">Procured</th>
		<th class="text-center" style="width:25px;">Consign</th>
		<th class="text-center" style="width:25px;">Credit&nbsp;Limit</th>
		<th class="text-center" style="width:100px;">Goods&nbsp;Deliver&nbsp;From</th>
		<th class="text-center" style="">Status</th>
		<th class="text-center pl-0 pr-0" style=""></th>
		<th class="text-center pl-0 pr-0" style=""></th>
	</tr>
	</thead>

	<tbody>
	</tbody>

	</table>
</div>


<div class="modal fade statusChangeAlertModal3"  tabindex="-1"
	id="js-ow-status-activate-modal" role="dialog"
	aria-labelledby="staffNameLabel" aria-hidden="true"
	style="text-align: center;">

    <div class="modal-dialog modal-dialog-centered  mw-75 w-50"
         role="document" style="display: inline-flex;">
        <div class="modal-content modal-inside bg-greenlobster">
            <div class="modal-header" style="border:none;"></div>
            <div class="modal-body text-center">
                <h5 class="modal-title text-white"
					style="margin-bottom:0px">Are you sure?
				</h5>
            </div>
            <div class="modal-footer"
				style="border:none;justify-content: center;">
                <div class="row" style="padding-left: 0px;padding-right:0px">
                    <button class="btn bg-primary primary-button" data-attr=""
                            style="width:100px"
                            onclick="owStatusActivate()">Activate</button>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade statusChangeAlertModal1"  tabindex="-1"
	id="js-ow-status-deactivate-modal" role="dialog"
	aria-labelledby="staffNameLabel" aria-hidden="true"
	style="text-align: center;">

    <div class="modal-dialog modal-dialog-centered  mw-75 w-50"
         role="document" style="display: inline-flex;">
        <div class="modal-content modal-inside bg-greenlobster">
            <div class="modal-header" style="border:none;"></div>
            <div class="modal-body text-center">
                <h5 class="modal-title text-white" style="margin-bottom:0px">
                    Are you sure?
				</h5>
            </div>
            <div class="modal-footer"
                 style="border:none;justify-content: center;">
                <div class="row" style="padding-left: 0px;padding-right:0px">
                    <button class="btn btn-danger deactivate"
						data-attr=""
						onclick="owStatusDeactivate()">Deactivate
					</button>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade statusChangeAlertModal2" id="ow-approve-reject-modal"
     tabindex="-1"	role="dialog" aria-labelledby="staffNameLabel"
     aria-hidden="true" style="text-align: center;">

    <div class="modal-dialog modal-dialog-centered  mw-75 w-50"
         role="document" style="display: inline-flex;">
        <div class="modal-content modal-inside bg-greenlobster">
            <div class="modal-header" style="border:none;"></div>
            <div class="modal-body text-center">
                <h5 class="modal-title text-white"
					style="margin-bottom:0px">Are you sure?
				</h5>
            </div>
            <div class="modal-footer"
				style="border:none;justify-content: center;">
                <div class="row" style="padding-left: 0px;padding-right:0px">
                    <button class="btn bg-primary primary-button deactivate mr-2"
						style="width:100px" data-attr=""
						onclick="owApproveLinking()">Approve</button>

                    <button class="btn btn-danger" data-dismiss="modal"
						style="width:100px"
						onclick="owRejectLinking()">Reject</button>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade statusChangeAlertModal3"  tabindex="-1"
	id="js-status-activate-modal" role="dialog"
	aria-labelledby="staffNameLabel" aria-hidden="true"
	style="text-align: center;">

    <div class="modal-dialog modal-dialog-centered  mw-75 w-50"
         role="document" style="display: inline-flex;">
        <div class="modal-content modal-inside bg-greenlobster">
            <div class="modal-header" style="border:none;"></div>
            <div class="modal-body text-center">
                <h5 class="modal-title text-white"
					style="margin-bottom:0px">Are you sure?
				</h5>
            </div>
            <div class="modal-footer" style="border:none;justify-content: center;">
                <div class="row" style="padding-left: 0px;padding-right:0px">
                    <button class="btn bg-primary primary-button" data-attr=""
						style="width:100px"
						onclick="statusActivate()">Activate
					</button>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- status update  modal pop up starts here-->
<div class="modal fade statusChangeAlertModal1"  tabindex="-1" id="js-status-deactivate-modal"	role="dialog" aria-labelledby="staffNameLabel"	aria-hidden="true" style="text-align: center;">

	<div class="modal-dialog modal-dialog-centered  mw-75 w-50"
		role="document" style="display: inline-flex;">
		<div class="modal-content modal-inside bg-greenlobster"> 
			 <div class="modal-header" style="border:none;"></div>
            <div class="modal-body text-center">
            	<h5 class="modal-title text-white" style="margin-bottom:0px">Are you sure?</h5>
            </div>
			<div class="modal-footer" style="border:none;justify-content: center;">
			<div class="row" style="padding-left: 0px;padding-right:0px">
				<button class="btn btn-danger deactivate"
				style="width:100px"
				data-attr="" onclick="statusDeactivate()">Deactivate</button>
			</div>
		</div>
		</div>
	</div>
	</div>

    <div class="modal fade statusChangeAlertModal2" id="approve-reject-modal"  tabindex="-1"	role="dialog" aria-labelledby="staffNameLabel"	aria-hidden="true" style="text-align: center;">

        <div class="modal-dialog modal-dialog-centered  mw-75 w-50"
             role="document" style="display: inline-flex;">
            <div class="modal-content modal-inside bg-greenlobster">
                <div class="modal-header" style="border:none;"></div>
                <div class="modal-body text-center">
                    <h5 class="modal-title text-white" style="margin-bottom:0px">
					Are you sure?</h5>
                </div>
                <div class="modal-footer" style="border:none;justify-content: center;">
                    <div class="row" style="padding-left: 0px;padding-right:0px">
						<button class="btn bg-primary primary-button deactivate mr-2"
								style="width:100px"
								data-attr="" onclick="approveLinking()">Approve</button>
						<button class="btn btn-danger" data-dismiss="modal"
								style="width:100px"
								onclick="rejectLinking()">Reject</button>
					</div>
                </div>
            </div>
        </div>
    </div>

	<div class="modal fade statusChangeAlertModal3"  tabindex="-1" id="js-status-activate-modal" role="dialog" aria-labelledby="staffNameLabel"	aria-hidden="true" style="text-align: center;">

	<div class="modal-dialog modal-dialog-centered  mw-75 w-50"
		role="document" style="display: inline-flex;">
		<div class="modal-content modal-inside bg-greenlobster">
			<div class="modal-header" style="border:none;"></div>
            <div class="modal-body text-center">
            	<h5 class="modal-title text-white" style="margin-bottom:0px">Are you sure?</h5>
            </div>
			<div class="modal-footer" style="border:none;justify-content: center;">
			<div class="row" style="padding-left: 0px;padding-right:0px">
				<button class="btn bg-primary primary-button deactivate"
				style="width:100px"
				data-attr="" onclick="statusActivate()">Activate</button>
			</div>
		</div>
		</div>
	</div>
	</div>

<!-- status update  modal pop up ends here-->

<!-- credit limit  modal pop up starts here-->
<div class="modal fade creditLimitModal"  tabindex="-1"	role="dialog" aria-labelledby="staffNameLabel"	aria-hidden="true" style="text-align: center;">

	<div class="modal-dialog modal-dialog-centered  mw-75 w-50"
		role="document" style="display: inline-flex;">
		<div class="modal-content modal-inside bg-greenlobster" style="width: 80%;">
			 <div class="modal-header">
                 <h3 style="margin-bottom:0px">Credit Limit</h3>
            </div>
			<div class="modal-body" style="padding-top: 0px;">
			<div class="row" style="padding-bottom: 0px;padding-top:10px;">
				<div class="col-md-7"></div>
			</div>
			<div class="row" style="padding-bottom: 0px;padding-top: 0px">
				<div class="col-md-4 text-white" style="text-align:left">Credit Limit</div>
				<div class="col-md-3"></div>
				<div class="col-md-5">
                    <input type="text" placeholder="0.00" id="credit-limit" class="form-control" style="text-align: right!important;margin-left: 12px;">
                    <input type="hidden" id="buffer_main_price"  value="">
                </div>
			</div>
			<div class="row" style="padding-top: 5px;">
				<div class="col-md-6 text-white" style="text-align:left">Used Limit</div>
				<div class="col-md-6 text-right" id="used-limit">0.00</div>
			</div>
			<div class="container" style="border-bottom:1px solid #fff;"></div>
			<div class="row">
				<div class="col-md-6 text-white" style="text-align:left">Available Limit</div>
				<div class="col-md-6 text-right" id="available-limit"></div>
			</div>
		</div>
		</div>
	</div>
	</div>
<!-- credit limit modal pop up ends here-->


<div class="modal fade" id="deleteConfirmModel" tabindex="-1" role="dialog" aria-labelledby="deleteConfirmModel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered  mw-75 w-50" role="document">
		<div class="modal-content modal-inside bg-greenlobster">
			<div class="modal-header" style="border:0"></div>
			<div class="modal-body text-center">
				<h5 class="modal-title text-white"
					id="statusModalLabel">
					Do you want to permanently delete this link?</h5>
			</div>
			<div class="modal-footer"
				 style="border-top:0 none; padding-left: 0px; padding-right: 0px;">
				<div class="row"
					 style="width: 100%; padding-left: 0px; padding-right: 0px;">
					<div class="col col-m-12 text-center">
						<button type="button"
								class="btn bg-primary primary-button text-white"
								onclick="deleteLinking()"
								data-dismiss="modal">Yes</button>
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

<!-- blue crab  modal pop up starts here-->
<div class="modal fade dealerBlueCrabModal" tabindex="-1" role="dialog"
	aria-hidden="true">
<div class="modal-dialog modal-lg modal-dialog-centered"
	style="width:400px;">
	<div class="modal-content bg-greenlobster">
	<div class="modal-body"
		style="display:flex;justify-content:center">
		<div class="row">
			<div class="b-col" style="margin-left:0;" >
				<button href="{{url('/documentdealer')}}"
					onclick="transition_link()" id="create-transaction-link"
					style=""
					class="pl-0 pr-0 ml-0 mr-0 btn btn-success
						sellerbuttonwide bg-salesorder">
					Create<br>Transaction
				</button>
			</div> 

			<!--
			<div class="b-col" style="margin-lelft:5px">
				<button class="btn text-white sellerbutton bg-salesorder">Sales Order</button>
			</div>
			<div class="b-col" style="margin-left:5px;">
				<button class="btn text-white sellerbutton bg-invoice">Invoice</button>
			</div>
			<div class="b-col" style="margin-left:5px">
				<button class="btn text-white sellerbutton bg-debitnote">Debit Note</button>
			</div>
			<div class="b-col" style="margin-left:5px">
				<button class="btn  text-white sellerbutton bg-creditnote">Credit Note</button>
			</div>
			-->

			<div class="b-col pl-0 pr-0 mr-0" style="margin-left:5px;">
				<a href="#" onclick="linkToRouteReidrectPath()"
					id="tracking-report-link">
					<button class="b-0 p-0 btn btn-success
						sellerbutton bg-tracking" style="">
						Tracking Report
					</button>
				</a>
			</div>

			<!--
			<div class="b-col" style="margin-left:5px;">
				<button class="btn text-white sellerbuttonwide bg-consignment">
				Consignment&nbsp;Note</button>
			</div>
			-->

		</div>
	</div>
	</div>
</div>
</div>
<!-- blue crab modal pop up ends here-->

<style>
table.dataTable thead th, table.dataTable thead td { border: none !important}
</style>
<script>
	var merchantLinkId = '';
	var ownerUserId = '{{$user_id}}';
    var merchantLinkRelationId = '';
    var merchantUserId = '';
    var merchantId = '';
    var creditLimitId = '';
    var dealerTableRow;
    var companyId;
    var locationipaddr='{{$locationipaddr}}';

    $(document).ready(function () {
        dealerDataTable.draw();
    });

	var dealerDataTable = $('#dealer_tbl').DataTable({
		pageLength: 10,
		bPaginate: true,
		info: false,
		ordering: true,
		responsive: true,
		processing: true,
		serverSide: true,
		autoWidth: false,
		bFilter: true,
		ajax: "{{route('data.ajax.getDealerData' )}}",
		initComplete: function(setting, json) {

		},
		columns: [
			{
				// no
				className: "text-center",
				mRender: function(data, type, full) {
					return full.indexNumber;
				}
			},
			{
				// system id
				className: "text-center",
				mRender: function(data, type, full) {
					//var template = '<a href="javascript:void(0)" class="btn-link os-linkcolor">'+full.company_system_id+'</a>';
					//return template;
					return full.company_system_id == null ? '-' : full.company_system_id;
				}
			},
			{
				// business registration no
				className: "text-center",
				mRender: function(data, type, full) {
					var businessRegNo = full.company_business_reg_no == null ? '-' : full.company_business_reg_no;
					if (full.row_type == 'oneway') {
						var template = '<a href="javascript:void(0)" data-company-id="'+full.company_id+'" class="btn-link os-linkcolor js-business-reg">'+businessRegNo+'</a>';
					} else {
						template = businessRegNo;
					}
					return template;
				}
			},
			{
				// company name
				className: "text-left",
				mRender: function(data, type, full) {
					return full.company_name;
				}

			},
			{
				// procured
				className: "text-center",
				mRender: function(data, type, full) {
					var template = '<a href="/procured" class="btn-link os-linkcolor" target="blank">300</a>';
					return template;
				}
			},
			{
				// consignment
				className: "text-center",
				mRender: function(data, type, full) {
					var template = '<a href="/dm-consignment/'+full.merchant_id+'" class="btn-link os-linkcolor" target="_blank">50</a></td>';
					return template;
				}
			},
			{
				// credit limit
				className: "text-right",
				mRender: function(data, type, full) {
					if (full.owner_user_id != ownerUserId) {
						if (full.status == 'active') {
							var template = '<a href="javascript:void(0)" class="btn-link os-linkcolor js-credit-limit" data-merchant-id="'+full.merchant_id+'" data-credit-limit-id="'+full.credit_limit_id+'" data-avail-credit-limit="'+number_format(full.avail_credit_limit,2)+'">'+number_format(full.credit_limit,2)+'</a>';
							return template;
						} else {
							return number_format(full.credit_limit,2);
						}
					} else {
						var template = '<a href="javascript:void(0)" class="btn-link os-linkcolor js-credit-limit" data-merchant-id="'+full.merchant_id+'" data-credit-limit-id="'+full.credit_limit_id+'" data-avail-credit-limit="'+number_format(full.avail_credit_limit,2)+'">'+number_format(full.credit_limit,2)+'</a>';
						return template;
					}
				}
			},
			{
				// location
				className: "text-center",
				mRender: function(data, type, full) {
					var template;
					if (full.owner_user_id == ownerUserId || full.status != 'pending') {
						template = '<a href="javascript:void(0)" data-row-type="'+full.row_type+'" data-merchantlink-relation-id="'+full.merchant_link_relation_id+'"  data-default-location-id="'+full.merchant_location_id+'" class="btn-link os-linkcolor js-merchant-location">'+full.merchant_location+'</a>';
					} else {
						template = 'Location';
					}

					return template;
				}
			},
			{
				// status
				className: "text-center",
				mRender: function(data, type, full) {
					var status;
					if (full.merchant_id != null) {
						if (full.owner_user_id == ownerUserId) {
							status = 'Active'
						} else if (full.status == 'pending') {
							if(full.responder == '1') {
								status = '<a href="javascript:void(0)" class="btn-link os-linkcolor js-approve-reject-modal" data-merchantlink-id="'+full.merchant_link_id+'">Pending</a>';
							} else {
								status = 'Pending';
							}
						} else if (full.status == 'active') {
							status = '<a href="javascript:void(0)" class="btn-link os-linkcolor js-deactivate-modal" data-merchantlink-relation-id="'+full.merchant_link_relation_id+'" data-merchantlink-id="'+full.merchant_link_id+'">Active</a>';
						} else if (full.status == 'inactive') {
							status = '<a href="javascript:void(0)" class="btn-link os-linkcolor js-activate-modal"  data-merchant-user-id="'+full.owner_user_id+'"  data-merchantlink-relation-id="'+full.merchant_link_relation_id+'" data-merchantlink-id="'+full.merchant_link_id+'">Inactive</a>';
						}
					} else {
						// one way

						if (full.status == 'pending') {
							status = '<a href="javascript:void(0)" class="btn-link os-linkcolor js-ow-approve-reject-modal" data-company-id="'+full.company_id+'">Pending</a>';
						} else if (full.status == 'active') {
							status = '<a href="javascript:void(0)" class="btn-link os-linkcolor js-ow-deactivate-modal" data-merchantlink-relation-id="'+full.merchant_link_relation_id+'" data-company-id="'+full.company_id+'">Active</a>';
						} else if (full.status == 'inactive') {
							status = '<a href="javascript:void(0)" class="btn-link os-linkcolor js-ow-activate-modal" data-merchantlink-relation-id="'+full.merchant_link_relation_id+'" data-company-id="'+full.company_id+'">Inactive</a>';
						}
					}

					return status;
				}
			},
			{
				// blue button
				className: "text-center",
				mRender: function(data, type, full) {
					if ((full.status == 'pending' || full.status == 'inactive') && full.owner_user_id != ownerUserId)  {
						var template = '<p onclick="getdataMerchantID('+full.merchant_id+')" class="mb-0" data-selected-user-id="'+full.owner_user_id+'"  style="padding-top:0;padding-bottom:0;cursor:not-allowed" data-merchant-id="'+full.merchant_id+'" ><image></p>';
					template = template.replace("<image>",'<img style="width:25px;height:25px;cursor:not-allowed; filter: grayscale(100%) brightness(160%);" class="mt=0 mb-0 text-center"src=\"{{asset("/images/bluecrab_25x25.png")}}\"/>')
				} else {
					var template = '<p onclick="getdataMerchantID('+full.merchant_id+')" class="mb-0 js-blue-crab-modal" data-selected-user-id="'+full.owner_user_id+'"  style="padding-top:0;padding-bottom:0" data-merchant-id="'+full.merchant_id+'" ><image></p>';
					template = template.replace("<image>",'<img style="width:25px;height:25px;cursor:pointer" class="mt=0 mb-0 text-center"src=\"{{asset("/images/bluecrab_25x25.png")}}\"/>')
				}
					return template;
				}
			},
			{
				// delete button
				className: "text-center",
				mRender: function(data, type, full) {
					var template = '';
					if (full.owner_user_id != ownerUserId) {
						if (full.delete_status == 'active') {
							template = '<p onclick="getdataMerchantID('+full.merchant_id+')" class="mb-0 js-delete-row" data-merchant-id="'+full.merchant_id+'"  data-company-id="'+full.company_id+'"  data-merchantlink-id="'+full.merchant_link_id+'"><image></p>';

							template = template.replace("<image>",'<img style="width:25px;height:25px;cursor:pointer" class="mt=0 mb-0 text-center"src=\"{{asset("/images/redcrab_25x25.png")}}\"/>')

						} else {
							template = '<p onclick="getdataMerchantID('+full.merchant_id+')" class="mb-0" style="cursor: not-allowed;" ta-merchant-id="'+full.merchant_id+'"  data-company-id="'+full.company_id+'"  data-merchantlink-id="'+full.merchant_link_id+'"><image></p>';
							template = template.replace("<image>",'<img style="width:25px;height:25px;cursor:pointer" class="mt=0 mb-0 text-center"src=\"{{asset("/images/redcrab_disabled_25x25.png")}}\"/>')
						}
					}
					return template;
				}
			}
		],
		"columnDefs": [
			{ "targets": [9,10], 'orderable' : false },
			{width:"200px",  targets: [7]},      // Default Location
			{width:"100px",  targets: [8]},      // Status
			{width:"30px",  targets: [0,9,10]},  // No.,bluecrab, redcrab
		]
	});

	$("#dealer_tbl").on('click', ".js-blue-crab-modal", function() {
		var merchantId = $(this).attr('data-merchant-id');
	   
		if (merchantId != 'null') {
			var createTransactionLink = '/documentdealer/'+merchantId;
		} else {
			var createTransactionLink = 'null'
		}

		var own_merchant_id = {{$globalAuth->get_data('merchant_id')}};
		
		if (own_merchant_id == merchantId) {
			$("#create-transaction-link").css('display','none');
		} else {
			$("#create-transaction-link").css('display','block');
		}

		console.log(createTransactionLink)
		$("#create-transaction-link").attr('href', createTransactionLink);
		$(".dealerBlueCrabModal").modal('show');
	});


	$('#dealer_tbl').on('click', '.js-credit-limit', function(event){
		merchantId = $(this).attr('data-merchant-id');
		creditLimitId = $(this).attr('data-credit-limit-id');
		var availCreditLimit = $(this).attr('data-avail-credit-limit');
		dealerTableRow = $(this).parents('tr');
		$("#credit-limit").val($(this).text());


		var cLimit = parseFloat($(this).text().replace(',', ''));
		var usedLimit = parseFloat($("#used-limit").text().replace(',', ''));
		$("#used-limit").text(number_format(availCreditLimit,2));
		$("#available-limit").text(number_format(cLimit - availCreditLimit,2))


		if ($(this).text() != '0.00') {
			//$("#buffer_main_price").val($(this).text());
			$("#buffer_main_price").val('');
		} else {
			$("#buffer_main_price").val('')
		}
		$(".creditLimitModal").modal('show');
		$("#credit-limit").focus();
	});

	$('.creditLimitModal').on('hidden.bs.modal', function (e) {
		$.ajax({
			url:"{{route('data.ajax.saveMerchantCreditLimit')}}",
			data: {
				merchantId : merchantId,
				creditLimitId: creditLimitId,
				creditLimit: $("#credit-limit").val()
			},
			type:"POST",
			success: function(response) {
				displayStatusMsgPopup(response.msg);
				$("#dealer_tbl").DataTable().ajax.reload(null, false );
			}
		})
	});


	$('#dealer_tbl').on('click', '.js-delete-row', function(event) {
		merchantLinkId = $(this).attr('data-merchantlink-id');
		companyId = $(this).data('company-id');
		merchantId = $(this).data('merchant-id');
		$("#deleteConfirmModel").modal('show');
	});

	$('#dealer_tbl').on('click', '.js-approve-reject-modal', function(event) {
		merchantLinkId = $(this).attr('data-merchantlink-id');
		$("#approve-reject-modal").modal('show');
	});

	$('#dealer_tbl').on('click', '.js-deactivate-modal', function(event) {
		merchantLinkRelationId = $(this).attr('data-merchantlink-relation-id');
		$("#js-status-deactivate-modal").modal('show');
	});

	$('#dealer_tbl').on('click', '.js-activate-modal', function(event) {
		merchantLinkId = $(this).attr('data-merchantlink-id');
		merchantLinkRelationId = $(this).attr('data-merchantlink-relation-id');
		merchantUserId = $(this).attr('data-merchant-user-id');
		$("#js-status-activate-modal").modal('show');
	});


	$('#dealer_tbl').on('click', '.js-ow-approve-reject-modal', function(event) {
		companyId = $(this).data('company-id');
		$("#ow-approve-reject-modal").modal('show');
	});


	$('#dealer_tbl').on('click', '.js-ow-deactivate-modal', function(event) {
		companyId = $(this).data('company-id');
		merchantLinkRelationId = $(this).data('merchantlink-relation-id');
		$("#js-ow-status-deactivate-modal").modal('show');
	});

	$('#dealer_tbl').on('click', '.js-ow-activate-modal', function(event) {
		companyId = $(this).data('company-id');
		merchantLinkRelationId = $(this).data('merchantlink-relation-id');
		$("#js-ow-status-activate-modal").modal('show');
	});


    function atm_money(num) {
        if (num.toString().length == 1) {
            return '00.0' + num.toString()
        } else if (num.toString().length == 2) {
            return '00.' + num.toString()
        } else if (num.toString().length == 3) {
            return '0' + num.toString()[0] + '.' + num.toString()[1] + num.toString()[2];
        } else if (num.toString().length >= 4) {
            return num.toString().slice(0, (num.toString().length - 2)) + '.' + num.toString()[(num.toString().length - 2)] + num.toString()[(num.toString().length - 1)];
        }
    }

    filter_price("#credit-limit","#buffer_main_price");

    function filter_price(target_field,buffer_in) {
        $(target_field).on( "keydown", function( event ) {
            event.preventDefault();

            var usedLimit = parseFloat($("#used-limit").text().replace(',', ''));

            if (event.keyCode == 8) {
                $(buffer_in).val('');
                $(target_field).val('');
                $(productTableRow).find('.js-credit-limit').text('0.00');
                $("#available-limit").text(number_format(0.00 - usedLimit,2))
                return null
            }

            if (isNaN(event.key) || $.inArray( event.keyCode, [13,38,40,37,39] ) !== -1 || event.keyCode == 13  ) {
                if ($(buffer_in).val() != '') {
                    var totalPrice = atm_money(parseInt($(buffer_in).val()));
                    $(target_field).val(totalPrice);
                    $(dealerTableRow).find('.js-credit-limit').text(totalPrice);
                    $("#available-limit").text(number_format(totalPrice - usedLimit,2))

                } else {
                    $(target_field).val('');
                    $(dealerTableRow).find('.js-credit-limit').text('0.00');
                    $("#available-limit").text(number_format(0.00 - usedLimit,2))

                }
                return null;
            }

            const input =  event.key;
            old_val = $(buffer_in).val()

            if (old_val === '0.00') {
                $(buffer_in).val('');
                $(target_field).val('');
                $(dealerTableRow).find('.js-product-cost').text('0.00');
                old_val = ''
            }

            $(buffer_in).val(''+old_val+input)
            var totalPrice = atm_money(parseInt($(buffer_in).val()));
            $("#available-limit").text(number_format(totalPrice - usedLimit,2))
            $(target_field).val(number_format(totalPrice, 2));
            $(dealerTableRow).find('.js-credit-limit').text(number_format(totalPrice, 2));
        });
    }


    function transition_link() {
		createTransactionLink = $("#create-transaction-link").attr('href');
		if (createTransactionLink != 'null') {
			window.open(modified_url_fn(createTransactionLink),'_blank')
		}
    }


    function owStatusDeactivate()
    {
        $.ajax({
            url:'{{ route('data.ajax.changeOnewayRelationStatus') }}',
            type: 'POST',
            data: {
                merchantLinkRelationId: merchantLinkRelationId,
                status: 'inactive'
            },
            dataType:'json',
            success: function(response) {
                if (response.status === 'true') {
                    $("#js-ow-status-deactivate-modal").modal('hide');
                    displayStatusMsgPopup('Status deactivated successfully');
                    $("#dealer_tbl").DataTable().ajax.reload(null, false );
					changeOnewayRelationStatusFromOceaniadb("inactive");
                }
            }
        });
    }


    function owStatusActivate()
    {
        $.ajax({
            url:'{{ route('data.ajax.changeOnewayRelationStatus') }}',
            type: 'POST',
            data: {
                merchantLinkRelationId: merchantLinkRelationId,
                status: 'active'
            },
            dataType:'json',
            success: function(response) {
                if (response.status === 'true') {
                    $("#js-ow-status-activate-modal").modal('hide');
                    displayStatusMsgPopup('Status activated successfully');
                    $("#dealer_tbl").DataTable().ajax.reload(null, false );
					changeOnewayRelationStatusFromOceaniadb("active");

				}
            }
        });
    }


    function owApproveLinking() {
        $.ajax({
            url:'{{ route('data.ajax.saveOnewayRelation') }}',
            type: 'POST',
            data: {
                companyId: companyId,
                ptype: 'dealer',
                status: 'active'
            },
            dataType:'json',
            success: function(response) {
                if (response.status === 'true') {
                    $("#ow-approve-reject-modal").modal('hide');
                    displayStatusMsgPopup('Status approved successfully');
                    $("#dealer_tbl").DataTable().ajax.reload(null, false );
					changeOnewayRelationStatusFromOceaniadb("active");


				}
            }
        });
    }


    function owRejectLinking() {
        $.ajax({
            url:'{{ route('data.ajax.saveOnewayRelation') }}',
            type: 'POST',
            data: {
                companyId: companyId,
                ptype: 'dealer',
                status: 'inactive'
            },
            dataType:'json',
            success: function(response) {
                if (response.status === 'true') {
                    $("#ow-approve-reject-modal").modal('hide');
                    displayStatusMsgPopup('Status rejected successfully');
                    $("#dealer_tbl").DataTable().ajax.reload(null, false );
					changeOnewayRelationStatusFromOceaniadb("inactive");

				}
            }
        });
    }


	function approveLinking() {
		$.ajax({
			url:'{{ route('data.ajax.saveMerchantLinkRelation') }}',
			type: 'POST',
			data: {
				merchantLinkId: merchantLinkId,
				ptype: 'dealer',
				status: 'active'
			},
			dataType:'json',
			success: function(response) {
				if (response.status === 'true') {
					$("#approve-reject-modal").modal('hide');
					displayStatusMsgPopup('Status approved Successfully');
					$("#dealer_tbl").DataTable().ajax.reload(null, false );
                    editMerchantLinkRelationFromOceaniadb('active');
				}
			}
		});
	}


	function rejectLinking() {
		$.ajax({
			url:'{{ route('data.ajax.saveMerchantLinkRelation') }}',
			type: 'POST',
			data: {
				merchantLinkId: merchantLinkId,
				ptype: 'dealer',
				status: 'inactive'
			},
			dataType:'json',
			success: function(response) {
				if (response.status === 'true') {
					$("#approve-reject-modal").modal('hide');
					displayStatusMsgPopup('Status Rejected Successfully');
					$("#dealer_tbl").DataTable().ajax.reload(null, false );
					editMerchantLinkRelationFromOceaniadb('inactive');
				}
			}
		});
	}


	function statusDeactivate()
	{
		$.ajax({
			url:'{{ route('data.ajax.deactivateMerchantLinkRelation') }}',
			type: 'POST',
			data: {
				merchantLinkRelationId: merchantLinkRelationId,
				status: 'inactive'
			},
			dataType:'json',
			success: function(response) {
				if (response.status === 'true') {
					$("#js-status-deactivate-modal").modal('hide');
					displayStatusMsgPopup(response.msg);
					$("#dealer_tbl").DataTable().ajax.reload(null, false );
					editMerchantLinkRelationinactiveFromOceaniadb('inactive' , merchantLinkRelationId);
		
				}
			}
		});
	}


	function statusActivate()
	{
		$.ajax({
			url:'{{ route('data.ajax.activateMerchantLinkRelation') }}',
			type: 'POST',
			data: {
				merchantLinkRelationId: merchantLinkRelationId,
				merchantLinkId: merchantLinkId,
				merchantUserId: merchantUserId
			},
			dataType:'json',
			success: function(response) {
				if (response.status === 'true') {
					$("#js-status-activate-modal").modal('hide');
					displayStatusMsgPopup(response.msg);
					$("#dealer_tbl").DataTable().ajax.reload(null, false );
					editMerchantLinkRelationFromOceaniadb("active");

				}
			}
		});
	}



	function deleteLinking() {
		if (merchantId != null) {
			$.ajax({
				url:'{{ route('data.ajax.delMerchantTwoWayLinking') }}',
				type: 'POST',
				data: {
					merchantLinkId: merchantLinkId,
					selectedMerchantId: merchantId
				},
				dataType:'json',
				success: function(response) {
					if (response.status === 'true') {
						displayStatusMsgPopup('Link deleted successfully');
						$("#dealer_tbl").DataTable().ajax.reload(null, false );
						deleteLinkingTwoWayFromOceaniadb();
					}
				}
			});


		} else {

			// delete one way

			$.ajax({
				url:'{{ route('data.ajax.delMerchantOnyWay') }}',
				type: 'POST',
				data: {
					companyId: companyId
				},
				dataType:'json',
				success: function(response) {
					if (response.status === 'true') {
						$("#deleteConfirmModel").modal('hide');
						displayStatusMsgPopup('Merchant deleted successfully');
						$("#dealer_tbl").DataTable().ajax.reload(null, false );
						deleteLinkingOneWayFromOceaniadb();

					}
				}
			});
		}
	}


	function displayStatusMsgPopup(msg) {
		$("#status-msg-element").text(msg);
		$("#msgModal").modal('show');
		setTimeout(function() {
			$("#msgModal").modal('hide');
			$('.modal-backdrop').remove();
		},3500);
	}


    function number_format (number, decimals, decPoint, thousandsSep) {
        number = (number + '').replace(/[^0-9+\-Ee.]/g, '')
        var n = !isFinite(+number) ? 0 : +number
        var prec = !isFinite(+decimals) ? 0 : Math.abs(decimals)
        var sep = (typeof thousandsSep === 'undefined') ? ',' : thousandsSep
        var dec = (typeof decPoint === 'undefined') ? '.' : decPoint
        var s = ''

        var toFixedFix = function (n, prec) {
            if (('' + n).indexOf('e') === -1) {
                return +(Math.round(n + 'e+' + prec) + 'e-' + prec)
            } else {
                var arr = ('' + n).split('e')
                var sig = ''
                if (+arr[1] + prec > 0) {
                    sig = '+'
                }
                return (+(Math.round(+arr[0] + 'e' + sig + (+arr[1] + prec)) + 'e-' + prec)).toFixed(prec)
            }
        }

        // @todo: for IE parseFloat(0.55).toFixed(0) = 0;
        s = (prec ? toFixedFix(n, prec).toString() : '' + Math.round(n)).split('.')
        if (s[0].length > 3) {
            s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep)
        }
        if ((s[1] || '').length < prec) {
            s[1] = s[1] || ''
            s[1] += new Array(prec - s[1].length + 1).join('0')
        }

        return s.join(dec)
    }

    
	function linkToRouteReidrectPath(){
		window.open(modified_url_fn(redirectToRouteTrackingReport));
	}


	function getdataMerchantID(getID) {
		var route = "{{url('/tracking-report')}}/" + getID;
		redirectToRouteTrackingReport = route;
	}

	//For oceaniadb
	function  deleteLinkingTwoWayFromOceaniadb(){
		$.ajax({
			url:'http://'+locationipaddr+'/api/creditaccount/deleteMerchantLinkWithRelation',
			type: 'POST',
			data: {
				id: merchantLinkId,
				selectedMerchantId: merchantId
			},
			dataType:'json',
			success: function(response) {
				console.log("mlr ",response)
			}
		});
	}

	function  deleteLinkingOneWayFromOceaniadb(){
		$.ajax({
			url:'http://'+locationipaddr+'/api/creditaccount/OneWay/deleteMerchantLinkWithRelation',
			type: 'POST',
			data: {
				companyId: companyId
			},
			dataType:'json',
			success: function(response) {
				console.log("mlr ",response)
			}
		});
	}



	function  editMerchantLinkRelationFromOceaniadb(status){
		$.ajax({
			url:'http://'+locationipaddr+'/api/creditaccount/editMerchantLinkRelation',
			type: 'POST',
			data: {
				merchantLinkId: merchantLinkId,
				ptype: 'supplier',
				status: status
			},
			dataType:'json',
			success: function(response) {
				console.log("mlr status ",response)
			}
		});
	}
	var mar_id = '';
	function  editMerchantLinkRelationinactiveFromOceaniadb(status , id){

		console.log("passed id is = " , id);
		 getmerid(id);
		console.log("mar_id" , mar_id);
		$.ajax({
			url:'http://'+locationipaddr+'/api/creditaccount/editMerchantLinkRelation',
			type: 'POST',
			data: {
				merchantLinkId: mar_id,
				ptype: 'supplier',
				status: status
			},
			dataType:'json',
			success: function(response) {
			}
		});
	}

	function getmerid (id){

			$.ajax({
			url:'{{ route('get.mer.id') }}',
			type: 'GET',
			data: {
				pass_id: id,
			},
			dataType:'json',
			success: function(response) {
				mar_id = response;
			}
		});

	}


	function  changeOnewayRelationStatusFromOceaniadb(status){
		$.ajax({
			url:'http://'+locationipaddr+'/api/creditaccount/changeOnewayRelationStatus',
			type: 'POST',
			data: {
				merchantLinkRelationId: companyId,
				status:  status
			},
			dataType:'json',
			success: function(response) {
				if (response.status === 'true') {
					$("#js-ow-status-deactivate-modal").modal('hide');
					displayStatusMsgPopup('Status deactivated successfully');
					$("#dealer_tbl").DataTable().ajax.reload(null, false );
				}
			}
		});
	}


</script>
