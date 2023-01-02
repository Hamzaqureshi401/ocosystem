    <nav style="opacity:0.9"
		class="navbar fixed-top navbar-expand-md navbar-dark bg-dark shadow-sm">
        <div class="container-fluid">
            <a style="font-weight:bold;vertical-align:middle;cursor:unset;"
				class="navbar-brand float-left"
				href="javascript:void(0)">
				<img src="{{ asset('images/small_logo.png') }}"
					style="object-fit:contain;width:30px;height:30px"/>
                &nbsp;Ocosystem 
            </a>
            
            <div class="d-flex justify-content-end align-items-center">
                    <span class="navbar-text" style="color: white ;margin-right: 50px; border-right: 1px solid white; padding-right: 50px">
                        Oil & Gas
                    </span>
                    <span class="navbar-text" style="color: rgba(255,255,255);">
			{{Auth::User()->name}}
                    </span>
                <a href="javascript:void(0)" class="btn btn-link"
                   data-toggle="modal" data-target="#logoutModal"
                   id="btnlogout" style="color: white;">
                    <i style="position:relative;left:25px"
					class="fa fa-times" aria-hidden="true"></i>
                </a>
            </div>
        </div>
    </nav>
