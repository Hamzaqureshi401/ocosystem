<style>
	.btn-gray {
		color: #fff !important;
		background-color: #a0a0a0 !important;
		border-color: #a0a0a0 !important;
	}
</style>

<div class="row" style="vertical-align:middle;margin-bottom:0">
	<div class="col-md-6" style="display:flex;align-items:center">
		<h2 class="mb-0">Supplier</h2>
	</div>
	@include('data.databuttons')
</div>
<div class="table-responsive" style="overflow-x: hidden;">
    <table class="table table-bordered deliveryman" id="supplier_tbl">
	<thead>
		<tr class="thead-dark">
			<th class="text-center" style="width:25px">No</th>
			<th class="text-center" style="width:25px;">Merchant&nbsp;ID</th>
			<th class="text-center" style="width:25px;">Business&nbsp;Reg.&nbsp;No.</th>
			<th class="text-left"   style="width:auto">Company Name</th>
			<th class="text-center" style="width:25px;">Procured</th>
			<th class="text-center" style="width:25px;">Consign</th>
			<th class="text-center" style="width:25px;">Credit&nbsp;Limit</th>
			<th class="text-center" style="width:100px">Default&nbsp;Location</th>
			<th class="text-center" style="">Status</th>
			<th class="text-center pl-0 pr-0" style=""></th>
			<th class="text-center pl-0 pr-0" style=""></th>
		</tr>
	</thead>
	<tbody>
	</tbody>
    </table>
</div>

<!-- Credit limit modal pop up starts here-->
<div class="modal fade creditLimitModalS" id="js-credit-limit-modal"
	tabindex="-1" role="dialog" aria-labelledby="staffNameLabel"
	aria-hidden="true" style="text-align: center;">

	<div class="modal-dialog modal-dialog-centered  mw-75 w-50"
		role="document" style="display: inline-flex;">
		<div class="modal-content modal-inside bg-greenlobster"
			style="width: 80%;">
			 <div class="modal-header">
                 <h3 style="margin-bottom:0px">Credit Limit</h3>
            </div>
			<div class="modal-body" style="padding-top: 0px;">
			<div class="row" style="padding-bottom: 0px;padding-top:10px">
				<div class="col-md-12 text-right">
				<b>MYR</b>
				</div>
			</div>
			<div class="row" style="padding-bottom: 0px;padding-top: 0px">
				<div class="col-md-6 text-white"
					style="text-align:left">Credit Limit</div>
				<div class="col-md-6 text-right"
					id="credit-limit">0.00</div>
			</div>
			<div class="row" style="padding-top: 5px;">
				<div class="col-md-6 text-white"
					style="text-align:left">Used Limit</div>
				<div class="col-md-6 text-right"
					id="used-limit">0.00</div>
			</div>
			<div class="container" style="border-bottom:1px solid #fff;"></div>
			<div class="row">
				<div class="col-md-6 text-white"
					style="text-align:left">Available Limit</div>
				<div class="col-md-6 text-right"
					id="available-limit">0.00</div>
			</div>
		</div>
		</div>
	</div>
	</div>
<!-- Credit limit modal pop up ends here-->

<!-- Blue crab modal pop up starts here-->
<div class="modal fade blueCrabModal" id="blue-crab-action-modal" tabindex="-1" 
	role="dialog"  aria-hidden="true">
	<div style="width:400px" class="modal-dialog modal-dialog-centered">
    <div style="width:100%" class="modal-content bg-greenlobster">
       <div class="modal-body" style="display:flex;justify-content:center">
		<div class="row" >
			   
			<button href="{{url('/documentsupplier')}}"
			 	class="p-0 mb-0 ml-0 mr-0 btn btn-success bg-salesorder
					sellerbuttonwide"
			 	style=""
				onclick="transition_link()"
				id="create-transaction-link">
				Create<br>Transaction
			</button>

			<!--
			<button
				style="padding-left:6px;margin-bottom:0;margin-left:5px;
				padding-top:7px;padding-bottom:5px;margin-right:0"
				class="btn bg-procurement sellerbuttonwide">
				Procurement</button>
			<button class="btn bg-purchaseorder sellerbuttontwo"
				style="padding-left:6px;margin-bottom:0;margin-left:5px;
				padding-top:7px;padding-bottom:5px;margin-right:0">
				Purchase<br>&nbsp;&nbsp;Order</button>
			-->

			<a href="{{url('/documentinventorycost')}}"
				id="inventory-cost-link">
				<button class="p-0 mb-0 mr-0 btn btn-success bg-inventory
					sellerbuttontwo"
					style="margin-left:5px;" id="inventory-cost-btn">
					Inventory<br>Cost
				</button>
			</a>

			<a href="#">
				<button onclick="linkToRouteReidrectPath()"
					style="margin-left:5px;
					padding-top:7px;padding-bottom:5px"
					class="b-0 pl-0 pr-0 mb-0 mr-0 btn btn-success sellerbutton
						bg-tracking">
					Tracking<br>Report
				</button>
			</a>

			</div>
			</div>
        </div>
    </div>
  </div>
