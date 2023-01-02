<style>
.search-btn{
    width:70px;
    height:70px;
    border-radius:5px;
    text-align:center;
    box-sizing:border-box;
    padding:5px;
}

#location-datatable.dataTable tbody tr {
	border-color: rgba(26, 188, 156, 0.7) !important;
	background-color: rgba(26, 188, 156, -0.3) !important;
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


#merchant-location-modal .modal-header {
	padding: 10px 10px !important;
}


#merchant-location-modal .modal-dialog,
#merchant-location-modal .modal-content {
    /* 80% of window height */
	/*
    max-height: 578px;
	*/
}

#merchant-location-modal .modal-body {
    /* 100% = dialog height, 120px = header + footer */
    /*max-height: calc(100% - 80px);*/
	/*
    max-height: 578px;
    overflow-y: auto;
	*/
}
</style>
<div class="col-md-6">
    <button class="btn btn-warning text-white sellerbutton float-right"
		style="padding-left:0;padding-right:0;padding-top:8px;margin-right:0"
		id="twoway" data-toggle="modal" data-target=".twoWayModal">
		Two&nbsp;Way</button>
    <button class="btn btn-success text-white sellerbutton float-right"
		style="padding-left:0;padding-right:0;padding-top:8px"
		data-toggle="modal" data-target=".oneWayModal">
		One&nbsp;Way</button>
</div>

<!-- two way modal pop up starts here-->
<div class="modal fade twoWayModal" tabindex="-1" role="dialog"  aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" style="width:600px;">
    <div class="modal-content bg-greenlobster">
        <div class="modal-header" style="border-bottom-width:0">
            <h3 style="margin-bottom:0"
				class="modal-title" id="myLargeModalLabel">
				Two Way
			</h3>
        </div>
        <div class="modal-body">
		<div class="row">
			<div class="col-md-10">
				<input type="text" id="merchant_id" class="form-control"
				placeholder="Merchant ID" style="outline:none;">
			</div>
			<div class="col-md-2">
				<button id="merchant_add_button"
					class="btn btn-success search-btn">
					<span class="fa fa-plus"
						style="padding-top:5px;font-size: 25px">
					</span>
				</button>
			</div>
		</div>
        </div>
    </div>
  </div>
</div>
<!-- two way modal pop up ends here-->


<!-- one way modal pop up starts here-->
<div class="modal fade oneWayModal" tabindex="-1" role="dialog"
	aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-centered"
		style="width:600px;">
    <div class="modal-content bg-greenlobster">
        <div class="modal-header">
            <h3 class="modal-title mb-0" id="myLargeModalLabel">One Way</h3>
        </div>
        <div class="modal-body">
		<form>
			<div style="margin-bottom:5px" class="form-group">
			<div class="align-items-center row">
			<div class="col-md-3">
				<label class="mb-0 label-control text-white">
				Company&nbsp;Name
			</label>
			</div>
			<div class="col-md-9">
				<input type="text" class="form-control"
					style="outline:none;" name="company_name">
			</div>
			</div>
			</div>
			<div style="margin-bottom:5px" class="form-group">
			<div class="align-items-center row">
				<div class="col-md-3">
					<label class="mb-0 text-white">
					Business&nbsp;Reg.&nbsp;No.</label>
				</div>
				<div class="col-md-9">
					<input type="text"
						   name="business_reg_no"
						   class="form-control"
					style="outline:none;">
				</div>
			</div>
			</div>
			<div style="margin-bottom:5px" class="form-group">
				<div class="align-items-center row">
				<div class="col-md-3">
					<label class="mb-0 label-control text-white">Address</label>
				</div>
				<div class="col-md-9">
					<input type="text" class="form-control"
						   name="address"
					style="outline:none;">
				</div>
				</div>
			</div>
			<div style="margin-bottom:5px" class="form-group">
				<div class="align-items-center row">
				<div class="col-md-3">
					<label class="mb-0 label-control text-white">Contact&nbsp;Name</label>
				</div>
				<div class="col-md-9">
					<input type="text" class="form-control"
						   name="contact_name"
					style="outline:none;">
				</div>
				</div>
			</div>
			<div style="margin-bottom:5px" class="form-group">
				<div class="align-items-center row">
				<div class="col-md-3">
					<label class="mb-0 label-control text-white">Mobile No.</label>
				</div>
				<div class="col-md-9">
					<input type="text" class="form-control"
						   name="mobile_no"
					style="outline:none;">
				</div>
				</div>
			</div>
		</form>
        </div>
	</div>
	</div>
</div>
<!-- one way modal pop up ends here-->

