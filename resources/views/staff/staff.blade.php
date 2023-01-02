<!-- Staff Management Datatable populate in this div by jquery -->
<div id="inner">
</div>

<!-- Modal Logout-->
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="logoutModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered  mw-75 w-50" role="document">
        <div class="modal-content modal-inside" style="background-color: rgba(0, 0, 255, 0.5);">
            <div class="modal-header" style="border-bottom:0px">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <h5 class="modal-title text-white" id="logoutModalLabel">Do you really want to logout?</h5>
            </div>
            <div class="modal-footer" style="border-top:0 none; padding-left: 0px; padding-right: 0px;">
                <div class="row" style="width: 100%; padding-left: 0px; padding-right: 0px;">
                    <div class="col col-m-12 text-center">
                        <a class="btn btn-primary" href="{{ route('logout') }}" style="width:100px"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            Yes
                        </a>
                        <button type="button" class="btn btn-danger" data-dismiss="modal" style="width:100px">No
                        </button>
                    </div>
                </div>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="statusModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered  mw-75 w-50" role="document">
        <div class="modal-content modal-inside" style="background-color: rgba(0, 0, 255, 0.5);">
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
                        <a class="btn btn-primary" data-dismiss="modal" href="#" style="width:100px">
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
        <div class="modal-content modal-inside" style="background-color: rgba(0, 0, 255, 0.5);">
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

<div class="row py-2">
    <div class="col align-self-end" style="width:80%">
        <h2>User Management</h2>
    </div>
    <div class="col col-auto align-self-center">
        <button class="btn btn-success btn-lg" style="width:120px"
		onclick="newStaff()">+User</button>
    </div>
</div>
<table class="table table-bordered" id="tablestaff">
    <thead class="thead-dark">
    <tr>
        <th class="text-center" style="width:30px;text-align: center;">No</th>
        <th class="text-center" style="width:100px;text-align: center;">Staff ID</th>
        <th>User Name</th>
        <th class="text-center" style="width:80px;">Roles</th>
        <th class="text-center" style="width:100px;text-align: center;">Status</th>
        <th class="text-center" style="width:30px;text-align: center;"></th>
    </tr>
    </thead>
    <tbody>
    </tbody>
</table>

<div id="showEditStaffModal"></div>

<script>
	$(document).ready(function () {
		staffTable.draw();
	});

	var staffTable = $('#tablestaff').DataTable({
		"processing": true,
		"serverSide": true,
		"ajax": "{{route('staff.index')}}",
		columns: [
			{data: 'DT_RowIndex', name: 'DT_RowIndex'},
			{data: 'staff_id', name: 'staff_id'},
			{data: 'name', name: 'name'},
			{data: 'type', name: 'type'},
			{data: 'status', name: 'status'},
			{data: 'deleted', name: 'deleted'},
		],
		"order": [],
		"columnDefs": [
			{"className": "dt-center", "targets": [0, 1, 3, 4, 5]},
		],
	});


	function newStaff() {
		$.ajax({
			url: "{{route('staff.store')}}",
			type: "POST",
			enctype: 'multipart/form-data',
			processData: false,
			contentType: false,
			cache: false,
			data: '',
			success: function (response) {
				if (response.status == "success" || response.status == '200') {
					staffTable.ajax.reload();
					toastr.success(response.message);
				}else{
					toastr.warning(response.message);
				}

			}, error: function (e) {
				toastr.warning(e.message);
			}
		});
	}

	$('#tablestaff tbody').on('click', 'td', function () {
		const tableCell = staffTable.cell(this).data();
		const tableRow = staffTable.row($(this).closest('tr')).data();
		const element = $(tableCell).data("field");


		if (element != null) {
			$.ajax({
				url: "{{route('staff.edit.modal')}}",
				type: 'post',
				data: {
					'id': tableRow['id'],
					'field_name': element
				},
				success: function (response) {
					$("#showEditStaffModal").html(response);
					jQuery("#editStaffModal").modal('show');
				},
				error: function (e) {
					console.log('error', e);
				}
			});
		}
		// alert( 'You clicked on '+data[0]+'\'s row' );
	});
</script>


<style>
	.modal-add-style {
		text-decoration: underline blue;
		cursor: pointer;
	}
</style>
