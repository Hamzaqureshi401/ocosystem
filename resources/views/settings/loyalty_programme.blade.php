<style type="text/css">
.upload>div {
	margin: auto;
	word-break: break-all;
}

.add > i {
	color: #fff;
}

.green {color:#28a745 !important;}

.editbtn:hover,.editbtn:active {
    background: transparent;
    color: #34dabb;
    border: 1px #34dabb solid;
    font-weight: bold;
}
.editbtn {
    background: transparent;
    color: #34dabb;
    border: 1px #34dabb solid;
    width:75px;
    height:40px;
}
.editbtn_activated{
    background: transparent;
    color: #34dabb;
    border: 1px #34dabb solid;
    font-weight: bold;
}
.edit_text {
	float: right;
	font-weight: normal;
	cursor: pointer;
    color: #34dabb;
}

</style>
<div class="top_row"
	style='margin-top:10px;'>
	<div class="row" style="width: 100%;border-bottom:1px solid #e0e0e0;margin-left: 0 ">
    <div class="col-md-11 align-items-center"
		style="display:flex;padding:0; color:#27a98a;font-weight:bold;">
           <h4 style="margin-bottom:0;">Loyalty Programme</h4>
    </div>
    <div class="col-md-1 col-auto align-items-center text-right"
		style="display:flex">
       <button class="btn editbtn mb-0" style=""
			id="editSaveToggle">Edit</button>

		<!--
        <button class="btn btn-primary btn-lg"
			style="width:120px;float:right;margin-bottom:5px;"
			id="editSaveToggle">Edit</button>
		-->
    </div>
</div>

<div class="row row_mb mt-3" id='updateProspectFields'>
	<label class="col-sm-3 col-form-label">
	Loyalty Programme 1 {{$currency}}&nbsp;&nbsp;&nbsp;=
	</label>
	<div class="col-sm-3" style="padding-right: 5px;">
	<input class="form-control text-center" placeholder=""
		value="{{$company_data->loyalty_pgm}}" 
		disabled=""	id="loyalty_programm_input_field"
		style="width:30%;display:inline-block;"
		name="loyalty_programm"> Pts
	</div>
</div>
<div id="loyalty_programm_res"></div>
<script>
	$('#editSaveToggle').click(function(){
		let event_name = $('#editSaveToggle').html()	

		if (event_name == 'Save') {
				r_val = $("#loyalty_programm_input_field").val();
				$.post('{{route('loyalty_programme.update')}}', {r_val}).
					done( (res) => {
					
						$('#editSaveToggle').html('Edit');
						$('#updateProspectFields :input').prop("disabled", true);
						$("#loyalty_programm_res").html(res);
				});

		} else if (event_name == 'Edit') {
			$('#updateProspectFields :input').prop("disabled", false);
			$('.greencrab_custom').removeClass('displayNone');
			$('.redcrab_custom').removeClass('displayNone');
			$('#editSaveToggle').html('Save')
		}
	});


</script>
