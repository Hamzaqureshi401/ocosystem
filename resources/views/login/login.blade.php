<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title>Ocosystem</title>

	<!-- Scripts -->
	<script async src="{{ asset('js/jquery-3.4.1.min.js') }}" defer></script>
	<script src="{{ asset('js/app.js') }}" defer></script>

	<!-- Styles -->
	<link href="{{ asset('css/app.css') }}" rel="stylesheet">
	<link href="{{ asset('css/styles.css') }}" rel="stylesheet">
	
	<script src="https://www.google.com/recaptcha/api.js" async defer></script>
	
	<!-- Styles -->
	<style>
	html, body {
		background-color: #fff;
		font-family: sans-serif;
		font-weight: 400;
		height: 100vh;
		margin: 0;
	}

	.top-right {
		position: absolute;
		right: 10px;
		top: 18px;
	}

	.links > a {
		//color: #636b6f;
		color: #e0e0e0;
		padding: 0 25px;
		font-size: 25px;
		font-weight: 600;
		letter-spacing: .1rem;
		text-decoration: none;
	}

	a.btn.btn-link:hover {
		color:#92ffa1;
		font-weight:1000;
	}

	.m-b-md {
		margin-bottom: 30px;
	}

	.modal-inside {
		min-height: 250px;
		border-radius: 12px;        
	}

	.modal-body button.close {
		position: absolute;
		top: 5px;
		right: 6px;
		opacity: 0.5;
		background: #555;
		border: none;
		border-radius: 50%;
		padding: 3px 6px;
		z-index: 3000;
		text-shadow: none;
		color: #fff;
	}

	.modal-inside .row {
		padding: 15px;
		margin: 0px;
		color: #fff;
	}

	.modal-footer h4 {
		text-align: left;
	}

	.height-gap {
		height: 20px;
	}

	.form-modal .modal-body,
	.form-modal .modal-content {
		background: none;
		outline: 0px none;
		box-shadow: none;
		border: none;
		border-radius: none;
	}

	.form-modal .m-footer {
		border: none;
		background: rgba(26, 188, 156, 0.7);
		text-align: right;
		}

	.modal-backdrop {
		position: fixed;
		top: 0;
		right: 0;
		bottom: 0;
		left: 0;
		background-color: white;
		opacity: 0.5;
		width: 100%;
		height: 100%;
		z-index: 1040;
	}

	input {
		background-color:white !important;
		background-image: none !important;
		font-size:25px;
		color: black !important;
	}

	.input-content {
		font-size:22px;
		height:50px;
	}

	.signInBtnnew {
		background: #0BEDC0;
		border-radius: 5px !important;
		color: #fff;
		padding: 8px 25px;
		width: 100%;
		font-size: 25px;
	}

	.signInSelect {
		color: #666;
	}

	.signInSelect .caret {
		color: rgb(36, 206, 193);
	}

	.logo {
		background-image:url('');
		background-size:90% auto;
		background-position:25px 80px;
		background-repeat:no-repeat;
	}

	.signup .row {
		padding: 5px;
		margin: 0px;
		color: #fff;
	}
</style>

