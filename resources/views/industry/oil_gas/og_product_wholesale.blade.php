<style>
	#tableOilGas {width:100% !important;}
</style>
<div style="padding-right:0" class="">

	<div class="row m-0 p-0" style="">
		<div class="col-md-6 p-0">
			<div class="d-flex" style="width: 100%;margin-top:20px;">
				<div style="padding-left:0" class="col align-self-center">
					<h2 class="mb-0">Wholesale Product: Oil & Gas</h2>
				</div>
			</div>
		</div> 

		<div class="col-md-6 pr-0">
			<button class="btn btn-success sellerbutton"
				style="padding-left:0;padding-right:0;float:right;
				margin: 0px 0px 5px 5px;"
				id="addInvenProduct">+Product
			</button>
		</div>
	</div>
</div>

<div style="padding-left:0;padding-right:0" class="col-sm-12">
    <table id="tableOilGas" class="table table-bordered" style="100%;">
        <thead class="thead-dark">
            <tr>
                <th style="" >No</th>
                <th style="">Product&nbsp;ID</th>
                <th class="text-left">Product&nbsp;Name</th>
                <th style="">Price/&ell;</th>
                <th style=""></th>
                <th style=""></th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
<div class="modal fade" id="UpdateMessagePopUp" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog  modal-dialog-centered mw-75 w-50">

		<!-- Modal content-->
		<div class="modal-content  modal-inside bg-greenlobster">
			<div class="modal-header" style="border:none;">&nbsp;

			</div>
			<div class="modal-body text-center">
				<p style="font-size: 20px; margin-bottom:0">
					Loyalty point updated successfully
				</p>
			</div>
			<div class="modal-footer" style="border: none;">&nbsp;

			</div>
		</div>
	</div>
</div>
<div id="res"></div>
<div id="productResponce"></div>
<div id="showEditInventoryModal"></div>
<div id="showEditInputInventoryModal"></div>

 <div class="modal fade" id="wholesale_modal" aria-modal="true" >
        <div class="modal-dialog modal-dialog-centered modal-mg">
            <div class="modal-content">
                <!-- Modal body -->
                <div class="modal-body">
					<input class="form-control input-30" id='wholesale_price'
						name="wholesale_price" placeholder="Wholesale price"
						style="margin-left: 2px;width: 100%;display: inline-flex;"/>
						<input type="hidden" id="wholesale_price_product_id" />
						<input type="hidden" id="wholesale_price_buffer" />
                </div>
            </div>
        </div>
</div>


