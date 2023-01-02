<!-- User Management Datatable populate in this div by jquery -->
<div id="inner">
</div>

<!-- Modal Logout-->
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog"
	aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered  mw-75 w-50" role="document">
        <div class="modal-content modal-inside"
			style="background-color: rgba(26, 188, 156, 0.7)">
            <div class="modal-header" style="border-bottom:0px">
                <button type="button" class="close" data-dismiss="modal"
					aria-label="Close">
                </button>
            </div>
            <div class="modal-body text-center">
                <h5 class="modal-title text-white" id="logoutModalLabel">
					Do you really want to logout?
				</h5>
            </div>
            <div class="modal-footer"
				style="border-top:0 none; padding-left: 0px;
					padding-right: 0px;">
                <div class="row" style="width: 100%; padding-left: 0px;
					padding-right: 0px;">
                    <div class="col col-m-12 text-center">
                        <a class="btn btn-primary" href="{{ route('logout') }}"
							style="width:100px"
							onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            Yes
                        </a>
                        <button type="button" class="btn btn-danger" data-dismiss="modal" style="width:100px">No
                        </button>
                    </div>
                </div>

                <form id="logout-formO" action="{{ route('logout') }}"
					method="POST" style="display: none;">
                    @csrf
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="statusModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered  mw-75 w-50" role="document">
        <div class="modal-content modal-inside" style="background-color: rgba(26, 188, 156, 0.7)">
            <div class="modal-header" style="border-bottom:0px">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <!-- <h5 class="modal-title text-white" id="statusModalLabel">Suggest text msg here?</h5> -->
            </div>
            <div class="modal-footer" style="border-top:0 none; padding-left: 0px; padding-right: 0px;">
                <div class="row" style="width: 100%; padding-left: 0px; padding-right: 0px;">
                    <div class="col col-m-12 text-center">
                        <a class="btn bg-primary primary-button" data-dismiss="modal" href="#" style="width:100px">
                            Approve
                        </a>
                        <button type="button" class="btn btn-danger" data-dismiss="modal" style="width:100px">Reject
                        </button>
                    </div>
                </div>

                <form id="status-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Row Delete-->
<div class="modal fade" id="dellModal" tabindex="-1" role="dialog" aria-labelledby="dellModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered  mw-75 w-50" role="document">
        <div class="modal-content modal-inside" style="background-color: rgba(26, 188, 156, 0.7)">
            <div class="modal-header" style="border-bottom:0px">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <h5 class="modal-title text-white" id="dellModalLabel">Do you really want to delete this row?</h5>
            </div>
            <div class="modal-footer" style="border-top:0 none; padding-left: 0px; padding-right: 0px;">
                <div class="row" style="width: 100%; padding-left: 0px; padding-right: 0px;">
                    <div class="col col-m-12 text-center">
                        <a class="btn btn-primary" href="#" style="width:100px">No</a>
                        <button type="button" class="btn btn-danger" data-dismiss="modal" style="width:100px">Yes
                        </button>
                    </div>
                </div>

                <form id="dell-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </div>
        </div>
    </div>
</div>

<div style="padding-top:0 !important;padding-bottom:0 !important;"
	class="row py-2">
    <div class="col align-self-center" style="width:80%">
        <h2>User Management</h2>
    </div>
    <div class="col col-auto align-self-center">
        <button class="btn btn-success btn-lg btn-log  sellerbutton"
		style="margin-right:0" onclick="newStaff()">+User
		</button>
    </div>
</div>
<table class="table table-bordered" id="tablestaff">
    <thead class="thead-dark">
    <tr>
        <th style="width:30px; ">No</th>
        <th style="width:100px;">User&nbsp;ID</th>
        <th>User Name</th>
		<th style="width:80px;">Location</th>
		<th style="width:80px;">Roles</th>
        <th style="width:100px;">Status</th>
        <th style="width:30px; "></th>
        <th style="width:30px; "></th>
        <th style="width:30px; "></th>
    </tr>
    </thead>
    <tbody>
    </tbody>
</table>

<div id="showEditUserModal"></div>

<div class="modal fade"  id="customMSGModal"  tabindex="-1"
	role="dialog" aria-labelledby="staffNameLabel"
	aria-hidden="true" style="text-align: center;" onclick="close_me(this)">

	<div class="modal-dialog modal-dialog-centered  mw-75 w-50"
		role="document" >
		<div class="modal-content modal-inside bg-greenlobster"
			style="width: 100%;  
				 background-color: {{@$color}} !important" >
			<div class="modal-header" style="border:0">&nbsp;</div>
			<div class="modal-body text-center">
				<h5 class="modal-title text-white mb-0"
					id="msgCustomTextMsgH5"></h5>
			</div>
			<div class="modal-footer"
				style="border-top:0 none;padding-left:0;padding-right:0;">
				&nbsp;
			</div>
		</div>
	</div>