</head>
<body class=""
	style="background-color:black">

	<div class="text-center"
		style="position:relative;top:22%">
		<div>
			<img style="height:auto;width:540px"
				src="images/ocosystem_logo_wht-13.png"/>
		</div>
		<!--
		<div>
			<span style="font-size:70px;color:white;">
				Ocosystem
			</span>
		</div>
		-->
	</div>


	<div class="flex-center position-ref full-height">
		@if (Route::has('login'))
			<div style="background-color:rgba(0,0,0,0.5)"
				class="top-right links">
			@if (Auth::check())
				@if (Auth::user()->type == 'admin')
				<a href="{{route('landing.ajax.superadmin')}}">Log In</a>
            	@else
				<a href="{{ url('/landing') }}">Log In</a>
				@endif
			@else
				<a  class="btn btn-link" data-toggle="modal"
					style="padding-right:8px;text-decoration:none"
					data-target="#SignUpModal">Sign Up</a>
				<span style="position:relative;top:5px;color:#e0e0e0;
					font-weight:bold;font-size:25px">|</span>
				<a  class="btn btn-link" data-toggle="modal"
					style="padding-left:10px;text-decoration:none"
					data-target="#loginModal">Log In</a>
			@endif
			</div>
		@endif
	</div>


	<div class='form-modal '>
	<div class="modal fade" id='loginModal'>
		<div class="modal-dialog"
			style="width:600px !important">
			<div class="modal-content" >
			<div class="modal-body">
			<div class="col-md-12 modal-inside logo bg-greenlobster"
				style="height:580px;padding-top:20px">

			<!-- Display Logo -->
			<div style="position:relative;top:50px;display:flex; justify-content:center">
			<img src="{{ asset('images/ocosystem_logo_wht-13.png') }}"
				style="width:60%">
			</div>

			<form method="POST"
				style='margin-top:90px'
				action="{{ route('login') }}">
			@csrf
			<div class="form-group row"
				style="padding-top:8px;padding-bottom:4px;display:flex;justify-content:center">
				<div class="col-md-12" style="text-align:center;width:90%">
					<input id="email" style="" type="email"
					class="input-content form-control @error('email') is-invalid @enderror"
					name="email" value="{{ old('email') }}"
					required autocomplete="email" autofocus
					placeholder="Enter email">

					@if (session('error'))
						<span class="invalid-feedback" role="alert">
							<strong>{{ session('error') }}</strong>
						</span>
					@endif

					@error('email')
					<span class="invalid-feedback" role="alert">
						<strong>{{ $message }}</strong>
					</span>
					@enderror
				</div>
			</div>

			<div class="form-group row"
				style="padding-top:4px;padding-bottom:8px;display:flex;justify-content:center">
				<div class="col-md-12" style="width:90%">
					<input id="password" style="" type="password"
					class="input-content form-control @error('password') is-invalid @enderror"
					name="password" required
					autocomplete="current-password"
					placeholder="Enter password">

					@error('password')
					<span class="invalid-feedback" role="alert">
						<strong>{{ $message }}</strong>
					</span>
					@enderror
				</div>
			</div>

			<div class="form-group row mb-0"
				style="display:flex;justify-content:center">
				<div class="col-md-12 offset-md-6" style="width:90%">
					<button type="submit"
					style="height:75px"
					class="input-content btn btn-primary btn-lg btn-block bg-signin">
						{{ __('Log In') }}
					</button>
				</div>
			</div>
			</form>					
			</div>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
</div>

