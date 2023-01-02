<!-- Staff Management Datatable populate in this div by jquery -->
<div id="inner">
</div>

<!-- Modal Logout-->
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="logoutModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-75 w-50"
         role="document">
        <div class="modal-content modal-inside bg-greenlobster">
            <div class="modal-body text-center">
                <h5 class="modal-title text-white" id="logoutModalLabel">
                    Do you really want to logout?</h5>
            </div>
            <div class="modal-footer" style="border-top:0 none;
				padding-left: 0px; padding-right: 0px;">
                <div class="row" style="width: 100%; padding-left: 0px;
					padding-right: 0px;">
                    <div class="col col-m-12 text-center">
                        <a class="btn btn-primary" href="{{ route('logout') }}"
                           style="width:100px"
                           onclick="event.preventDefault();
						   	document.getElementById('logout-form').submit();">
                            Yes
                        </a>
                        <button type="button" class="btn btn-danger"
                                data-dismiss="modal" style="width:100px">No
                        </button>
                    </div>
                </div>

                <form id="logout-form" action="{{ route('logout') }}"
                      method="POST" style="display: none;">
                    @csrf
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="statusModal" tabindex="-1" role="dialog"
     aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered  mw-75 w-50" role="document">
        <div class="modal-content modal-inside"
             style="background-color: rgba(0, 0, 255, 0.5);">
            <div class="modal-header" style="border-bottom:0px">
                <button type="button" class="close" data-dismiss="modal"
                        aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
            </div>

            <div class="modal-footer" style="border-top:0 none;
				padding-left: 0px; padding-right: 0px;">
                <div class="row" style="width: 100%; padding-left: 0px;
					padding-right: 0px;">
                    <div class="col col-m-12 text-center">
                        <a class="btn btn-primary" data-dismiss="modal"
                           href="#" style="width:100px"> Approve </a>
                        <button type="button" class="btn btn-danger"
                                data-dismiss="modal" style="width:100px">Reject
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

<!-- Modal Row Delete-->
<div class="modal fade" id="dellModal" tabindex="-1" role="dialog"
     aria-labelledby="dellModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered  mw-75 w-50" role="document">
        <div class="modal-content modal-inside"
             style="background-color: rgba(0, 0, 255, 0.5);">
            <div class="modal-header" style="border-bottom:0px">
                <button type="button" class="close" data-dismiss="modal"
                        aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <h5 class="modal-title text-white" id="dellModalLabel">
                    Do you really want to delete this row?</h5>
            </div>
            <div class="modal-footer" style="border-top:0 none;
				padding-left: 0px; padding-right: 0px;">
                <div class="row" style="width: 100%; padding-left: 0px;
					padding-right: 0px;">
                    <div class="col col-m-12 text-center">
                        <a class="btn btn-primary" href="#"
                           style="width:100px">No</a>
                        <button type="button" class="btn btn-danger"
                                data-dismiss="modal" style="width:100px">Yes
                        </button>
                    </div>
                </div>

                <form id="dell-form" action="{{ route('logout') }}"
                      method="POST" style="display: none;">
                    @csrf
                </form>
            </div>
        </div>
    </div>
</div>

<div id="ProjectManagement" class="tabcontent">
    <div class="row py-2 "  style="display:flex;height:83px!important;
	padding-top:8px !important;padding-bottom:0 !important">
    <div class="col align-self-center" style="width:80%">
        <h2>Project Management</h2>

    </div>
    <div class="col col-auto align-self-center">
        <button class="btn btn-success btn-log sellerbutton mr-0"
                style="padding-left:8px" id="addService">
            <span>+Project</span>
        </button>
    </div>
</div>
<table class="table table-bordered" id="tableProjectMgmt" style="width:100%;">
    <thead class="thead-dark">
    <tr>
		<th class="text-center"
            style="width:30px;text-align: center;">No
        </th>
        <th class="text-center"
            style="width:150px;text-align: center;">Project&nbsp;ID
        </th>
        <th style="width:auto;text-align: center;">Project&nbsp;Name</th>
		<th class="text-center"
            style="width:30px;text-align: center;
				background-image: unset !important">
		</th>
		<th class="text-center"
            style="width:30px;text-align: center;
				background-image: unset !important">
		</th>
		<th class="text-center"
            style="width:30px;text-align: center;
				background-image: unset !important">
		</th>
    </tr>
    </thead>
    <tbody>
    <tr>
		<td style="text-align:center;">  1</td>
		<td style="text-align:center;"><a href="{{ url('/project/1') }}" target="_blank">1300000001</a></td>
		<td>New Infastucture</td>
		<td style="text-align:center;">
            <img style="width:25px;height:25px;cursor:pointer;"
			src="{{asset('images/yellowcrab_50x50.png')}}">
        </td>

        <td style="text-align:center;">
            <img style="width:25px;height:25px;cursor:pointer;"
			src="{{asset('images/bluecrab_50x50.png')}}">
        </td>

        <td style="text-align:center;">
            <img style="width:25px;height:25px;cursor:pointer;"
			src="{{asset('images/redcrab_50x50.png')}}">
        </td>

	</tr>

    <tr>
		<td style="text-align:center;"> 2</td>
		<td style="text-align:center;"> <a href="{{ url('/project/1') }}" target="_blank">1300000002</a></td>
		<td>Some Other Infastructure</td>
		<td style="text-align:center;">
            <img style="width:30px;height:30px;cursor:pointer;"
			src="{{asset('images/yellowcrab_50x50.png')}}">
        </td>

        <td style="text-align:center;">
            <img style="width:30px;height:30px;cursor:pointer;"
			src="{{asset('images/bluecrab_50x50.png')}}">
        </td>

        <td style="text-align:center;">
            <img style="width:30px;height:30px;cursor:pointer;"
			src="{{asset('images/redcrab_50x50.png')}}">
        </td>


	</tr>
    </tbody>
