<style>
	.vamiddle {
		vertical-align:middle !important;
	}
</style>
	<div id="landing-content" style="width: 100%">
    <div class="clearfix"></div>
	<div class="row py-2" style="height:80px;display:flex">
	<div class="col align-self-center" style="width:80%">
		<h2>Commission Scheme Management</h2>
	</div>

	<div class="modal fade" id="added" tabindex="-1" role="dialog"
		aria-labelledby="showMsgModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered  mw-75 w-50"
			role="document">
            <div class="modal-content modal-inside bg-greenlobster">
                <div class="modal-header" style="border:0">&nbsp;</div>
                <div class="modal-body text-center">
                    <h5 class="modal-title text-white"
                    id="statusModalLabel">Commission added successfully</h5>
                </div>
                <div class="modal-footer" style="border:0">&nbsp;</div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="deleted" tabindex="-1" role="dialog"
		aria-labelledby="showMsgModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered  mw-75 w-50"
			role="document">
            <div class="modal-content modal-inside bg-greenlobster">
                <div class="modal-header" style="border:0">&nbsp;</div>
                <div class="modal-body text-center">
	                    <h5 class="modal-title text-white"
	                    id="delete_statusModalLabel">Deleted successfully</h5>
                </div>
                <div class="modal-footer" style="border:0">&nbsp;</div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="no_schemes" tabindex="-1" role="dialog"
		aria-labelledby="showMsgModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered  mw-75 w-50"
			role="document">
            <div class="modal-content modal-inside bg-greenlobster">
                <div class="modal-header" style="border:0">&nbsp;</div>
                <div class="modal-body text-center">
	                    <h5 class="modal-title text-white"
	                    >No scheme found</h5>
                </div>
                <div class="modal-footer" style="border:0">&nbsp;</div>
            </div>
        </div>
    </div>


	<div class="modal fade" id="showMsgModal" tabindex="-1" role="dialog"
		aria-labelledby="showMsgModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered  mw-75 w-50"
			role="document">
            <div class="modal-content modal-inside bg-greenlobster">
                <div style="border:0" class="modal-header"></div>
                <div class="modal-body text-center">
                    <h5 class="modal-title text-white"
                    id="statusModalLabel">Do you want to permanently delete this commission scheme?</h5>
                </div>
                <div class="modal-footer"
                    style="border-top:0 none; padding-left: 0px; padding-right: 0px;">
                    <div class="row"
                        style="width: 100%; padding-left: 0px; padding-right: 0px;">
                        <div class="col col-m-12 text-center">
                        	<input type="hidden" name="delete_id" id="delete_id" value="">
                            <button type="button"
                            class="btn bg-primary primary-button"
                            data-dismiss="modal" id="delete_confirm_button" style=":hover{color: #ffffff}">Yes</button>
                            <button type="button"
                            class="btn btn-danger primary-button"
                            data-dismiss="modal">No</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="type_modal"  tabindex="-1"
		role="dialog" aria-labelledby=""
		aria-hidden="true" style="text-align: center;">
		<div class="modal-dialog modal-dialog-centered  mw-75 w-50"
			role="document" style="display: inline-flex;">

			<div class="modal-content bg-greenlobster">
			<div class="modal-header">
				<h3 style="margin-bottom:0" class="modal-title">Type</h3>
			</div>
          	
            <div class="modal-body text-left">
	          	<h5 id="scheme1" class="pl-1">
					<span class="type_change" data-type="staff"
					style="cursor:pointer">
					Staff
					</span>
				</h5>
	          	<h5 id="scheme2" class="pl-1">
					<span class="type_change" data-type="agent"
					style="cursor:pointer">
					Agent
					</span>
				</h5>
	          	<h5 id="scheme3" class="pl-1">
					<span class="type_change" data-type="partner"
					style="cursor:pointer">
					Partner
					</span>
				</h5>
	        </div>
          </div>
        </div>
    </div>


    <div class="modal fade" id="scheme_modal" tabindex="-1">
        <div class="modal-dialog modal-dialog modal-dialog-centered  mw-75 w-50"
			role="document">
          <div class="modal-content bg-greenlobster">
          	<div class="modal-header">
	         <h3 style="margin-bottom:0" class="modal-title">Scheme</h3>
	       </div>
          	
            <div class="modal-body" id="scheme_names">
	          	
	        </div>
          </div>
        </div>
    </div>

    <div class="modal fade" id="commission_scheme_modal" tabindex="-1">
        <div class="modal-dialog modal-dialog modal-dialog-centered  mw-75 w-50"
			role="document">
          <div class="modal-content bg-greenlobster ">
          	<div class="modal-header">
	         <h5 class="modal-title">Commission Scheme Definition</h5>
	       </div>
          	
            <div class="modal-body">
		          <div class="form-group row">
				    <label for="inputCompany" class="col-sm-2 col-form-label">Company</label>
				    <div class="col-sm-10">
				      <input type="number" class="form-control company_pct" onfocus="this.value=''" value="" id="inputCompany" style="width: 18%; display:inline-block;" maxlength="2">
				      <div style="display:inline-block;">%</div>
				    </div>
				  </div>
	          	  <div class="form-group row">
				    <label for="inputPool" class="col-sm-2 col-form-label">Pool</label>
				    <div class="col-sm-10">
				      <input type="number" class="form-control pool_pct" onfocus="this.value=''"  value="" id="inputPool" style="width: 18%; display:inline-block;">
				      <div style="display:inline-block;">%</div>
				    </div>
				  </div>
				  <div class="form-group row">
				    <label for="inputAgent" class="col-sm-2 col-form-label">Agent</label>
				    <div class="col-sm-10">
				      <input type="number" class="form-control agent_pct" onfocus="this.value=''" value="" id="inputAgent" style="width: 18%; display:inline-block;">
				      <div style="display:inline-block;">%</div>
				    </div>
				  </div>
	        </div>
          </div>
        </div>
    </div>

    <div class="modal fade" id="editCompany" tabindex="-1" role="dialog"
		aria-labelledby="branch_nameModallabel" aria-hidden="true">
	    <div class="modal-dialog modal-dialog-centered" role="document">
			<div class="modal-content">
			<form action="#"  method="post" enctype="multipart/form-data"
				autocomplete="off" id="updateProspectFields">
				<div class="modal-body">
					<div style="margin-bottom:0" class="form-group">
						<input type="text" class="form-control"
							id="company_name" placeholder="Company Name" name="company"
							value="" autocomplete="off">
					</div>
				</div>
				<input type="hidden" name="location_id" value="">
			</form>
	        </div>
	    </div>
	</div>
 
    <div class="modal fade" id="repairAmountModal" style="margin-top:16%;">
        <div class="modal-dialog">
          <div class="modal-content">
            <!-- Modal body -->
            <div class="modal-body">
                <input id="repairAmountInput" type="text" class="pl-1" style="width: 100%; border: 1px solid #ddd;">
            </div>
          </div>
        </div>
    </div>

		<div class="col col-auto align-self-center"
			style="margin-bottom:-8px;margin-top: -3px;text-align: center;">
			<button class="btn btn-success btn-log bg-virtualcabinet sellerbutton text-center"
				style=" padding:0;display:block;text-align: center;"
				id="guidbutton"
				onclick="">
				<span>Guide</span>
			</button>
			<button class="btn btn-success btn-log sellerbuttonwide text-center"
				style="margin-right:0;padding-left:0;padding-right:0;"
				id="add_company">
				<span >+Company</span>
		  </button>
		</div>
    </div>

    <table class="table table-bordered display" id="comm_table"
		style="width:100%;">
		<thead class="thead-dark">
		<tr>
			<th class="text-center" id="index_head_id" style="width:30px">No.</th>
			<th class="text-center" style="width:150px:" >Company ID</th>
			<th class="" style="">Company Name</th>
			<th class="text-center">Type</th>
			<th class="text-center" style="">Scheme</th>
			<th class="text-center" style="">Source</th>
			<th class="text-center" style="width:50px">Pool</th>
			<th class="text-center" style="width:50px">Commission</th>
			<!-- Yellow Crab: [S] -->
			<th class="text-center" style="width:30px"></th>
			<!-- Blue Crab:   [O] -->
			<th class="text-center" style="width:30px"></th>
			<!-- Red Crab:    [X] -->
			<th class="text-center" style="width:30px"></th>
			<!--<button class="btn" style="background: red; border-radius: 5px; margin: auto; width:30px; height: 30px; display: block; padding: 3px;"><i class="fas fa-times text-white"></i> </<button></button>-->
		</tr>
		</thead>
		<tbody>
          
		</tbody>
	</table>
    
