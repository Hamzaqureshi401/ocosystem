    <div class="row py-2" style="padding-bottom:25px !important">
            <div class="col align-self-center" style="width:80%">
                <h2>Central Admin</h2>
            </div>
        </div>
    </div>
            <table class="table table-bordered centraladmin" id="deliverycontrol_tbl">
                <thead>
                    <tr class="thead-dark">
                        <th class="text-center" style="width:10px">No</th>
                        <th class="text-center" style="width:150px">Document No.</th>
                        <th class="text-center" style="width:150px">Date</th>
                        <th class="text-center" style="width:100px">Source</th>
                        <th class="text-center" style="width:120px">Amount (MYR)</th>
                        <th class="text-center" style="width:120px">Deliveryman</th>
                        <th class="text-center" style="width:150px">From</th>
                        <th class="text-center" style="width:50px">Status</th>
                        <th class="text-center" style="width:25%;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center" style="padding-top:15px;">1</td>
                        <td class="text-center" style="padding-top:15px;">232131231</td>
                        <td class="text-center" style="padding-top:15px;">28May19 11:14</td>
                        <td class="text-center" style="padding-top:15px;">randoM</td>
                        <td class="text-right" style="padding-top:15px;">232.323</td>
                        <td class="text-center" style="padding-top:15px;">DeliverMan</td>
                        <td class="text-center" style="padding-top:15px;">Cheras
                        </td>
                        <td class="text-center" style="padding-top:15px;">Pending</td>
                        <td class="text-center">
                            <button class="btn btn-success">DO/Invoice</button>
                            <button class="btn btn-light dis-width">TR</button>
                            <button class="btn btn-danger">Discard</button>
                        </td>
                    </tr>
                </tbody>
        </table>
    <script>
        $(document).ready( function () {
            $('#deliverycontrol_tbl').DataTable();
        } );
    </script>
