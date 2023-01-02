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

.btn-enable{background: lightblue;}
.btn-disable{background: #4d4d4d;color:white;}
.table{
	width: 100% !important;
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
		<h5 style="margin-bottom:0">20Jun19-19Jun20</h5>
	</div>
</div>

<?php
/* Test variables */
$myreturn = [];
$current_year = 0;

/* Complete list of support documents */
$docs = [
	'Receipt Issued',
	'Tracking Report',
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
	'Wastage Declaration',
	'Picking List'
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
		$carbon = new \Carbon\Carbon();
	?>
		<div style="margin: 5px;">
			<span style="font-family: sans-serif;font-size: large;">
				{{date('Y')}}&nbsp;</span>
			@for($i = 0,$carbon->month = 1; $i < 12; $i++)

			@if(($doc=='Delivery Order Issued' || $doc=='Delivery Order Received')&& ($i==5))
			<button class="btn-enable btn btn-sm primary-btn" disabled>
					<a href="/show-issue-dolist" target="_blank">{{$carbon->format('M')}}</a>
				</button>
			@else
			<button class="btn-disable btn btn-sm primary-btn" disabled>
					{{$carbon->format('M')}}
				</button>

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
						onclick="invoicestatement({{$id}}{{','}}{{$created_at->year}}{{','}}{{$carbon->month}});">
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

