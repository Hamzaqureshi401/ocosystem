@extends('layouts.layout')
@section('content')
<div id="landing-view">
<div id="landing-content" style="width: 100%">
    <div class="clearfix"></div>
	<div class="row py-2">
		<div class="col align-self-end" style="width:80%">
			<h2 style="margin-bottom: 15px;">File</h2>
		</div>
    <div class="modal" id="rackFileNameModal" style="margin-top:16%;">
        <div class="modal-dialog">
          <div class="modal-content">
            <!-- Modal body -->
            <div class="modal-body">
                <input id="rackFileNameInput" type="text" class="pl-1" style="width: 100%; border: 1px solid #ddd;">
            </div>
          </div>
        </div>
    </div>
 
    <div class="modal" id="repairAmountModal" style="margin-top:5%;">
        <div class="modal-dialog">
          <div class="modal-content">
            <!-- Modal body -->
            <div class="modal-body">
                <input id="repairAmountInput" type="text" class="pl-1" style="width: 100%; border: 1px solid #ddd;">
            </div>
          </div>
        </div>
    </div>

		<div class="col col-auto align-self-center" style="margin-bottom:-8px;margin-top: -3px;text-align: center;">
			
        <button class="btn btn-success btn-log sellerbutton text-center" style="margin-right:0;    padding: 0;" id="addRackFileButton" > <span >+File</span>
      </button>
		</div>
    </div>


    <table class="table table-bordered display" id="repair_table" style="width:100%;">
          <thead class="thead-dark">
          <tr>
              <th style="width:1%;text-align: center; white-space: nowrap;">No.</th>
              <th style="width:1%;text-align: center; white-space: nowrap;">File ID</th>
              <th>File Name</th>
              <th style="width: 1%; text-align: center; white-space: nowrap;">Date</th>
               <th style="width: 1%; text-align: center; white-space: nowrap;"><div class="fa fa-camera"></th>
              <th style="width: 1%;"></th>
               </tr>
          </thead>
          <tbody>
          <!-- <tr id="cloningrow">
              <td class="text-center">1</td>
              <td class="text-center">ID1231233</td>
              <td class="text-center"><p>&nbsp19Jul19&nbsp00:32:54</p></td>
              <td class="text-left"><a href="#">Amazon Echo Dot (2nd Gen) - Smart speaker With Alexa - Black</a></td>
              <td class="text-center">Repair</td>
              <td class="text-center"><a href="#">null</a></td>
              <td class="text-center" id="datep"> <p style="color: #007bff;" onclick="show_dialog2()">date</p></td>
              <td class="text-center"><a href="#">0.00</a></td>
           </tr> -->

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
</style>

@section('scripts')
@include('settings.buttonpermission')

<script type="text/javascript">
    $(document).ready(function () {
       var tts_table =  $('#repair_table').DataTable({
           "order": [],
           "columnDefs": [
               {"className": "dt-center", "targets": [0,1,4,]},
               {"className": "dt-right", "targets": []},
               {"targets": -1, 'orderable' : false}
           ],
           "autoWidth" : false,
       });
       var counter=1;
       
    $('#addRackFileButton').on( 'click', function () {
    tts_table.row.add([counter +'',
      '1',
      '<p class="rackFileNameOutput" data-toggle="modal"  data-target="#rackFileNameModal" style="color:#007bff;width100%;margin:0;">File</p>',
      '<p style="margin:0;">&nbsp19Jul19&nbsp00:32:54</p>',
      '<a href="#" id="file"><div class="fa fa-camera"></div></a>',
      ' <p data-field="deleted" style="background-color:red;border-radius:5px;margin:auto;width:25px;height:25px;display:block;cursor: pointer;" class="text-danger remove"><i class="fas fa-times text-white" style="color:white;opacity:1.0;padding-left:7px;padding-top:4px;-webkit-text-stroke: 1px red;"></i></p>'] ).draw();
    counter++;
  });
   });

</script>
<script type="text/javascript">
    $('table').on('click','tr p.rackFileNameOutput',function(e){
      e.preventDefault();
      $(".rackFileNameOutput").removeClass("rackFileNameOutputVal");
      $(this).addClass( 'rackFileNameOutputVal' );
      document.getElementById("rackFileNameInput").value = $(this).text();
        $("#rackFileNameInput").keyup(function(){
            var currentText = $(this).val();
            $(".rackFileNameOutputVal").text(currentText);
        });
    });
</script>

@endsection
</div>
</div>
@endsection
