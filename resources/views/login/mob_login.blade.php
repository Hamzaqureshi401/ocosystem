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

	<!-- Styles -->
	<style>
	/*
	html, body {
		background-color: #fff;
		font-family: 'sans-serif';
		font-weight: 400;
		height: auto;
		margin: 0;
	}
	*/
	a.btn.btn-link:hover {
		color:#92ffa1;
		font-weight:1000;
	}
	.m-b-md {
		margin-bottom: 30px;
	}	
	.height-gap {
		height: 20px;
	}
	input {
		background-color:transparent !important;
		background-image: none !important;
		font-size:25px;
		color: white !important;
	}
	.input-content {
		font-size:22px;
		height:50px;
		border-radius:10px;
		width: 80%;
	}
	.logo {
		background-image:url('');
		background-size:90% auto;
		background-position:top center;
		background-repeat:no-repeat;
	}
	.anim_login {
		height: 50vh;
		background-image:url('images/anim_torus.gif');
		background-size:contain;
		background-position:center;
		background-repeat:no-repeat;
		background-color:black;
		color:transparent;
		border: none;
		width: 86%;
	}

	.anim_login:focus{
		outline: none !important;
	}

</style>
</head>
<body style="background-color: #000000;text-align: -webkit-center;">
	@if (Route::has('login'))
		@if (Auth::check())
			@if (Auth::user()->type == 'admin')
			<a href="{{route('landing.ajax.superadmin')}}">Log In</a>
			@else
			<a href="{{ url('/landing') }}">Log In</a>
			@endif
		@else
		<div style="text-align: center; padding-top: 20px;">
		<img src="{{ asset('images/small_logo.png') }}" width="60px" height="60px">
		</div>
		<div style="text-align: center; color:white;font-size: 20px;font-weight: bold; margin-top: 10px;">Ocosystem</div>

				<form method="POST"
			action="{{ route('login') }}" style="margin-top: 10px;">
		@csrf
		<div class="form-group">
			<input id="email" type="email"
			class="input-content form-control @error('email') is-invalid @enderror"
			name="email" value="{{ old('email') }}"
			required autocomplete="email" autofocus
			placeholder="Email Address">

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

		<div class="form-group" style="margin-bottom:0">
			<input id="password" type="password"
			class="input-content form-control @error('password') is-invalid @enderror"
			name="password" required
			autocomplete="current-password"
			placeholder="Password">

			@error('password')
			<span class="invalid-feedback" role="alert">
				<strong>{{ $message }}</strong>
			</span>
			@enderror
		</div>

		<div class="form-group  mb-0" style="margin-bottom:0">
			<button style="cursor:pointer"
				type="submit" class="anim_login">
					{{ __('Login') }}
			</button>
		</div>
		</form>
		@endif
	@endif
</body>	
</html>
