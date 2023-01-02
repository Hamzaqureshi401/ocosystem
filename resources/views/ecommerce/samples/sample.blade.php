<div class="row py-2" style="padding-bottom:0 !important">
    <div class="col align-self-center" style="width:80%">
        <h2>Product: Drum & Barrel</h2>
    </div>
    <div class="col col-auto align-self-center">
        <button class="btn btn-success btn-log sellerbutton mr-0"
                style="padding-left:8px" onclick="newProduct()">
            <span>+Product</span>
        </button>
    </div>
</div>

<div class="modal" id="membershipBuyModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Modal body -->
            <div class="modal-body">
                <input id="memberBuyInput" type="text" class="pl-1"
				style="width: 100%; border: 1px solid #ddd;">
            </div>
        </div>
    </div>
</div>

<div class="modal" id="membershipGetModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Modal body -->
            <div class="modal-body">
                <input id="memberGetInput" type="text" class="pl-1"
				style="width: 100%; border: 1px solid #ddd;">
            </div>
        </div>
    </div>
</div>

<table class="table table-bordered" id="tableDrumBarrel" style="width:100%;">
    <thead class="thead-dark">
    <tr>
        <th class="text-center" style="width:30px;text-align: center;">No</th>
        <th class="text-center" style="width:20px;text-align: center;">
			Product&nbsp;ID</th>
        <th>Product&nbsp;Name</th>
        <th class="text-center" style="width:50px;text-align: center;">
			Price&nbsp;(MYR)</th>
        <th class="text-center" style="width:50px;text-align: center;">
			Deposit&nbsp;(MYR)</th>
        <th class="text-center"
			style="width:10px;text-align: center;background-image: unset !important "></th>
    </tr>
    </thead>
    <tbody>

    </tbody>
</table>

<div class="modal" id="drumbarrelPriceModal">
    <div class="modal-dialog" style="width: 250px;">
        <div class="modal-content">
            <!-- Modal body -->
            <div class="modal-body">
                <input id="drumbarrelPriceInput" type="text" maxlength="15" class="pl-1"
                       style="width: 100%; border: 1px solid #ddd;text-align: right;">
                <input id="drumbarrelBufferPriceInput" maxlength="15" type="hidden"
					class="pl-1"
					style="width: 100%; border: 1px solid #ddd;text-align: right;">
            </div>
        </div>
    </div>
</div>
<div class="modal" id="drumbarrelDepositModal">
    <div class="modal-dialog" style="width: 250px;">
        <div class="modal-content">
            <!-- Modal body -->
            <div class="modal-body">
                <input id="drumbarrelDepositInput" type="text" maxlength="15" class="pl-1"
                       style="width: 100%; border: 1px solid #ddd;text-align: right;">
                <input id="drumbarrelBufferDepositInput" type="hidden" maxlength="15"
					class="pl-1"
					style="width: 100%; border: 1px solid #ddd;text-align: right;">
            </div>
        </div>
    </div>