<div class="modal fade" id="msgModal"  tabindex="-1"
	role="dialog" aria-labelledby="staffNameLabel"
	aria-hidden="true" style="text-align: center;">

	<div class="modal-dialog modal-dialog-centered  mw-75 w-50"
         role="document" style="display: inline-flex;">
        <div class="modal-content modal-inside bg-greenlobster"
             style="width: 100%;">
            <div style="border:0" class="modal-header">&nbsp;</div>
            <div class="modal-body text-center">
                <h5 class="modal-title text-white"
                    id="status-msg-element">
				</h5>
            </div>
            <div style="border:0" class="modal-footer">&nbsp;</div>
        </div>
    </div>
</div>

<div class="modal fade" id="locationModal" tabindex="-1" role="dialog"
	aria-labelledby="staffNameLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
		<div class="modal-content bg-greenmidlobster"
			style="height: 400px; padding: 15px;">
			<div class="row py-2">
			<div class="col align-self-end">
				<h3> Location</h3>

				<?php
				if(isset($locations)) {
				 	$count_of_locations = count($location);
					$count = 0;

				Log::debug('count_of_locations='.$count_of_locations);
				Log::debug('location='.json_encode($location));

				Log::debug('location[0]='.json_encode($location[0]));
				Log::debug('location[1]='.json_encode($location[1]));
				Log::debug('location[2]='.json_encode($location[2]));
				Log::debug('location[3]='.json_encode($location[3]));

				/* Why are we dividing locations by "4"???
				   Why not 5, nor 6? What's the algorithm??? */
				$count_of_row = $count_of_locations/4;
				if($count_of_locations > 0){
				for($i = 0; $i< $count_of_row; $i++){
					if($count_of_locations > 4){
				?>
			<div class="row">
				<?php
				/* WTF is this "5"? What does it do???? 
				   What a brain dead hardcoded algorithm!!! */
				for($j = 0; $j< 5; $j++){
				?>
				<div class="col-md-3">
				<h5 style="cursor: pointer;"
					onclick="add_terminal('{{$location[$count]->id}}')">
					{{$location[$count]->branch}}
				</h5>
				</div>
				<?php $count++; $count_of_locations--; }?>
			</div>
				<?php
				}else{
					$last_row = $count_of_locations;
					?>
				<div class="row">
					<?php
					for($j = 0; $j< $last_row; $j++){
					?>
					<div class="col-md-3">
					<h5 style="cursor: pointer;"
						onclick="add_terminal('{{$location[$count]->id}}')">
						{{$location[$count]->branch}}
					</h5>
					</div>
					<?php $count++; $count_of_locations--; }?>
				</div>
				<?php
				}}}}
				?>
			</div>
			</div>
		</div>
    </div>
</div>


<!-- Default Location Modal -->
<div class="modal fade" id="merchant-location-modal" tabindex="-1"
	role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-md"
		style="max-width:550px"
		role="document">
		<div class="modal-content bg-greenlobster"
			style="height:100%;max-height:100%">
			<div class="modal-header">
				<h4 style="line-height:1.5em;margin-bottom:0">
					Central Administration: Goods Delivered From
				</h4>
			</div>
			<div class="modal-body">
				
				<table id="location-datatable"
					style="height:100%;width:100%;">
					<thead>
					<tr>
						<th></th>
					</tr>
					</thead>
				</table>
			</div>
		</div>
	</div>
</div>


