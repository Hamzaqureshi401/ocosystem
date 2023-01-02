@if($fieldName == 'staff_name' || $fieldName == 'staff_role')
    <div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="staffModallabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
           <div class="modal-content">
				@if($fieldName == 'staff_role')
				<div class="modal-header bg-dark">
				<h3 style='margin-bottom:0;color:#fff;'>Roles & Authorisation</h3>
				</div>
				@endif
 
                <form action="#" id="updateProspectFields" method="post"
                      enctype="multipart/form-data" autocomplete="off">
                    {{ csrf_field() }}
                    <div class="modal-body">

                        @if($fieldName == 'staff_name')
                         <div class="form-group">
                                <input type="text" class="form-control"
									id="staffName" placeholder="User Name" name="name"
								   value="{{$userData->name}}" autocomplete="off">
                            </div>
                            <div class="form-group">
                                <input type="email" class="form-control"
									placeholder="Email Address" name="email"
								   value="{{$userData->email}} " id="staff_email"
								   autocomplete="new-email" >
                            </div>
                           
                            <div class="form-group">
                                <input type="password" class="form-control"
									id="staff_password" placeholder="Password"
									name="password" autocomplete="new-password">
                            </div>
                            <div class="form-group">
                                <input type="password" class="form-control"
									id="verifypass" placeholder="Verify Password"
								   name="password_confirmation" autocomplete="off">
                            </div>
                            <div class="form-group">
							 <select class="form-control"  name="department">
                            <option class="form-control">Select Department</option>
                            @foreach ($department as $c)
                            <option class="form-control"
								value="{{$c->field_value}}"
								{{ $c->field_value == $userData->staff->department ? "selected":null}}>{{$c->field_value}}</option>
                            @endforeach
                           </select>
                            </div>

                            <div class="form-group">
							<select class="form-control" name='position'>
							<option class="form-control">Select Position</option>
							@foreach ($position as $c)
							<option class="form-control"
								value="{{$c->field_value}}"
								{{ ($c->field_value == $userData->staff->position ? "selected":null)}} >{{$c->field_value}}</option>
							@endforeach
							</select> 
                            </div>

                            <span style="color:red"><strong>Disclaimer:</strong> Email must be valid. Verify Password and Password need to be matched with minimum 6 characters.</span>
							@elseif($fieldName == 'staff_role')
                         
                            <div class="form-group">
								<ul id='role_list' style="list-style: none;">

                                @php 
                                    $data = \App\Models\role::where('superadmin',false)->get();
                                 @endphp                              
                                 @php

                                    $data =  $data->filter(function ($item) {
                                        return $item->name != 'sadmin';
                                    });

                                @endphp
                                @if (!$is_g_King and ($is_king == true or $is_this_user_sadmin))
                                 <li class='' onclick="sadmin()" id="sadmin" style="font-weight: 700;cursor:pointer;">Secondary Administrator</li>
                                 <hr style="border: 0.8px solid #dcdcdc;margin-right: 40px;" />
                                 @endif

                                 @if ($is_g_user_sadmin and (!($is_king == true or $is_this_user_sadmin)))
                                 <li class='' onclick="" id="sadmin" style="font-weight: 700;cursor:pointer;color:green;">Secondary Administrator</li>
                                 <hr style="border: 0.8px solid #dcdcdc;margin-right: 40px;" />
                                 @endif

                                 @if (!$is_g_user_sadmin && !$is_g_King && (!($is_king == true  or $is_this_user_sadmin)))
								<li class='' onclick="void(0)" id="sadmin" style="font-weight: 700;cursor:pointer;color:#ccc">Secondary Administrator</li>
                                 <hr style="border: 0.8px solid #dcdcdc;margin-right: 40px;" />
                                 @endif
								 	@if ($is_g_King)
                                      <li class='' id="padmin" style="font-weight: 700;cursor:pointer;color:green">Primary Administrator</li>
                                        <hr style="border: 0.8px solid #dcdcdc;margin-right: 40px;" />
                                    @endif
                               
                                    @foreach ($data as $d)
										<li class='li_role' onclick="add_role('{{$d->name}}')"
										id="{{$d->name}}">{{$d->description}}</li>
                                    @endforeach
                    
                              </ul>
                            </div>
                            <style type="text/css">
                                .role_selected {color:green;font-weight: 700}
                                #role_list > li:hover {font-weight: 700;cursor:pointer;}

								@if (($is_g_user_sadmin && (!$is_king && !$is_this_user_sadmin)) ||  $is_g_King || $is_self)
									  #role_list, .li_role,.role_list {  
									pointer-events: none;
									cursor: not-allowed;
								}
                              @endif
                            </style>
                     
                        @endif

                        <input type="hidden" name="user_id" value="{{$id}}">
                    </div>
                </form>
            </div>
        </div>
        <script type="text/javascript">
        $('#updateProspectFields').attr('autocomplete','off');

            var formChange = false;
            $( '#updateProspectFields' ).change(function() {
                if (formChange == false & $('#staff_email').val == '' & $('#staff_password').val == '') {
                reset_dilog_field();
                formChange = true;
                }
            });

        function reset_dilog_field() {
            @if ($userData->email == '')
        $('#staff_email').val('');
        $('#staff_password').val('');
        @endif
             }
        </script>
    </div>