</table>
</div>
<div class="modal" id="projmgmtPriceModal">
    <div class="modal-dialog" style="width: 250px;">
        <div class="modal-content">
            <!-- Modal body -->
            <div class="modal-body">
                <input id="projmgmtPriceInput" type="text" class="pl-1" maxlength="15"
                       style="width: 100%; border: 1px solid #ddd;text-align: right;" >
                <input type="hidden" id="projmgmtBufferPriceInput" maxlength="15" >

            </div>
        </div>
    </div>
</div>

<div id="showEditServiceModal"></div>
<div id="showEditInputServiceModal"></div>
<div id="showEditStaffModal"></div>
<script>




    $(document).ready(function () {
        var projmgmtTable =  $('#tableProjectMgmt').DataTable({
          "order": [],
          "columnDefs": [
              {"className": "dt-center", "targets": [0,2,1,3,5]},
              {"targets": -1, 'orderable' : false}
          ],
          "autoWidth" : true,
      });
        
    });

   


    $('#addService').on('click', function () {
        addService();
    });

    var product = projmgmtTable;
    var prd = false;

    // function addService() {

    //     if (prd == true) {
    //         return null
    //     }
        
    //     prd = true;

    //     $.ajax({
    //         type: "GET",
    //         enctype: 'multipart/form-data',
    //         processData: false,
    //         contentType: false,
    //         cache: false,
    //         data: '',
    //         success: function (response) {
    //             console.log(response);
    //             projmgmtTable.ajax.reload();
    //             $("#showEditServiceModal").html(response);
    //             //$("#msgModal").modal('show');
    //             prd = false;

    //         }, error: function (e) {
    //             console.log(e.message);
    //         }
    //     });
	// 	*/
    // }

    function details(product_id) {
        $.ajax({
            type: 'POST',
            'headers': {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: {
                "product_id": product_id
            },
            success: function (response) {
                console.log(response)
                $("#productResponce").html(response);
                $('#modal').modal('show')
            },
            error: function (e) {
                console.log('error', e);
            }
        });
    }

    $('#tableProjectMgmt tbody').on('click', 'td', function () {
        const tableCell = projmgmtTable.cell(this).data();
        const tableRow = projmgmtTable.row($(this).closest('tr')).data();
        const element = $(tableCell).data("field");


        if (element == 'cuz_productname') {
            return null
        }
        console.log(element);
        console.log(tableRow['DT_RowIndex']);
        if (element != null) {
            console.log(element);

			/*
            $.ajax({
                url: "{{route('custompro.edit.modal')}}",
                type: 'post',
                data: {
                    'id': tableRow['id'],
                    'field_name': element
                },
                success: function (response) {
                    $("#showEditServiceModal").html(response);
                    $("#showMsgModal").modal('show');
                    console.log(response)
                    //$('#msgModal').modal('show');
                },
                error: function (e) {
                    console.log('error', e);
                }
            });
			*/
        }

        // alert( 'You clicked on '+data[0]+'\'s row COmmmmits;
    });
    filter_price("#projmgmtPriceInput", "#projmgmtBufferPriceInput");
    $('table').on('click', 'tr p.priceOutput', function (e) {
        e.preventDefault();
        //var textVal = $('#projmgmtPriceInput').val();

        var price = $(this).val();
        var product_id = $(this).data("product_id");


        var regex = /^(\$|)([1-9]\d{0,2}(\,\d{3})*|([1-9]\d*))(\.\d{2})?/;

        var passed = price.match(regex);

        if (passed == null) {

            $('#projmgmtPriceInput').val('0.00');
        }
        $(".priceOutput").removeClass("priceOutputVal");
        $(this).addClass('priceOutputVal');
        document.getElementById("projmgmtPriceInput").value = $(this).text();

        $("#projmgmtPriceInput").keyup(function () {
            var maxChars = 15;
            if ($(this).val().length > maxChars) {
                $(this).val($(this).val().substr(0, maxChars));
                return false;
                //Take action, alert or whatever suits
                // alert("This field can take a maximum of 30 characters");
            }else{
                $(this).val($(this).val().replace(/^0+(?!\.|$)/, ''));
            }

            price = $(this).val();
            if (price != "") {
                $(".priceOutputVal").text(price);
            }

        });

        document.getElementById("projmgmtPriceInput").onblur = function () {

            if (price != "") {
                updateProductPrice(product_id, price);
            }
        };


    });


    function updateProductPrice(product_id, price) {
        price = formatFigures(price);
        $.ajax({
            type: "GET",
            data: {
                product_id: product_id,
                price: price

            },
            success: function (response) {
                projmgmtTable.ajax.reload();
                $("#showEditServiceModal").html(response);
                //$("#msgModal").modal('hide');
            }, error: function (e) {
                console.log(e.message)
            }
        });
    }

    function formatFigures(value) {
        var ns = value;
        // Just remove commas and periods - regex can do any chars
        ns = ns.replace(/([,.])+/g, '');
        return ns
    }


</script>
<style>
    .modal-add-style {
        text-decoration: underline blue;
        cursor: pointer;
    }
</style>
