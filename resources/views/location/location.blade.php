<style>
	a.foodcourt-link:hover {
		text-decoration: none;
	}

	.dt-valign {
		vertical-align:middle !important;
	}
</style>
<div id="landing-content" style="width: 100%">
    <div class="clearfix"></div>
	<div class="row py-2"
		style="padding-top:0 !important;padding-bottom:0 !important">
		<div class="col align-self-center" style="width:80%">
			<h2 style="margin-bottom:0">Location Management</h2>
		</div>

		<div class="col col-auto align-self-center">
			<button class="btn btn-success btn-log sellerbuttonwide
				"style="padding-left:8px" id="addwarehousebutton"
				onclick=" newLocation(true);"><span>+Warehouse</span>
			</button>
			<button class="btn btn-success btn-log sellerbuttonwide"
				style="padding-left:8px;margin-right:5px" id="addfoodcourtbutton"
				onclick="newLocation('foodcourt'); ">
				<span>+FoodCourt</span>
			</button>
			<button class="btn btn-success btn-log sellerbutton"
				style="padding-left:8px;margin-right:0" id="addbranchbutton"
				onclick="newLocation(false);"> <span>+Branch</span>
			</button>

		</div>
    </div>

    <table class="table table-bordered" id="tablelocation" style="width:100%;">
		<thead class="thead-dark">
		<tr>
			<th style="width:30px;text-align: center;">No</th>
			<th style="width:100px;text-align: center;">Location ID</th>
			<th class="text-left" style="width:250px;">Branch</th>
			<th>Address</th>
			<th style="width:100px;text-align: center;">Warehouse</th>
			<th style="width:100px;text-align: center;">FoodCourt</th>
			<th style="width:100px;text-align: center;">Licence Key</th>
			<th style="width:100px;text-align: center;">Hardware&nbsp;Address</th>
			<th style="width:30px;text-align: center;">Retag</th>
			<th style="width:30px;text-align: center;"></th>
			<th style="width:30px;text-align: center;"></th>
		</tr>
		</thead>
		<tbody></tbody>
	</table>
</div>
<div id="shwoLocationModal" style="text-align: center;"></div>

<!-- select options modal -->
<input type="hidden" name="rowId" id="rowId">
<div class="modal fade" id="selectOptions" tabindex="-1" role="dialog"
	aria-labelledby="staffNameLabel" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered"
	role="document">
	<div class="modal-content modal-inside bg-greenlobster">
	<div class="modal-body">
		<div class="col-md-12"
			style="display:flex;justify-content:center">
			<button type="button"
				class="btn btn-success primary-button sellerbuttonwide"
				style="margin-top:0;margin-bottom:0;margin-right:5px;
					margin-left: 0;"
				data-dismiss="modal">
				Consignment
			</button>

			<button type="button"
				class="btn btn-success bg-red sellerbuttonwide"
				id="opt_hour" style="margin-top: 0;margin-bottom:0;"
				data-toggle="modal" data-dismiss="modal"
				data-target="#popupLocation" >
				Terminal Operation Hour
			</button>

			@if (Auth::user() && Auth::user()->type == 'admin')
			<button type="button"
				class="btn btn-success bg-black sellerbuttonwide"
				id="controller_mgt" style="margin-top: 0;margin-bottom:0;"
				data-toggle="modal" data-dismiss="modal" data-target="#"
				onclick="passLocId()">
				Forecourt Controller
			</button>
			@endif
		</div>
	</div>
	</div>
</div>
</div>
<!-- // select options -->

<!-- terminal operation hour modal -->
<div class="modal fade" id="popupLocation" tabindex="-1" role="dialog"
	aria-labelledby="staffNameLabel" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered mw-75 w-50"
	role="document">
