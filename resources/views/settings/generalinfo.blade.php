<div class="modal fade" id="MsgModal"
	tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered  mw-75 w-50" role="document">
        <div class="modal-content modal-inside bg-greenlobster">
            <div class="modal-header" style="border:0">&nbsp;</div>
            <div class="modal-body text-center">
                <h5 class="modal-title text-white" style="mb-0"
                    id="Msg">
				</h5>
            </div>
            <div class="modal-footer" style="border:0">&nbsp;</div>
        </div>
    </div>
</div>

<div class="top_row"
	style='margin-top:0px;margin-left: 16px;margin-right: 16px;'>
	<div class="row" style="width: 100%;border-bottom:1px solid #e0e0e0;margin-left: 4px ">
    <div class="col-md-11 align-self-end"  style="padding:0; color:#27a98a;font-weight:bold;">
           <h4 style="margin-bottom: 5px;">Company Details</h4>
    </div>
    <div class="col-md-1 col-auto align-self-center text-right">
        <button class="btn editbtn mb-2" style=""
			id="editSaveToggle">Edit</button>
		<!--
        <button class="btn btn-primary btn-lg"
			style="width:120px;float:right;margin-bottom:5px;"
			id="editSaveToggle">Edit</button>
		-->
    </div>
</div>

<div class="clearfix"></div>
	<div id='detailsData' style="padding-top: 10px;">
	<form id='updateProspectFields' action="#" onsubmit="return false" >
	@csrf
		<!--my html -->
		<div class="row" style="margin-bottom: -10px;">
		    <label  class="col-sm-2 col-form-label">Company Name</label>
		    <div class="col-sm-4">
		      <input class="form-control" placeholder="Company Name" name="company_name" value="{{$this_company->name}}"  required />
		    </div>

		    <label   class="col-sm-2 col-form-label">Business Reg. No</label>
		    <div class="col-sm-2" style="padding-right: 5px;">
		     	<input class="form-control" placeholder="Business Reg. No." name="reg_no" value="{{$this_company->business_reg_no}}" required />
		    </div>
		    <div class="col-sm-2">
		    	<p class="float-right"  onclick="upload_NRIC();">
		    		@if ($attachment->count() > 0)
	        		<i class="fas fa-camera green" id="cam_icon_nric" style="color:#aaa;font-size: 40px; margin-right: 12px;cursor: pointer; "></i>
	        		@else 
				<i class="fas fa-camera" id="cam_icon_nric" style="color:#aaa;font-size: 40px; margin-right: 12px;cursor: pointer; "></i>
	        		@endif
	        	</p>
		    </div>
		</div>


		<!--my html -->
		<div class="row" style="margin-bottom: 10px;">
		    <label  class="col-sm-2 col-form-label">GST/SST/VAT No</label>
		    <div class="col-sm-4">
		      <input class="form-control" placeholder="GST/SST/VAT No" name="gst_vat_sst" value="{{$this_company->gst_vat_sst}}"  required />
		    </div>

		    <label   class="col-sm-2 col-form-label"></label>
		    <div class="col-sm-2" style="padding-right: 5px;">

		    </div>
		</div>


		<!--2-->
		<div id='director'>
		
		@if ($director->count() < 1)
			<div class="row row_mb align-items-center" id='ddiv_1' >
			    <label   class="col-sm-2 col-form-label">Director</label>
			    <div class="col-sm-4">
			     	<input class="form-control" placeholder="Director"
						name="director[]" value="" required />
			    </div>

			    <label  class="col-sm-2 col-form-label">NRIC</label>
			    <div class="col-sm-2" style="padding-right: 5px;">
			     	<input class="form-control input-30" placeholder="NRIC"
						name="nric[]" value="" required />
			    </div>
			    <div class="col-sm-2" style="padding: unset; ">
					<div class="greencrab_custom align-items-center"
						onclick="add_director()">
						<img class="" src="/images/greencrab_50x50.png"
						style="width:25px;height:25px;cursor:pointer;
							float: left;"/>
					</div>

				<!--
					<p class="greencrab_custom text-danger bg-greencrab1"
						style="float: left;"  onclick="add_director()">
						<i class="fas fa-plus text-white bg-greencrab2"
							style="padding-left: 5px;"></i> 
					</p>
				-->
			    </div>
			</div>
		@endif

		<!----Director ---->
		@foreach ($director as $d)
		<div class="row row_mb align-items-center"  id='ddiv_{{$loop->index}}'>
			 <label   class="col-sm-2 col-form-label">Director</label>
			    <div class="col-sm-4">
			     	<input class="form-control" placeholder="Director"  name="director[]" value="{{$d->name}}" required />
			    </div>

			    <label  class="col-sm-2 col-form-label">NRIC</label>
			    <div class="col-sm-2" style="padding-right: 5px;">
			     	<input class="form-control input-30" placeholder="NRIC" name="nric[]" value="{{$d->nric}}" required />
			    </div>


				@if ($loop->index == 0)
			    <div class="col-sm-2" style="padding: unset; ">
					<div class="greencrab_custom align-items-center"
						onclick="add_director()">
						<img class="" src="/images/greencrab_50x50.png"
						style="width:25px;height:25px;cursor:pointer;
							float: left;"/>
					</div>
				</div>
				<!--
				<div class="col-sm-2"
					style="padding: unset;margin-top: 5px;margin-left: -7px;
						padding-left: 7px;">
					<p class="greencrab_custom" onclick="add_director()">
					<i class="fas fa-plus text-white"></i></p>
				</div>
				-->

				@else

			    <div class="col-sm-2" style="padding: unset; ">
					<div class="redcrab_custom align-items-center"
						onclick="remove_director('{{$loop->index}}')">
						<img class="" src="/images/redcrab_50x50.png"
						style="width:25px;height:25px;cursor:pointer;
							float: left;"/>
					</div>
				</div>

				<!--
				<div class="col-sm-2"
					style="padding: unset;margin-top: 5px;margin-left: -7px;
						padding-left: 7px;">
					<p class='text-danger redcrab_custom'
						onclick="remove_director('{{$loop->index}}')">
					<i class="fas fa-times text-white"></i></p>
				</div>
				-->
				@endif
		</div>
		@endforeach
		</div>

		<div class="row row_mb">
			<label  class="col-sm-2 col-form-label">Company Address</label>
		    <div class="col-sm-8" style="padding-right: 5px;">
		    <input class="form-control"  placeholder="Company Address"
				name="address" value="{{$this_company->office_address}}"
				required />
		    </div>
		</div>

		<div id='contacts'>
		@if ($contacts->count() < 1)
		<div class="row row_mb">
		    <label  class="col-sm-2 col-form-label">Contact Person</label>
		    <div class="col-sm-4">
		      <input class="form-control" placeholder="Name"  name="contact_name[]" value="">
		    </div>

		    <label class="col-sm-2 col-form-label">Mobile</label>
		    <div class="col-sm-2" style="padding-right:5px">
		     	<input class="form-control input-30" placeholder="Mobile"
					name="contact_mobile[]" value="" />
		    </div>

			<div class="col-sm-2" style="padding: unset; ">
				<div class="greencrab_custom align-items-center"
					onclick="add_person()">
					<img class="" src="/images/greencrab_50x50.png"
					style="width:25px;height:25px;cursor:pointer;
						float: left;"/>
				</div>
			</div>

			<!--
		    <div class="col-sm-2" style="padding: unset;">
		    	<p class="greencrab_custom text-danger bg-greencrab1"
					style="float: left;" onclick="add_person()">
	    			<i class="fas fa-plus text-white bg-greencrab2"
					style="padding-left: 5px;"></i> 
	    		</p>
			</div>
			-->
		</div>
		@endif

		@foreach ($contacts as $c)
		<div class="row row_mb align-items-center" id='cdiv_{{$loop->index}}'>
		    <label for="staticEmail" class="col-sm-2 col-form-label">Contact Person</label>
		    <div class="col-sm-4">
		     <input class="form-control" placeholder="Name"  name="contact_name[]" value="{{$c->name}}">
		    </div>

		    <label for="staticEmail" class="col-sm-2 col-form-label">Mobile</label>
		    <div class="col-sm-2" style="padding-right: 5px;">
		     	<input class="form-control input-30" placeholder="Mobile" name="contact_mobile[]" value="{{$c->mobile_no}}" />
		    </div>
		    <div class="col-sm-2" style="padding: unset;">
				@if ($loop->index == 0)

				<div class="greencrab_custom align-items-center"
					onclick="add_person()">
					<img class="" src="/images/greencrab_50x50.png"
					style="width:25px;height:25px;cursor:pointer;
						float: left;"/>
				</div>
		 
			 	@else

				<div class="redcrab_custom align-items-center"
					data-field="deleted"
					onclick="remove_person('{{$loop->index}}')">
					<img class="" src="/images/redcrab_50x50.png"
					style="width:25px;height:25px;cursor:pointer;
						float: left;"/>
				</div>
	
				<!--
			 	<p data-field="deleted"
					class="redcrab_custom text-danger bg-redcrab1"
					style="float: left;"
					onclick="remove_person('{{$loop->index}}')">
					<i class="fas fa-times text-white bg-redcrab2"
					style="padding-left: 7px;">
					</i>
				</p>
				-->
				@endif
		    </div>
		</div>
		@endforeach
		</div>	  
		<!--end of my html -->

 