</div>
<br>
<hr>
<div id='new_partner'></div>
<script>
	var validate = function(e) {
	  var t = e.value;
	  e.value = (t.indexOf(".") >= 0) ? (t.substr(0, t.indexOf(".")) + t.substr(t.indexOf("."), 3)) : t;
	}
</script>


<script>
 


 var commTable = $('#comm_table').DataTable({
	"order": [],
	"ajax": {
        "url": "{{route('landing.ajax.Scheme_management')}}",
        "type": "GET",

    },
    columns: [
        {data: 'DT_RowIndex', name: 'DT_RowIndex',
			class: 'text-center index vamiddle'},
        {data: 'systemid', name: 'systemid', class: 'text-center vamiddle'},
        {data: 'company_name', name: 'company_name', class:'vamiddle'},
        {data: 'type', name: 'type', class:'vamiddle'},
        {data: 'scheme_name', name: 'scheme_name', class:'vamiddle'},
        {data: 'source', name: 'source', class:'vamiddle'},
        {data: 'pool_amt', name: 'pool_amt', class: 'text-center vamiddle'},
        {data: 'commission_amt', name: 'commission_amt',
			class: 'text-center vamiddle'},
        {data: 'button1', name: 'button1', class: 'text-center vamiddle'},
        {data: 'button2', name: 'button2', class: 'text-center vamiddle'},
        {data: 'button3', name: 'button3', class: 'text-center vamiddle'},
    ],
    "autoWidth" : true,
    columnDefs: [ {
            "class": "index",
            targets: 0
        } ],

	columnDefs: [
	   { orderable: false, targets: -3},
	   { orderable: false, targets: -2},
	   { orderable: false, targets: -1}
	]
   
});





