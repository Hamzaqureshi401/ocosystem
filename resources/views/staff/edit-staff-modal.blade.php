@if($fieldName == 'staff_id' || $fieldName == 'staff_name' || $fieldName == 'staff_role')
    <div class="modal fade" id="editStaffModal" tabindex="-1" role="dialog" aria-labelledby="staffModallabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form action="#" id="updateProspectFields" method="post"
                      enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <div class="modal-body">

                        @if($fieldName == 'staff_id')
                            <div class="form-group">
                                <input type="email" class="form-control" placeholder="Email Address" name="email"
                                       value="{{$userData->email}}">
                            </div>
                            <div class="form-group">
                                <input type="password" class="form-control" id="password" placeholder="Password" name="password">
                            </div>
                            <div class="form-group">
                                <input type="password" class="form-control" id="verifypass" placeholder="Verify Password"
                                       name="password_confirmation">
                            </div>
                        @elseif($fieldName == 'staff_name')
                            <div class="form-group">
                                <input type="text" class="form-control" id="staffName" placeholder="Staff Name" name="name"
                                       value="{{$userData->name}}">
                            </div>
                            <div class="form-group">
                                <input type="text" class="form-control" placeholder="Department" name="department"
                                       value="{{$userData->staff->department}}">
                            </div>
                            <div class="form-group">
                                <input type="text" class="form-control" id="staffName" placeholder="Position" name="position"
                                       value="{{$userData->staff->position}}">
                            </div>
                        @elseif($fieldName == 'staff_role')
                            <div class="form-group">
                                <input type="text" class="form-control"  placeholder="Roles" name="type"
                                       value="{{$userData->type}}">
                            </div>
                        @endif

                        <input type="hidden" name="user_id" value="{{$id}}">
                    </div>
                </form>
            </div>
        </div>
    </div>
@elseif($fieldName == 'status')
    <div class="modal fade" id="editStaffModal" tabindex="-1" role="dialog" aria-labelledby="staffNameLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered  mw-75 w-50" role="document">
            <div class="modal-content modal-inside" style="background-color: rgba(0, 0, 255, 0.5);">
                <div class="modal-header" style="border-bottom:0px">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <h5 class="modal-title text-white" id="statusModalLabel">Are you sure?</h5>
                </div>
                <div class="modal-footer" style="border-top:0 none; padding-left: 0px; padding-right: 0px;">
                    <div class="row" style="width: 100%; padding-left: 0px; padding-right: 0px;">
                        <div class="col col-m-12 text-center">
                            @if($userData->status == 'pending')
                                <button type="button" class="btn bg-staff primary-button" onclick="updateStaffStatus({{$id}}, 'active')
                                        ">Approve</button>
                                <button type="button" class="btn btn-danger primary-button" onclick="updateStaffStatus({{$id}}, 'inactive')">Reject</button>
                            @elseif($userData->status == 'active')
                                <button type="button" class="btn btn-danger primary-button" onclick="updateStaffStatus({{$id}}, 'inactive')">Deactivate</button>
                            @elseif($userData->status == 'inactive')
                                <button type="button" class="btn bg-staff primary-button" onclick="updateStaffStatus({{$id}}, 'active')">Activate</button>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
@elseif($fieldName == 'deleted')
    <div class="modal fade" id="editStaffModal" tabindex="-1" role="dialog" aria-labelledby="staffNameLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered  mw-75 w-50" role="document">
            <div class="modal-content modal-inside" style="background-color: rgba(0, 0, 255, 0.5);">
                <div class="modal-header" style="border-bottom:0px">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <h5 class="modal-title text-white" id="statusModalLabel">Do you really want to delete this
                        staff information?</h5>
                </div>
                <div class="modal-footer" style="border-top:0 none; padding-left: 0px; padding-right: 0px;">
                    <div class="row" style="width: 100%; padding-left: 0px; padding-right: 0px;">
                        <div class="col col-m-12 text-center">
                            <button type="button" class="btn bg-staff primary-button" onclick="deleteData({{$id}})">Yes</button>
                            <button type="button" class="btn btn-danger primary-button" data-dismiss="modal">No</button>
                        </div>
                    </div>

                    <form id="status-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>
            </div>
        </div>

    </div>
@endif



@if($fieldName == 'staff_id' || $fieldName == 'staff_name')
    <script>
        $('#editStaffModal').submit(function (e) {
            e.preventDefault();
            $("#editStaffModal").modal('hide');
        });

        $('#editStaffModal').on('hidden.bs.modal', function (e) {
            updateStaff();
        });

        function updateStaff() {

            var email = $('input[name=email]').val();

            const form = $('#updateProspectFields')[0];
            const formData = new FormData(form);
            $.ajax({
                url: "{{route('staff.edit.update')}}",
                type: "POST",
                enctype: 'multipart/form-data',
                processData: false,  // Important!
                contentType: false,
                cache: false,
                data: formData,
                success: function (response) {

                    if (response.status == '200') {
                        staffTable.ajax.reload();
                        toastr.success(response.message);


                    } else {
                        toastr.warning(response.message)

                    }
                }, error: function (e) {
                    toastr.warning(e.message)

                }
            });


        }

    </script>

@elseif($fieldName == 'status')

    <script>

        function updateStaffStatus(id, status) {

            $.ajax({
                url: "{{route('staff.edit.status.update')}}",
                type: "POST",
                enctype: 'multipart/form-data',
                data: {
                    'id': id,
                    'status': status
                },
                success: function (response) {
                    if (response.status == '200') {
                        staffTable.ajax.reload();
                        toastr.success(response.message);
                        $("#editStaffModal").modal('hide');

                    } else {
                        toastr.warning(response.message)

                    }
                }, error: function (e) {
                    toastr.warning(e.message)

                }
            });
        }
    </script>
@elseif($fieldName == 'deleted')
    <script>

        function deleteData(id) {

            const url = "{{ route('staff.destroy', ['model_id' => "MODEL_ID"]) }}".replace("MODEL_ID", id);

            $.ajax({
                url: url,
                method: "DELETE",
                enctype: 'multipart/form-data',
                success: function (response) {
                    if (response.status == '200') {
                        staffTable.ajax.reload();
                        toastr.success(response.message);
                        $("#editStaffModal").modal('hide');

                    } else {
                        toastr.warning(response.message)

                    }
                }, error: function (e) {
                    toastr.warning(e.message)

                }
            });
        }
    </script>
@endif



