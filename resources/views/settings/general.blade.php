@if (isset($msg_dilog))
    <div class="modal fade" id="showMsgModal"
         tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered  mw-75 w-50" role="document">
            <div class="modal-content modal-inside bg-greenlobster">
                <div class="modal-header" style="border:0">&nbsp;</div>
                <div class="modal-body text-center">
                    <h5 class="modal-title text-white"
                        id="statusModalLabel">{{$msg_dilog}}</h5>
                </div>
                <div class="modal-footer" style="border:0">&nbsp;</div>
            </div>

        </div>
        <style>
            .btn {
                color: #fff !Important;
            }
        </style>

        <script type="text/javascript">
            $("#showMsgModal").modal('show');
            $('#showMsgModal').on('hidden.bs.modal', function (e) {
                $('.modal-backdrop').remove();
            });
            $('.modal-backdrop').click(function () {
                $('.modal-backdrop').remove();
            });

            setTimeout(function () {
                $("#showMsgModal").modal('hide');
                $('.modal-backdrop').remove();
                @if (isset( $OH_done))
                openTab('General');
                @endif
            }, 2500)
        </script>

        @elseif (isset($getData))
            <div class="modal fade" id="getDataOH" tabindex="-1" role="dialog"
                 aria-labelledby="getDataOH" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <form action="#" id="OH_FORM" method="post"
                              enctype="multipart/form-data"
                              onsubmit="return false" autocomplete="off">
                            {{ csrf_field() }}
                            <div class="modal-body">
                                <div class="form-group">
                                    <input type="text" class="form-control"
                                           id="keyName" placeholder="{{ucfirst($type)}}" name="keyName"
                                           autocomplete="off" style="width: 100%;"/>
                                    <input type="hidden" name="addType" value="{{$type}}"/>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <script>
                $('#OH_FORM').submit(function (e) {
                    e.preventDefault();
                    $("#getDataOH").modal('hide');
                });

                $('#getDataOH').on('hidden.bs.modal', function (e) {
                    addOH();
                });

                function addOH() {
                    const form = $('#OH_FORM')[0];
                    const formData = new FormData(form);
                    $.ajax({
                        url: "{{route('oH.submitData')}}",
                        type: "POST",
                        enctype: 'multipart/form-data',
                        processData: false,  // Important!
                        contentType: false,
                        cache: false,
                        data: formData,
                        success: async function (response) {
                            $("#getDataOH").modal('hide');
                            $("#response").html(response);
                            await $("#showMsgModal").modal('show', function () {
                                openTab('General');
                            });

                        }, error: function (e) {
                            console.log(e.message)
                        }
                    });
                }
            </script>

        @elseif (isset($deleted))
            <div class="modal fade" id="deleteDataOH" tabindex="-1" role="dialog" aria-labelledby="showMsgModal"
                 aria-hidden="true" style="    text-align: center;">
                <div class="modal-dialog modal-dialog-centered  mw-75 w-50" role="document">
                    <div class="modal-content modal-inside bg-greenlobster">
                        <div class="modal-body text-center">
                            <h5 class="modal-title text-white"
                                id="statusModalLabel">Are you sure to delete this record?</h5>
                        </div>
                        <div class="modal-footer"
                             style="border-top:0 none; padding-left: 0px; padding-right: 0px;">
                            <div class="row"
                                 style="width: 100%; padding-left: 0px; padding-right: 0px;">
                                <div class="col col-m-12 text-center">
                                    <button type="button"
                                            class="btn bg-primary primary-button"
                                            onclick="deleteData2({{$id}})"
                                            data-dismiss="modal">Yes
                                    </button>
                                    <button type="button"
                                            class="btn btn-danger primary-button"
                                            data-dismiss="modal">No
                                    </button>
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
                .btn {
                    color: #fff !Important;
                }

                .modal-dialog {
                    display: inline-flex;
                }

                .modal-inside {
                    width: 100%;
                }
            </style>
            <script>
                function deleteData2(id) {

                    const url = "{{ route('OH.deletedata')}}"

                    $.ajax({
                        url: url,
                        method: "POST",
                        data: {
                            'id': id
                        },
                        success: function (response) {
                            $("#modal").modal('hide')
                            $('.modal-backdrop').remove();
                            $("#response").html(response)
                            $('#msgModal').modal('show');
                        }, error: function (e) {
                            console.log(e.message)

                        }
                    });
                }
            </script>
        @else

            @include('settings.generalinfo')
            @include('settings.logo')
            @include('financialyear.financialyear')
            @include('settings.country')
            @include('settings.organisationhierarchy')
            @include('productsetting.productsetting')

        @endif
        <div style="width:100%;height: 35px;"></div>
        <div id='response'></div>
    </div>
