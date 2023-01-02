@extends('industry.oil_gas.og_oilgas')
@section('content_landing')

    <div class="clearfix"></div>
    <div class="row py-2 pb-2" style="display:flex">
        <div class="col align-self-center" style="width:80%">

            <table>
                <tr>
                    <td><img src="/images/no-image.jpg" height="100" width="100"> </td>
                    <td><h5> Product Name <br> Product ID</h5></td>
                </tr>
            </table>
        </div>
    </div>
    <div class="">
        <table class="table table-bordered display" id="comm_table"
               style="width:100%;">
            <thead class="thead-dark">
            <tr>
                <th class="text-center" id="index_head_id" style="width:30px">No.</th>
                <th class="text-center" style="width:150px">Part ID</th>
                <th class="" style="">Part Name</th>
                <th class="text-center">Serial No</th>
                <th class="text-center">Last</th>
                <th class="text-center" style="">Next</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td class="text-center">1</td>
                <td class="text-center"></td>
                <td class=""></td>
                <td class="text-center"></td>
                <td class="text-center"></td>
                <td class="text-center"></td>
            </tr>

            </tbody>
        </table>
    </div>

@endsection
@section('script')
    <script>
        $('#servAsset').click(function() {
            ajaxLoadView('{{ route('industryServMn.assetLoad') }}')
        });
    </script>
@endsection