<div class="modal-content modal-inside bg-greenlobster">
	<div class="modal-header text-center" style="border-width:0"></div>
	<div class="modal-body text-center " style="height:200px">
		<h3 class="text-white"
		id="statusModalLabel">Terminal Operation Hour</h3>
		<br/>
	<div class="row">
	<div class="col-md-4">
		<label>Start Time</label>
		<select type="time" name="time"
			onmousedown="if(this.options.length>6){this.size=6;}"
			onchange="this.blur()"
			onblur="this.size=0;" size="1" id='starttime'
			class="form-control" step="1"
			style="cursor:not-allowed;padding-right: 0;text-align: center;" >

			@for ($h=00; $h < 24; $h++)
			@for ($m=00; $m < 60; $m++)
				{!! $time = strtotime($h.":".$m.":00") !!}
				{!! $time = date('H:i:s', $time) !!}
				<option value={!! $time !!}>{!! $time !!}</option>
			@endfor
			@endfor
		</select>
	</div>
	<div class="col-md-4">
		<label>Close Time</label>
		<select type="time" name="time"
			onmousedown="if(this.options.length>6){this.size=6;}"
			onchange="this.blur()"
			onblur="this.size=0;" size="1" id='endtime'
			class="form-control" step="1"
			style="cursor:not-allowed;padding-right: 0;text-align: center;">

			@for ($h=0; $h < 24; $h++)
			@for ($m=0; $m < 60; $m++)
				{!! $time = strtotime($h.":".$m.":00") !!}
				{!! $time = date('H:i:s', $time) !!}
				<option value={!! $time !!}>{!! $time !!}</option>
			@endfor
			@endfor
		</select>
	</div>
	<div class="col-md-4">
		<button type="button"
		class="btn btn-danger primary-button "
		style="margin-top: 30px; cursor: pointer" id="reset">Reset</button>
		<button type="button"
		class="btn btn-primary primary-button "
		style="margin-top: 30px; cursor: not-allowed" id="set" >OK</button>
	</div>
	</div>
	</div>
</div>
</div>
</div>
<!-- // terminal operation hour modal -->

<div class="modal fade creditLimitModalS" id="js-food-court-modal"
	tabindex="-1" role="dialog" aria-labelledby="staffNameLabel"
	aria-hidden="true" style="text-align: center;">

    <div class="modal-dialog modal-dialog-centered  mw-75 w-50"
         role="document" style="display: inline-flex;">
        <div class="modal-content modal-inside bg-greenlobster" style="width: 80%;">
            <div class="modal-header">
                <h3 style="margin-bottom:0px">FoodCourt Operator</h3>
            </div>
            <div class="modal-body" style="padding-top: 0px;">
                <div class="row mt-2">
                    <div class="col-md-12 text-center" id="js-operator-name" style="font-size: 18px;"></div>
                </div>
                <div class="row" style="padding-top: 5px;">
                    <div class="col-md-12 text-center" id="js-operator-id" style="font-size: 18px;"></div>
                </div>

            </div>
        </div>
    </div>
</div>

 <!--  message modal -->
<div class="modal fade" id="messageModal" tabindex="-1" role="dialog"
	aria-labelledby="staffNameLabel" aria-hidden="true" >
	<div class="modal-dialog modal-dialog-centered  mw-75 w-50"
		role="document">
		<div class="modal-content modal-inside bg-greenlobster"
			style="width: 500px;">
            <div class="modal-header" style="border:0;">&nbsp;</div>
			<div class="modal-body text-center">
				<h5 class="text-center" id="info"></h5>
			</div>
            <div class="modal-footer" style="border:0;">&nbsp;</div>
		</div>
	</div>
</div>
 <!-- // message modal -->

 <div class="modal fade" id="confirmLModal" tabindex="-1" 
		role="dialog"  
			aria-labelledby="logoutModalLabel" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered  mw-75 w-50" role="document">
        <div class="modal-content modal-inside bg-greenlobster">
            <div style="border:0" class="modal-header"></div>
            <div class="modal-body text-center">
                <h5 class="modal-title text-white" id="logoutModalLabel">
				Are you sure you want to issue license key?</h5>
            </div>
            <div class="modal-footer" style="border-top:0 none; padding-left: 0px; padding-right: 0px;">
                <div class="row" style="width: 100%; padding-left: 0px; padding-right: 0px;">
                    <div class="col col-m-12 text-center">
						<a class="btn btn-primary" href="#!" style="width:100px" 
						onclick="license_key.action()" data-dismiss="modal">
                            Confirm
                        </a>
                        <button type="button" class="btn btn-danger" data-dismiss="modal" style="width:100px">Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<div class="modal fade" id="license_key_modal"  tabindex="-1" 
	role="dialog">
        <div class="modal-dialog modal-dialog-centered  mw- 75 w- 50" role="document">
            <div class="modal-content modal---inside bg-greenlobster" >
		<div class="modal-header" >
			<h3 class="modal-title text-white">License Key</h3>
            	</div>
		<div class="modal-body">
			<h2 id="license_key_field" style="color:#fff;"></h2>
		</div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="resetHardwareConfirmModal" tabindex="-1" 
		role="dialog"  
			aria-labelledby="logoutModalLabel" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered  mw-75 w-50" role="document">
        <div class="modal-content modal-inside bg-greenlobster">
            <div style="border:0" class="modal-header"></div>
            <div class="modal-body text-center">
                <h5 class="modal-title text-white">
				Do you want to retag this location to new hardware?</h5>
            </div>
            <div class="modal-footer" style="border-top:0 none; padding-left: 0px; padding-right: 0px;">
                <div class="row" style="width: 100%; padding-left: 0px; padding-right: 0px;">
                    <div class="col col-m-12 text-center">
						<a class="btn btn-primary" href="#!" style="width:100px" 
						onclick="reset_confirm.action()" data-dismiss="modal">
                            Confirm
                        </a>
                        <button type="button" class="btn btn-danger" data-dismiss="modal" style="width:100px">Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<span id="loc-Id" style="display: none;"></span>