</div>
<div id="showEditDrumbarrelModal"></div>
<div id="showEditInputDrumbarrelModal"></div>
<div id="showEditStaffModal"></div>
<script type="text/javascript">
    $(document).ready(function () {
        drumbarrelTable.draw();
    });

    var drumbarrelTable = $('#tableDrumBarrel').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "{{route('drumbarrel.ajax.index')}}",
            "type": "POST",
            'headers': {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'drum_productid', name: 'drum_productid'},
            {data: 'drum_productname', name: 'drum_productname'},
            {data: 'drum_price', name: 'drum_price'},
            {data: 'drum_deposit', name: 'drum_deposit'},
            {data: 'deleted', name: 'deleted'},
        ],
        "order": [],
        "columnDefs": [
            {"className": "dt-center", "targets": [0, 1, 3, 4, 5]},
        ],
    })

    var product = drumbarrelTable;
    var prd = false;

    function newProduct() {

        if (prd == true) {
            return null
        }
        ;
        prd = true;
        $.ajax({
            url: "{{route('drumbarrel.store')}}",
            type: "GET",
            enctype: 'multipart/form-data',
            processData: false,
            contentType: false,
            cache: false,
            data: '',
            success: function (response) {
                console.log(response);
                drumbarrelTable.ajax.reload();
                $("#showEditDrumbarrelModal").html(response);
                //$("#msgModal").modal('show');
                prd = false;

            }, error: function (e) {
                console.log(e.message);
            }
        });
    }

    function details(product_id) {
        $.ajax({
            url: "{{route('drumbarrel.details.dialog')}}",
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

    $('#tableDrumBarrel tbody').on('click', 'td', function () {
        const tableCell = drumbarrelTable.cell(this).data();
        const tableRow = drumbarrelTable.row($(this).closest('tr')).data();
        const element = $(tableCell).data("field");


        if (element == 'drum_productname') {
            return null
        }
        console.log(element);
        console.log(tableRow['DT_RowIndex']);
        if (element != null) {
            console.log(element);

            $.ajax({
                url: "{{route('drumbarrel.edit.modal')}}",
                type: 'post',
                data: {
                    'id': tableRow['id'],
                    'field_name': element
                },
                success: function (response) {
                    $("#showEditDrumbarrelModal").html(response);
                    $("#showMsgModal").modal('show');
                    console.log(response)
                    //$('#msgModal').modal('show');
                },
                error: function (e) {
                    console.log('error', e);
                }
            });
        }

        // alert( 'You clicked on '+data[0]+'\'s row COmmmmits;
    });
    filter_price("#drumbarrelPriceInput","#drumbarrelBufferPriceInput");
    $('table').on('click', 'tr p.priceOutput', function (e) {
        e.preventDefault();
        var textVal = $('#drumbarrelPriceInput').val();
        var product_id = $(this).data("product_id");


        var regex = /^(\$|)([1-9]\d{0,2}(\,\d{3})*|([1-9]\d*))(\.\d{2})?/;
        var passed = textVal.match(regex);
        if (passed == null) {

            $('#drumbarrelPriceInput').val('0.00');
        }
        $(".priceOutput").removeClass("priceOutputVal");
        $(this).addClass('priceOutputVal');
        document.getElementById("drumbarrelPriceInput").value = $(this).text();
        $("#drumbarrelPriceInput").keyup(function () {
            var maxChars = 15;
            if ($(this).val().length > maxChars) {
                $(this).val($(this).val().substr(0, maxChars));
                return false;
            }else{
                $(this).val($(this).val().replace(/^0+(?!\.|$)/, ''));
            }
            price = $(this).val();
            if (price != "") {
                $(".priceOutputVal").text(price);
            }
        });
        document.getElementById("drumbarrelPriceInput").onblur = function () {
            if (price != "") {
                updateDrumbarrelPrice(product_id, price);
            }
        };
    });

    filter_price("#drumbarrelDepositInput", "#drumbarrelBufferDepositInput");
    $('table').on('click', 'tr p.depositOutput', function (e) {
        e.preventDefault();
        var textVal = $('#drumbarrelDepositInput').val();
        var product_id = $(this).data("product_id");


        var regex = /^(\$|)([1-9]\d{0,2}(\,\d{3})*|([1-9]\d*))(\.\d{2})?/;
        var passed = textVal.match(regex);

        if (passed == null) {
            $('#drumbarrelDepositInput').val('0.00');
        }
        $(".depositOutput").removeClass("depositOutputVal");
        $(this).addClass('depositOutputVal');
        document.getElementById("drumbarrelDepositInput").value = $(this).text();
        $("#drumbarrelDepositInput").keyup(function () {
            var maxChars = 15;
            if ($(this).val().length > maxChars) {
                $(this).val($(this).val().substr(0, maxChars));
                return false;
                //Take action, alert or whatever suits
                // alert("This field can take a maximum of 30 characters");
            }else{
                $(this).val($(this).val().replace(/^0+(?!\.|$)/, ''));
            }

            deposit = $(this).val();
            if (deposit != "") {
                $(".depositOutputVal").text(deposit);
            }
        });

        document.getElementById("drumbarrelDepositInput").onblur = function () {
            if (deposit != "") {
                updateDrumbarrelDeposit(product_id, deposit);
            }
        };
    });


    function updateDrumbarrelPrice(product_id, price) {
        price = formatFigures(price);
        $.ajax({
            url: "{{route('drumbarrel.ajax.price')}}",
            type: "GET",
            data: {
                product_id: product_id,
                price: price

            },
            success: function (response) {
                drumbarrelTable.ajax.reload();
                //$("#modal").modal('hide');
                $("#showEditDrumbarrelModal").html(response);

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

    function updateDrumbarrelDeposit(product_id, deposit) {
        deposit = formatFigures(deposit);
        $.ajax({
            url: "{{route('drumbarrel.ajax.deposit')}}",
            type: "GET",
            data: {
                product_id: product_id,
                deposit: deposit

            },
            success: function (response) {
                drumbarrelTable.ajax.reload();
                //$("#modal").modal('hide');
                $("#showEditDrumbarrelModal").html(response);

            }, error: function (e) {
                console.log(e.message)
            }
        });
    }

</script>
