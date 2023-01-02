<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" http-equiv="Content-type" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Ocosystem') }}</title>

    <!-- Styles -->
    <link href="{{ asset('css/all.css') }}" rel="stylesheet">
    <link href="{{ asset('css/bootstrap.css') }}" rel="stylesheet">
    <link href="{{ asset('css/bootstraptabs.css') }}" rel="stylesheet">
    <link href="{{ asset('css/toastr.css') }}" rel="stylesheet">
    <link href="{{ asset('css/styles.css') }}" rel="stylesheet">
    <link href="{{ asset('css/opos_styles.css') }}" rel="stylesheet">
    <link href="{{ asset('css/jquery.dataTables.css') }}" rel="stylesheet">
    <link href="{{ asset('css/fixedColumns.dataTables.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/select.dataTables.min.css') }}" rel="stylesheet">
</head>
@stack('styles')
<body>
    @php
        $geturl = Request::getPathInfo();
        $exURL = explode("/",$geturl); 
    @endphp

@if($exURL[1]=='opossum' && $exURL[2]=='terminal') 
<main style="margin-top:0;height: 100vh;overflow-y:auto; overflow-x:hidden;">
@else
@include('common.header')
<main style="margin-top:0;">
@endif
    <div class="container-fluid gm"
         style="padding-left:20px;padding-right:20px">
        @yield('content')
    </div>
</main>

@if($exURL[1]=='opossum' && $exURL[2]=='terminal') 
@else
@include('common.footer')
@endif

<script src="{{ asset('js/jquery-3.4.1.min.js') }}"></script>
<script src="{{ asset('js/bootstrap.js') }}"></script>
<script src="{{ asset('js/jquery.inputmask.bundle.js') }}"></script>
<script src="{{ asset('js/jquery.dataTables.js') }}"></script>
<script src="{{ asset('js/toastr.js')}}"></script> 
<script src="{{ asset('js/button_processing.js')}}"></script>
<script src="{{ asset('js/dataTables.fixedColumns.js') }}"></script>
<script src="{{ asset('js/dataTables.buttons.js') }}"></script>
<script src="{{ asset('js/dataTables.flash.buttons.js') }}"></script>
<script src="{{ asset('js/dataTables.select.min.js') }}"></script>
<script src="{{ asset('js/jszip.min.js') }}"></script>
<script src="{{ asset('js/buttons.js') }}"></script>
<script src="{{ asset('js/og_buttons.js') }}"></script>
<script src="{{ asset('js/Chart.bundle.min.js') }}"></script>
<script src="{{ asset('js/custom.js') }}"></script>
@yield('scripts')
<script type="text/javascript">

    $.ajaxSetup({
    headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),

            @if(isset($merchant_hash) || !empty(Request()->merchant_hash)) 
            'merchant_hash': "{{ !empty(Request()->merchant_hash) ? Request()->merchant_hash:$merchant_hash}}",
            'X-MERCHANT-HASH': "{{ !empty(Request()->merchant_hash) ? Request()->merchant_hash:$merchant_hash}}" 
            @endif

        },
        statusCode : {
			440: function() {
			   window.location = '/'
			},
            @if(isset($merchant_hash) || !empty(Request()->merchant_hash)) 
            200: function() {
                new_tab_event_listen()
            }
            @endif
        },
        async: false
    });

    toastr.options = {
        timeOut: 3,
        positionClass: "toast-top-center"
    };

    var  event_
    @if(isset($merchant_hash) || !empty(Request()->merchant_hash)) 
    function new_tab_event_listen() {
		event_ = $("a[target=_blank]").on('click',function(e) {
            e.preventDefault()
            target = e.target
            url = $(target).attr('href');
            modified_url = modified_url_fn(url)
            myWindow = window.open(modified_url, "_blank")
		});

		event_2 = $("a[target=blank]").on('click',function(e) {
			e.preventDefault()
			target = e.target
			url = $(target).attr('href');
			modified_url = modified_url_fn(url)
			myWindow = window.open(modified_url, "_blank")
		});
    }

    $('.landing-view').on('change',function(){
        new_tab_event_listen()
    });
    $('body').on('change',function(){
        new_tab_event_listen()
    });
    $('head').on('change',function(){
        new_tab_event_listen()
    });
    $('html').on('change',function(){
        new_tab_event_listen()
    });

    function merchant_hash_value() {
        return "{{ !empty(Request()->merchant_hash) ? Request()->merchant_hash:$merchant_hash}}" 
    }
    new_tab_event_listen()
    @endif

    const modified_url_fn = function(url) {
        @if(isset($merchant_hash) || !empty(Request()->merchant_hash)) 
            merchant_hash = "?&?&merchant_hash="+"{{ !empty(Request()->merchant_hash) ? Request()->merchant_hash:$merchant_hash}}" 
        @else
            merchant_hash = ''
        @endif
        modified_url = url+merchant_hash
        return modified_url
	}

	const headder_link_open = function() {
    	@if(isset($merchant_hash) || !empty(Request()->merchant_hash)) 
			url = "/viewmerchant/{{request()->id}}/processed";
		@else
			url = '/';
		@endif
		window.location = modified_url_fn(url);
	}

	const openNewTabURL = function(url) {
		console.log("URL", url);
		url = modified_url_fn(url);
		console.log("Modified Url", url);
		window.open(url);
		console.log("Opening window");
		window.focus();
	}
	
	setInterval( function() {
		if ($('body').hasClass('modal-open') == false) {
			$(".modal-backdrop").remove();
		}	
	}, 2500);

</script>

@yield('js','')
</body>
</html>