</div>
<!-- Blue crab modal pop up ends here-->

<div class="modal fade statusChangeAlertModal1"  tabindex="-1"
	id="js-ow-status-deactivate-modal" role="dialog"
	aria-labelledby="staffNameLabel"
	aria-hidden="true" style="text-align: center;">

    <div class="modal-dialog modal-dialog-centered  mw-75 w-50"
         role="document" style="display: inline-flex;">
        <div class="modal-content modal-inside bg-greenlobster">
            <div class="modal-header" style="border:none;"></div>
            <div class="modal-body text-center">
                <h5 class="modal-title text-white" style="margin-bottom:0px">
                    Are you sure?</h5>
            </div>
            <div class="modal-footer"
                 style="border:none;justify-content: center;">
                <div class="row" style="padding-left: 0px;padding-right:0px">
                    <button class="btn btn-danger deactivate"
						data-attr="" onclick="owStatusDeactivate()">
						Deactivate
					</button>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Status update modal pop up starts here-->
<div class="modal fade statusChangeAlertModal1" tabindex="-1"
	id="js-status-deactivate-modal" role="dialog"
	aria-labelledby="staffNameLabel" aria-hidden="true"
	style="text-align: center;">

	<div class="modal-dialog modal-dialog-centered  mw-75 w-50"
		role="document" style="display: inline-flex;">
		<div class="modal-content modal-inside bg-greenlobster">
		<div class="modal-header" style="border:none;"></div>
            <div class="modal-body text-center">
            	<h5 class="modal-title text-white" style="margin-bottom:0px">
				Are you sure?</h5>
            </div>
			<div class="modal-footer"
				style="border:none;justify-content: center;">
			<div class="row" style="padding-left: 0px;padding-right:0px">
				<button class="btn btn-danger deactivate"
				data-attr="" onclick="statusDeactivate()">Deactivate</button>
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
                <h5 class="modal-title text-white" style="margin-bottom:0px">
					Are you sure?
				</h5>
            </div>
            <div class="modal-footer" style="border:none;justify-content: center;">
                <div class="row" style="padding-left: 0px;padding-right:0px">
                    <button class="btn bg-primary primary-button deactivate mr-2"
						style="width:100px"
						data-attr="" onclick="owApproveLinking()">Approve
					</button>
                    <button class="btn btn-danger" data-dismiss="modal"
						style="width:100px"
						onclick="owRejectLinking()">Reject
					</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade statusChangeAlertModal2" id="approve-reject-modal"
	tabindex="-1" role="dialog" aria-labelledby="staffNameLabel"
	aria-hidden="true" style="text-align: center;">

	<div class="modal-dialog modal-dialog-centered  mw-75 w-50"
		role="document" style="display: inline-flex;">
		<div class="modal-content modal-inside bg-greenlobster">
			 <div class="modal-header" style="border:none;"></div>
            <div class="modal-body text-center">
            	<h5 class="modal-title text-white" style="margin-bottom:0px">Are you sure?</h5>
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

	<div class="modal fade statusChangeAlertModal3"  tabindex="-1" id="js-status-activate-modal"	role="dialog" aria-labelledby="staffNameLabel"	aria-hidden="true" style="text-align: center;">

	<div class="modal-dialog modal-dialog-centered  mw-75 w-50"
		role="document" style="display: inline-flex;">
		<div class="modal-content modal-inside bg-greenlobster">
			<div class="modal-header" style="border:none;"></div>
            <div class="modal-body text-center">
            	<h5 class="modal-title text-white" style="margin-bottom:0px">Are you sure?</h5>
            </div>
			<div class="modal-footer" style="border:none;justify-content: center;">
			<div class="row" style="padding-left: 0px;padding-right:0px">
				<button class="btn bg-primary primary-button" data-attr=""
				style="width:100px"
				onclick="statusActivate()">Activate</button>
			</div>
		</div>
		</div>
	</div>
	</div>



