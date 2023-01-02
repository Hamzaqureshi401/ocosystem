@extends('layouts.layout')

@section('content')
<div class="row py-2"
	style="padding-top:0!important;padding-bottom:0!important;display:flex">
	<div class="col align-self-center" style="width:80%">
		<div class="col align-self-center"
			style="padding-left:0;width:40%; float: left">
			<h2 class="mb-0">Commission Earner</h2>
		</div>
	</div>
	<div class="col col-auto align-self-center"
		style="margin-bottom:0;margin-top:0;text-align: center;">
		<button class="btn btn-success btn-log bg-virtualcabinet
			sellerbuttonwide text-center"
			style=" padding:0;margin-bottom:0"
			id="companyStatement"
			onclick="">
			<span>Company Statement</span>
		</button>
		<button class="btn btn-success btn-log sellerbuttonwide text-center"
			style="margin-bottom:0;margin-right:0;padding-left:0;padding-right:0;"
			id="add_agent">
			<span >+Commission Earner</span>
		</button>
	</div>
</div>

<div class="modal fade" id="added" tabindex="-1" role="dialog"
	aria-labelledby="showMsgModal" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered  mw-75 w-50" role="document">
	<div class="modal-content modal-inside bg-greenlobster">
		<div class="modal-header" style="border:0">&nbsp;</div>
		<div class="modal-body text-center">
			<h5 style="margin-bottom:0"
				class="modal-title text-white"
				id="statusModalLabel">
				Commission Earner added successfully
			</h5>
		</div>
		<div class="modal-footer" style="border:0">&nbsp;</div>
	</div>
</div>
</div>

<div class="modal fade" id="showMsgModal" tabindex="-1" role="dialog" aria-labelledby="showMsgModal" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered  mw-75 w-50" role="document">
		<div class="modal-content modal-inside bg-greenlobster">
			<div style="border-width:0" class="modal-header text-center"></div>
			<div class="modal-body text-center">
				<h5 class="modal-title text-white"
				id="statusModalLabel">Do you want to permanently delete?</h5>
			</div>
			<div class="modal-footer"
				style="border-top:0 none; padding-left: 0px; padding-right: 0px;">
				<div class="row"
					style="width: 100%; padding-left: 0px; padding-right: 0px;">
					<div class="col col-m-12 text-center">
						<input type="hidden" name="delete_id" id="delete_id" value="">
						<button type="button"
						class="btn bg-primary primary-button"
						data-dismiss="modal" id="delete_confirm_button">Yes</button>
						<button type="button"
						class="btn btn-danger primary-button"
						data-dismiss="modal">No</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!--#####################################END DELETE CONFIRMATION MODAL###################################-->


<!--########################################Modal to confirm Delete######################################-->
<div class="modal fade" id="agent_deleted" tabindex="-1" role="dialog"
	aria-labelledby="showMsgModal" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered  mw-75 w-50"
		role="document">
		<div class="modal-content modal-inside bg-greenlobster">
			<div class="modal-header" style="border:0">&nbsp;</div>
			<div class="modal-body text-center">
					<h5 class="modal-title text-white"
					id="agent_delete_statusModalLabel">Deleted successfully</h5>
			</div>
			<div class="modal-footer" style="border:0">&nbsp;</div>
		</div>
	</div>
</div>

<div class="modal fade" id="editAgentName" tabindex="-1" role="dialog"
	aria-labelledby="branch_nameModallabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
		<form action="#"  method="post" enctype="multipart/form-data"
			autocomplete="off" id="updateProspectFields">
			<div class="modal-body">
				<div style="margin-bottom:0" class="form-group">
					<input type="text" class="form-control"
						id="agent_name" placeholder="Commission Earner"
						name="company"
						value="" autocomplete="off">
				</div>
			</div>
			<input type="hidden" name="location_id" value="">
		</form>

		</div>
	</div>
</div>

<h4 id="company" style="margin: auto auto -24px 260px"></h4>
<table class="table table-bordered display" id="partner_table"
	style="width:100%;">
	<thead class="thead-dark">
	<tr>
		<th class="text-center" id="index_head_id" style="width:30px">No.</th>
		<th class="text-center" style="width:150px">Personal ID</th>
		<th class="width: 100%;">Commission Earner</th>
		<th class="text-center" style="width:80px">Pool</th>
		<th class="text-center" style="width:50px">Commission</th>
		<!-- Blue Crab: [S] -->
		<th class="text-center" style="width:30px"></th>
		<!--Delete-->
		<th class="text-center" style="width:30px"></th>
		<!--<button class="btn" style="background: red; border-radius: 5px; margin: auto; width:30px; height: 30px; display: block; padding: 3px;"><i class="fas fa-times text-white"></i> </<button></button>-->
	</tr>
	</thead>
	<tbody>
	  
	</tbody>
</table>

@endsection

