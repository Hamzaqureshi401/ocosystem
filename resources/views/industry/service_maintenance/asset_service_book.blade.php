@extends('industry.oil_gas.og_oilgas')
@section('content_landing')

    <div class="clearfix"></div>
    <div class="row py-2" style="height:80px;display:flex">
        <div class="col align-self-center" style="width:80%">
            <h2>Service Book</h2>
        </div>
    </div>
    <table class="table table-bordered display" id="comm_table"
           style="width:100%;">
        <thead class="thead-dark">
        <tr>
            <th class="text-center" id="index_head_id" style="width:30px">No.</th>
            <th class="text-center" style="width:150px">Job ID</th>
            <th class="" style="">Description</th>
            <th class="text-center">Technician</th>
            <th class="text-center" style="">Date</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td class="text-center">1</td>
            <td class="text-center">12345678</td>
            <td class=""></td>
            <td class="text-center"></td>
            <td class="text-center"></td>
        </tr>

        </tbody>
    </table>

@endsection
@section('script')
<script>
    $('#servAsset').click(function() {
        ajaxLoadView('{{ route('industryServMn.assetLoad') }}')
    });
</script>
@endsection