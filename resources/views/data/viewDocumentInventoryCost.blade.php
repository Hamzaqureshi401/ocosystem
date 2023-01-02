@extends('layouts.layout')
@section('content')
<style type="text/css">

    #products-datatable tbody td{
        display: table-cell;
        vertical-align: inherit;
        padding-bottom: 2px !important;
        padding-top: 2px !important;
    }

    #products-datatable thead th {
        padding: 12px 18px 12px 18px;
    }

    .date_table >  tbody > tr > th {
        font-size:22px;
        color:white;
        background-color: rgba(255, 255,255, 0.5);
    }

    .date_table > tbody > tr > td {
        color:#fff;
        font-weight: 600;
        border:unset;
        font-size: 20px;
        cursor:pointer;
    }




</style>
<link href="{{ asset('css/ionicons.min.css') }}" rel="stylesheet">

<div id="landing-view">
    <div id="landing-content" style="width: 100%">

        <div class="row mb-2" style="padding-top:5px">
            <div class="col-sm-3" style="align-self:center">
                <h2 style="margin-bottom:0px;padding-top: 0;">Inventory Cost</h2>
            </div>
            <div class="col-sm-5" style="align-self:center;float:left;padding-left:0">
                <h4 style="margin-bottom:0px;padding-top: 0;line-height:1.5;">{{ $merchant_data['name'] }}</h4>
                <p style="font-size:18px;margin-bottom:0">{{ $merchant_data['systemId'] }}</p>
            </div>
            <div class="col-sm-3 js-date-container offset-1 text-right" style="float: right;" >
                <div class="row">
                    <div class="col-md-12 text-right mb-1" style="line-height: 0.5;margin-top:0;">
                        <h4>
                            {{$inventoryCost->doc_no}}
                        </h4>
                    </div>
                </div>
                <div class="row">
                    <dov class="col-md-12">
                        <div style="right:190px;display:inline;padding-left:0; margin-bottom:20px">
                            <h4>
                                {{date('dMy', strtotime($inventoryCost->doc_date))}}
                            </h4>
                        </div>

                    </dov>
                </div>
            </div>

        </div>



        <table class="table table-bordered" id="products-datatable" style="width:100%;">
            <thead class="thead-dark">
            <tr>
                <th style="width:30px;">No</th>
                <th style="width:150px;">Product ID</th>
                <th>Product Name</th>
                <th style="width:90px;text-align: center;" >Cost (MYR)</th>
                <th style="width:60px;text-align: center;" nowrap>Qty</th>
                <th style="width:60px;">
                    Amount(MYR)
                </th>
            </tr>
            </thead>
            <tbody>
            @foreach($inventory_data as $key => $in_product)
            <tr>
                <td></td>
                <td>{{$in_product->systemid}}</td>
                <td><img src="{{ asset('images/product/'.$in_product->id.'/thumb/'.$in_product->thumbnail_1) }}" data-field='inven_pro_name' style=' width: 40px;height: 40px;display: inline-block;margin-right: 8px;object-fit:contain;'>@if(!empty($in_product->name)) {{$in_product->name}} @endif</td>
                <td class="text-center">
                    {{number_format($in_product->cost/100, 2)}}
                </td>
                <td>
                    {{$in_product->quantity}}

                </td>
                <td>
                    {{ number_format( ($in_product->cost/100) * $in_product->quantity, 2 ) }}
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>





    </div>





    <style>
        .btn {color: #fff !Important;}

    </style>

    <style>
        .form-control:disabled, .form-control[readonly] {
            background-color: #e9ecef !important;
            opacity: 1;
        }
        a{
            text-decoration: none;
        }
    </style>

    @section('scripts')
    @include('settings.buttonpermission')

    <script type="text/javascript">
        ;
        var productTableRow;

        $(document).ready(function () {

            var tablevocherproduct =  $('#products-datatable').DataTable({
                "order": [],
                "columnDefs": [
                    {
                        "targets": [3,4,5],
                        'orderable' : false
                    },
                    {
                        "className": "dt-center",
                        "targets": [0,1]
                    },
                    {
                        "className": "dt-body-nowrap num_td",
                        "targets": [2]
                    },
                    {
                        "className": "dt-body-nowrap num_td cost-td",
                        "targets": [3]
                    },
                    {
                        "className": "dt-center qty-td",
                        "targets": [4]
                    },
                    {
                        "className": "text-right",
                        "targets": [5]
                    }

                ],
                "autoWidth" : true
            });
            tablevocherproduct.on( 'order.dt search.dt', function () {
                tablevocherproduct.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
                    cell.innerHTML = i+1;
                } );
            }).draw();



        });

    </script>
    @include('settings.buttonpermission')


    @endsection
</div>
</div>
@endsection