<div class="modal fade statusChangeAlertModal3"  tabindex="-1" id="js-ow-status-activate-modal"	role="dialog" aria-labelledby="staffNameLabel"	aria-hidden="true" style="text-align: center;">

    <div class="modal-dialog modal-dialog-centered  mw-75 w-50"
         role="document" style="display: inline-flex;">
        <div class="modal-content modal-inside bg-greenlobster">
            <div class="modal-header" style="border:none;"></div>
            <div class="modal-body text-center">
                <h5 class="modal-title text-white" style="margin-bottom:0px">Are you sure?</h5>
            </div>
            <div class="modal-footer" style="border:none;justify-content: center;">
                <div class="row" style="padding-left: 0px;padding-right:0px">
                    <button class="btn bg-primary primary-button" data-attr=""
                            style="width:100px"
                            onclick="owStatusActivate()">Activate</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- status update  modal pop up ends here-->


<div class="modal fade" id="deleteConfirmModel" tabindex="-1" role="dialog" aria-labelledby="deleteConfirmModel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered  mw-75 w-50" role="document">
		<div class="modal-content modal-inside bg-greenlobster">
			<div class="modal-header" style="border:0"></div>
			<div class="modal-body text-center">
				<h5 class="modal-title text-white"
					id="statusModalLabel">Do you want to permanently delete this link</h5>
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
<div class="modal fade merchantIDModal" tabindex="-1" role="dialog"  aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content bg-greenlobster">
        <div class="modal-body">
		<form>
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="label-control text-white">Company&nbsp;Name</label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-3">
                            <label class=" text-white">Business&nbsp;Reg.&nbsp;No.</label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="label-control text-white">Address</label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="label-control text-white">Contact&nbsp;Name</label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="label-control text-white">Mobile No.</label>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
  </div>
</div>
<!-- blue crab modal pop up ends here-->

<script>



	var ownerUserId = '{{$user_id}}';
	var merchantLinkId = '';
    var merchantLinkRelationId = '';
    var merchantUserId = '';
    var merchantId;
    var selectedUserId;
    var companyId;
    var redirectToRouteTrackingReport;