<!---- END Director ---->
	</form>
</div>


<script type="text/javascript">
    function hide_Msg() {
        $("#MsgModal").modal('hide');
        $('.modal-backdrop').remove();
    }
	$('#updateProspectFields :input').prop("disabled", true);
	$('.greencrab_custom').addClass('displayNone');
	$('.redcrab_custom').addClass('displayNone');
	
	$('#editSaveToggle').click(function(){
		let event_name = $('#editSaveToggle').html()	

		if (event_name == 'Save') {
			const form = $('#updateProspectFields')[0];
			const formData = new FormData(form);
			$.ajax({
				url: "{{route('companydetails.saveForm')}}",
				type: "POST",
				enctype: 'multipart/form-data',
				processData: false,  // Important!
				contentType: false,
				cache: false,
				data: formData,
				success: function (response) {
					$('#updateProspectFields :input').prop("disabled", true);
					$('.greencrab_custom').addClass('displayNone');
					$('.redcrab_custom').addClass('displayNone');
					$('#editSaveToggle').html('Edit');
					$("#MsgModal").modal('show');
					$("#Msg").html('Company details updated');
					setTimeout(hide_Msg, 3000);
				}, error: function (e) {
					console.log(e.message)
				}
			});

		} else if (event_name == 'Edit') {
			$('#updateProspectFields :input').prop("disabled", false);
			$('.greencrab_custom').removeClass('displayNone');
			$('.redcrab_custom').removeClass('displayNone');
			$('#editSaveToggle').html('Save')
		}
	});

	function add_person() {
		let div_no = eval($("#contacts > div").length + 1);

		$("#contacts").append('<div class="row row_mb align-items-center"  id="cdiv_'+div_no+'" ><label   class="col-sm-2 col-form-label">Contact Person</label><div class="col-sm-4"><input class="form-control" placeholder="Name"  name="contact_name[]" value=""></div><label class="col-sm-2 col-form-label">Mobile</label><div class="col-sm-2" style="padding-right: 5px;"><input class="form-control input-30" placeholder="Mobile" name="contact_mobile[]" value="" /></div> <div class="col-sm-2" style="padding: unset;"><div class="col-sm-2" style="padding: unset; "> <div class="redcrab_custom align-items-center" data-field="deleted" onclick="remove_person('+div_no+')"> <img class="" src="/images/redcrab_50x50.png" style="width:25px;height:25px;cursor:pointer; float: left;"/> </div> </div> </div> </div>');
	}
 

	function remove_person(e) {
		$('#cdiv_'+e).remove();
	}

	function add_director() {
		let div_no = eval($("#director > div").length + 1);

		$('#director').append('<div class="row row_mb align-items-center" id="ddiv_'+div_no+'"><label class="col-sm-2 col-form-label">Director</label><div class="col-sm-4"><input class="form-control" placeholder="Director"  name="director[]" value="" required /></div><label class="col-sm-2 col-form-label">NRIC</label><div class="col-sm-2" style="padding-right: 5px;"><input class="form-control input-30" placeholder="NRIC" name="nric[]" value="" required /></div><div class="col-sm-2" style="padding: unset;"> <div class="redcrab_custom align-items-center" data-field="deleted" onclick="remove_director('+div_no+')"> <img class="" src="/images/redcrab_50x50.png" style="width:25px;height:25px;cursor:pointer; float: left;"/> </div> </div></div>')

	}

	function remove_director(e) {
		$('#ddiv_'+e).remove();
	}	