<br/><br/><br/>
<script>
    var oilGasTable = $('#tableOilGas').DataTable({
        "processing": false,
        "serverSide": true,
        "ajax": {
            "url": "{{route('industryoilgas.ajax.index')}}",
            "type": "POST",
            'headers': {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'og_product_id', name: 'og_product_id'},
            {data: 'og_product_name', name: 'og_product_name'},
            {data: 'og_price_wholesale', name: 'og_price_wholesale'},
            {data: 'og_color', name: 'og_color'},
            {data: 'deleted', name: 'deleted'},
        ],
        "order": [0, 'desc'],
        "columnDefs": [
            {"width": "30px", "targets": 0},
            {"width": "150px", "targets": 1},
            {"width": "100px", "targets": 3},
            {"width": "66px", "targets": 4},
            {"width": "30px", "targets": 5},
            {"className": "dt-center p-0 m-0", "targets": [4]},
            {"className": "dt-center", "targets": [0, 1, 3, 4,5 ]},
            {"orderable": false, "targets": [4]},
        ],
    });


    $('#addInvenProduct').on('click', function () {
        addInvenProduct();
    });

    $('#loyaltyUpdateModal').on('blur', function(){
    })

    //update loyalty function
    function update_loyalty(fuel_product_id, id, current_loyalty_value){
        $('#fuel_product_loyalty'+id).focusout(function(){
            let new_loyalty_value = $('#fuel_product_loyalty'+id).val();
             $.ajax({
            url: "{{route('industryoilgas-update-loyalty-point')}}",
            type: 'POST',
            'headers': {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: {
                "product_id": fuel_product_id,
                "new_loyalty_value":new_loyalty_value
            },
            success: function (response) {
                $('body').removeClass('modal-open')
                $('.modal-backdrop').removeClass('modal-backdrop')
                oilGasTable.ajax.reload();
                $("#UpdateMessagePopUp").modal('show');
                setTimeout(function(){
                    $("#UpdateMessagePopUp").modal('hide');
                }, 2500)
            },
            error: function (e) {
                console.log('error', e);
            }
        });
        })
        
    }

    var product = oilGasTable;
    var prd = false;

    function addInvenProduct() {

        if (prd == true) {
            return null
        }
        ;
        prd = true;
        $.ajax({
            url: "{{route('industryoilgas.store')}}",
            type: "GET",
            enctype: 'multipart/form-data',
            processData: false,
            contentType: false,
            cache: false,
            data: '',
            success: function (response) {
                oilGasTable.ajax.reload();
                $("#showEditInventoryModal").html(response);
                prd = false;

            }, error: function (e) {
                console.log(e.message);
            }
        });
    }
    function details(product_id) {
        $.ajax({
            url: "{{route('product.details.dialog')}}",
            type: 'POST',
            'headers': {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: {
                "product_id": product_id
            },
            success: function (response) {
                $("#productResponce").html(response);
                $('#modal').modal('show')
            },
            error: function (e) {
                console.log('error', e);
            }
        });
    }
    $('#tableOilGas tbody').on('click', 'td', function () {
        const tableCell = oilGasTable.cell(this).data();
        const tableRow = oilGasTable.row($(this).closest('tr')).data();
        const element = $(tableCell).data("field");


        if (element == 'inven_pro_name') {
            return null
        }

        if (element != null) {
            console.log(element);

            $.ajax({
                url: "{{route('industryoilgas.edit.modal')}}",
                type: 'post',
                'headers': {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                data: {
                    'id': tableRow['id'],
                    'field_name': element
                },
                success: function (response) {
                    $("#showEditInventoryModal").html(response);
                    $("#showMsgModal").modal('show');
                },
                error: function (e) {
                    console.log('error', e);
                }
            });
        }

        // alert( 'You clicked on '+data[0]+'\'s row COmmmmits;
    });
    function myFunction() {
        var x = document.getElementById("myDIV");
        if (x.style.display === "none") {
            x.style.display = "block";
        } else {
            x.style.display = "none";
        }
        inventoryTable.draw();
    }

    $('body').on('click', '.docs_id', function() {
        var url = $(this).attr('url');
        window.open(url, '_blank');
    });
	function select_colorModal(id) {	
		$('.color_bar_sel').val(id);
		$("#colorSelectDialog").modal('show');
	}

	function update_color(value, product_id) {
		$.post("{{route('og.product.update.color')}}", {
			ogproduct_id: product_id,
			color:value
	}).done(function(res){
            oilGasTable.ajax.reload();
			$("#productResponce").html(res);
	});
	}

	function wholesalepriceModal(product_id, price) {
		$("#wholesale_price_product_id").val(product_id);
		$("#wholesale_price_buffer").val(0);
		$("#wholesale_price").val(price);
		$("#wholesale_modal").modal('show');	
	}
	
     $('#wholesale_modal').on('hidden.bs.modal', function (e) {
		product_id		= $("#wholesale_price_product_id").val();
		price			= $('#wholesale_price_buffer').val();
		$.post('{{route('Og_Product.wholesale.update')}}', {
			product_id: product_id,
			price:	price
		}).done( (res) => {
			oilGasTable.ajax.reload();
			$('#res').html(res);
		});
	});
	
	filter_price('#wholesale_price','#wholesale_price_buffer')
	/////////////////////////////////////////
	// ATM Price
	
function filter_price(target_field,buffer_in) {
	$(target_field).off();
	$(target_field).on( "keydown", function( event ) {
		event.preventDefault()
		if (event.keyCode == 8) {
			$(buffer_in).val('')
			$(target_field).val('')
			return null
		}	
		if (isNaN(event.key) ||
		$.inArray( event.keyCode, [13,38,40,37,39] ) !== -1 ||
		event.keyCode == 13) {
			if ($(buffer_in).val() != '') {
				$(target_field).val(atm_money(parseInt($(buffer_in).val())))
			} else {
				$(target_field).val('')
			}
			return null;
		}

		const input =  event.key;
		old_val = $(buffer_in).val()
		if (old_val === '0.00') {
			$(buffer_in).val('')
			$(target_field).val('')
			old_val = ''
		}
		$(buffer_in).val(''+old_val+input)
		$(target_field).val(atm_money(parseInt($(buffer_in).val())))
		});
    }

function atm_money(num) {
		if (num.toString().length == 1) {
			return '00.0' + num.toString()
		} else if (num.toString().length == 2) {
			return '00.' + num.toString()
		} else if (num.toString().length == 3) {
			return '0' + num.toString()[0] + '.' + num.toString()[1] +
				num.toString()[2];
		} else if (num.toString().length >= 4) {
			return num.toString().slice(0, (num.toString().length - 2)) +
				'.' + num.toString()[(num.toString().length - 2)] +
				num.toString()[(num.toString().length - 1)];
		}
    }



</script>