commTable.draw();

$(".dataTables_scrollHeadInner").css({"width":"100%"});
$(".table ").css({"width":"100%"});



     /*if ($('.show_agent').prop('disabled')) {
		  $(this).css('cursor', 'not-allowed');
	  }*/

	/*
    $('.index').css('padding-top', '15px');
	*/

      function newComm() {
		$.ajax({
			url: "{{route('landing.ajax.index')}}",
			type: "POST",
			enctype: 'multipart/form-data',
			processData: false,
			contentType: false,
			cache: false,
			data: '',
			success: function (response) {
				if (response.success) {
					commTable.ajax.reload();
					$("#statusModalLabel").html(response.message);
                    $('#added').modal('show');
					setTimeout(function () {
						$("#added").fadeOut("slow", function () {
							$('#added').modal('hide');
						});
					}, 1000);

				}else{
					//toastr.warning(response.message);
				}

			}, error: function (e) {
				//toastr.warning(e.message);
			}
		});
	}

	$(document).on("click", '#add_company', function(){
		newComm();
	});


	function showSchemeNames(){
	   	//Populate Scheme Names
	    $.ajax({
			url: "{{route('landing.ajax.get-scheme')}}",
			type: 'GET',
			'headers': {
	            'X-CSRF-TOKEN': '{{ csrf_token() }}'
	        },
			success: function (response) {
			    var scheme_names = response;
                //alert(JSON.stringify(scheme_names));
			    if (scheme_names.length > 0) {
			    	for (var i = 0; i < scheme_names.length; i++) {
						$('#scheme_names').append('<h5 id="scheme1" class="pl-1"><span class="scheme_change" data-scheme_name="'+scheme_names[i]['name']+'" style="cursor:pointer">'+scheme_names[i]['name']+'</span></h5>');
					}
			        
					function updateSchemeName(comm_scheme_id,comm_scheme_name){
					    $.ajax({
							url: "{{route('landing.ajax.Update_scheme_name')}}",
							type: "POST",
							cache: false,
							data: {
								'id': comm_scheme_id,
								'comm_scheme_name': comm_scheme_name
							},
							success: function (response) {
								if (response.success) {
									commTable.ajax.reload();
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

                    var scheme_id  = 0;
				    $(document).on("click", '.show_edit_scheme_modal', function(e){
				       $('#scheme_modal').modal('show');
				       scheme_id = $(this).data("scheme_id");
				       $(document).on("click", '.scheme_change', function(){
					      var scheme_name = $(this).data("scheme_name");
					      updateSchemeName(scheme_id, scheme_name);
					      //$( ".scheme_change").unbind();
					      $('#scheme_modal').modal('hide');
					    });
				    });
 
				}else{
                    $(document).on("click", '.show_edit_scheme_modal', function(e){
				    	$('#no_schemes').modal('show');
				    	setTimeout(function () {
				           $("#no_schemes").fadeOut("slow", function () {
		                       $('#no_schemes').modal('hide');
				              });
				          }, 1000);
			        });
			    }  
			},
		});
	}
    
    showSchemeNames();
   
    function updateType(comm_id,type){
     $.ajax({
			url: "{{route('landing.ajax.Update_type')}}",
			type: "POST",
			cache: true,
			data: {
					'id': comm_id,
					'type': type
				},
			success: function (response) {
				if (response.success) {

					//commTable.ajax.reload();
					//toastr.success(response.message);
					alert(response.message);
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
    var comm_id = 0;

    $(document).on("click", '.show_edit_type_modal', function(){
       comm_id = $(this).data("id");
       $(document).on("click", '.type_change', function(){
	      var comm_type = $(this).data("type");
	      var typeAjax = $.ajax({
				url: "{{route('landing.ajax.Update_type')}}",
				type: "POST",
				cache: false,
				'headers': {
	                'X-CSRF-TOKEN': '{{ csrf_token() }}'
	            },
				data: {
					'id': comm_id,
					'type': comm_type
				},
				success: function (response) {
					if (response.success) {

						commTable.ajax.reload();
						//toastr.success(response.message);
						//alert(response.message);
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

	       
	      //updateType(comm_id, comm_type);
	      $('#type_modal').modal('hide');
	    });
    });

    $(document).on("click", '.show_agent', function(){
    	
    	function loadView(route) {
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

        var company = $(this).data("company_name");
		var url = '{{ route("agent", ":CompanyName") }}';

		url = url.replace(':CompanyName', company);
           loadView(url);	
     });
    

    function deleteComm(d_id){
     $.ajax({
			url: "{{route('landing.ajax.destroy')}}",
			type: "POST",
			cache: false,
			data: {
				'id': d_id
			},
			success: function (response) {
				if (response.success) {

					commTable.ajax.reload();

					$("#delete_statusModalLabel").html(response.message);
                    $('#deleted').modal('show');
					setTimeout(function () {
						$("#deleted").fadeOut("slow", function () {
							$('#deleted').modal('hide');
						});
					}, 1000);
					//toastr.success(response.message);
				}else{
					//alert('fail');
					//toastr.warning(response.message);
				}

			}, error: function (e) {
				//toastr.warning(e.message);
			}
		});
	}
   
    
    $('#delete_confirm_button').hover(function(){
	  $(this).css("color", "white");
	 });
    
	var d_id = 0;
	$(document).on("click", '.delete_button', function(){
    	d_id = $(this).data("id");
    	$('#showMsgModal').modal('show');
    	$(document).on("click", '#delete_confirm_button', function(){
           deleteComm(d_id);
           //itemClickEvent(commTable);
    	});
    });

    function updateComponyName(comm_id,comm_name){
     $.ajax({
			url: "{{route('landing.ajax.Update_company_name')}}",
			type: "POST",
			cache: false,
			data: {
				'id': comm_id,
				'name': comm_name
			},
			success: function (response) {
				if (response.success) {

					commTable.ajax.reload();
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
    let inputs = document.querySelectorAll('input');
    var company_name = '';
    $(document).on("click", '.show_edit_company_modal', function(){
    	inputs.forEach(input => input.value = '');
    	id = $(this).data("id");
    	  company_name = $(this).data("company_name");
    	  if (company_name != 'Company Name') {
    	  	$('#company_name').val(company_name);
    	  }
        $('#editCompany').modal('show');
       
       
        $('#editCompany').on('hidden.bs.modal', function (){
          var new_company_name = $('#company_name').val();
         if (new_company_name != '') {
          	  if (company_name != new_company_name) {
                 updateComponyName(id, new_company_name);
          	  }
          }else{
	          new_company_name = 'Company Name';
	          updateComponyName(id, new_company_name);
	        }
		});


		$('#company_name').keypress(function(event){
			var keycode = (event.keyCode ? event.keyCode : event.which);
            if(keycode == '13'){
            	event.preventDefault()
		        var new_company_name = $('#company_name').val();
		        if (new_company_name != '') {
		        	if (company_name != new_company_name) {
	                 updateComponyName(id, new_company_name);
	                 $('#editCompany').modal('hide');
	          	  }
		        }else{
                  new_company_name = 'Company Name';
                  updateComponyName(id, new_company_name);
                  $('#editCompany').modal('hide');
		        }
		    }
		});
    })


	function updateSchemeDefinition(comm_scheme_id,company, pool, agent){
	    $.ajax({
			url: "{{route('landing.ajax.Update_scheme_definition')}}",
			type: "POST",
			cache: false,
			data: {
				'comm_id': comm_scheme_id,
				'company_percentage': company,
				'pool_percentage': pool,
				'agent_percentage': agent
			},
			success: function (response) {
				if (response.success) {
				   
					commTable.ajax.reload();
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

    function getSchemeDefinition(comm_scheme_id){
	    $.ajax({
			url: "{{route('landing.ajax.Get_scheme_definition')}}",
			type: "POST",
			cache: false,
			data: {
				'id': comm_scheme_id
			},
			success: function (response) {
				if (response) {
				   
					var definition = response;
                    var c_pct = definition['company_pct'];
			        var p_pct = definition['pool_pct'];
			        var a_pct = definition['agent_pct'];
			        //console.log(JSON.stringify(definition));

			        if (c_pct > 0) {
			        	$('#inputCompany').val(c_pct);
			        }if (p_pct > 0) {
			        	$('#inputPool').val(p_pct);
			        }
			        if (a_pct > 0) {
			           $('#inputAgent').val(a_pct);
			        }
				}else{
					//alert('fail');
					//toastr.warning(response.message);
				}

			}, error: function (e) {
				//toastr.warning(e.message);
			}
		});
	}


	var scheme_id = 0;
	var last_company_pct = null;
	var last_pool_pct = null;
	var last_agent_pct = null;

	$(document).on("click", '.commission_scheme_definition',function(){
        
     	inputs.forEach(input => input.value = '');
    	scheme_id = $(this).data("scheme_id");
        getSchemeDefinition(scheme_id); 
    	 last_company_pct = $('.company_pct').val();
		 last_pool_pct = $('.pool_pct').val();
		 last_agent_pct = $('.agent_pct').val();

		
        $(document).on('hidden.bs.modal', '#commission_scheme_modal', function () {
			var company_pct = $('.company_pct').val();
			var pool_pct = $('.pool_pct').val();
			var agent_pct = $('.agent_pct').val();

			if (company_pct && pool_pct && agent_pct != '') {
				if(last_agent_pct !== agent_pct || last_pool_pct !== pool_pct || last_company_pct !== company_pct){
                   updateSchemeDefinition(scheme_id,company_pct, pool_pct, agent_pct);
				}
			}
		});
	});
</script>
