<!--Start receipt modal-->
<style type="text/css">
    .nohover {
        /* here copy default .btn class styles */
        cursor:default !important;
        background-color: #fff;
        color:#007bff;
        display: inline-block;
        font-weight: 400;
        user-select: none;
        border: 1px solid #007bff;
        padding: 0.375rem 0.75rem;
        line-height: 1.5;
        transition: color 0.15s ease-in-out,
		background-color 0.15s ease-in-out,
		border-color 0.15s ease-in-out,
		box-shadow 0.15s ease-in-out;
        /* or something like that */
    }
	
	.modalnew{
		background-color: #fff !important;
		border-radius: 5px;
	}
</style>
<div class="modal fade receiptModal" id="receiptModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content " style="max-width: 370px;margin:auto">

            <div class="modal-body modalnew" style="font-size: 14px;">
                <span class="receipthr"><hr></span>
                <div class="row">
                    <div class="col-md-6"><strong>Description</strong></div>
                    <div class="col-md-2" style="text-align: center;"><strong>Qty</strong></div>
                    <div class="col-md-2" style="text-align: center;"><strong>Disc</strong></div>
                    <div class="col-md-2" style="text-align: right;">
                        <strong>Total</strong>
                    </div>
                </div>
                <span class="receipthr"><hr></span>

                @foreach ($products as $i)
                    <div class="row">
                        <div class="col-md-6">{{$i->name}}</div>
                        <div class="col-md-2" style="text-align: center;">{{$i->quantity}}</div>
                        <div class="col-md-2" style="text-align: center;">{{$i->discount_pct}}</div>
                        <div class="col-md-2" style="text-align: right;">
                            {{number_format(($i->quantity * $i->price)/100 , 2) }}
                        </div>
                    </div>

                @endforeach


                <span class="receipthr">
					<hr style="border: 0.5px solid black !important;">
				</span>
                <div class="row">
                    <div class="col-md-5"> Item Amount</div>
                    <div class="col-md-1"></div>
                    <div class="col-md-2" style="text-align: right;">MYR </div>
                    <div class="col-md-1"></div>
                    <div class="col-md-3" style="text-align: right;">
                        <strong>{{number_format(($receipt->amount/100),2)}}</strong>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-5"> SST 6%</div>
                    <div class="col-md-1">+</div>
                    <div class="col-md-2" style="text-align: right;">MYR</div>
                    <div class="col-md-1"></div>
                    <div class="col-md-3" style="text-align: right;">
                        {{number_format(($receipt->service_tax),2)}}
                    </div>
                </div>
                <div class="clearfix"></div>
                <span class="receipthr"><hr style="border-top: 1px solid #000 !important;"></span>

                <div class="row">
                    <div class="col-md-5"> Total</div>
                    <div class="col-md-1"></div>
                    <div class="col-md-2" style="text-align: right;">MYR</div>
                    <div class="col-md-1"></div>
                    <div class="col-md-3" style="text-align: right;">
                        <strong>{{number_format(($receipt->amount/100),2)}}</strong>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-5">Credit Card</div>
                    <div class="col-md-1">-</div>
                    <div class="col-md-2" style="text-align: right;">MYR</div>
                    <div class="col-md-1"></div>
                    <div class="col-md-3" style="text-align: right;">
                        {{number_format(($receipt->amount/100),2)}}
                    </div>
                </div>

                <span class="receipthr">
				<hr style="border:0.5px solid black !important"/>
				</span>

				<div class="row">
                    <div class="col-md-5">
                        Platform
                    </div>
                    <div class="col-md-1">
                    </div>
                    <div class="col-md-6" style="text-align: right;">
                        {{$platform_name}}
                    </div>
                </div>

				<div class="row">
                    <div class="col-md-5">
                        Platform&nbsp;ID
                    </div>
                    <div class="col-md-1">
                    </div>
                    <div class="col-md-6" style="text-align: right;">
                        {{$platform_id}}
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-5">
                        Buyer Name
                    </div>
                    <div class="col-md-1">
                    </div>
                    <div class="col-md-2" style="text-align: right;">

                    </div>
                    <div class="col-md-4" style="text-align: right;">
                        {{$receipt->name}}
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-5">
                        Buyer ID
                    </div>
                    <div class="col-md-1">
                    </div>
                    <div class="col-md-6" style="text-align: right;">
                        {{$receipt->buyerid}}
                    </div>
                </div>

                 <div class="row">
                    <div class="col-md-5">
                        Date 
                    </div>
                    <div class="col-md-1">
                    </div>
                    <div class="col-md-6" style="text-align: right;">
                        {{ date('dMy H:i:s', strtotime($receipt->created_at)) }}
                    </div>
                </div>
        </div>
    </div>
</div>
</div>
<!--End of receipt Popup-->


<script type="text/javascript">

    //$( document ).ready(function() {
        //     var table_no = $("#tableno").text();
        //     $('#table_no').text(table_no);
        // kitchen_print();

    //});
    $('#receiptModal').on('hidden.bs.modal', function (e) {
        $('.modal-backdrop').remove();
    });
    $("#no_table_select").hide();

    $(document).on("click", ".bg-recpt-takeaway", function () {

        $(".div-1").fadeOut("slow", function () {

        });
    });

</script>
