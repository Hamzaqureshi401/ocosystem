<!--Start Code modal-->
<div class="modal fade" id="codeModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" style="min-width: 140vh;">
        <div class="modal-content">
            <div class="modal-header">
                 <h3>Select Product</h3>
            </div>
            <div class="modal-body">

                <table class="table table-bordered" id="codeopossumtable" style="width:100%;">
                    <thead class="thead-dark">
                    <tr>
                    <th>No</th>
                      <th>Product ID</th>
                        <th>Product Name</th>
                        <th>Products</th>
                     </tr>
                    </thead>
                </table>

            </div>
        </div>
    </div>
</div>
<style type="text/css">
/*        .width_long {color:#007bff;}/*/

    .width_long {width: 550px;}
/*    #codeopossumtable > tbody > tr:hover {
        color: #fff !important;
        background: blue;
    }*/

</style>
<script type="text/javascript">
    $(document).ready(function () {
        codeopossumtable.draw();
    });

    var codeopossumtable = $('#codeopossumtable').DataTable({
        "processing": false,
        "serverSide": true,
        "ajax": {
            "url": "{{route('opossum.table.code')}}",
            "type": "POST",
            "data": { 'terminal_id': '{{ $terminal->systemid }}' }
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'product_id', name: 'product_id'},
            {data: 'product_name', name: 'product_name'},
            {data: 'source', name: 'source'},
        ],
        "order": [0, 'desc'],
        "columnDefs": [
            {"className": "dt-center", "targets": [0, 1,3]},
            {"className": "width_long", "targets": [2]},
        ],
    });

    $('#codeopossumtable tbody').on('click', 'td', function () {
        const tableCell = codeopossumtable.cell(this).data();
        const tableRow = codeopossumtable.row($(this).closest('tr')).data();
        const element = $(tableCell).data("field");

        if (element != null) {
            const id =  tableRow['id']
            addDProduct(id)
        }
    });
</script>