</script>
<style type="text/css">
.bg-greencrab1, .bg-redcrab1{margin-top:6px;}
.row_mb{margin-bottom: 8px;}

.display_flex {display: inline-flex !important;flex-direction: column;}
.main_flex_block {display: flex;}
.displayNone {display: none;}

@media only screen and (max-width: 650px) {
	.main_flex_block {display: block;}
	.display_flex {
		display: unset !important;
		flex-direction: unset;
		margin-top: unset !important;
		margin-right: unset !important;
	}
	.display_flex > label {padding-left: unset;}
	.display_flex > p {margin-top: 10px;margin-bottom: unset }
	.top_row {margin-top: 15px !important}
}
</style>

<div class="modal fade" id="upload_NRIC" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg  mw-75 w-50" role="document">
        <div class="modal-content modal-inside" style="background-color: rgba(26, 188, 156, 0.7);">
            <div class="modal-body text-center">
                <h5 class="modal-title text-white" id="statusModalLabel"></h5>

                <div class="row upload">

                    @foreach ($attachment as $a)
                    <div class="col-md-3" id="upload_NRIC{{$a->id}}"
						style='height:200px;background: #343a40;display:block'>

                        @if (in_array(pathinfo($a->filename, PATHINFO_EXTENSION),
							array('jpg','JPG','png','PNG', 'jpeg','JPEG' ,'gif','GIF' ,'bmp','BMP', 'tiff','TIFF') ))

                        <div class="thmb" style="object-fit:contains;">
                            <a target="_blank" style="color:#fff"
							href='{{"/company/$a->company_id/attachment/$a->id/$a->filename"}}'>
							<img src="/company/{{$a->company_id}}/attachment/{{$a->id}}/{{$a->filename}}"
							style="object-fit:contain;height: 200px;width:100%;"></a> @else
                            <div class="thmb" style="padding-top: 35px">
                                <i class="fa fa-file text-white"
									style="color:#fff;font-size: 60px"></i>
                                <br/>
                                <a target="_blank" style="color:#fff"
								href='{{"/company/$a->company_id/attachment/$a->id/$a->filename"}}'>{{$a->name}}</a> @endif
                            </div>
                            
                            <!--here we go-->
                            <button class="redCrabShell"
								style="position: absolute;bottom: 20px;right: 63px;padding-bottom: 24px;padding-left: 5px;border: none;"
								onclick="del_NRIC_f({{$a->id}})">
								<i class="fa fa-times redCrab" style="padding: 0px;">
								</i>
							</button>

                            <button class="btn btn-sm  btn-add add"
								onclick="upload_NRIC_f({{$a->id}})"
								style="position: absolute;bottom: 10px;right: 10px;">
								<i class="fa fa-camera green" style="font-size: 40px">
								</i>
							</button>
                        </div>

						@if ($loop->index % 3 == 2)
					</div>
					<div class="row upload">
                        @endif @endforeach @for ($i = $attachment->count(); $i
                        < 6; $i++) 
                       

                        <div class="col-md-3" id="upload_NRIC{{$i.'new'}}" style='height:200px;background: #343a40;display:block'>
							<span class="delete{{$i.'new'}}"></span>
							<button class="btn btn-sm  btn-add"
								onclick="upload_NRIC_f('{{$i.'new'}}')"
								style="position: absolute;bottom: 10px;right: 10px;">
								<i class="fa fa-camera text-white"
								style="color:#fff;font-size: 40px">
								</i>
							</button>
						</div>
					@if ($i % 3 == 2)
                </div>
                <div class="row upload">
                    @endif @endfor
                </div>

                <input type="file" name="file2" id="file2" class="hidden" style="display: none;" />
                <input type="hidden" name="blockNo" id="blockNo" value="null" />

                <script type="text/javascript">
                	function upload_NRIC(){
						$('#upload_NRIC').modal('show');
					}
                    function upload_NRIC_f(id) {
                        $("#blockNo").val(id);
                        $("#file2").click();
                    }

					async function del_NRIC_f(id) {
          
                        var fd = new FormData();
                        fd.append('blockNo', id);

                        var url = $("#upload_NRIC" + id + " > .thmb > a").attr('href');
						var filename = url.substring(url.lastIndexOf('/')+1);
						fd.append('fileName', filename);

						await   $.ajax({
                            url: '{{route('settings.delNRIC')}}',
                            type: 'post',
                            data: fd,
                            contentType: false,
                            processData: false,
                            dataType: 'json',
                            success: function(response) {

                            	console.log("Block Id = "+id)
                                $("#upload_NRIC" + id + " > .thmb").remove();
                                $("#upload_NRIC" + id + " > .redCrabShell").remove();
                                $("#delete_red" + id).remove();
                                $("#upload_NRIC" + id + " > .btn-add > i").toggleClass('green');
                                console.log(response.count)
                                if (response.count.toString() < 1) {
                                	$('#cam_icon_nric').removeClass('green');
                                }

                            }
                        });
                    }

                    // file selected
                    $("#file2").change(function() {
                    
                        var fd = new FormData();

                        var files = $('#file2')[0].files[0];

                        fd.append('file', files);

                        fd.append('blockNo', $("#blockNo").val());

                        uploadDataNRIC(fd);
                    });

                    // Sending AJAX request and upload file
                    function uploadDataNRIC(formdata) {
                    	 
                        $.ajax({
                            url: '{{route('companydetails.saveNRIC')}}',
                            type: 'post',
                            data: formdata,
                            contentType: false,
                            processData: false,
                            dataType: 'json',
                            success: function(response) {
                                addThumbnailNRIC(response);
                                $('#cam_icon_nric').addClass('green');
                                console.log(response)
                            }
                        });
                    }

                  async function addThumbnailNRIC(data) {

                        var target = $("#blockNo").val();


                        var is_fill = $("#upload_NRIC" + target + " > .thmb").length;

                        if (is_fill > 0) {
                        	await del_NRIC_f(target)	
                        }

                        console.log(is_fill)
                        console.log(target)


                        $("#blockNo").val('');

                        var len = $("#upload_NRIC" + target + " div.thmb").remove();

                        var name = data.name;
                        var size = convertSize(data.size);
                        var src = data.src;
                        var type = data.type;
                        var id = data.id;

                        // Creating an thumbnail


                        if (type === 'image') {

                        await    $("#upload_NRIC" + target).append('<div class="thmb" style="object-fit:contains;padding-top: 35px;"><a target="_blank" style="color:#fff" href="' + src + '"><img src="' + src + '" width="100%" height="78%" style="object-fit:contain;" ></a></div>');
                        } else {
                        await    $("#upload_NRIC" + target).append('<div class="thmb" style="object-fit:contains;padding-top: 35px;"><i class="fa fa-file text-white" style="color:#fff;font-size: 60px"></i><br/> <a target="_blank" style="color:#fff" href="' + src + '">' + name + '</a></div>');
                        }

                     await   $("#upload_NRIC" + target + "> button > i").addClass('green');



                     await  $("#upload_NRIC" + target).append('<button class="redCrabShell"  id="delete_red'+target+'" style="position: absolute;bottom: 20px;right: 63px;padding-bottom: 24px;padding-left: 5px;border: none;color:#fff" onclick="del_NRIC_f(\''+target+ '\')"><i class="fa fa-times redCrab" style="padding: 0px;"></i></button>');

                       // $("#upload_NRIC" + target).attr("id","upload_NRIC"+id);

                    }
                </script>
            </div>
        </div>
    </div>
</div>
<br>
<style type="text/css">
.upload>div {
	margin: auto;
	word-break: break-all;
}

.add > i {
	color: #fff;
}

.green {color:#28a745 !important;}

.editbtn:hover,.editbtn:active {
    background: transparent;
    color: #34dabb;
    border: 1px #34dabb solid;
    font-weight: bold;
}
.editbtn {
    background: transparent;
    color: #34dabb;
    border: 1px #34dabb solid;
    width:75px;
    height:40px;
}
.editbtn_activated{
    background: transparent;
    color: #34dabb;
    border: 1px #34dabb solid;
    font-weight: bold;
}
.edit_text {
	float: right;
	font-weight: normal;
	cursor: pointer;
    color: #34dabb;
}

</style>