@elseif($fieldName == 'status')
    <div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="staffNameLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered  mw-75 w-50" role="document">
            <div class="modal-content modal-inside bg-greenlobster">
                <div class="modal-header" style="border:0"></div>
                <div class="modal-body text-center">
                    <h5 class="modal-title text-white" id="statusModalLabel">Are you sure?</h5>
                </div>
                <div class="modal-footer" style="border-top:0 none; padding-left: 0px; padding-right: 0px;">
                    <div class="row" style="width: 100%; padding-left: 0px; padding-right: 0px;">
                        <div class="col col-m-12 text-center">
                            @if($userData->status == 'pending')
                                <button type="button" class="btn bg-primary primary-button" onclick="updateUserStatus({{$userData->id}}, 'active')
                                        ">Approve</button>
                                <button type="button" class="btn btn-danger primary-button" onclick="updateUserStatus({{$userData->id}}, 'inactive')">Reject</button>
                            @elseif($userData->status == 'active')
                                <button type="button" class="btn btn-danger primary-button" onclick="updateUserStatus({{$userData->id}}, 'inactive')">Deactivate</button>
                            @elseif($userData->status == 'inactive')
                                <button type="button" class="btn bg-primary primary-button" onclick="updateUserStatus({{$userData->id}}, 'active')">Activate</button>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
    <style>
        .btn {color:#fff !Important;}
    </style>
@elseif($fieldName == 'deleted')
    <div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="staffNameLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered  mw-75 w-50" role="document">
            <div class="modal-content modal-inside bg-greenlobster">
                <div class="modal-header" style="border:0"></div>
                <div class="modal-body text-center">
                    <h5 class="modal-title text-white"
					id="statusModalLabel">Do you want to permanently delete this
					user information?</h5>
                </div>
                <div class="modal-footer"
					style="border-top:0 none; padding-left: 0px; padding-right: 0px;">
                    <div class="row"
						style="width: 100%; padding-left: 0px; padding-right: 0px;">
                        <div class="col col-m-12 text-center">
                            <button type="button"
							class="btn bg-primary primary-button"
							onclick="deleteData({{$userData->id}})"
							data-dismiss="modal">Yes</button>
                            <button type="button"
							class="btn btn-danger primary-button"
							data-dismiss="modal">No</button>
                        </div>
                    </div>

                    <form id="status-form" action="{{ route('logout') }}"
						method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>
            </div>
        </div>

    </div>
    <style>
        .btn {color: #fff !Important;}
    </style>
    @elseif($fieldName == 'msg_dilog')
        <div class="modal fade" id="editUserModal"  tabindex="-1" role="dialog" aria-labelledby="staffNameLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered  mw-75 w-50" role="document">
            <div class="modal-content modal-inside bg-greenlobster" >
                <div class="modal-header" style="border:0">&nbsp;</div>
                <div class="modal-body text-center">
                    <h5 class="modal-title text-white" id="statusModalLabel">{{$msg}}</h5>
                </div>
                <div class="modal-footer" style="border-top:0 none; padding-left: 0px; padding-right: 0px;">

                    <form id="status-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
					&nbsp;
                </div>
            </div>
        </div>

    </div>
    <style>
        .btn {color: #fff !Important;}
    </style>
    <script type="text/javascript">
        
     $('#editUserModal').on('hidden.bs.modal', function (e) {
            $('.modal-backdrop').remove();
        });
        $('.modal-backdrop').click(function(){
                $('.modal-backdrop').remove();
         });
        
        setTimeout(function() {
            $("#editUserModal").modal('hide');
        },2500)
    </script>

@elseif ($fieldName == 'bluecrab')
<div class="modal fade" id="editUserModal"  tabindex="-1" role="dialog"
	aria-labelledby="staffNameLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered  mw-75 w-50"
		role="document">
		<div class="modal-content modal-inside bg-greenlobster" >
		<div class="modal-header">	
			<h3 class="modal-title text-white" id="statusModalLabel">
				Function
			</h3>
		</div>
		<div class="modal-body" >
		<ul style="list-style-type:none;padding-left:20px"
			class="mb-0" id="id01">
		@foreach ($function as $f) 
		<li>		
		<span 
			class="function_sel 
			 @if($is_g_King || $is_self ) function_active fn_disabled @endif
			{{$f->is_active == true ? 'function_active':''}} text-white"
			 f-id="{{$f->id}}" style="font-size: 19px;font-weight:500;
					cursor:pointer">{{ucfirst($f->name)}}</span>
		</li>
		@endforeach
		</div>
		</ul>
                <!--div class="modal-footer" style="border-top:0 none; padding-left: 0px; padding-right: 0px;">
                </div-->
		</div>
        </div>

    </div>
    <style>
	.btn {color: #fff !Important;}
	.function_active {color: darkgreen !important;font-weight:600}
	.function_sel:hover {color: darkgreen !important;}
	.function_sel {letter-spacing:1px;}
	
    </style>
<script type="text/javascript">
	var is_changed = false;	
	$('.function_sel').on('click', function(z) {
		target = (z.target);

		if ($(target).hasClass('fn_disabled') == true ) {
			return;
		}1

		f_id = $(target).attr('f-id');
		$.post("{{route('user.function.toggle')}}",{
			f_id:f_id, u_id:{{$userData->id}}
		}).done(function(res) {
			
			is_changed = true;	
			$(target).toggleClass('function_active');
		});
	});
        
     $('#editUserModal').on('hidden.bs.modal', function (e) {
            $('.modal-backdrop').remove();
			if (is_changed == true) {
				customMSGModal("Function updated");
			}
        });
       
     $('.modal-backdrop').click(function(){
		 $('.modal-backdrop').remove();
	 });
    </script>	    
@elseif ($fieldName == 'pinkcrab')
<div class="modal fade" id="editUserModal"  tabindex="-1" 
	role="dialog" aria-labelledby="staffNameLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered  mw- 75 w- 50" role="document">
            <div class="modal-content modal---inside bg-greenlobster" >
		<div class="modal-header" >
			<h3 class="modal-title text-white"  id="statusModalLabel">Mobile Roles</h3>
            	</div>
		<div class="modal-body">
			<!--hr style="border-bottom: 1px #fff solid;width:100%;" /-->
			<ul style="list-style-type:none;" id="id01">
			@foreach ($function as $f) 
			<li>		
				<span class="function_sel
					 @if($is_g_King || $is_self ) function_active fn_disabled @endif
					 {{$f->is_active == true ? 'function_active':''}} text-white"
			 		f-id="{{$f->id}}" style="font-size: 19px;font-weight:500;
					cursor:pointer">{{ucfirst($f->description)}}</span>
			</li>
			@endforeach
		</div>
		</ul>
                <!-- div class="modal-footer" style="border:0;"> 
                </div --->
            </div>
        </div>

    </div>
    <style>
        .btn {color: #fff !Important;}
	.function_active {color: darkgreen !important;font-weight:600}
	.function_sel:hover {color: darkgreen !important;}
	.function_sel {letter-spacing:1px;}
	
    </style>
		
	<script type="text/javascript">
	var is_changed = false;
	$('.function_sel').on('click', function(z) {
		target = (z.target);
		
		if ($(target).hasClass('fn_disabled')) {
			return;
		}

		f_id = $(target).attr('f-id');
		$.post("{{route('user.mobroles.toggle')}}",{
			f_id:f_id, u_id:{{$id}}
		}).done(function(res) {
			is_changed = true
			$(target).toggleClass('function_active');
		});
	});
        
     $('#editUserModal').on('hidden.bs.modal', function (e) {
		 $('.modal-backdrop').remove();
		 if (is_changed == true) {
			 customMSGModal("Mobile Roles updated");
		 }
        });
       
     $('.modal-backdrop').click(function(){
                $('.modal-backdrop').remove();
         });
    </script>

@elseif ($fieldName == 'location')
<div class="modal fade" id="editUserModal"  tabindex="-1" 
	role="dialog" aria-labelledby="staffNameLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered  mw- 75 w- 50" role="document">
            <div class="modal-content modal---inside bg-greenlobster" >
		<div class="modal-header" >
			<h3 class="modal-title text-white"  id="statusModalLabel">
				Terminal Location Authorization
			</h3>
		</div>
		<div class="modal-body">
			<ul style="list-style-type:none;" id="id01">
			@foreach ($branch_location as $f) 
			<li>		
			<span class="function_sel @if($is_g_King || $is_self ) function_active fn_disabled @endif
					 {{$f->is_active == true ? 'function_active':''}} text-white"
			 		f-id="{{$f->id}}" style="font-size: 19px;font-weight:500;
					cursor:pointer">{{ucfirst($f->branch)}}</span>
			</li>
			@endforeach
		</div>
		</ul>
                <!-- div class="modal-footer" style="border:0;"> 
                </div --->
            </div>
        </div>

    </div>
    <style>
        .btn {color: #fff !Important;}
		.function_active {color: darkgreen !important;font-weight:600}
		.function_sel:hover {color: darkgreen !important;}
		.function_sel {letter-spacing:1px;}
    </style>
		<script type="text/javascript">
	var is_chaged = false;
	$('.function_sel').on('click', function(z) {
		target = (z.target);
		if ($(target).hasClass('fn_disabled')) {
				return
		}

		f_id = $(target).attr('f-id');
		$.post("{{route('user.location.auth')}}",{
			f_id:f_id, u_id:{{$id}}
		}).done(function(res) {
			$(target).toggleClass('function_active');
			is_chaged = true;
		});
	});
        
	$('#editUserModal').on('hidden.bs.modal', function (e) {
		$('.modal-backdrop').remove();
		if (is_chaged == true) {
			customMSGModal("Terminal Location Authorization updated");
		}
	});
       
	$('.modal-backdrop').click(function(){
		$('.modal-backdrop').remove();
	});
    </script>
@endif

@if($fieldName == 'staff_id' || $fieldName == 'staff_name')
    <script>
        $('#editUserModal').submit(function (e) {
            e.preventDefault();
            $("#editUserModal").modal('hide');
        });

        $('#editUserModal').on('hidden.bs.modal', function (e) {
            updateUser();
        });

        var $form = $('form'),
        origForm = $form.serialize();

        function updateUser() {
           const form = $('#updateProspectFields')[0];
            var email = $('input[name=email]').val();

          
            const formData = new FormData(form);
            $.ajax({
                url: "{{route('user.edit.update')}}",
                type: "POST",
                enctype: 'multipart/form-data',
                processData: false,  // Important!
                contentType: false,
                cache: false,
                data: formData,
                success: function (response) {

                    staffTable.ajax.reload();
                    $("#editUserModal").modal('hide');
                    $("#showEditUserModal").html(response);
                    $("#editUserModal").modal('show');

                }, error: function (e) {
                    console.log(e.message)
                }
            });
        }

    </script>

@elseif($fieldName == 'status')

    <script>

        function updateUserStatus(id, status) {

            $.ajax({
                url: "{{route('user.edit.status.update')}}",
                type: "POST",
                enctype: 'multipart/form-data',
                data: {
                    'id': id,
                    'status': status
                },
                success: function (response) {
                    staffTable.ajax.reload();
                    $("#editUserModal").modal('hide');
                    $("#showEditUserModal").html(response);
                    $("#editUserModal").modal('show');

                }, error: function (e) {
                    console.log(e.message)
                }
            });
        }

    </script>
@elseif($fieldName == 'staff_role')
           <script type="text/javascript">
                $('#editUserModal').on('hidden.bs.modal', function (e) {
                    updateRoles();
                });
            var roles = [];
                function add_role(role) {

                $("#"+role).toggleClass("role_selected");
                toggleRole(roles, role);
                }

                function toggleRole(array, value) {
                    var index = array.indexOf(value);

                    if (index === -1) {
                        array.push(value);
                    } else {
                        array.splice(index, 1);
                    }
                }

                function updateRoles() {
                    if (arraysEqual(preRole,roles) == true) {return null}
					$.ajax({
						url: "{{route('user.edit.role.update')}}",
						type: "POST",
						enctype: 'multipart/form-data',
						data: {
							'id': {{$userData->id}},
							'roles': roles
						},
						success: function (response) {
							staffTable.ajax.reload();
							$("#editUserModal").modal('hide');
							$("#showEditUserModal").html(response);
							$("#editUserModal").modal('show');
						}, error: function (e) {
							console.log(e.message)
						}
					});
                }

              @if ($is_g_King != true)
                @foreach ($role as $v)
                @if ($v->role_name->name == 'sadmin')
                    toggleRole(roles, 'sadmin');
                    $("#sadmin").toggleClass("role_selected");
                @else
                    add_role('{{$v->role_name->name}}')
                @endif
                @endforeach
            @else 
                @foreach ($data as $v)
                 
                        add_role('{{$v->name}}')
                 
                @endforeach
            @endif

        @if ($is_king == true or $is_this_user_sadmin)
    

            function sadmin() {
                   const index = roles.indexOf('sadmin');
                    $("#sadmin").toggleClass("role_selected");
                if (index == -1) {
                    add_sadmin();
                } else {
                    remove_sadmin();
                }
            }

            function add_sadmin () {
                      roles = []
                $(".li_role").removeClass("role_selected");
                      @foreach ($data as $v)
                        add_role('{{$v->name}}')
                @endforeach
               toggleRole(roles, 'sadmin') 
            }

            function remove_sadmin() {
                roles = []
                 $(".li_role").removeClass("role_selected");
            }
            @endif
function arraysEqual(a1,a2) {
    /* WARNING: arrays must not contain {objects} or behavior may be undefined */
    return JSON.stringify(a1)==JSON.stringify(a2);
}
            var preRole = roles.slice(0);
          
            </script>
@elseif($fieldName == 'deleted')
    <script>

        function deleteData(id) {

            const url = "{{ route('user.destroy', ['model_id' => "MODEL_ID"]) }}".replace("MODEL_ID", id);

            $.ajax({
                url: url,
                method: "DELETE",
                enctype: 'multipart/form-data',
                success: function (response) {
                    staffTable.ajax.reload();
                    $("#editUserModal").modal('hide');
                    $("#showEditUserModal").html(response);
                    $("#editUserModal").modal('show');
                }, error: function (e) {
                    console.log(e.message)
                }
            });
        }

		$('#editUserModal').on('hidden.bs.modal', function (e) {
			$('.modal-backdrop').remove();
		});
    </script>
@endif
