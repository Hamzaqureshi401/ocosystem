{{-- dd($globalAuth->get_data('UserData')) --}}
@extends('layouts.layout')
@section('content')
<div id='landing-view'>
<style>
.pl_selected{
    color: green !important;
}
.slimcell {
	width:80px;
	padding-top:2px !important;
	padding-bottom:2px !important;
}
.os-linkcolor {
	color:#007bff;
}
</style>

<div class="row py-2"
	style="padding-top:0 !important; padding-bottom:0 !important">
    
	<div class="col-md-5 align-items-center" style="display:flex;">
		<h2 class="mb-0">Matrix</h2>
	</div>
	
	<div class="col-md-5 align-items-center"
		style="text-align:left;padding-right:10px;display:flex">
		<span>
		<h4 class="mb-0">{{$category->name ?? 'Category'}}</h4>
		<h5 class="mb-0">{{$sub_category->name ?? 'Sub Category'}}</h5>
		</span>
	</div>

	<div class="col-md-2" style="display:flex;justify-content:flex-end">
		<img src="{{asset('/images/greencrab_br5.png')}}" 
			style="margin-bottom:5px;cursor:pointer" 
			onclick="new_attr()" class="sellerbutton p-0 mr-0" />
	</div>
	<input type="hidden" value="0" id="merchant_selected">	
	<input type="hidden" value="0" id="totalclicks">
</div>

<div class="py-2"
	style="padding-top:0 !important; padding-bottom:0 !important">
<table class="table table-bordered align-content-center"
	id="role_popup_table" style="">
	<thead class="thead-dark">
	<tr>
		<th style="">No</th>
		<th style="">Set</th>
		<th style="">Attribute</th>
		<th style=""></th>
	</tr>
	</thead>
	<tbody class="tablebody">
	</tbody>
</table>
</div>

<div id="showEditModal"></div>


<br><br>

<div class="modal fade" id="attrModal"  tabindex="-1" 
	role="dialog" aria-labelledby="staffNameLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-lg  mw- 75 w -50" role="document">
		<div class="modal-content modal---inside bg-greenlobster" >
			<div class="modal-header" >
				<h3 class="modal-title text-white"  id="statusModalLabel">Attribute</h3>
				<!--img id="new_attrib_btn" src="{{asset('/images/greencrab_br5.png')}}" 
					style='cursor:pointer' onclick="attribute_new()"
					class="Sellermenu" height="50"  /-->
            </div>
			<div class="modal-body">
				<table class="table table-bordered align-content-center"
					id="m_attr_popup_table" style="width:100%">
					<thead class="thead-dark">
						<tr>
							<th style="">No</th>
							<th style="">Attribute</th>
							<th style=""></th>
						</tr>
					</thead>
					<tbody class="tablebody">
					</tbody>
				</table>

			</div>
      </div>
   </div>
 </div>
 
 <div class="modal fade" id="name_change_modal" aria-modal="true" >
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <!-- Modal body -->
                <div class="modal-body">
					<input type="text" class="form-control input-30" id='matrix_name'
						placeholder="Set Name" m-id='0'
						style="margin-left: 2px;width: 100%;display: inline-flex;" />
                </div>
            </div>
        </div>
</div>

<div class="modal fade" id="confirmModal" tabindex="-1" 
		role="dialog"  
			aria-labelledby="logoutModalLabel" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered  mw-75 w-50" role="document">
        <div class="modal-content modal-inside bg-greenlobster">
            <div style="border:0" class="modal-header"></div>
            <div class="modal-body text-center">
                <h5 class="modal-title text-white" id="logoutModalLabel">
				Do you really want to delete?</h5>
            </div>
            <div class="modal-footer" style="border-top:0 none; padding-left: 0px; padding-right: 0px;">
                <div class="row" style="width: 100%; padding-left: 0px; padding-right: 0px;">
                    <div class="col col-m-12 text-center">
						<a class="btn btn-primary" href="#!" style="width:100px" 
						onclick="delete_confirm.action()" data-dismiss="modal">
                            Yes
                        </a>
                        <button type="button" class="btn btn-danger" data-dismiss="modal" style="width:100px">No
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="generateBarcodeModal" tabindex="-1" 
		role="dialog"  
			aria-labelledby="logoutModalLabel" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered  mw-75 w-50" role="document">
        <div class="modal-content modal-inside bg-greenlobster">
            <div style="border:0" class="modal-header"></div>
            <div class="modal-body text-center">
                <h5 class="modal-title text-white" id="logoutModalLabel">
				Do you really want to generate barcodes?</h5>
            </div>
            <div class="modal-footer" style="border-top:0 none; padding-left: 0px; padding-right: 0px;">
                <div class="row" style="width: 100%; padding-left: 0px; padding-right: 0px;">
                    <div class="col col-m-12 text-center">
						<a class="btn btn-primary" href="#!" style="width:100px" 
						onclick="generate_barcode()" data-dismiss="modal">
                            Yes
                        </a>
                        <button type="button" class="btn btn-danger" data-dismiss="modal" style="width:100px">No
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="res"></div>

@endsection
@section('js')
@include('settings.buttonpermission')

<script>
function generate_barcode() {

	$.get("{{route('settings.barcode.generate')}}",{bm_id:'{{$id}}'}).done(function(res) {
		$("#res").html(res);
	});
}

function generate_barcode_no_msg() {
	$.get("{{route('settings.barcode.generate')}}",{bm_id:'{{$id}}'})
}

