<div style='margin-top:10px;margin-left: 0;margin-right: 16px;' class='row'>
	<div class="col-md-12" style='margin-top:10px;color:#27a98a;font-weight:bold;' >
        <h4 style="padding-bottom:5px;border-bottom:1px solid #e0e0e0;">
			Organisation Hierarchy</h4> 
		<div class="col-md-12" style="padding-left: unset;padding-right: unset;">
		<div class="row">
			<div class="col-md-2 col-sm-6" style="padding-left: unset;padding-right: unset;">
				<label class="form-control"
				style="padding-left:15px;border:unset">Department</label>
			</div>
			<div class="col-md-10 col-sm-6" style="padding-left: unset;padding-right: unset;">
				<select class="form-control input-30" style="margin-left: 2px;width: 31%;display: inline-flex;" id="oh_department">
				<option class="form-control" value="select_null">Department View</option>
				@foreach ($department as $c)
				<option class="form-control" value="{{$c->id}}">{{$c->field_value}}</option>
				@endforeach
				</select>

				<button class="btn btn-sm btn-success introducernameButton btn-add"
					id='add_department' style="margin-left: 5px;padding-top: 0.3em;display: inline-block;">
					<i class="fa fa-plus text-white" id='add_department'>
					</i> Item
				</button>

				<button class="btn btn-sm btn-success introducernameButton btn-add" style="margin-left: 1px;padding-top: 0.3em;background-color:red;display: inline-block;border-color:red;" id="remove_department">
					<i class="fa fa-times text-white">
					</i> Item
				</button>
			</div>
		</div>
		</div>

		<div class="col-md-12" style="padding-left: unset;padding-right: unset;">
		<div class="row">
			<div class="col-md-2 col-sm-6" style="padding-left: unset;padding-right: unset;">
				<label class="form-control"
				style="padding-left:15px;border:unset">Position</label>
			</div>
			<div class="col-md-10 col-sm-6" style="padding-left: unset;padding-right: unset;">
				<select class="form-control input-30" id='oh_position' style="margin-left: 2px;width: 31%;display: inline-flex;">
				<option class="form-control" value="select_null">Position View</option>
				@foreach ($position as $c)
				<option class="form-control" value="{{$c->id}}">{{$c->field_value}}</option>
				@endforeach
				</select>
				<button class="btn btn-sm btn-success introducernameButton btn-add"
					id='add_position' style="margin-left: 5px;padding-top: 0.3em;display: inline-block;">
					<i class="fa fa-plus text-white">
					</i> Item
				</button>

				<button class="btn btn-sm btn-success introducernameButton btn-add" style="margin-left: 1px;padding-top: 0.3em;background-color:red;display: inline-block;border-color:red;" id="remove_position">
					<i class="fa fa-times text-white">
					</i> Item
				</button>
			</div>
		</div>
		</div>

		<div class="clearfix"></div>

		<div>
			<p style="margin-top:20px;color:red;margin-bottom: 0;">
			*The change of department and position will affect the username management, username pop up, department and position dropdowns, kindly click the views to test the additions.
			</p>
		</div>
	</div>
</div>
<style type="text/css">
	.focusDel:focus {
  		background: red;
  		color: #fff;
	}
	.focusDel {
		background: red;
		color: #fff;
	}
</style>

<script type="text/javascript">
$("#add_department").click(function(){
	getData('department');
});

$("#add_position").click(function(){
	getData('position');
});


$("#remove_department").click(function(){
	selectDelete('department');
});


$("#remove_position").click(function(){
	selectDelete('position');
});

function selectDelete(type) {
$('#oh_position').removeClass('focusDel');
$('#oh_department').removeClass('focusDel');

	if (type == 'position') {
		var data = $('#oh_position').addClass('focusDel');

		$('.focusDel').on('change', function() {
			deleteData('position');
		});

	} else if (type == 'department') {
		var data = $('#oh_department').addClass('focusDel');

		$('.focusDel').on('change', function() {
			deleteData('department');
		});
	}
}
function deleteData(type) {
	console.log(type)
	if (type == 'position') {
		var data = $('#oh_position').val();
	} else if (type == 'department') {
		var data = $('#oh_department').val();
	}

console.log(data)
//	if (data = 'select_null') {return null}
	
	$.ajax({
		url: "{{route('oH.getdata')}}",
		type: 'post',
		data: {
			'addType': 'deleted',
			'data': data,
		},
		success: function (response, textStatus, request) {
		$('#response').html(response);
			$("#deleteDataOH").modal('show');
		},
		error: function (e) {
			$('#response').html(e);
			$("#getDataOH").modal('show');
		}
	});

	$('#oh_position').removeClass('focusDel');
	$('#oh_department').removeClass('focusDel');	
}


function getData(type) {
	$.ajax({
		url: "{{route('oH.getdata')}}",
		type: 'post',
		data: {
			'addType': type
		},
		success: function (response, textStatus, request) {
		$('#response').html(response);
			$("#getDataOH").modal('show');
		},
		error: function (e) {
			$('#response').html(e);
			$("#getDataOH").modal('show');
		}
	});
}

</script>
