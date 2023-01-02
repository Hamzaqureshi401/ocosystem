<style type="text/css">
.slimcell {
    padding-top:0px !important;
    padding-bottom:0px !important;
}

.logo_text {
	float: right;
	font-weight: 500;
	cursor: pointer;
	color: #aaa
}

.textarea_box > div > h4 {
	float: left;
}

.text_green {
	color: green !important;
	font-weight: 700;
	font-size: 18px
}

.title_hdr {
	padding-bottom: 0;
	border-bottom: 1px solid #e0e0e0;
	color: #27a98a;
	font-weight: bold
}

.title_txbox {
	padding-top: 10px;
	padding-bottom: 20px;
	margin-bottom: 20px
}

input[type="text"] {
	vertical-align: top;
	font-family: 'Inconsolata', Courier, monospace;
	font-weight: 700;
	padding: 4px;
	height: 30px;
	margin-right: 8px;
}

.prawn:hover,.prawn:active {
	background: transparent;
	color: #34dabb;
	border: 1px #34dabb solid;
	font-weight: bold;
}

.prawn {
	background: transparent;
	color: #ccc ;
	border: 1px #ccc solid;   
}

.palette-color-picker-button, .m_name {
	width: 100% !important;
	border: 0 !important;
	padding: 0px !important;
	height: 35px !important;
    margin: auto;
	margin-right: 0px !important;
	box-shadow: none !important;
	background: transparent !important;
	outline: none !important;
}

.palette-color-picker-button:active, .palatte-color-picker-button:focus {
	border:0 !important;
	background: transparent !important;
	box-shadow: none !important;    
	outline: none !important;
}

.m_name {
	cursor:pointer;
	width:90% !important;
	margin-right:8px;
	color: blue;
}

.vt_middle{vertical-align: middle !important;}

</style>

<div style='margin-top:0px;margin-left: 16px;margin-right: 16px;' class='row'>

<div class="row"
	style="width: 100%;border-bottom:1px solid #e0e0e0;margin-left:0">
	<div class="col-md-11 align-self-end"
		style="padding:0; color:#27a98a;font-weight:bold;">
	   <h3 class="mb-0">Barcode Matrix</h3>
	</div>
	<div class="col-md-1 text-right p-0">
		<img src="{{asset('/images/greencrab_br5.png')}}"
			class="sellerbutton p-0 mr-0"  
			onclick="add_barcode_matrix()"
			style="margin-bottom:5px;float:right;cursor:pointer">
	</div>
</div>

<div class="clearfix"></div>

<div style="padding-top: 10px;width: 100%;">
<table class="table table-bordered align-content-center"
	id="barcode_matrix_popup_table" style="width:100%">
	<thead class="thead-dark">
	<tr>
		<th style="">No</th>
		<th style="">Category</th>
		<th style="">Sub Category</th>
		<th style="">Colour</th>
		<th style="">Matrix</th>
		<th style=""></th>
	</tr>
	</thead>
	<tbody class="tablebody">
	</tbody>
</table>
</div>
<div style="padding:30px"></div>

<div class="modal fade" id="category_sel_modal" aria-modal="true" >
        <div class="modal-dialog modal-dialog-centered modal-">
            <div class="modal-content">
                <!-- Modal body -->
                <div class="modal-body">
					<select class="form-control input-30" id='selectcategory'
						style="margin-left: 2px;width: 100%;display: inline-flex;">
						<option class="form-control" value="cat">Select Category</option>
					@foreach ($product_category as $c)
						<option class="form-control" value="{{$c->id}}">{{$c->name}}</option>
					@endforeach
					</select>
                </div>
            </div>
        </div>
</div>

<div class="modal fade" id="subcategory_sel_modal" aria-modal="true" >
<div class="modal-dialog modal-dialog-centered modal- -lg">
            <div class="modal-content">
                <!-- Modal body -->
                <div class="modal-body">
					<select class="form-control input-30" id='selectsubcategory'
						style="margin-left: 2px;width: 100%;display: inline-flex;">
			<option class="form-control"
				value="subcat">Select Subcategory</option>
					</select>
                </div>
            </div>
