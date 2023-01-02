
    <div class="modal fade" id="showMsgModal" tabindex="-1" role="dialog" aria-labelledby="showMsgModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered  mw-75 w-50" role="document">
            <div class="modal-content modal-inside bg-greenlobster">
                <div style="border-width:0" class="modal-header text-center"></div>
                <div class="modal-body text-center">
                    <h5 class="modal-title text-white"
                    id="statusModalLabel">Do you want to permanently delete this
                    terminal?</h5>
                </div>
                <div class="modal-footer"
                    style="border-top:0 none; padding-left: 0px; padding-right: 0px;">
                    <div class="row"
                        style="width: 100%; padding-left: 0px; padding-right: 0px;">
                        <div class="col col-m-12 text-center">
                            <button type="button"
                            class="btn bg-primary primary-button"
                            onclick="deleteData({{$franchise->id}})"
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
    
    <script>
        function deleteData(id) {

            const url = "{{ route('data.ajax.destoryFranchiseTerminal', ['model_id' => "MODEL_ID"]) }}".replace("MODEL_ID", id);

            $.ajax({
                url: url,
                method: "POST",
                enctype: 'multipart/form-data',
                success: function (response) {  
                    $("#showMsgModal").modal('hide')
                    $("#showEditInventoryModal").html(response)
                    $('#showMsgModal').modal('show');
                     franchiseDataTable.ajax.reload();
                }, error: function (e) {
                    console.log(e.message)

                }
            });
        }

    </script>