$(document).ready( function () {
    supplierDataTable.draw();
});

	var supplierDataTable = $('#supplier_tbl').DataTable({
        pageLength: 10,
        bPaginate: true,
        info: false,
        ordering: true,
        responsive: true,
        processing: true,
        serverSide: true,
		autoWidth: false,
        bFilter: true,
        ajax: "{{route('data.ajax.getSupplierData' )}}",
        initComplete: function(setting, json) {
            console.log(json.data);
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
                    //var template = '<a href="javascript:void(0)" class="btn-link os-linkcolor" data-toggle="modal" data-target=".merchantIDModal">'+full.company_system_id+'</a>';
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
					var template = '<a href="javascript:void(0)" data-limit="<limit>" class="btn-link os-linkcolor js-credit-limit-modal" >'+number_format(full.credit_limit,2)+'</a>';
					template = template.replace("<limit>", full.used_limit);
                    return template;
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
                            status = '<a href="javascript:void(0)" class="btn-link os-linkcolor js-activate-modal" data-merchant-user-id="'+full.owner_user_id+'" data-merchantlink-relation-id="'+full.merchant_link_relation_id+'" data-merchantlink-id="'+full.merchant_link_id+'">Inactive</a>';
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
                    if ((full.status == 'pending' || full.status == 'inactive') && full.owner_user_id != ownerUserId) {
                        var template = '<p onclick="getdataMerchantID('+full.merchant_id+')" class="mb-0" data-selected-user-id="'+full.owner_user_id+'"  style="padding-top:0;padding-bottom:0 ;cursor: not-allowed;" data-merchant-id="'+full.merchant_id+'" ><image></p>';
                        template = template.replace("<image>",'<img style="width:25px;height:25px;cursor:not-allowed; filter: grayscale(100%) brightness(160%);" class="mt=0 mb-0 text-center"src=\"{{asset("/images/bluecrab_25x25.png")}}\"/>')
                    } else {
                        var template = '<p onclick="getdataMerchantID('+full.merchant_id+')" class="mb-0 js-blue-crab-modal" data-selected-user-id="'+full.owner_user_id+'"  style="padding-top:0;padding-bottom:0 ;" data-merchant-id="'+full.merchant_id+'" ><image></p>';
                        template = template.replace("<image>",'<img style="width:25px;height:25px;cursor:pointer; " class="mt=0 mb-0 text-center"src=\"{{asset("/images/bluecrab_25x25.png")}}\"/>')
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
							template = '<p  onclick="getdataMerchantID('+full.merchant_id+')" class="mb-0 js-delete-row" data-merchant-id="'+full.merchant_id+'"  data-company-id="'+full.company_id+'"  data-merchantlink-id="'+full.merchant_link_id+'"><image></p>';
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
			{"targets": [9,10], 'orderable' : false},
			{width:"200px",  targets: [7]},  // Default Location
			{width:"100px",  targets: [8]},  // Status
			{width:"30px",  targets: [0,9,10]},  // No.,bluecrab, redcrab
		]
    });


    $("#supplier_tbl").on('click', ".js-credit-limit-modal", function() {
        $("#credit-limit").text($(this).text());

        var cLimit = parseFloat($(this).text().replace(',', ''));
        var usedLimit = parseFloat($(this).attr('data-limit').replace(',', ''));
		if (isNaN(usedLimit.toFixed(2)) == false) {
			$('#used-limit').text(usedLimit.toFixed(2));
		} else {
			usedLimit = 0;
			$('#used-limit').text(usedLimit.toFixed(2));
		}
        $("#available-limit").text(number_format(cLimit - usedLimit,2))
        $("#js-credit-limit-modal").modal('show');

    });

$("#supplier_tbl").on('click', ".js-blue-crab-modal", function() {
    var merchantId = $(this).attr('data-merchant-id');
    var selectedUserId = $(this).attr('data-selected-user-id');
    
    if (merchantId != 'null') {
        var createTransactionLink = '/documentsupplier/'+merchantId;
    } else {
        var createTransactionLink = 'null'
    }

    $("#create-transaction-link").attr('href', createTransactionLink);

    console.log(
        "Merchant id "+merchantId
    )

    if (selectedUserId != ownerUserId) {
        var inventoryCostRoute = `javascript:openNewTabURL('/documentinventorycost/${merchantId}')`;
        $("#inventory-cost-link").attr('href', inventoryCostRoute);
        $("#inventory-cost-btn").css('display', 'block');
		$("#create-transaction-link").css('display','block');
        $("#inventory-cost-btn").css('cursor', 'pointer');
        $("#inventory-cost-btn").removeAttr('disabled', 'disabled');
        var trackingReportLink = 'tracking-report/'+merchantId
        $("#tracking-report-link").attr('href', trackingReportLink);

    } else {
        $("#inventory-cost-link").attr('href', 'javascript:void(0)');
		// Ownself don't have Inventory Cost button
        $("#inventory-cost-btn").css('display', 'none');
		$("#create-transaction-link").css('display','none');
    }
    $("#blue-crab-action-modal").modal('show');
});

$('#supplier_tbl').on('click', '.js-delete-row', function(event) {
	merchantLinkId = $(this).attr('data-merchantlink-id');
    companyId = $(this).data('company-id');
    merchantId = $(this).data('merchant-id');
    $("#deleteConfirmModel").modal('show');
});

$('#supplier_tbl').on('click', '.js-approve-reject-modal', function(event) {
	merchantLinkId = $(this).attr('data-merchantlink-id');
	$("#approve-reject-modal").modal('show');
});

$('#supplier_tbl').on('click', '.js-ow-approve-reject-modal', function(event) {
    companyId = $(this).data('company-id');
    $("#ow-approve-reject-modal").modal('show');
});

$('#supplier_tbl').on('click', '.js-ow-deactivate-modal', function(event) {
    companyId = $(this).data('company-id');
    merchantLinkRelationId = $(this).data('merchantlink-relation-id');
    $("#js-ow-status-deactivate-modal").modal('show');
});


$('#supplier_tbl').on('click', '.js-deactivate-modal', function(event) {
    merchantLinkRelationId = $(this).attr('data-merchantlink-relation-id');
    $("#js-status-deactivate-modal").modal('show');
});


$('#supplier_tbl').on('click', '.js-activate-modal', function(event) {
    merchantLinkId = $(this).attr('data-merchantlink-id');
    merchantLinkRelationId = $(this).attr('data-merchantlink-relation-id');
    merchantUserId = $(this).attr('data-merchant-user-id');
    $("#js-status-activate-modal").modal('show');
});


    $('#supplier_tbl').on('click', '.js-ow-activate-modal', function(event) {
        companyId = $(this).data('company-id');
        merchantLinkRelationId = $(this).data('merchantlink-relation-id');
        $("#js-ow-status-activate-modal").modal('show');
    });



$('.status_column').click(function(){
	var html = $(this).html();
	var split = $(this).attr('data-attr');
	$('.deactivate').attr('data-attr',split);
	if(html == 'pending'){
		$('.statusChangeAlertModal2').modal('show');
	}else if(html == 'active'){
		$('.statusChangeAlertModal1').modal('show');
	}else if(html == 'inactive'){
		$('.statusChangeAlertModal3').modal('show');
	}
});

    /*
$('.deactivate').click(function(){
	var html = $(this).html();
	if(html == 'Deactivate'){
		val = 'inactive';
	}else if(html == 'Approve'){
		val = 'active';
	}else if(html == 'Activate'){
		val = 'active';
	}
	var id = $(this).attr('data-attr');
	$.ajax({
		type: 'POST',
		url: '/update_supplier_status',
		datatype: 'json',
		data:{val:val,id:id},
		success: function(res){
			$('.statusChangeAlertModal1').modal('hide');
			$('.statusChangeAlertModal2').modal('hide');
			$('.statusChangeAlertModal3').modal('hide');
			console.log(res);
			location.reload();
		}
	});
});
*/

function transition_link() {
            createTransactionLink = $("#create-transaction-link").attr('href');
            if (createTransactionLink != 'null')
            {
                window.open(modified_url_fn(createTransactionLink),'_blank')
            }
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
                $("#supplier_tbl").DataTable().ajax.reload(null, false );
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
                $("#supplier_tbl").DataTable().ajax.reload(null, false );
            }
        }
    });
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
                $("#supplier_tbl").DataTable().ajax.reload(null, false );
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
                $("#supplier_tbl").DataTable().ajax.reload(null, false );
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
            ptype: 'supplier',
            status: 'active'
        },
        dataType:'json',
        success: function(response) {
            if (response.status === 'true') {
                $("#ow-approve-reject-modal").modal('hide');
                displayStatusMsgPopup('Status approved successfully');
                $("#supplier_tbl").DataTable().ajax.reload(null, false );
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
            ptype: 'supplier',
            status: 'inactive'
        },
        dataType:'json',
        success: function(response) {
            if (response.status === 'true') {
                $("#ow-approve-reject-modal").modal('hide');
                displayStatusMsgPopup('Status rejected successfully');
                $("#supplier_tbl").DataTable().ajax.reload(null, false );
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
			ptype: 'supplier',
			status: 'active'
		},
		dataType:'json',
		success: function(response) {
			if (response.status === 'true') {
                $("#approve-reject-modal").modal('hide');
				displayStatusMsgPopup('Status approved successfully');
				$("#supplier_tbl").DataTable().ajax.reload(null, false );
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
			ptype: 'supplier',
			status: 'inactive'
		},
		dataType:'json',
		success: function(response) {
			if (response.status === 'true') {
                $("#approve-reject-modal").modal('hide');
				displayStatusMsgPopup('Status rejected successfully');
				$("#supplier_tbl").DataTable().ajax.reload(null, false );
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
                    $("#deleteConfirmModel").modal('hide');
                    displayStatusMsgPopup('Link deleted successfully');
                    $("#supplier_tbl").DataTable().ajax.reload(null, false );
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
                    $("#supplier_tbl").DataTable().ajax.reload(null, false );
                }
            }
        });
    }

}

function linkToRouteReidrectPath(){
    window.open( modified_url_fn(redirectToRouteTrackingReport));
}

function getdataMerchantID(getID) {

    var route = "{{url('/tracking-report')}}/" + getID;

    redirectToRouteTrackingReport = route;
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
</script>
