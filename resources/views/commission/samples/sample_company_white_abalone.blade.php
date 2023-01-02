<div class="modal fade" id="editModal" tabindex="-1" role="dialog"
	aria-labelledby="branch_nameModallabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
		<form action="#"  method="post" enctype="multipart/form-data"
			autocomplete="off" id="updateProspectFields">
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

