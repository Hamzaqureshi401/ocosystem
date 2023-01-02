<style>
.statement{
	background: #e6e6e6;
	width: 100%;
	padding: 10px;
	margin: 0 auto;
	border: 2px solid #e6e6e6;
	border-radius: 20px;
}

.ym{
	background: #c6c6c6;
	width: 100%;
	margin: 0 auto;
	padding: 5px 15px 5px 15px;
	border-radius: 15px;
}

.btn-enable{background: lightblue;color:#007bff;}
.btn-disable{background: #4d4d4d;color:white;}
.table{
	width: 100% !important;
}
table.dataTable.display tbody tr.odd > .sorting_1, table.dataTable.order-column.stripe tbody tr.odd > .sorting_1{
	background-color: #ffffff !important;
}

th, td { white-space: nowrap; }
div.dataTables_wrapper {
	margin: 0 auto;
}

div.container {
	width: 80%;
}

#inventory-cost-modal .indexNumber-width {
	width:10% !important;
}

#inventory-cost-modal .dated-width {
	width:20% !important;
}

.js-document-no a:hover {
	text-decoration: none;
}
.void_doc > td:last-child {background:red; color:#fff;margin:0;}
.no-line {
	text-decoration:none;
}
</style>

<script type="text/javascript">
	var JS_BASE_URL="{{url('/')}}";
</script>

<div class="row"
	style="display:flex;align-items:center;height:70px;margin-bottom:-5px;">
	<div class="col-md-8">
		<h2 style="margin-bottom:0">Document: Automatic Virtual Cabinet</h2>
	</div>
	<div class="col-md-4 text-right">
		<h5 style="margin-bottom:0">{{$start_yr}}-{{$end_yr}}</h5>
	</div>
</div>


<?php
/* Test variables */
$myreturn = [];
$current_year = $FY->start_financial_year->toString();

//echo $trvalue;

/* Complete list of support documents */
$docs = [
	/*
	'OPOSsum Sales: Fuel, Electrical Vehicle, Hydrogen, Outdoor e-Wallet & Outdoor Payment Terminal',
	'OPOSsum Sales: C-Store & Open Item',
	'Receipt  OPOSsum',
	*/
	'Receipt  E-Commerce',
	'Receipt  Issued',
	'Receipt  Received',
	'Pending',
	'Purchase Order Issued',
	'Purchase Order Received',
	'Credit Note Issued',
	'Credit Note Received',
	'Debit Note Issued',
	'Debit Note Received',
	'Delivery Order Issued',
	'Delivery Order Received',
	'Sales Order',
	'Invoice Issued',
	'Invoice Received',
	'Quotation/Tender Issued',
	'Quotation/Tender Received',
	'Consignment Note Issued',
	'Consignment Note Received',
	'Inventory Cost',
	'Wastage & Damage Declaration',
	'Picking List',
	'Receipt Issued',
	'Receipt Received'
];
?>

@foreach ($docs as $doc)

<div class="statement" style="">
<div class="row" style="margin-left:0;margin-right:0;
	display:flex;align-items:center">
	<p style="font-size:20px;margin-bottom:8px;font-family: sans-serif">{{ $doc }}</p>
</div>


<div class="ym">

	{{--*/ $y = 1; $index = 0;/*--}}

	<?php if((is_null($myreturn)) || ($current_year == 0)){
		$carbon = \Carbon\Carbon::parse($FY->start_financial_year->toDateTimeString());
		$carbon =  \Carbon\Carbon::parse($carbon->format('Y').'-01-01 00:00:00');
		$month = (date('n'))-($carbon->format('n'));
	?>
		<div style="margin: 5px;">
			<span style="font-family:sans-serif;font-size:large;vertical-align:middle">
				{{date('Y' , strtotime(@$start_yr))}}
				&nbsp;
			</span>

			@for($i = 0; $i < 12; $i++)
			@if($doc == 'Delivery Order Issued' && ($DOIssued[$i]!=0))
			<button class="btn-enable btn btn-sm primary-btn">
				<a href="javascript:openNewTabURL('/show-issued-dolist')" 
					style="text-decoration: none;">{{$carbon->format('M')}}</a>
			</button>
			@else
			@if($doc=='Delivery Order Received' && ($DORev[$i]!=0))
			<button class="btn-enable btn btn-sm primary-btn">
				<a href="javascript:openNewTabURL('/show-received-dolist');" 
					style="text-decoration: none;">{{$carbon->format('M')}}</a>
			</button>
			@else
			@if($doc=='Invoice Issued' && ($invoiceIssued[$i]!=0))
			<button class="btn-enable btn btn-sm primary-btn">
				<a href="javascript:openNewTabURL('/invoice/issued-list')" 
					style="text-decoration: none;">{{$carbon->format('M')}}</a>
			</button>
			@else
			@if($doc=='Invoice Received' && ($invoiceRev[$i]!=0))
			<button class="btn-enable btn btn-sm primary-btn">
				<a href="javascript:openNewTabURL('/invoice/received-list')" 
					 style="text-decoration: none;">{{$carbon->format('M')}}</a>
			</button>
			@else
			@if($doc=='Credit Note Issued' && $creditissue[$i]!=0)
			<button class="btn-enable btn btn-sm primary-btn creditNoteIssue"  
			data-year=" {{date('Y' , strtotime(@$start_yr))}}" data-month="{{$i+1}}"
                    enabled>{{$carbon->format('M')}}
				<!-- <a href="/creditnote-issued-list" target="_blank" style="text-decoration: none;"></a> -->
			</button>
			@else
			@if($doc=='Credit Note Received' && $creditreceived[$i]!=0)
			<button class="btn-enable btn btn-sm primary-btn creditNoteReceived"  
			data-year=" {{date('Y' , strtotime(@$start_yr))}}" data-month="{{$i+1}}"
                    enabled>{{$carbon->format('M')}}
				<!-- <a href="/creditnote-issued-list" target="_blank" style="text-decoration: none;"></a> -->
			</button>
			@else
			@if($doc=='Debit Note Issued' &&   $debitissue[$i]!=0)
			<button class="btn-enable btn btn-sm primary-btn debitNoteIssue" 
					data-year=" {{date('Y' , strtotime(@$start_yr))}}" data-month="{{$i+1}}"
                    enabled>{{$carbon->format('M')}}
			</button>
			@else
			@if($doc=='Debit Note Received' &&   $debitreceived[$i]!=0)
			<button class="btn-enable btn btn-sm primary-btn debitNoteReceived" 
					data-year=" {{date('Y' , strtotime(@$start_yr))}}" data-month="{{$i+1}}"
                    enabled>{{$carbon->format('M')}}
			</button>
			@else
			@if($doc=='Purchase Order Issued' && ($purchaseorders[$i] !=  0))
			<button class="btn-enable btn btn-sm primary-btn">
				<a href="javascript:openNewTabURL('/purchaseorder-issued-list')" 
						style="text-decoration: none;">{{$carbon->format('M')}}</a>
			</button>
			@else
			@if($doc=='Purchase Order Received' && ($purchaseorders_rev[$i] != 0))
			<button class="btn-enable btn btn-sm primary-btn">
				<a href="javascript:openNewTabURL('/purchaseorder-received-list')" 
				style="text-decoration: none;">{{$carbon->format('M')}}</a>
			</button>
			@else
			@if($doc=='Sales Order' && ($salesOrder[$i] != 0))
			<button class="btn-enable btn btn-sm primary-btn">
				<a href="javascript:openNewTabURL('/salesorder-issued-list');" 
					style="text-decoration: none;">{{$carbon->format('M')}}</a>
			</button>
			@else
			@if($doc=='Quotation/Tender Issued' && (false))
			<button class="btn-enable btn btn-sm primary-btn">
				<a href="javascript:openNewTabURL('/quotation-issued-list')" 
				style="text-decoration: none;">{{$carbon->format('M')}}</a>
			</button>
			@else
			@if($doc=='Quotation/Tender Received' && (false))
			<button class="btn-enable btn btn-sm primary-btn">
				<a href="javascript:openNewTabURL('/quotation-received-list')" 
					style="text-decoration: none;">{{$carbon->format('M')}}</a>
			</button>
			@else
			@if($doc=='Consignment Note Issued' && (false))
			<button class="btn-enable btn btn-sm primary-btn">
				<a href="javascript:openNewTabURL('/consignment-issued-list'" 
					style="text-decoration: none;">{{$carbon->format('M')}}</a>
			</button>
			@else
			@if($doc=='Consignment Note Received' && (false))
			<button class="btn-enable btn btn-sm primary-btn">
				<a href="javascript:openNewTabURL('/consignment-received-list')"
					style="text-decoration: none;">{{$carbon->format('M')}}</a>
			</button>
			@else
            @if($doc=='Inventory Cost' && $inventoryCost[$i]!=0)
                <button class="btn-enable btn btn-sm primary-btn js-inventory-cost" data-month="{{$i}}">
                    {{$carbon->format('M')}}
                </button>
            @else
			@if($doc=='Receipt  Issued' && (false))
			<button class="btn-enable btn btn-sm primary-btn">
				<a href="javascript:openNewTabURL('/receipt-goldfish-issued-list');"
					style="text-decoration: none;">{{$carbon->format('M')}}</a>
			</button>
			@else
			@if($doc=='Receipt  Received' && (false))
			<button class="btn-enable btn btn-sm primary-btn">
				<a href="javascript:openNewTabURL('/receipt-goldfish-received-list');" 
					style="text-decoration: none;">{{$carbon->format('M')}}</a>
			</button>
			@else
			@if(!empty($damagewastedate) &&
				$doc=='Wastage & Damage Declaration' &&
				$damagewastedate[$i]!=0)
                <button class="btn-enable btn btn-sm primary-btn wastageDamage"
                    data-year=" {{date('Y' , strtotime(@$start_yr))}}" data-month="{{$i+1}}"
                    enabled>{{$carbon->format('M')}}
                    <!-- <a href="/wastage-list" target="_blank" style="text-decoration: none;"></a> -->
                </button>
            @else
			@if($doc=='Picking List' && (false))
			<button class="btn-enable btn btn-sm primary-btn">
				<a href="javascript:openNewTabURL('/picking-list')" 
					style="text-decoration: none;">{{$carbon->format('M')}}</a>
			</button>
			@else
			@if($doc=='Receipt  OPOSsum' && (($opmonthreceipt[$i])!=0))
			<button class="btn-enable btn btn-sm primary-btn"  
				 onclick="oposum_model('{{$carbon->format('My')}}','{{$i}}')">
				{{$carbon->format('M')}}
			</button>
			@else
			@if($doc=='Tracking Report' && !empty($tracking_report[$i]))
			<button class="btn-enable btn btn-sm primary-btn" 
				 onclick="tr_model('{{$carbon->format('My')}}','{{$i}}')">
				{{$carbon->format('M')}}
			</button>
			@else
			@if($doc=='Receipt Issued' && !empty($arpayment_issued[$i]))
			<button class="btn-enable btn btn-sm primary-btn" 
				onclick="openNewTabURL('{{route("receipt.payment.issue_list")}}')">
				{{$carbon->format('M')}}
			</button>
		
			@else
			@if($doc == 'Receipt Received' && !empty($arpayment_rev[$i]))
			<button class="btn-enable btn btn-sm primary-btn" 
				 onclick="openNewTabURL('{{route("receipt.payment.rev_list")}}')">
				{{$carbon->format('M')}}
			</button>

			@else
			<button class="btn-disable btn btn-sm primary-btn gaurav">
				{{$carbon->format('M')}}
			</button>
			@endif
			@endif
			@endif
			@endif
			@endif
			@endif
			@endif
			@endif
			@endif
			@endif
			@endif
			@endif
			@endif
            @endif
			@endif
			@endif
			@endif
			@endif
			@endif
			@endif
			@endif
			@endif
			@endif
			@endif
				<?php  $carbon->month = ++$carbon->month; ?>
			@endfor
		</div>
	<?php } ?>

	@foreach($myreturn as $returned)
		{{--*/ $created_at = new Carbon\Carbon($returned->created_at); $carbon = new Carbon();
		$m = $years[$created_at->year]; sort($m);
		$month = $m[0]; $index = 0;/*--}}

		@if($y != $created_at->year)
		<div style="margin: 5px;">
			<span style="font-family: sans-serif;font-size: large;">
			{{$created_at->year}}&nbsp;</span>
			@for($i = 0,$carbon->month = 1; $i < 12; $i++)

				@if(in_array($carbon->month, $m) )

				/* invoicestatement has to be replaced with a dynamic
				 * method which accepts a document code */

				<button class="btn-enable btn btn-sm primary-btn"
					onclick="invoicestatement({{$id}},{{$created_at->year}},{{$carbon->month}});">
					<span id="hsto{{$created_at->year}}-{{$carbon->month}}">{{$carbon->format('M')}}</span>
					<span style="display: none;" id="isto{{$created_at->year}}-{{$carbon->month}}">...</span>
				</button>
				{{--*/ if($index < count($m) - 1)$month = $m[++$index]; /*--}}

				@else
				<button class="btn-disable btn btn-sm primary-btn {{$i}}" disabled>
					{{$carbon->format('M')}}
				</button>
				@endif

				<?php  $carbon->month = ++$carbon->month; ?>

			@endfor
		</div>
		@endif
		{{--*/ $y = $created_at->year; /*--}}
		<?php $i++;?>
	@endforeach {{-- $myreturn as $return --}}
</div>
</div>
<br>

@endforeach {{-- $docs as doc --}}
<br><br>
<div id="response"></div>


<div class="modal fade" id="inventory-cost-modal" tabindex="-1" role="dialog" aria-modal="true">
	<div class="modal-dialog modal-lg modal-dialog-centered" style="min-width: 140vh;">
		<div class="modal-content">
			<div class="modal-header">
				<h3 class="mb-0">Inventory Cost</h3>
			</div>
			<div class="modal-body">

				<table class="table table-bordered" id="inventory-cost-datatable" style="width:100%;">
					<thead class="thead-dark" style="background-color: #ed5336;">
						<tr>
							<th>No</th>
							<th>Document No</th>
							<th>Dated</th>
						</tr>
					</thead>
					<tbody>

					</tbody>
				</table>
			</div>

		</div>
	</div>
</div>


<div class="modal fade" id="wastedamage-modal" tabindex="-1" role="dialog" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered" style="min-width: 750px;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="mb-0">Wastage & Damage Declaration</h3>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="wastage_table"
						width="100%">
                        <thead class="thead-dark">
                            <tr>
                                <th style="width:30px">No.</th>
                                <th style="width:120px">Document ID</th>
                                <th style="width:80px">Date</th>
                                <th style="width:auto"> Branch</th>
                            </tr>
                        </thead>
                    </table>
              </div>
            </div>
        </div>
    </div>
</div>




<div class="modal fade" id="creditissue-modal" tabindex="-1" role="dialog" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered" style="min-width: 750px;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="mb-0" id='creditheader'>Credit Note Issued</h3>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="creditIssue_table" width="100%">
                        <thead class="thead-dark">
                            <tr>
                                <th style="width:200px">No.</th>
                                <th>Document ID</th>
                                <th>Date</th>
                                <th style="width:auto"> Company Name</th>
								<th style="width:95px;">Amount</th>
                            </tr>
                        </thead>
                    </table>
				</div>
            </div>
        </div>
    </div>
</div>



<div class="modal fade" id="debitissue-modal" tabindex="-1" role="dialog" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered" style="min-width: 750px;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="mb-0" id='debitheader'>Debit Note Issued</h3>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="debitIssue_table" width="100%">
                        <thead class="thead-dark">
                            <tr>
                                <th style="width:200px">No.</th>
                                <th>Document ID</th>
                                <th>Date</th>
                                <th style="width:auto"> Company Name</th>
								<th style="width:95px;">Amount</th>
                            </tr>
                        </thead>
                    </table>
              </div>

            </div>

        </div>
    </div>
</div>


<script>
$(document).ready(function(){

	$(".js-inventory-cost").on('click', function() {
		var month = $(this).attr('data-month');

		if ($.fn.DataTable.isDataTable('#inventory-cost-datatable') ) {
			$('#inventory-cost-datatable').DataTable().destroy();
		}
		$('#inventory-cost-datatable tbody').empty();

		var productInventoryCost = $('#inventory-cost-datatable').DataTable({
			pageLength: 10,
			bPaginate: true,
			info: false,
			ordering: true,
			responsive: true,
			processing: true,
			serverSide: true,
			bFilter: true,
			"ajax": {
				"url": "{{route('vcab.ajax.inventoryCosts')}}",
				"type": "POST",
				"data": {
					"month": month
				},
				'headers': {
					'X-CSRF-TOKEN': '{{ csrf_token() }}'
				},
			},
			initComplete: function() {
				$("#inventory-cost-modal").modal('show');
			},
			columns: [
				{
					// no
					className: "dt-center indexNumber-width",
					mRender: function(data, type, full) {
						return full.indexNumber;
					}
				},
				{
					className: "dt-center",
					mRender: function(data, type, full) {
						var template = '<p class="os-linkcolor loyaltyOutput js-document-no" data-id="'+full.inventory_cost_id+'" style="cursor: pointer; margin: 0; text-align: center;"><a href="/view-document-inventory-cost/'+full.inventory_cost_id+'" target="_blank">'+full.doc_no+'</a></p>';
						return template;
					}
				},
				{
					className: "text-center dated-width",
					mRender: function(data, type, full) {
						return full.dated;
					}
				}
				/*
				,

				{
					className: "text-right",
					mRender: function(data, type, full) {
						return full.cost;
					}
				},
				{
					className: "text-center",
					mRender: function(data, type, full) {
						return full.quantity;
					}
				}
				*/
			],
			"columnDefs": [
			]
		});


    });

	
    
    $(".wastageDamage").on('click', function() {

        var month = $(this).attr('data-month');
        var year = $(this).attr('data-year');
        if ($.fn.DataTable.isDataTable('#wastage_table')) {
            $('#wastage_table').DataTable().destroy();
        }
        $('#wastage_table tbody').empty();

        var wastageTable = $('#wastage_table').DataTable({
            pageLength: 10,
            bPaginate: true,
            info: false,
            ordering: true,
            responsive: true,
            processing: true,
            serverSide: true,
            bFilter: true,
            columnDefs: [{
                    "className": "dt-center",
                    "targets": [0, 1, 2]
                }
            ],
            "ajax": {
                "url": "{{route('wastage.wastagelistform')}}",
                "type": "POST",
                "data": {
                    "month": month,
                    "year": year
                },
                'headers': {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
            },
            initComplete: function() {
                $("#wastedamage-modal").modal('show');

            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex'},
                {
                    data: 'inven_pro_id',
                    name: 'inven_pro_id'
                },
                {
                    data: 'inven_pro_date',
                    name: 'inven_pro_date'
                },
                {
                    data: 'inven_pro_branch',
                    name: 'inven_pro_branch'
                },
            ],
            "order": [],
        });
    });



	//creditNoteIssue
 	$(".creditNoteIssue").on('click', function() {

        var month = $(this).attr('data-month');
        var year = $(this).attr('data-year');
        if ($.fn.DataTable.isDataTable('#creditIssue_table')) {
            $('#creditIssue_table').DataTable().destroy();
        }
        $('#creditIssue_table tbody').empty();

        var creditNoteTable = $('#creditIssue_table').removeAttr('width').DataTable({
            pageLength: 10,
            bPaginate: true,
            info: false,
            ordering: true,
            responsive: true,
            processing: true,
            serverSide: true,
            bFilter: true,
            columnDefs: [
				{
                    "className": "dt-center no-line",
                    "targets": [0, 1]
                },
				{
                	"className": 'text-left',
					"targets": [3]
                },
                {
                   "className": 'text-right',
                    "targets": [4]
                }
            ],
            "ajax": {
                "url": "{{route('creditnote.issued')}}",
                "type": "POST",
                "data": {
                    "month": month,
                    "year": year,
					"type": "Issue"
                },
                'headers': {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
            },
            initComplete: function() {
				$('#creditheader').html('Credit Note Issued');
                $("#creditissue-modal").modal('show');
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex'},
                {
                    data: 'inven_pro_id',
                    name: 'inven_pro_id'
                },
                {
                    data: 'inven_pro_date',
                    name: 'inven_pro_date'
                },
                {
                    data: 'inven_pro_branch',
                    name: 'inven_pro_branch'
                },
				{
                    data: 'amount',
                    name: 'amount'
                },
            ],
            "order": [],
        });
    });

	//debitNoteIssue
 	$(".debitNoteIssue").on('click', function() {

        var month = $(this).attr('data-month');
        var year = $(this).attr('data-year');
        if ($.fn.DataTable.isDataTable('#debitIssue_table')) {
            $('#debitIssue_table').DataTable().destroy();
        }
        $('#debitIssue_table tbody').empty();

        var wastageTable = $('#debitIssue_table').removeAttr('width').DataTable({
            pageLength: 10,
            bPaginate: true,
            info: false,
            ordering: true,
            responsive: true,
            processing: true,
            serverSide: true,
            bFilter: true,
            columnDefs: [{
                    "className": "dt-center",
                    "targets": [0, 1, 2]
                },
				{
                	"className": 'text-left',
                     "targets": [3]
                },
                {
                   "className": 'text-right',
                    "targets": [4]
                }
            ],
            "ajax": {
                "url": "{{route('debitnote.issued')}}",
                "type": "POST",
                "data": {
                    "month": month,
                    "year": year,
					"type": 'Issue'
                },
                'headers': {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
            },
            initComplete: function() {
				$('#debitheader').html('Debit Note Issued');
                $("#debitissue-modal").modal('show');
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex'},
               
                {
                    data: 'inven_pro_id',
                    name: 'inven_pro_id'
                },
                {
                    data: 'inven_pro_date',
                    name: 'inven_pro_date'
                },
                {
                    data: 'inven_pro_branch',
                    name: 'inven_pro_branch'
                }
				,
				 {
                    data: 'amount',
                    name: 'amount'
                },
            ],
            "order": [],
        });
    });



	//creditNoteReceived
 	$(".creditNoteReceived").on('click', function() {

        var month = $(this).attr('data-month');
        var year = $(this).attr('data-year');
        if ($.fn.DataTable.isDataTable('#creditIssue_table')) {
            $('#creditIssue_table').DataTable().destroy();
        }
        $('#creditIssue_table tbody').empty();

        var creditNoteTable = $('#creditIssue_table').removeAttr('width').DataTable({
            pageLength: 10,
            bPaginate: true,
            info: false,
            ordering: true,
            responsive: true,
            processing: true,
            serverSide: true,
            bFilter: true,
            columnDefs: [{
                    "className": "dt-center",
                    "targets": [0, 1]
                },
				{
                	"className": 'text-left',
					"targets": [3]
                },
                {
                   "className": 'text-right',
                    "targets": [4]
                }
            ],
            "ajax": {
                "url": "{{route('creditnote.issued')}}",
                "type": "POST",
                "data": {
                    "month": month,
                    "year": year,
					"type": "Received"
                },
                'headers': {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
            },
            initComplete: function() {
               
				$('#creditheader').html('Credit Note Received');
 				$("#creditissue-modal").modal('show');
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex'},
               
                {
                    data: 'inven_pro_id',
                    name: 'inven_pro_id'
                },
                {
                    data: 'inven_pro_date',
                    name: 'inven_pro_date'
                },
                {
                    data: 'inven_pro_branch',
                    name: 'inven_pro_branch'
                },
				 {
                    data: 'amount',
                    name: 'amount'
                },
            ],
            "order": [],
        });
    });

	//debitNoteReceived
 	$(".debitNoteReceived").on('click', function() {

        var month = $(this).attr('data-month');
        var year = $(this).attr('data-year');
        if ($.fn.DataTable.isDataTable('#debitIssue_table')) {
            $('#debitIssue_table').DataTable().destroy();
        }
        $('#debitIssue_table tbody').empty();

        var wastageTable = $('#debitIssue_table').removeAttr('width').DataTable({
            pageLength: 10,
            bPaginate: true,
            info: false,
            ordering: true,
            responsive: true,
            processing: true,
            serverSide: true,
            bFilter: true,
            columnDefs: [{
                    "className": "dt-center",
                    "targets": [0, 1, 2]
                },
				{
                	"className": 'text-left',
                     "targets": [3]
                },
                {
                   "className": 'text-right',
                    "targets": [4]
                }

            ],
            "ajax": {
                "url": "{{route('debitnote.issued')}}",
                "type": "POST",
                "data": {
                    "month": month,
                    "year": year,
					"type": 'Received'
                },
                'headers': {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
            },
            initComplete: function() {
              
				$('#debitheader').html('Debit Note Received');
				$("#debitissue-modal").modal('show');


            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex'},
               
                {
                    data: 'inven_pro_id',
                    name: 'inven_pro_id'
                },
                {
                    data: 'inven_pro_date',
                    name: 'inven_pro_date'
                },
                {
                    data: 'inven_pro_branch',
                    name: 'inven_pro_branch'
                }
				,
				 {
                    data: 'amount',
                    name: 'amount'
                },
            ],
            "order": [],
        });
    });






});


function oposum_model(date,month) {
    $.ajax({
		url: "{{route('vcab.terminal_model')}}",
		type: 'post',
		headers:{
			'X-CSRF-TOKEN': '{{ csrf_token() }}'
		},
		data: {
			'date': date,
			'month':month
		},
		success: function (response) {
			$('#response').html(response);
			$('#terminalModal').modal('show');
		},
		error: function (e) {
			$('#response').html(e);
		}
    });
}

function tr_model(date,month) {
	$.ajax({
		url: "{{route('vcab.tracking_report_model')}}",
		type: 'post',
		headers:{
			'X-CSRF-TOKEN': '{{ csrf_token() }}'
		},
		data: {
			'date': date,
			'month':month
		},
		success: function (response) {
			$('#response').html(response);
			$('#terminalModal').modal('show');
		},
		error: function (e) {
			$('#response').html(e);
		}
	});
}
</script>