<script>

	var locationDatatable;
	var linkingId = '';
	var locationId = '';
	var rowType = '';
    var companyId = '';
    $(document).ready(function(){

		locationDatatable = $('#location-datatable').DataTable({
			"paging":   false,
			"ordering": false,
			"info":     false,
			"searching": false,
			"ajax": "{{route('opossum.ajax.getLocations')}}",
			"initComplete": function(setting, json) {
				$('#location-datatable tbody').on( 'click', 'tr', function () {
					$('#location-datatable tbody tr').removeClass('selected');
					$(this).toggleClass('selected');
				});

			},
			columns: [
				{
					// branch
					mRender: function(data, type, full) {
						var branch = full.branch == null ? 'Branch' : full.branch;
						return '<h5 style="margin-bottom:5px;margin-top:5px;cursor:pointer;" data-id="'+full.id+'" data-dismiss="modal">'+branch+'</h5>';
					}
				},

			]
		});


        $("#supplier_tbl, #dealer_tbl").on('click', '.js-business-reg', function(){
            companyId = $(this).data('company-id');

            $.ajax({
                type: "GET",
                url: "/get-one-way-merchant-data/"+companyId,
                dataType:"json",
                success: function(response) {
                    $('[name="company_name"]').val(response.company_name);
                    $('[name="business_reg_no"]').val(response.business_reg_no);
                    $('[name="address"]').val(response.address);
                    $('[name="contact_name"]').val(response.contact_name);
                    $('[name="mobile_no"]').val(response.mobile_no);
                    $('.oneWayModal').modal('show');

                }
            })
        });

    	$("#supplier_tbl, #dealer_tbl").on('click', '.js-merchant-location', function(){
			locationId = $(this).data('default-location-id');
			rowType = $(this).data('row-type');
			linkingId = $(this).data('merchantlink-relation-id');
			$('#location-datatable tbody tr').removeClass('selected');
			if (locationId != '') {
				$('#location-datatable').find('[data-id="'+locationId+'"]').parents('tr').addClass('selected');
			}
			$("#merchant-location-modal").modal('show');
		});

		$('#merchant-location-modal').on('hidden.bs.modal', function (e) {
			var selectedLocationId = '';
			locationDatatable.rows('.selected').data().each(function(location, index){
                selectedLocationId = location.id;
			});

            if (selectedLocationId == locationId) {
                return false;
            }

            var type = 'supplier';
			if ($.fn.DataTable.isDataTable('#dealer_tbl') ) {
				type = 'dealer';
			}

			$.ajax({
				url:'{{route('data.ajax.saveMerchantDefaultLocation')}}',
				type: 'post',
				data: {
					locationId: selectedLocationId,
					rowType: rowType,
                    linkingId: linkingId,
					type: type
				},
				dataType:'json',
				success: function(response) {
					if (response.status === 'true') {
						displayStatusMsgPopup('Default location saved successfully');
                        if ($.fn.DataTable.isDataTable('#supplier_tbl') ) {
                            $("#supplier_tbl").DataTable().ajax.reload(null, false );
                        }
                        if ($.fn.DataTable.isDataTable('#dealer_tbl') ) {
                            $("#dealer_tbl").DataTable().ajax.reload(null, false );
                        }
					}
				}
			});

		});


        $('.oneWayModal').on('hidden.bs.modal', function (e) {
            var data = {
                company_name: $('[name="company_name"]').val(),
                business_reg_no: $('[name="business_reg_no"]').val(),
                address: $('[name="address"]').val(),
                contact_name: $('[name="contact_name"]').val(),
                mobile_no: $('[name="mobile_no"]').val(),
                company_id: companyId
            };

            if (data.company_name != ''
                    || data.business_reg_no != ''
                    || data.address != ''
                    || data.contact_name != ''
                    || data.mobile_no) {

                $.ajax({
                    type:"POST",
                    url:'{{ route('data.ajax.saveMerchantOneway') }}',
                    dataType:"JSON",
                    data: data,
                    success: function(response) {
                        if(response.status == 'true') {
                            if ($.fn.DataTable.isDataTable('#supplier_tbl') ) {
                                $("#supplier_tbl").DataTable().ajax.reload(null, false );
                            }
                            if ($.fn.DataTable.isDataTable('#dealer_tbl') ) {
                                $("#dealer_tbl").DataTable().ajax.reload(null, false );
                            }
                        }
                        displayStatusMsgPopup(response.msg);
                    }
                });

            }


        });
	});

	function fetch_location() {
        console.log('Open');
      $('#locationModal').modal('show')
    }



    function displayStatusMsgPopup(msg) {
        $("#status-msg-element").text(msg);
        $("#msgModal").modal('show');
        setTimeout(function() {
            $("#msgModal").modal('hide');
            $('.modal-backdrop').remove();
        },3500);
    }

    $('#merchant_add_button').click(function(){
        var id = $('#merchant_id').val();
        $.ajax({
            type:'GET',
            url:'{{ route('data.ajax.saveMerchantTwoWayLinking') }}',
            datatype: 'json',
            data:{merchant_id:id},
            success: function(response){
                $('.twoWayModal').modal('hide');
                if(response.status == 'true') {
                    if ($.fn.DataTable.isDataTable('#supplier_tbl') ) {
                        $("#supplier_tbl").DataTable().ajax.reload(null, false );
                    }
                    if ($.fn.DataTable.isDataTable('#dealer_tbl') ) {
                        $("#dealer_tbl").DataTable().ajax.reload(null, false );
                    }
                }
                displayStatusMsgPopup(response.msg);
            }
        });
    });

    /*
    $('#merchant_add_button').click(function(){
    	var id = $('#merchant_id').val();
    	$.ajax({
    		type:'POST',
    		url:'/add_new_dealer',
    		datatype: 'json',
    		data:{merchant_id:id},
    		success: function(res){
    			$('.twoWayModal').modal('hide');
    			console.log(res);
    		}
    	});
    });
    */
</script>