<style>
	.btn {color: #fff !Important;}
</style>

<style>
.form-control:disabled, .form-control[readonly] {
    background-color: #e9ecef !important;
    opacity: 1;
}
</style>
<div id="res"></div>
<script type="text/javascript">

    $(document).ready(function(){

        $("#tablelocation").on('click', '.js-food-court', function() {
            var self = $(this);
            var tableRow = $('#tablelocation').DataTable().row($(this).closest('tr')).data();
            var terminal_id = $(tableRow['terminal_id']).html();

            $("#js-operator-name").text($(self).data('operator-name'));
            $("#js-operator-id").text($(self).data('operator-id'));
        });
    })

    // terminal operating hour
    $('#opt_hour').on('click', function() {
        $('#starttime').attr('disabled','on');
        $('#endtime').attr('disabled','on');
        $('#set').attr('disabled','on');
        $('#set').hide();
        $('#reset').show();

        var rowId = $("#rowId").val();

        $.ajax({
            type: "post",
            url: "{{ route('location.terminalhour.get') }}",
            data: {
                rowId: rowId
            },
            success: function (response) {
                if (response.status == 202) {
                    $("#starttime").val(response.starttime);
                    $("#endtime").val(response.endtime);
                }
            }
        });
    });

	// After setting Start Time
	$('#starttime').on('change', function() {
		$('#endtime').removeAttr('disabled');
        $('#starttime').attr('disabled','on');
        $('#starttime').css('cursor','not-allowed');
        $('#endtime').css('cursor','pointer');
        $('#set').css('cursor','not-allowed')
	});

	// After setting Close Time
    $('#endtime').on('change', function() {
        $('#endtime').attr('disabled','on');
        $('#endtime').css('cursor','not-allowed');
        $('#set').removeAttr('disabled');
        $('#set').css('cursor','pointer');
    });

    // reset time
    $('#reset').on('click', function() {
        $('#starttime').removeAttr('disabled');
        $('#starttime').css('cursor','pointer');
        $('#reset').hide();
        $('#set').show();
    })


    // set time
    $('#set').on('click', function(){
        var endtime = $("#endtime").val();
        var starttime = $("#starttime").val();
        var rowId = $("#rowId").val();
        $.ajax({
            type:"POST",
            url:"{{ route('location.terminalhour.update') }}",
            data:{
                rowId:rowId,
                starttime:starttime,
                endtime:endtime,
                'staff_id': "{{Auth::User()->staff->systemid}}"
            },
            cache:false,
            success:function(res){
                if(res.status == 202) {
                    // hide time popup modal
                    $("#popupLocation").modal('hide');

                    // disable button
                    $("#endtime").attr('disabled', true);

                    // display message modal
                    $("#messageModal").modal('show');

                    // hide message modal
                    setTimeout(hide_mes, 3000);

                    // pass message
                    $("#info").html(res.message);

                } else {
                    // hide time popup modal
                    $("#popupLocation").modal('hide');

                    // display message modal
                    $("#messageModal").modal('show');

                    // hide message modal
                    setTimeout(hide_mes, 3000);

                    // style message
                    $("#info").css('color', 'red');

                    // pass message
                    $("#info").html(res.message);
                }
            }
        });
    });

    // hide modal message
    function hide_mes () {
        $("#messageModal").modal('hide');
    }


    $(document).ready(function () {
        locationTable.draw();
    });

	var locationTable = $('#tablelocation').DataTable({
		"processing": true,
		"serverSide": true,
		"autoWidth": false,
		"ajax": {
			"url": "{{route('location.ajax.index')}}",
			"type": "POST"
		},
		columns: [
			{data: 'DT_RowIndex', name: 'DT_RowIndex'},
			{data: 'loc_id', name: 'loc_id'},
			{data: 'branch', name: 'branch'},
			{data: 'address', name: 'address'},
			{data: 'warehouse', name: 'warehouse'},
			{data: 'foodcourt', name: 'foodcourt'},
			{data:'licence_key',name:'licence_key'},
			{data:'hwaddr',name:'hwaddr'},
			{data: 'retag', name:'retag'},
			{data: 'bluecrab', name: 'bluecrab'},
			{data: 'deleted', name: 'deleted'},
		],
		"order": [],
		"columnDefs": [
			{"className": "dt-valign", "targets": [2,3]},
			{"className": "dt-center dt-valign", "targets": [0,1,4,5,6,7,8,9,10]},
			{ orderable: false, targets: [8,9,10] },
		],
	});


	@if (Auth::user() && Auth::user()->type != 'admin')
		locationTable.column(7).visible(false);
		locationTable.column(8).visible(false);
	@endif

    function newLocation(iswarehouse) {
        data = new FormData();
        if (iswarehouse == true) {
			data.append("warehouse","yes")
        }else if(iswarehouse == 'foodcourt'){
            data.append("foodcourt","yes")
        } else {
			data.append("warehouse","no")
        }

		$.ajax({
            url: "{{route('location.store')}}",
            type: "POST",
            enctype: 'multipart/form-data',
            processData: false,
            contentType: false,
            cache: false,
            data: data,
            success: function (response) {
				locationTable.ajax.reload();
				$("#shwoLocationModal").html(response);
				$("#msgModal").modal('show');
				setTimeout(function() {$('#msgModal').modal('hide');}, 3000);

            }, error: function (e) {
                console.log(e.message);
            }
        });
    }

    $('#tablelocation tbody').on('click', 'td', function () {
        const tableCell = locationTable.cell(this).data();
        const tableRow = locationTable.row($(this).closest('tr')).data();
        const element = $(tableCell).data("field");
        $("#rowId").val(tableRow['id']);

        if (element != null) {
            $.ajax({
                url: "{{route('location.edit.modal')}}",
                type: 'post',
                data: {
                    'id': tableRow['id'],
                    'field_name': element
                },
                success: function (response) {
                    $("#shwoLocationModal").html(response);
                    jQuery("#editModal").modal('show');
                },
                error: function (e) {
                    console.log('error', e);
                }
            });
        }
        // alert( 'You clicked on '+data[0]+'\'s row COmmmmits;
    });

    $('#tablelocation tbody').on('click', 'tr', function () {
        var data = $('#tablelocation').DataTable().row( this ).data();
        $('#loc-Id').text(data.id);
    });

    function passLocId () {
        var locationId = $('#loc-Id').text();
        window.open(modified_url_fn('/get-controller-mgmt/' + locationId));
    }

	function check_controler_allowed(e) {
		is_disabled = $(e).attr('data-controller');
		if (is_disabled == 'true') {
			$("#controller_mgt").css('display','none');
		} else {
			$("#controller_mgt").css('display','block');
		}
	}


	license_key = {
			id: null,
			action: null,
			display_confirm: function(url) {
					$('#confirmLModal').modal('show')
			},
			actionx: function() {
					this.action();
					$('#confirmLModal').modal('hide');
			}
	}
	
	@if (Auth::user() && Auth::user()->type != 'admin')
		locationTable.column(6).visible(false);
	@endif

	function generate_license_key(id) {
		license_key.id = id
		license_key.action = function() {
			$.post("{{route('location.generate_license_key')}}", {location_id:license_key.id}).done(function(res) {
				locationTable.ajax.reload()
				$("#res").html(res)
			});
		}
		license_key.display_confirm();
	}

	function license_key_modal(key) {
		$("#license_key_field").html(key);
		$("#license_key_modal").modal('show');
	}
	
	var reset_confirm = {
		id: null,
		display_confirm_reset: function(id) {
				this.id = id;
				$('#resetHardwareConfirmModal').modal('show')
		},
		action: function() {
			$('#resetHardwareConfirmModal').modal('hide');
			$.post("{{route('terminal.reset_hardware')}}", {location_id: reset_confirm.id}).done(function(res) {
					locationTable.ajax.reload()
					$("#shwoLocationModal").html(res)
			}).fail( (res) =>  $("#shwoLocationModal").html(res) );
		}
	}

</script>
</div>
