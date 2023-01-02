<style>
.tabcontent{
    background-color: white;
}
.tab {
    border: unset;
}

.tab {
    background: white;
}

.tabcorners {
    border-top-right-radius: 10px;
    border-top-left-radius: 10px;
}

.tab button.active {
    background-color: #fff;
    border: 1px solid #aaa;
    border-bottom: unset;
    font-weight: 700;
    border-right: 0px solid #aaa;
    cursor: pointer !important;
    font-size: 19px;
    border-top-left-radius: 10px;
    border-top-right-radius: 10px;
}

.tab button {
    font-size: 19px;
    border-left: 1px solid #aaa;
    border-top: 1px solid #aaa;
    border-top-left-radius: 10px;
    border-top-right-radius: 10px;
    padding-top: 8px;
    padding-bottom: 10px;
    background-color: #f0f0f0;
}

.tab button:last-child {
    border-right: 1px solid #aaa;
}

#tabData {
    margin-left: 5px;
}
.modal {
    text-align: center;
}

@media screen and (min-width: 768px) {
	.modal:before {
		display: inline-flex;
		vertical-align: middle;
		content: " ";
		height: 100%;
	}
}

.modal-dialog {
	display: inline-flex;
	text-align: left;
	vertical-align: middle;
	width: 500px;
}
</style>

<div id="landing-content" style="width: 100%">
    <div class="clearfix"></div>

    <div class="tab">
        <button class="tablinks tabcorners" style="width:150px;"
            onclick="openTab(event); inventoryView()">
			Inventory</button>

        <button class="tablinks tabcorners" style="width: 230px;"
            onclick="openTab(event); restaurantnservicesView();">
			Restaurant&nbsp;&&nbsp;Services</button>

        <button class="tablinks tabcorners" style="width: 160px;"
            onclick="openTab(event); rawmaterialView();">
			Raw Material</button>

		<button class="tablinks tabcorners" style="width: 150px;"
			onclick="openTab(event); voucherView()">
			Voucher</button>

		<button class="tablinks tabcorners" style="width: 150px;"
			onclick="openTab(event); warrantyView();">
			Warranty</button>

		<button class="tablinks tabcorners" style="width: 150px;"
			onclick="openTab(event); cashCardView();">
			Cash&nbsp;Card</button>

		<button class="tablinks tabcorners" style="width: 160px;"
			onclick="openTab(event); drumBarrelshipView();">
			Drum&nbsp;&amp;&nbsp;Barrel</button>

		<button class="tablinks tabcorners" style="width: 160px;"
			onclick="openTab(event); customizationView();">
			Customization</button>

		<button class="tablinks tabcorners" style="width: 160px;"
			onclick="openTab(event); eCommerceView();">
			E-Commerce</button>
    </div>


    <div id="response_data_product" class="tabcontent p-0">
    </div>


    @section('scripts')

    @endsection

    <style>
    .modal-add-style {
        text-decoration: underline blue;
        cursor: pointer;
    }

    .tabcontent {
        border: unset !important;
    }
    </style>
    <!--- End of staff -->
</div>
<div id="productResponce"></div>

<script>
function eCommerceView() {
	  $.ajax({
        url: "{{route('ecommerce.ajax.product')}}",
        type: 'GET',
        dataType: "html",
        success: function (response) {
            $("#response_data_product").html(response);
        },
        error: function (e) {
            console.log('error', e);
        }
    });
}

 

function customizationView() {
    $.ajax({
        url: "{{route('customization.ajax.product')}}",
        type: 'GET',
        dataType: "html",
        success: function (response) {
            $("#response_data_product").html(response);
        },
        error: function (e) {
            console.log('error', e);
        }
    });
}

function cashCardView() {
    $.ajax({
        url: "{{route('membership.ajax.member')}}",
        type: 'GET',
        dataType: "html",
        success: function (response) {
            $("#response_data_product").html(response);
        },
        error: function (e) {
            console.log('error', e);
        }
    });
}

function warrantyView() {
    $.ajax({
        url: "{{route('warranty.ajax.product')}}",
        type: 'GET',
        dataType: "html",
        success: function (response) {
            $("#response_data_product").html(response);
        },
        error: function (e) {
            console.log('error', e);
        }
    });
}

function voucherView() {
    $.ajax({
        url: "{{route('voucher.ajax.product')}}",
        type: 'GET',
        dataType: "html",
        success: function (response) {
            $("#response_data_product").html(response);
        },
        error: function (e) {
            console.log('error', e);
        }
    });
}

function rawmaterialView() {
    $.ajax({
        url: "{{route('rawmaterial.ajax.product')}}",
        type: 'GET',
        dataType: "html",
        success: function (response) {
            $("#response_data_product").html(response);
        },
        error: function (e) {
            console.log('error', e);
        }
    });
}

function restaurantnservicesView() {
    $.ajax({
        url: "{{route('restaurantnservices.ajax.product')}}",
        type: 'GET',
        dataType: "html",
        success: function (response) {
            $("#response_data_product").html(response);
        },
        error: function (e) {
            console.log('error', e);
        }
    });
}


function inventoryView() {
    $.ajax({
        url: "{{route('inventory.ajax.product')}}",
        type: 'GET',
        dataType: "html",
        success: function (response) {
            $("#response_data_product").html(response);
        },
        error: function (e) {
            console.log('error', e);
        }
    });
}

function drumBarrelshipView() {
    $.ajax({
        url: "{{route('drumbarrel.ajax.product')}}",
        type: 'GET',
        dataType: "html",
        success: function (response) {
            $("#response_data_product").html(response);
        },
        error: function (e) {
            console.log('error', e);
        }
	});
}


function openTab(evt) {
    var target = evt.target;
    document.getElementById('response_data_product').style.display = " block";
	$( ".tab > button" ).removeClass("active");
    $( target ).addClass( "active" );
}


function details(product_id) {
	$.ajax({
        url: "{{route('product.details.dialog')}}",
        type: 'POST',
        data: {
            "product_id":product_id
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


function atm_money(num) {
	if (num.toString().length == 1) {
        return '0.0' + num.toString()

    } else if (num.toString().length == 2) {
        return '0.' + num.toString()

    } else if (num.toString().length == 3) {
        return '0' + num.toString()[0] +'.'+
			num.toString()[1] + num.toString()[2];

    } else if (num.toString().length >= 4) {
		return num.toString().slice(0,(num.toString().length - 2)) +
			'.'+ num.toString()[(num.toString().length - 2)] +
			num.toString()[(num.toString().length - 1)];
    }
}


function filter_price(target_field,buffer_in) {

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

				//alert('1. target_field='+$(target_field).val());
				//alert('1. buffer_in='+$(buffer_in).val());

				$(target_field).val(atm_money(parseInt($(buffer_in).val())))

				//alert('2. target_field='+$(target_field).val());
				//alert('2. buffer_in='+$(buffer_in).val());

			} else {
				$(target_field).val('')
			}
            return null;
        }

		//alert('3. target_field='+$(target_field).val());
		//alert('3. buffer_in='+$(buffer_in).val());

		const input =  event.key;
		old_val = $(buffer_in).val()

		if (old_val === '0.00') {
			$(buffer_in).val('')
			$(target_field).val('')
			old_val = ''
		}

		$(buffer_in).val(''+old_val+input)
		$(target_field).val(atm_money(parseInt($(buffer_in).val())))

		//alert('4. target_field='+$(target_field).val());
		//alert('4. buffer_in='+$(buffer_in).val());
	});
}
</script>

</div>
