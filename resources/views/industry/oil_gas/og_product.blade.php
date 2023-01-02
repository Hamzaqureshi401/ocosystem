@extends('industry.oil_gas.og_oilgas')

@section('content_landing')
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

.tabcontent {
	border:none;
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

<div class="clearfix"></div>

<div class="tab" style='margin-top: 0;' >
 
	<button class="tablinks tabcorners" style="width:150px;"
		onclick="openTab(event);  retailView()">
		Retail</button>

	<button class="tablinks tabcorners" style="width:150px;"
		onclick="openTab(event); wholesaleView()">Wholesale</button>

</div>

<div id="response_data_product" class="tabcontent p-0">
</div>


<div class="modal fade" id="colorSelectDialog" tabindex="-1" role="dialog" aria-labelledby="productcontModallabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document" style="/*max-width: 700px;*/">

        <div style="border-radius:10px" class="modal-content bg-greenlobster">
            <div class="modal-header ">
            <h3 class="modal-title mb-0">Product: Oil & Gas </h3>
            </div>
            <div style="padding:20px !important">

            <div class="row" style="justify-content:center">
                <ul name="discountItemtLevel" id="discountItemtLevelId"
				style="margin-bottom:0;padding-left:40px;padding-right:35px"
				class="discountItemtLevel">
				@foreach($color as $c)
					<li class="btn btn-success btn-log discountbutton
						color_bar_sel" 
						onclick="update_color('{{$c}}', this.value)"
						data-dismiss="modal"
						style= "padding-left:20px ;padding-top: 20px;
						background:{{$c}}"  value="{{$c}}">
						<span value="1"></span>
					</li>

					@if ($loop->index == 4)
					</ul>
					<ul name="discountItemtLevel" id="discountItemtLevelId"
						style="margin-bottom:0;padding-left:40px;
							padding-right:35px"
						class="discountItemtLevel">
					@endif
				@endforeach
               </ul>
            </div>
            <input type="hidden" id='product_id' value="117" />
        </div>
        </div>
    </div>
</div>

@endsection

@section('js')


<script>

function retailView() {
	  $.ajax({
        url: "{{route('Og_Product.retail')}}",
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

function wholesaleView() {
	  $.ajax({
        url: "{{route('Og_Product.wholesale')}}",
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
        return '00.0' + num.toString()

    } else if (num.toString().length == 2) {
        return '00.' + num.toString()

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
@endsection
