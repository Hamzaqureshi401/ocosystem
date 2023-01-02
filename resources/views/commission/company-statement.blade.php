@extends('layouts.layout')

@section('content')
		<div class="row py-2" style="padding-top:0!important;padding-bottom:0!important;margin-bottom:8px; margin-top:20px;height:80px;display:flex">
			<div class="col-md-6" style="width:40%">
				<div class="col align-self-center"
					style="padding-left:0;margin-bottom:10px;width:40%; float: left">
					<h6>16086 Albatros Street Protea Glen</h6>
					<h6>Ext 16</h6>
				</div>
			</div>
			<div class="col-md-2" style="width:30%; margin-bottom:0px;">
				<div class="col align-self-center"
					style="padding-left:0;margin-bottom:0px;width:100%; float: left">
					
				</div>
			</div>
			<div style="margin-left: -20px;" class=" col-md-4 input-append date" id="datepicker" data-date="02-2012" 
		         data-date-format="mm-yyyy">
				 <input  type="text" readonly="readonly" name="date">	  
				 <span class="add-on" style="display:inline-block;"><i class="fa fa-th"></i></span>	
		    </div>	
		</div><br>
  
        <center><h4>Company Commission Statement<?php//§§§§§§§§§e echo DNS1D::getBarcodeHTML('4445645656', 'C39');*/ ?></h4></center>
		<table class="table table-bordered display" id="partner_table"
			style="width:100%;">
			<thead class="thead-dark">
			<tr>
				<th class="text-center" style="width:150px">Date</th>
				<th class="text-center" style="width:150px">Document No</th>
				<th class="width: 100%;">Commission Earner</th>
				<th class="text-center" style="width:80px">Type</th>
				<th class="text-center" style="width:50px">Sales</th>
				<th class="text-center" style="width:50px">Commission</th>
				<th class="text-center" style="width:50px">Amount</th>
			</tr>
			</thead>
			<tbody>
			  
			</tbody>
		</table>







@endsection

@section('scripts')
		<script>
           //var $j = jQuery.noConflict();
            $("#datepicker").datepicker( {
			    format: "mm-yyyy",
			    viewMode: "months", 
			    minViewMode: "months"
			});

			$('.datepicker').addClass('bg-greenlobster');
				   
           var Base_URL = $('meta[name="base-url"]').attr('content');

			const str = window.location.href;
		  	var n = str.lastIndexOf('/');
            var company = str.substring(n + 1);
            var comp = decodeURI(company)
	        $('#company').html(comp);
            
            $('#partnerbutton').click(function(){
             window.location.replace(Base_URL+'landing');
            });

		       var agentTable = $('#partner_table').DataTable({
		       	"order": [],
			    columns: [
			    {data: 'DT_RowIndex', name: 'DT_RowIndex', class: 'text-center data_index'},
			        {data: 'systemid', name: 'systemid', class: 'text-center'},
			        {data: 'name', name: 'name'},
			        {data: 'pool_amt', name: 'pool_amt', class: 'text-center'},
			        {data: 'commission_amt', name: 'commission_amt', class: 'text-center'},
			        {data: 'button2', name: 'button2'},
			        {data: 'button1', name: 'button1'},
			    ],
			    bFilter: false, 
			    bInfo: false,
			     "autoWidth" : true,
			     "bPaginate": false,

				columnDefs: [
				   { orderable: false, targets: [ -1, -2,-3, -4, -5,-6,-7]}
				]
		       
		    });


		   agentTable.draw();

		   $(".dataTables_scrollHeadInner").css({"width":"100%"});
           $(".table ").css({"width":"100%"});


			 

		</script>

<!---button permission---->

@endsection