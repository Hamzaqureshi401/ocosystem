@extends('layouts.layout')

@section('content')
    <style>
        .butns{
            display: none
        }
    </style>
    @include('industry.oil_gas.og_header')
    @include('industry.oil_gas.og_buttons')
    
			<div id="landing">

        @yield('content_landing')
			</div>
        @yield('script')
        @yield('js','')
        
    @section('script')


    @endsection


@endsection
@section('js')
    <script>
        function ajaxLoadView(route) {
            $.ajax({
                url: route,
                type: 'GET',
                dataType: "html",
                success: function (response) {
                    $("#landing").html(response);
                },
                error: function (e) {
                    console.log('error', e);
                }
            });
        }
        $('#servAsset').click(function() {
            ajaxLoadView('{{ route('industryServMn.assetLoad') }}')
        });

    </script>

    <!-- Modal Logout-->
    <div class="modal fade" id="valueModal" tabindex="-1" role="dialog"
         style="padding-right:0 !important"
         aria-labelledby="logoutModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered  mw-75 w-50 modal-lg" role="document">
            <div class="modal-content modal-inside bg-greenlobster">
                <div style="border:0" class="modal-header">
                </div>
                <div class="modal-body text-center" id="modalBody">

                </div>

            </div>
        </div>
    </div>

    <script>
        function loadModal(url){
            $("#modalBody").load(url);
        }
    </script>

    <script>
        $("#datepicker").datepicker( {
            format: "mm-yyyy",
            viewMode: "months",
            minViewMode: "months"
        });

        $('.datepicker').addClass('bg-greenlobster');
    </script>
@endsection