</div>

<script>
	
	var customMSGModal = function(msg) {
		$("#msgCustomTextMsgH5").html(msg);
		$("#customMSGModal").modal('show');
		setTimeout(function() {
			$("#customMSGModal").modal('hide');
		},3500)
	}
		
	$(document).ready(function () {
		staffTable.draw();    
	});

	var staffTable = $('#tablestaff').DataTable({
		"processing": false,
		"serverSide": true,
		"autoWidth":  false,
		"ajax": "{{route('user.index')}}",
		columns: [
			{data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'sysid', name: 'sysid'},
			{data: 'name', name: 'name'},
			{data: 'location', name: 'location'},
			{data: 'type', name: 'type'},
			{data: 'status', name: 'status'},
			{data: 'pinkcrab', name: 'pinkcrab'},
			{data: 'bluecrab', name: 'bluecrab'},
			{data: 'deleted', name: 'deleted'},
		],
		"order": [],
		"columnDefs": [
			{"className": "dt-center", "targets": [0, 1, 3, 4, 5, 6, 7, 8]},
			{"orderable": false, "targets": [6,7,8]},
		],
        "initComplete": function(settings, json) {
           
        }, 
        "drawCallback": function( settings ) {
            $('.King > td:nth(0)').html('K');
            $('.G_King > td:nth(0)').html('K');
        }
	});


	function newStaff() {
		$.ajax({
			url: "{{route('user.store')}}",
			type: "POST",
			enctype: 'multipart/form-data',
			processData: false,
			contentType: false,
			cache: false,
			data: '',
			success: function (response) {
					staffTable.ajax.reload();
                    $("#showEditUserModal").html(response);
                    $("#editUserModal").modal('show');

			}, error: function (e) {
				console.log(e.message);
			}
		});
	}

	$('#tablestaff tbody').on('click', 'td', function () {
		const tableCell = staffTable.cell(this).data();
		const tableRow = staffTable.row($(this).closest('tr')).data();
		const element = $(tableCell).data("field");




		if ($($(this).closest('tr')).hasClass('G_King') == true && 
				element != 'staff_role' && element != 'bluecrab' && element != 'pinkcrab'
				&& element != 'location'	) {
			console.log("element name",element);
            console.log("Kings account")
            return null;
        }

		if ($($(this).closest('tr')).hasClass('King') == true && 
			element != 'staff_role' && element != 'bluecrab' && element != 'pinkcrab'
			&& element != 'location') {
            console.log("Kings account")
            return null;
        }


        if ($($(this).closest('tr')).hasClass('role_disabled') == true && element == 'staff_role') {
            console.log("Roles disabled")
            return null;
        }
         

        if ($($(this).closest('tr')).hasClass('status_disabled') == true && element == 'status') {
            console.log("Status disabled")
            return null;
        }


        if ($($(this).closest('tr')).hasClass('name_disable') == true && element == 'staff_name') {
            console.log("name disabled")
            return null;
        }


        if ($($(this).closest('tr')).hasClass('self') == true && (element == 'status')) {
            console.log("Self account")
            return null;
        }
	
	
		if ($($(this).closest('tr')).hasClass('crab_disable') == true && (element == 'pinkcrab' || element == 'bluecrab')) {
            console.log("Status not active")
            return null;
        }


		if (element != null) {
			$.ajax({
				url: "{{route('user.edit.modal')}}",
				type: 'post',
				data: {
					'id': tableRow['id'],
					'field_name': element
				},
				success: function (response) {
					$("#showEditUserModal").html(response);
					jQuery("#editUserModal").modal('show');
				},
				error: function (e) {
					console.log('error', e);
				}
			});
		}
		// alert( 'You clicked on '+data[0]+'\'s row COmmmmits;
	});
</script>


<style>
	.modal-add-style {
		text-decoration: underline blue;
		cursor: pointer;
	}
    td > p { color: #007bff;}
    
   .G_King > td > p[data-field='status'],.G_King > td > p[data-field='staff_name'] , .King > td > p[data-field=status] {color:black !important;}

 tr.status_disabled p[data-field=status], tr.self p[data-field=status] ,tr.role_disabled p[data-field=staff_role] , tr.name_disiable p[data-field=staff_name], tr.crab_disable div[data-field=pinkcrab] > img,  tr.crab_disable div[data-field=bluecrab] > img{
		color:grey !important;cursor:  not-allowed !important;filter: grayscale(100%) brightness(150%);}

  tr.self p[data-field=staff_name], td > p[data-field=staff_role] {color:#007bff !important;}

    .King,.G_King {
        background: yellow !important;
    }
    .sadmin {background: yellow !important;}
	.fn_disabled {
		pointer-events: none !important;
		cursor: not-allowed !important;
	}
</style>
