
@extends('layouts.layout')

@section('content')

<div id="landing-view">
 <style type="text/css">
    .inside_qty{
       margin-top: -3px;
    }
    #prodstockreport tbody td{
        display: table-cell;
        vertical-align: inherit;
        padding-bottom: 2px !important;
        padding-top: 2px !important;
    }
    div.col-sm-3 p {
        margin-bottom: 0px;
    }

 </style>
<link href="{{ asset('css/ionicons.min.css') }}" rel="stylesheet">


<div class="row py-2" style="padding-bottom:0px !important">
    <div class="col align-self-center" style="width:80%">
        <h2>@if($stockreport_data->refund_type == 'stockin') Stock In @else Stock Out @endif</h2>
    </div>
    <div class="col-sm-1" style="align-self:center">
      
    </div>
    <div class="col-sm-5" style="align-self:center;float:left;padding-left:0">
<h4 style="margin-bottom:0px;padding-top: 0;line-height:1.5;">Report ID : {{$stockreport_data->document_no}}</h4>
    <p>Location : {{$stockreport_data->location}} </p>
    </div>
    <div class="col-sm-3" style="float: right;">
        <p>Staff Name: {{$stockreport_data->staff_name}}</p>
        <p>Staff ID: {{$stockreport_data->staff_id}}</p>
        <p>Date: <?php echo date('dMy H:i:s',strtotime($stockreport_data->last_update)); ?></p>
    </div>
</div>

<table class="table table-bordered" id="prodstockreport" style="width:100%;">
    <thead class="thead-dark">
    <tr>
        <th style="width:30px;text-align: center;">No</th>
        <th style="width:100px;text-align: center;">Product&nbsp;ID</th>
        <th>Product Name</th>
        <th style="text-align: center;">Colour</th>
        <th style="text-align: center;">Matrix</th>
        <th style="text-align: center; width: 100px;">@if($stockreport_data->refund_type == 'stockin') Qty In @else Qty Out @endif</th>
    </tr>
    </thead>
    <tbody>
    @foreach($stockreport as $key => $value)
        <tr>
            <td style="width:30px;text-align: center;">{{$key+1}}</td>
            <td style="width:100px;text-align: center;">{{$value->systemid}}</td>
            <td><img src="{{ asset('images/product/'.$value->id.'/thumb/'.$value->thumbnail_1) }}" style="height:40px;width:40px;object-fit:contain;margin-right:8px;">{{$value->name}}</td>
            <td style="text-align: center;">@if($value->colour) {{$value->colour}} @else - @endif</td>
            <td style="text-align: center;">@if($value->matrix) {{$value->matrix}} @else - @endif</td>
            <td style="text-align: center;">@if($value->quantity) {{$value->quantity}} @else - @endif</td>
        </tr>
    @endforeach
    </tbody>
</table>
<div id="productResponse"></div>
     @section('scripts')
 @include('settings.buttonpermission')
 <script type="text/javascript">
      $(document).ready(function () {
        // prodstockreportTable.draw();
            var tableinventory =  $('#prodstockreport').DataTable({
              // "order": [[ 3, "desc" ]]
            });
     });
        // var prodstockreportTable = $('#prodstockreportTable').DataTable({
        //     "order": [],
        //     "columnDefs": [
        //         {"className": "dt-center", "targets": [0,1,3,4,5]},
        //         {"className": "dt-body-nowrap num_td", "targets": [6]},
        //     ],
        // });
    
   </script>

  @endsection
</div>

 @endsection