function new_attr() {
	$.get("{{route('setting.barcode.attrib.new')}}",{bm_id:'{{$id}}'}).done(function(res){
		table_matrix_popup_table.ajax.reload()	
		table_matrix_popup_table.ajax.reload()
		$("#res").html(res)

	})
}
function change_name(id, name) {
		$('#matrix_name').attr('m-id',id);
		$('#matrix_name').val(name);
		$('#name_change_modal').modal('show');
}
$('#attrModal').on('hidden.bs.modal', function (e) {
	//	$("#generateBarcodeModal").modal('show');
	generate_barcode()
});

$('#name_change_modal').on('hidden.bs.modal', function (e) {
		value	= 	$("#matrix_name").val();
		id		=	$("#matrix_name").attr('m-id');
		
		if (id != 0) {
		$.get('{{route("setting.barcode.attrib.rename")}}',{
				m_id	:	id,
				name	:	value
			}).done(function(z) {
				$("#res").html(z);
				table_matrix_popup_table.ajax.reload() 
				generate_barcode_no_msg() 
			});	
		}	

		$("#matrix_name").attr('m-id','0');

});		

delete_confirm = {
		id: null,
		action: null,
		display_confirm: function(url) {
				$('#confirmModal').modal('show')
		},
		actionx: function() {
				this.action();
				$('#confirmModal').modal('hide');
		}
}

function delete_matrix_attr(id) {
	delete_confirm.id = id
	delete_confirm.action = function() {
		$.get("{{route('setting.barcode.matrixdel.delete')}}",
       			{m_id:delete_confirm.id}).done(function(res) {
				table_matrix_popup_table.ajax.reload();
				$("#res").html(res);
				generate_barcode_no_msg() 
			});
	}

	delete_confirm.display_confirm();

}

function attribute_new() {
		id = 	$("#new_colour_btn").attr('ma-id')
				
		$.get("{{route('settings.barcode.attribute.new')}}",{
					ma_id : id
		})
		.done(function(res){
				table_m_attr_popup_table.ajax.reload() 
				table_matrix_popup_table.ajax.reload();
				$("#res").html(res)

		});
}

function update_attrib_name() {
	$(".attribute-input").on('change', function(e) {
		a_id	=	$(e.target).attr('a-id');
		m_id 	=	$(e.target).attr('m-id')
		val		=	$(e.target).val();
		$.get('{{route('settings.barcode.attribute.value')}}',{
			a_id	:	a_id,
			name	:	val,
			ma_id	:	m_id
		}).done(function(res) {
			table_m_attr_popup_table.ajax.reload() 
			table_matrix_popup_table.ajax.reload();
			$("#res").html(res)
		});
	})	
}
function delete_attribute(id) {
	delete_confirm.id = id
	delete_confirm.action = function(res) {
			$.get("{{route('settings.barcode.attribute.delete')}}",
 			{a_id:delete_confirm.id}).done(function(res) {
				table_m_attr_popup_table.ajax.reload() 
				table_matrix_popup_table.ajax.reload();
				$("#res").html(res);		
		
		});
	}

	delete_confirm.display_confirm();

}
var table_m_attr_popup_table
function show_attr(id) {
	
	$("#new_attrib_btn").attr('ma-id',id)
	
	 table_m_attr_popup_table = $('#m_attr_popup_table').DataTable(
	{
		"destroy": true,
		"processing": false,
		"serverSide": true,
		"autoWidth": false,
		"ajax": {
			url:"{{route('setting.barcode.attr.table')}}",
			type: "POST",
			"data": {"mb_id":  id}
        },
        columns: [
			{data: 'DT_RowIndex', name: 'DT_RowIndex'},
			{data: 'attr', name: 'attr'},
			{data: 'del', name: 'del'},
        ],
        "order": [],
        "columnDefs": [  
			{"className": "dt-center vt_middle ", "targets": [0,2]},
			{"width":"30px","targets":[0,2]},	
			{ orderable: false, targets: [2]}
		],
		"drawCallback": function(settings, json) {
			update_attrib_name()
		},
	});

	table_m_attr_popup_table.draw()

		$("#attrModal").modal('show');
}

var table_matrix_popup_table = $('#role_popup_table').DataTable(
	{
		"destroy": true,
		"processing": false,
		"serverSide": true,
		"autoWidth": false,
		"ajax": {
			url:"{{route('setting.barcode.matrix.table')}}",
			type: "POST",
			"data": {"bm_id": {{$id ?? ''}}}
        },
        columns: [
		{data: 'DT_RowIndex', name: 'DT_RowIndex'},
		{data: 'set', name: 'set'},
        {data: 'attr', name: 'attr'},
		{data: 'del', name: 'del'},
        ],
        "order": [],
        "columnDefs": [  
			{"className": "dt-center vt_middle", "targets": [0,3]},
			{"className": "dt-center vt_middle slimcell os-linkcolor", "targets": [2]},
			{"width":"30px","targets":[0,2,3]},	
			{"className": "vt_middle", "targets": [0,1]},
			{ orderable: false, targets: [3]}
		],
	});

	table_matrix_popup_table.draw()


function activate_role(id,type,e) {
	merchant_id = $(e).attr('merchant-id')

	$.post("{{route('merchant.module.toggle_rule')}}", {
		id: id,
		type: type,
		merchant_id: merchant_id
	}).done(function(res) {
		$(e).toggleClass('active_button_activated')
	})
}
</script>


<style>
.modal-add-style {
	text-decoration: underline blue;
	cursor: pointer;
}
 .active_button:hover,.active_button:active {
        background: transparent;
        color: #34dabb;
        border: 1px #34dabb solid;
        font-weight: bold;
    }
    .active_button {
        background: transparent;
        color: #ccc ;
        border: 1px #ccc solid;   
    }
    .active_button_activated{
        background: transparent;
        color: #34dabb;
        border: 1px #34dabb solid;
        font-weight: bold;       
    }
.vt_middle{vertical-align: middle !important;}
</style>
</div>



@endsection