</div>
</div>


<div class="modal fade" id="color_sel_modal"  tabindex="-1" 
	role="dialog"  aria-hidden="true">
<div class="modal-dialog modal-dialog-centered modal-lg  mw-75 w-50"
	role="document">
	<div class="modal-content modal---inside bg-greenlobster" >
		<div style="padding-top:5px; padding-bottom:5px"
			class="modal-header align-items-center" >
			<h3 class="modal-title text-white" id="statusModalLabel">
				Colour
			</h3>
			<img src="{{asset('/images/greencrab_br5.png')}}"
				id="new_colour_btn" onclick='add_color()'
				class="sellerbutton pt-0 mr-0 mb-0"
				style="cursor:pointer"/>
		</div>

		<div class="modal-body">
			<table class="table table-bordered align-content-center"
				id="m_color_popup_table" style="width:100%">
				<thead class="thead-dark">
					<tr>
						<th style="">No</th>
						<th style="">Colour</th>
						<th style=''>Code</th>
						<th style=""></th>
					</tr>
				</thead>
			<tbody class="tablebody"></tbody>
			</table>
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
<div id="res"></div>
<script type="text/javascript">

function show_catergory_modal(id) {
	$('#selectcategory').attr('m-id',id)
	$("#category_sel_modal").modal("show")
}

function show_subcategory_modal(key, id) {
	if (key != 0) {
		get_dropDown(key) 
	}

	$('#selectsubcategory').attr('m-id',id)
	$("#subcategory_sel_modal").modal("show");
}

function add_barcode_matrix() {
	$.get("{{route('setting.barcode.matrix.new')}}")
		.done(function(res) {
			table_barcode_matrix_popup_table.ajax.reload()
				$("#res").html(res)
		})
}


$('#category_sel_modal').on('hidden.bs.modal', function (e) {
		
	m_id = $('#selectcategory').attr('m-id')
					
	val = $('#selectcategory').val()
			
	if (val != 'cat') {

		$.get("{{route('settings.barcode.matrix.updatecategory')}}", {
			m_id	:	m_id,
			cat_id 	: 	val,
		}).done(function(res){
			table_barcode_matrix_popup_table.ajax.reload()
		});

	}

	$('#selectcategory').removeAttr('m-id')
});

$('#subcategory_sel_modal').on('hidden.bs.modal', function (e) {
	
	m_id = $('#selectsubcategory').attr('m-id')
					
	val = $('#selectsubcategory').val()
			
	if (val != 'subcat') {

		$.get("{{route('settings.barcode.matrix.updatesubcategory')}}", {
			m_id		:	m_id,
			subcat_id 	: 	val,
		}).done(function(res){
			table_barcode_matrix_popup_table.ajax.reload()
			$("#res").html(res)
		});

	}

	$('#selectisubcategory').removeAttr('m-id')

});

function show_color_modal(id) {
	$("#new_colour_btn").attr('m-id',id);
 table_m_color_popup_table = $('#m_color_popup_table').DataTable(
	{
		"destroy": true,
		"processing": false,
		"serverSide": true,
		"autoWidth": false,
		"ajax": {
			url:"{{route('setting.barcode.color.table')}}",
			type: "POST",
			"data": {"m_id":  id}
        },
        columns: [
			{data: 'DT_RowIndex', name: 'DT_RowIndex'},
			{data: 'colour', name: 'colour'},
			{data:'hex', name:'hex'},
			{data: 'del', name: 'del'},
        ],
        "order": [],
        "columnDefs": [  
			{"className": "dt-center vt_middle slimcell", "targets": [0,1,2,3]},
			{"width":"30px","targets":[0]},	
			{"width":"30px",targets:[3]},
			{ orderable: false, targets: [3]}
		],
		"drawCallback": function(settings, json) {
				colorChangeEvent()
		},
	});

	table_m_color_popup_table.draw()
	$("#color_sel_modal").modal('show');
}

