<div style='margin-top:5px' class='row'>
	<div class="row col-md-12 textarea_box"
		style='padding-top:0 !important;' >
		
<?php $docs = [ 
				'Sales Order' => "so",
				'Purchase Order' => "po",
				'Invoice' => "inv",
				'Delivery Order' => "do",
				'Debit Note' => "dn",
				'Credit Note' => "cn",
				'Receipt' => "rcp",
				'Consignment Note' => "consign",
				'Quotation' => "quo"
		];
	?>

	@foreach ($docs as $doc => $db)
	<div style="display:flex;align-items:flex-end"
		class="col-md-12 title_hdr">
		<div class="col pl-0" style="width:80%">
			<h3 class="float-left"
				style="margin-bottom:0 !important;">
				{{$doc}}
			</h3>
		</div>

		<div class="col pr-0" style="width:20%">
			<button 
				class="float-right btn logobtn logo_text mb-2 @php
				$var = $db."_has_logo";
				if (isset($data->$var)) {
					echo $data->$var == 1 ? "logobtn_activated":null;
				}
			@endphp"
				style=""
				onclick="change_data(this,'{{str_replace(" ",'_', $doc)}}_logo')">Logo
			</button>
		</div>

	</div>
	<div class="col-md-12 title_txbox">
		<textarea class="form-control"
			onchange="change_data(this, '{{str_replace(" ",'_', $doc)}}' )"
			placeholder="Please enter footer message here" rows="4">@php
				$var = $db."_footer";
				echo $data->$var ?? null;;
			@endphp</textarea>
	</div>
	@endforeach

	</div>		
</div>

<div id="res"></div>
<style type="text/css">
.logo_text {float: right;font-weight: 500;cursor: pointer;color: #aaa}
.textarea_box > div > h3 {float: left;}
.text_green {color:#34dabb !important;font-weight: 700;font-size: 18px}
.title_hdr{padding-bottom:0;border-bottom:1px solid #e0e0e0;
		color:#27a98a;font-weight:bold}
.title_txbox{padding-top: 10px;padding-bottom: 20px;margin-bottom:20px}

.logobtn:hover,.logobtn:active {
	background: transparent;
	color: #34dabb;
	border: 1px #34dabb solid;
	font-weight: bold;
}
.logobtn {
	background: transparent;
	color: #ccc ;
	border: 1px #ccc solid;
	width:75px;
	height:40px;
}
.logobtn_activated{
	background: transparent;
	color: #34dabb;
	border: 1px #34dabb solid;
	font-weight: bold;
}


</style>

<script type="text/javascript">
$('.logo_text').click(function(e) {
	var target = $( e.target );
	$(target).toggleClass('text_green');
	$(target).toggleClass('logobtn_activated');
});

var change_data =  function(e, field) {
	val = $(e).val();
	if (val == undefined) {
		val = 0;
	}
	send_ajax_data_update(val, field);
}

var send_ajax_data_update = function (val, field) {
	$.post("{{route('landing.ajax.updateHeaderFooter')}}",{
		field:field,
		data: val
	}).done(function(res) {
		$("#res").html(res);
	});
}
</script>
