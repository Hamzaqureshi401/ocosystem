<div style='margin-top:10px;margin-left: 0;margin-right: 16px;' class='row'>
	<div class="col-md-12"
		style='margin-top:5px; color:#27a98a;font-weight:bold;padding-left:0' >
        <h4 style="padding-bottom:5px;border-bottom:1px solid #e0e0e0;">
			Domicile & Jurisdiction
		</h4>   
        <div class="col-md-6" style="padding-left: unset;padding-right: unset;">
			<div class="row">
            <div class="col-md-4 col-sm-6"
				style="padding-left: unset;padding-right: unset;">
				<label class="form-control"
				style="padding-left:15px;border:unset">Country</label>
			</div>
			<div class="col-md-6 col-sm-6"
				style="padding-left: unset;padding-right:unset;
					max-width: 51%;flex: 0 0 50.52%;">
				<select class="form-control" id="country_select" >
				<option class="form-control" value="select_Country">
					Select country
				</option>
				@foreach ($country_list as $c)
				<option class="form-control" value="{{$c->id}}">
					{{$c->name}}
				</option>
				@endforeach
				</select>
			</div>
		</div>
		</div>
		<div class="col-md-6" style="padding-left: unset;padding-right: unset;">
		<div class="row">
		<div class="col-md-4 col-sm-6"
			style="padding-left: unset;padding-right: unset;">
			<label class="form-control"
				style="padding-left:15px;border:unset">Currency
			</label>
		</div>
		<div class="col-md-6 col-sm-6"
			style="padding-left: unset;padding-right: unset;
			max-width: 51%;flex: 0 0 50.52%;">
		<select class="form-control" id="currency_select" >
            <option class="form-control" value="currency_select">
				Select currency
			</option>
            @foreach ($currency_list  as $c)
            <option class="form-control" value="{{$c->id}}">
				{{$c->name}}, {{$c->code}}
			</option>
            @endforeach
		</select>
		</div>
		</div>
		</div>
		<div class="clearfix"></div>
		<div id='msg'></div>
	</div>
	</div>
</div>

<script>
$('#country_select option[value="{{$selected_country}}"]').attr("selected","selected");
$('#currency_select option[value="{{$selected_currency}}"]').attr("selected","selected");

$('#country_select').change(function() {
	if ( $('#country_select').val() != 'select_Country' ) {
		$.ajax({
			url: "{{route('country.update')}}",
			type: 'post',
			data: {
					'countryID':  $('#country_select').val(),
			},
			success: function (response, textStatus, request) {
			$('#msg').html(response);
			},
			error: function (e) {
				$('#msg').html(e);
			}
		});
	}
});

$('#currency_select').change(function() {
	if ( $('#currency_select').val() != 'currency_select' ) {
		$.ajax({
			url: "{{route('currency.update')}}",
			type: 'post',
			data: {
				'currencyID':  $('#currency_select').val(),
			},
			success: function (response, textStatus, request) {
				$('#msg').html(response);
			},
			error: function (e) {
				$('#msg').html(e);
			}
		});
	}
});

</script>