function add_color() {
	m_id = $("#new_colour_btn").attr('m-id');
	$.get("{{route('setting.barcode.color.new')}}",{
		m_id : m_id
	}).done(function(res) {
		table_m_color_popup_table.ajax.reload()
		table_barcode_matrix_popup_table.ajax.reload()
			//$("#res").html(res)
	});
}

$("#color_sel_modal").on('hidden.bs.modal', function() {
	 
	m_id = $("#new_colour_btn").attr('m-id');
	$.get("{{route('settings.barcode.generate')}}",{bm_id:m_id});
});

function colorChangeEvent() {
	$('.colorInput').on('change', function(e) {
		e = e.target	
		id = $(e).attr('mc-id');
		mt = $(e).attr('mt-id');
		value = $(e).val()
	
		$.get("{{route('setting.barcode.color.update')}}",{
			mc_id:id,
			m_id:mt,
			color: value
		}).done(function(e) {
			table_m_color_popup_table.ajax.reload()
			table_barcode_matrix_popup_table.ajax.reload()
			$("#res").html(e)
		});
	});
}

function get_dropDown(key) {

		var dropdown = $('#selectsubcategory');

		console.log(dropdown);
		dropdown.empty();

		var firstIndex = '<option class="form-control" value="subcat">Select Subcategory</option>';

		const url = "{{route('productsetting.get_dropDown',['OPTION'=>'subcat','KEY'=>'KEY'])}}".replace('KEY', key);

		$.getJSON(url, function (data) {

				dropdown.empty();
				dropdown.append(firstIndex)
						dropdown.prop('selectedIndex', 0);

				$.each(data, function (key, entry) {
						dropdown.append($('<option class="form-control"></option>').attr('value', entry.id).text(entry.name));
				})
		});
}

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

function delete_matrix(id) {
	delete_confirm.id = id
	delete_confirm.action = function() {
		$.get("{{route('setting.barcode.matrix.delete')}}", {m_id:delete_confirm.id}).done(function(res) {
			table_barcode_matrix_popup_table.ajax.reload()
			$("#res").html(res)
		});
	}

	delete_confirm.display_confirm();
}

function delete_color(id) {
		delete_confirm.id = id
				console.log(id)
	delete_confirm.action = function() {
		$.get("{{route('setting.barcode.color.delete')}}", {mc_id:delete_confirm.id}).done(function(res) {
			table_m_color_popup_table.ajax.reload()
			table_barcode_matrix_popup_table.ajax.reload()
			$("#res").html(res)
		});
	}

	//$("#color_sel_modal").modal('hide');
	delete_confirm.display_confirm();
}

table_barcode_matrix_popup_table = $('#barcode_matrix_popup_table').DataTable(
	{
		"destroy": true,
		"processing": false,
		"serverSide": true,
		"autoWidth": false,
		"ajax": {
			url:"{{route('setting.barcode.maintabe')}}",
			type: "POST",
			"data": {"merchant_id":  '1'}
        },
        columns: [
		{data: 'DT_RowIndex', name: 'DT_RowIndex'},
        {data: 'cat', name: 'cat'},
        {data: 'subcat', name: 'subcat'},
        {data: 'colour', name: 'colour'},
        {data: 'matrix', name: 'matrix'},
		{data: 'del', name: 'del'},
        ],
        "order": [],
        "columnDefs": [  
			{"className": "dt-center vt_middle slimcell", "targets": [0,3,4,5]},
			{"width":"30px","targets":[0,5]},	
			{"width":"50px","targets":[3,4]},	
			{ orderable: false, targets: [0,5]}
		],
	});


</script>
<script src="{{asset('js/palette-color-picker.js')}}"></script>