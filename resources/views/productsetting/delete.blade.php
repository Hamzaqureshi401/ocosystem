  <div class="modal fade" id="deleteProd_data" tabindex="-1" role="dialog" aria-labelledby="showMsgModal" aria-hidden="true" style="    text-align: center;">
        <div class="modal-dialog modal-dialog-centered  mw-75 w-50" role="document">
            <div class="modal-content modal-inside bg-greenlobster">
                <div class="modal-header" style="border:0"></div>
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
                            onclick="deleteData2('{{$id}}','{{$deleteType}}')"
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
        .modal-dialog {display:inline-flex;}
.modal-inside {width:100%;}
    </style>
    <script>
        function deleteData2(id,deleteType) {

            const url = "{{ route('productsetting.deletedata')}}"
            var thistype = deleteType;
            $.ajax({
                url: url,
                method: "POST",
                data: {
                    'id':id,
                    'deleteType':deleteType,
                },
                success: function (response) {
                    $("#modal").modal('hide')
                    $('.modal-backdrop').remove();
                    $("#response").html(response)
                    

                if (thistype == 'category' || thistype == 'brand') {

                     if (thistype == 'category') {
                         dropdown = $('#selectcategory');
                        dropdown.empty();
                        dropdown.append('<option class="form-control" value="cat">Select Category</option>');
                        dropdown.prop('selectedIndex', 0);
                     url = "{{route('productsetting.get_dropDown',['OPTION'=>'cat','KEY'=>'cat'])}}"

                } else {
                     dropdown = $('#selectbrand');
                        dropdown.empty();
                        dropdown.append('<option class="form-control" value="cat">Select Brand</option>');
                        dropdown.prop('selectedIndex', 0);
                  var url = "{{route('productsetting.get_dropDown',['OPTION'=>'OPTION','KEY'=>'KEY'])}}".replace('OPTION','brand');
                }
                        $.getJSON(url, function (data) {
             $.each(data, function (key, entry) {
                dropdown.append($('<option class="form-control"></option>').attr('value', entry.id).text(entry.name));
                })
                });
                }

                
                if (thistype != 'brand') {
                        disable_all();
                        $('#selectcategory').prop('selectedIndex', 0);
                        $('#selectsubcategory').prop('selectedIndex', 0);
                        $('#selectproduct').prop('selectedIndex', 0);
                }

                $('#msgModal').modal('show')
                    
                }, error: function (e) {
                    console.log(e.message)

                }
            });
        }

    </script>
