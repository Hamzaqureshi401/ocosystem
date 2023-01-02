@if ($model == "getdata" && isset($type))
 <div class="modal fade" id="addProd_data" tabindex="-1" role="dialog" aria-labelledby="getDataOH" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
       <div class="modal-content">
            <form action="#" id="addProd_data_FORM" method="post"
                  enctype="multipart/form-data" onsubmit="return false" autocomplete="off">
                {{ csrf_field() }}
                <div class="modal-body">

					@if ($type == 'add_product')
                       <div style="margin-bottom:0" class="form-group">
                            <input type="text" class="form-control" id="keyName" placeholder="Product" name="productname"
                                 autocomplete="off" style="width: 100%;"/>
                         </div>
                    
                        <input type="hidden" name="categoryid" value="{{$selectcategory}}">
                        <input type="hidden" name="subcategoryid" value="{{$selectsubcategory}}">
                         <input type="hidden" id="addType" name="addType" value="product"/>

					@elseif ($type == 'add_category')
                        <div style="margin-bottom:0" class="form-group">
                            <input type="text" class="form-control" id="keyName" placeholder="Category" name="categoryname"
                                 autocomplete="off" style="width: 100%;"/>
                         </div>
                         <input type="hidden" id="addType" name="addType" value="category"/>

					@elseif ($type == 'add_subcategory')
						<div style="margin-bottom:0" class="form-group">
            				<input type="text" class="form-control" id="keyName" placeholder="Sub Category" name="subcategoryname"
                                 autocomplete="off" style="width: 100%;"/>
                         </div>
                         <input type="hidden" name="categoryid" value="{{$selectcategory}}">
                         <input type="hidden" id="addType" name="addType" value="subcategory"/>
                    @elseif ($type == 'add_Brand')
                        <div style="margin-bottom:0" class="form-group">
                            <input type="text" class="form-control" id="keyName" placeholder="Brand" name="brandname"
                                 autocomplete="off" style="width: 100%;"/>
                         </div>
                          <input type="hidden" id="addType" name="addType" value="brand"/>
					@endif

				</div>
	        </form>
	    </div>
	 </div>
</div>
<script>
$('#addProd_data').submit(function (e) {
	e.preventDefault();
	$("#addProd_data").modal('hide');
});

$('#addProd_data').on('hidden.bs.modal', function (e) {
	saveData();
});


function saveData() {
	const form = $('#addProd_data_FORM')[0];
	const formData = new FormData(form);
	const thistype = $("#addType").val();

	if (thistype == 'subcategory') {
		var urlthis = "{{route('productsetting.addProd_data.add.subcategory')}}";
	} else if (thistype == 'category') {
		var urlthis = "{{route('productsetting.addProd_data.add.category')}}";
	} else if (thistype == 'product') {
		var urlthis = "{{route('productsetting.addProd_data.add.product')}}";
	} else if (thistype == 'brand') { 
		var urlthis = "{{route('productsetting.addProd_data.add.brand')}}";
	} else {
		var urlthis = null;
	}

	console.log(urlthis)

	$.ajax({
		url: urlthis,
		type: "POST",
		enctype: 'multipart/form-data',
		processData: false,  // Important!
		contentType: false,
		cache: false,
		data: formData,
		success: async function (response) {
			$("#addProd_data").modal('hide');
			$("#response").html(response);

			if (thistype == 'category' || thistype == 'brand') {
				 if (thistype == 'category') {
					dropdown = $('#selectcategory');
					dropdown.empty();
					dropdown.append('<option class="form-control" value="cat">Select Category</option>');
					dropdown.prop('selectedIndex', 0);
					url = "{{route('productsetting.get_dropDown',['OPTION'=>'OPTION','KEY'=>'KEY'])}}".replace('OPTION','cat').replace('KEY','cat');
					console.log(url)
				} else {
					dropdown = $('#selectbrand');
					dropdown.empty();
					dropdown.append('<option class="form-control" value="cat">Select Brand</option>');
					dropdown.prop('selectedIndex', 0);
					url = "{{route('productsetting.get_dropDown',['OPTION'=>'OPTION','KEY'=>'KEY'])}}".replace('OPTION','brand');
				}
				console.log(url)
				$.getJSON(url, function (data) {
					$.each(data, function (key, entry) {
						dropdown.append($('<option class="form-control"></option>').attr('value', entry.id).text(entry.name));
					})
				});
			}

			if (thistype != 'brand') { 
				disable_all();
				$('#selectcategory').prop('selectedIndex', 0);
				$('#selectsubcategory').prop('selectedIndex', 0);
				$('#selectproduct').prop('selectedIndex', 0);
			}
		}, error: function (e) {
			console.log(e.message)
		}
	});
}
</script>
@endif
