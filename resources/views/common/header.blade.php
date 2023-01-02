<header>
    <nav style="opacity:0.9"
		class="navbar fixed-top navbar-expand-md navbar-dark bg-dark shadow-sm">
        <div class="container-fluid">
            <a style="font-weight:bold;display:flex"
				class="navbar-brand float-left"
				href="javascript:(0)" onclick="headder_link_open()" >
				<img class="mr-3" src="{{ asset('images/ocosystem_logo_hwht-12.png') }}"
					style="object-fit:contain;width:auto;height:30px"/>
            </a>
            @if (Auth::user()->type == 'admin')
            <div class="d-flex justify-content-end" style="margin-left: auto;">
              	<button class="btn btn-success btn-log bg-whiteprawn"
				style="margin-right: 15px"
				id="superadminbutton">Super Admin</button>
            </div>
            @endif

            <div class="d-flex justify-content-end">
                    <span class="navbar-text" style="color: rgba(255,255,255);">
                        {{ Auth::user()->name }}
                    </span>
                <a href="javascript:void(0)"
					class="btn btn-link align-self-center"
					data-toggle="modal" data-target="#logoutModal"
					id="btnlogout" style="color: white;padding-top:8px">
					<i style="position:relative;left:25px"
						class="fa fa-times" aria-hidden="true">
					</i>
                </a>
            </div>
        </div>
    </nav>
    @if (!isset($superAdmin))
		@include('common.buttons')
	@else
		@include('common.superadminbuttons')
	@endif
</header>
