<div class="clearfix"></div>
<div class="row py-2" style="height:80px;display:flex">
    <div class="col align-self-center" style="width:80%">
        <h2>Asset Management</h2>
    </div>

    <div class="col col-auto align-self-center" style="margin-bottom:-8px;margin-top: -3px;text-align: center;">
        <button onclick="loadModal('{{ route('assets.create') }}')" data-toggle="modal" data-target="#valueModal" class="btn btn-success btn-sm btn-log sellerbutton text-center" style="padding:0;display:block;text-align: center;" id="add_asset">
            <span>+ Asset</span>
        </button>
    </div>
</div>
<table class="table table-bordered display" id="comm_table"
       style="width:100%;">
    <thead class="thead-dark">
    <tr>
        <th class="text-center" id="index_head_id" style="width:30px">No.</th>
        <th class="text-center" style="width:150px">Asset ID</th>
        <th class="" style="">Asset Name</th>
        <th class="text-center">Serial No</th>
        <th class="text-center" style="">Location</th>
        <th class="text-center" style="">Ownership</th>
        <th class="text-center" style="width:50px">Value</th>
        <!-- Yellow Crab: [S] -->
        <th class="text-center" style="width:30px"></th>
        <!-- Blue Crab:   [O] -->
        <th class="text-center" style="width:30px"></th>
        <!-- Red Crab:    [X] -->
        <th class="text-center" style="width:30px"></th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td class="text-center">1</td>
        <td class="text-center">12345678</td>
        <td class=""><a href="#">Sony walky</a></td>
        <td class="text-center">9876543</td>
        <td class="text-center">Chers</td>
        <td class="text-center"></td>
        <td class="text-center">
            <a href="javascript:void(0)" style="cursor:pointer; margin-top: 0;" onclick="loadModal('{{ route("industryServMn.assetValues") }}')"  data-toggle="modal" data-target="#valueModal" data-id="1">25.80</a>
        </td>
        <td class="text-center vamiddle">
            <div data-_a_pct="" data-_p_pct="" class="text-center commission_scheme_definition" style="" onclick="window.open('{{ route('assetProduct') }}')">
                <img class="" src="/images/yellowcrab_50x50.png" style="width:25px;height:25px;cursor:pointer">
            </div>
        </td>
        <td class=" text-center vamiddle">
            <div data-field="bluecrab" data-toggle="modal" data-company_name="aa" style="cursor:pointer"
                 class="show_agent text-center align-items-center" onclick="window.open('{{ route('assetServiceBook') }}')">
                <img src="/images/bluecrab_50x50.png" style="width:25px;height:25px;">
            </div>
        </td>
        <td class=" text-center vamiddle"><input type="image" src="/images/redcrab_25x25.png" data-id=""
                                                 data-scheme_id="" alt=""
                                                 class="text-danger bg-redcrab1 delete_button" data-toggle="modal"
                                                 data-target="#showMsgModal"
                                                 style="width:25px;height:25px;align-items:center; margin-top: 0; cursor: pointer;"></td>
    </tr>

    </tbody>
</table>