@section('scripts')
<script>
   
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
		"ajax": {
			"url": "{{route('landing.ajax.Agent-Data')}}",
			"type": "GET",
		},
		columns: [
		{data: 'DT_RowIndex', name: 'DT_RowIndex', class: 'text-center data_index'},
			{data: 'systemid', name: 'systemid', class: 'text-center'},
			{data: 'name', name: 'name'},
			{data: 'pool_amt', name: 'pool_amt', class: 'text-center'},
			{data: 'commission_amt', name: 'commission_amt', class: 'text-center'},
			{data: 'button2', name: 'button2', class:'text-center'},
			{data: 'button1', name: 'button1', class:'text-center'},
		],
		 "autoWidth" : true,

		columnDefs: [
		   { orderable: false, targets: [ -1, -2]}
		]
	   
	});


   agentTable.draw();

   $(".dataTables_scrollHeadInner").css({"width":"100%"});
   $(".table ").css({"width":"100%"});

   function newAgent() {
	$.ajax({
		url: "{{route('landing.ajax.Add_agent')}}",
		type: "POST",
		enctype: 'multipart/form-data',
		processData: false,
		contentType: false,
		cache: false,
		data: '',
		success: function (response) {
			if (response.success) {
				agentTable.ajax.reload();
				//toastr.success(response.message);
				$("#statusModalLabel").html(response.message);
				$('#added').modal('show');
					setTimeout(function () {
					   $("#added").fadeOut("slow", function () {

							$('#added').modal('hide');
						  });
					  }, 1000);
			}else{
				toastr.warning(response.message);
			}

		}, error: function (e) {
			toastr.warning(e.message);
		}
	});
  }

  $('#add_agent').click(function(){
	   newAgent();
	});



	function deleteAgent(d_id){
	 $.ajax({
			url: "{{route('landing.ajax.destroy.agent')}}",
			type: "POST",
			enctype: 'multipart/form-data',
			processData: true,
			data: {
					'id': d_id
				},
			success: function (response) {
				if (response.success) {

					agentTable.ajax.reload();
					$("#agent_delete_statusModalLabel").html(response.message);
					$('#agent_deleted').modal('show');
					setTimeout(function () {
					   $("#agent_deleted").fadeOut("slow", function () {

							$('#agent_deleted').modal('hide');
						  });
					  }, 1000);
					//toastr.success(response.message);
				}else{
					alert('fail');
					toastr.warning(response.message);
				}

			}, error: function (e) {
				toastr.warning(e.message);
			}
		});
	}

	$('#delete_confirm_button').hover(function(){
	  $(this).css("color", "white");
	 });

	var d_id = 0;
	$(document).on('click', '.delete_button', function(){
		d_id = $(this).data("id");
		$('#showMsgModal').modal('show');
		$('#delete_confirm_button').click(function(){
		   deleteAgent(d_id);
		});
	});

	


   let inputs = document.querySelectorAll('input');
	function updateAgentName(agent_id, agent_name){
	 $.ajax({
			url: "{{route('landing.ajax.Update_agent_name')}}",
			type: "POST",
			enctype: 'multipart/form-data',
			processData: true,
			data: {
				'id': agent_id,
				'name': agent_name
			},
			success: function (response) {
				if (response.success) {

					agentTable.ajax.reload();
					//toastr.success(response.message);
					$("#statusModalLabel").html(response.message);
					$('#added').modal('show');
					setTimeout(function () {
						$("#added").fadeOut("slow", function () {
							$('#added').modal('hide');
						});
					}, 1000);
				}else{
					//alert('fail');
					//toastr.warning(response.message);
				}

			}, error: function (e) {
				//toastr.warning(e.message);
			}
		});
	}
	
	var id = 0;
	var agent_name = ''
	//let inputs = document.querySelectorAll('input');
	$(document).on("click", ".show_edit_agent_name_modal", function(){
		inputs.forEach(input => input.value = '');
		id = $(this).data("id");
		agent_name = $(this).data("agent_name");
		if (agent_name != 'Commission Earner') {
			$('#agent_name').val(agent_name);
		  }
		$('#editAgentName').modal('show');
		$('#editAgentName').on('hidden.bs.modal', function () {
		  var new_agent_name = $('#agent_name').val();
		  if (new_agent_name != '') {
			if (agent_name != new_agent_name) {
			   updateAgentName(id, new_agent_name);
			}
		  }else{
			new_agent_name = 'Commission Earner';
			updateAgentName(id, new_agent_name);
		  }
		})

		$('#agent_name').keypress(function(event){
			var keycode = (event.keyCode ? event.keyCode : event.which);
			if(keycode == '13'){
				event.preventDefault()
				var new_agent_name = $('#agent_name').val();
				  if (new_agent_name != '') {
					  if (agent_name != new_agent_name) {
						   updateAgentName(id, new_agent_name);
						   $('#editAgentName').modal('hide');
						}
				  }else{
					new_agent_name = 'Commission Earner';
					updateAgentName(id, new_agent_name);
					$('#editAgentName').modal('hide');
				  } 
			}
		});
	});
	

	$(document).on("click", '#companyStatement', function(){

			function loadCompanyStatement(route) {
				$.ajax({
					url: route,
					type: 'GET',
					'headers': {
						'X-CSRF-TOKEN': '{{ csrf_token() }}'
					},
					dataType: "html",
					success: function (response) {
						window.open(route);
					},
					error: function (e) {
						console.log('error', e);
					}
				});
			}
			//$('#company').html(comp);

			//var company = $(this).data("company_name");
			//var url = '{//{ route("agent", ":CompanyName") }}';

			//url = url.replace(':CompanyName', company);

			var statement_url = '{{ route("company-statement") }}';
			
			loadCompanyStatement(statement_url);	
		});




	 $(document).on("click", '.personalStatement', function(){

			function loadPersonalStatement(route) {
				$.ajax({
					url: route,
					type: 'GET',
					'headers': {
						'X-CSRF-TOKEN': '{{ csrf_token() }}'
					},
					dataType: "html",
					success: function (response) {
						window.open(route);
					},
					error: function (e) {
						console.log('error', e);
					}
				});
			}
			//$('#company').html(comp);

			//var company = $(this).data("company_name");
			//var url = '{//{ route("agent", ":CompanyName") }}';

			//url = url.replace(':CompanyName', company);

			var statement_url = '{{ route("personal-statement") }}';
			
			loadPersonalStatement(statement_url);	
		});  

</script>

<!---button permission---->

@endsection
