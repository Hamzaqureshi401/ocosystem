@if($model == 'branch')
<div class="modal fade" id="editModal" tabindex="-1" role="dialog"
	aria-labelledby="branch_nameModallabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
		<form style="margin-bottom:0" action="#"  method="post"
			enctype="multipart/form-data" autocomplete="off"
			id="updateProspectFields">
			<div class="modal-body">
				<div style="margin-bottom:0" class="form-group">
					<input type="text" class="form-control"
						id="branchName" placeholder="Branch" name="branch"
						value="{{$location->branch}}" autocomplete="off">
				</div>
			</div>
			<input type="hidden" name="location_id" value="{{$location->id}}">
		</form>
        </div>
    </div>
</div>

@elseif($model == 'address')
<div class="modal fade" id="editModal" tabindex="-1" role="dialog"
	aria-labelledby="addressModallabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"
		style='min-width: 85%;' role="document">
		<div class="modal-content">
            <form action="#"  method="post" enctype="multipart/form-data"
				style="margin-bottom:0"
				autocomplete="off" id="updateProspectFields">
                <div class="modal-body">
                    <div style="margin-bottom:0" class="form-group">
					<input type="text" class="form-control"
						id="address" placeholder="Address" name="address"
						value="{{$location->address_line1}}"
						autocomplete="off">
                    </div>
                </div>
                  <input type="hidden" name="location_id"
				  	value="{{$location->id}}">
            </form>
        </div>
    </div>
</div> 

@elseif($model == 'deleted')
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog"
		aria-labelledby="staffNameLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered  mw-75 w-50" role="document">
		<div class="modal-content modal-inside bg-greenlobster">
			<div style="border:0" class="modal-header"></div>
			<div class="modal-body text-center">
				<h5 class="modal-title text-white" id="statusModalLabel">
				Do you want to permanently delete this location information?
				</h5>
			</div>
			<div class="modal-footer"
				style="border-top:0 none; padding-left: 0px; padding-right: 0px;">
				<div class="row"
					style="width: 100%; padding-left: 0px; padding-right: 0px;">
					<div class="col col-m-12 text-center">
						<button type="button"
						class="btn bg-primary primary-button"
						onclick="deleteData({{$location->id}})"
						data-dismiss="modal">Yes</button>
						<button type="button"
						class="btn btn-danger primary-button"
						data-dismiss="modal">No</button>
					</div>
				</div>

				<form id="status-form" action="{{ route('logout') }}"
					method="POST" style="display: none;">
					@csrf
				</form>
			</div>
		</div>
        </div>
    </div>
    <style>
        .btn {color: #fff !Important;}
    </style>
	<script>
        function deleteData(id) {
            const url = "{{ route('location.destroy', ['model_id' => "MODEL_ID"]) }}".replace("MODEL_ID", id);
            $.ajax({
                url: url,
                method: "DELETE",
                enctype: 'multipart/form-data',
                success: function (response) {
                    locationTable.ajax.reload();
                    $("#editModal").modal('hide');
                    $("#shwoLocationModal").html(response);
                    $("#msgModal").modal('show');
                }, error: function (e) {
                    console.log(e.message)
                }
            });
        }

        $('#msgModal').on('hidden.bs.modal', function (e) {
            $('.modal-backdrop').remove();
        });
	</script>
@endif


@if($model == 'branch' || $model == 'address')
	<script type="text/javascript">
		$('#editModal').submit(function (e) {
            e.preventDefault();
            $("#editModal").modal('hide');
		});

		$('#editModal').on('hidden.bs.modal', function (e) {
            updateField();
		});

		function updateField() {
			const form = $('#updateProspectFields')[0];
			const formData = new FormData(form);
			//console.log(formData)
            $.ajax({
                url: "{{route('location.post.update')}}",
                type: "POST",
                enctype: 'multipart/form-data',
                processData: false,  // Important!
                contentType: false,
                cache: false,
                data: formData,
                success: function (response) {
                    locationTable.ajax.reload();
                    $("#editModal").modal('hide');
                    $("#shwoLocationModal").html(response);
                    $("#msgModal").modal('hide');
                }, error: function (e) {
                    console.log(e.message)
                }
            });
        }
	</script>
@endif