<!-----------Sign Up Model------------------>

	<div class='form-modal '>
	<div class="modal fade" id='SignUpModal'>
		<div class="modal-dialog"
			style="width:600px !important">
			<div class="modal-content" >
			<div class="modal-body">
			<div class="col-md-12 signup modal-inside logo bg-greenlobster"
				style="height:700px;">

			<!-- Display Logo -->
			<!--
			<div style="position:relative;top:50px;display:flex; justify-content:center">
			<img src="{{ asset('images/small_logo.png') }}"
				style="width:20%">
			</div>
			-->
			<form method="POST"
				style='margin-top:150px'
				action="{{ route('signup') }}" autocomplete="off">
	
			@csrf

			<div class="form-group row">
				<div class="col-md-12">
					<input id="company_name" type="text"
					class="input-content form-control @error('company_name') is-invalid @enderror"
					name="company_name" value="{{ old('company_name') }}"
					required autocomplete="company_name" autofocus
					placeholder="Enter company name">

					@error('company_name')
					<span class="invalid-feedback" role="alert">
						<strong>{{ $message }}</strong>
					</span>
					@enderror
				</div>
			</div>


			<div class="form-group row">
				<div class="col-md-12">
					<input id="contact_person" type="text"
					class="input-content form-control @error('contact_person') is-invalid @enderror"
					name="contact_person" value="{{ old('contact_person') }}"
					required autocomplete="contact_person" autofocus
					placeholder="Enter contact person">

					@error('contact_person')
					<span class="invalid-feedback" role="alert">
						<strong>{{ $message }}</strong>
					</span>
					@enderror
				</div>
			</div>


			<div class="form-group row">
				<div class="col-md-12">
					<input id="contact_mobile" type="text"
					class="input-content form-control @error('contact_mobile') is-invalid @enderror"
					name="contact_mobile" value="{{ old('contact_mobile') }}"
					required autocomplete="contact_mobile" autofocus
					placeholder="Enter mobile number">

					@error('contact_mobile')
					<span class="invalid-feedback" role="alert">
						<strong>{{ $message }}</strong>
					</span>
					@enderror
				</div>
			</div>

			<div class="form-group row">
				<div class="col-md-12">
					<input id="email" type="email"
					class="input-content form-control @error('email') is-invalid @enderror"
					name="email" value="{{ old('email') }}"
					required autocomplete="email" autofocus
					placeholder="Enter email">

					@error('email')
					<span class="invalid-feedback" role="alert">
						<strong>{{ $message }}</strong>
					</span>
					@enderror
				</div>
			</div>

			<div class="form-group row">
				<div class="col-md-12">
					<input id="password" type="password"
					class="input-content form-control @error('password') is-invalid @enderror"
					name="password" required
					autocomplete="current-password"
					placeholder="Enter password">

					@error('password')
					<span class="invalid-feedback" role="alert">
						<strong>{{ $message }}</strong>
					</span>
					@enderror
				</div>
			</div>

			<div class="form-group row">
				<div class="col-md-12">
					<input id="confirm-password" type="password"
					class="input-content form-control @error('password_confirmation') is-invalid @enderror"
					name="password_confirmation" required
					autocomplete="confirm-password"
					placeholder="Enter confirm password">

					@error('password_confirmation')
					<span class="invalid-feedback" role="alert">
						<strong>{{ $message }}</strong>
					</span>
					@enderror
				</div>
			</div>

			<div class="form-group row mb-0">
				<div class="col-md-12 offset-md-6">
					<button type="submit"
					style="height:75px"
					id="signup_btn"
					disabled="true"
					class="input-content btn btn-primary btn-lg btn-block bg-signin">
						{{ __('Sign Up') }}
					</button>
				</div>
			</div>

 			<div class="form-group row">
			<div class="col-md-12">

				<div class="g-recaptcha"
				data-sitekey="6Lcm0asZAAAAAHZXRxjALiJKgVgbmr4DaebMwVwY">
				</div>
				
			</div>
			</div>
 

			</form>					
			</div>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
</div>


{{-- Error model --}}
<div class="modal fade" id="msgModal"  tabindex="-1"
	role="dialog" aria-labelledby="staffNameLabel"
	aria-hidden="true" style="text-align: center;">

	<div class="modal-dialog modal-dialog-centered  mw-75 w-50"
		role="document" style="display: inline-flex; top: 30%;">
		<div class="modal-content bg-greenlobster"
			style="width: 100%;">
			<div class="modal-header" style="border:0">&nbsp;</div>
			<div class="modal-body text-center">
				<h5 style="font-size: x-large;" class="modal-title text-white"
					id="statusModalLabel">
				</h5>
			</div>
			<div class="modal-footer" style="border:0">&nbsp;</div>
		</div>
	</div>
</div>



<script src="{{ asset('js/jquery-3.4.1.min.js') }}"></script>

<script type="text/javascript">
	$(document).ready(function(){
		setInterval(function(){ 
			  var response = grecaptcha.getResponse();
			  if(response.length != 0) 
			  {  
			    $('#signup_btn').attr("disabled", false)
			  }
		 }, 5000);
	});
</script>

@if(session()->has('message'))
<script type="text/javascript">
	$(document).ready(function(){
		$("#statusModalLabel").text("");
		$("#statusModalLabel").text("{{ session()->get('message') }}");
		$("#msgModal").modal("toggle");
		setTimeout(function() {
			$("#msgModal").modal("hide");
		}, 5000);
	});
</script>
@endif

<script type="text/javascript">
	$(document).ready(function(){

		var message = "";
		@if($errors->any())
			message = "{!!$errors->first()!!}";
			$("#statusModalLabel").text("");
			$("#statusModalLabel").text(message);
			$("#msgModal").modal("toggle");
			/*
			setTimeout(function() {
				$("#msgModal").modal("hide");
			}, 2500);
			*/
		@endif
	});
</script>

</body>	
</html>
