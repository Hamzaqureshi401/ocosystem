<div class="modal fade " id="colorGuide" tabindex="-1" role="dialog"> 
<div class="modal modal-dialog modal-dialog-centered  mw-75 w-50"
	role="document">
	<div class="modal-content modal-inside bg-purplelobster">
	<div class="modal-header">
		<h3 class="mb-0 modal-title text-white" id="statusModalLabel">
			Guide
		</h3>
	</div>

	<div class="modal-body mb-2">
		<div class="row align-items-center p-0">

			<div class="pl-0 col-md-12">
				<h5 class="mb-1">
					<b>1. +Tank</b>
				</h5>
				<ol style="list-style: lower-alpha">
				<li>
				<b>Direct tanks are created via this account:</b><br>
				Retail > POS OPOSsum > Direct Terminal > Screen D > Tank >
				Direct Tank Management > Location > +Tank
				</li>
				<li>
				<b>Franchise tanks are created via Franchisee's account:</b><br>
				Retail > POS OPOSsum > Franchise Terminal > Screen D > Tank >
				Franchise Tank Management > Location > +Tank
				</li>
				</ol>
			</div>


			<div class="pl-0 col-md-12">
				<h5 class="mb-1">
					<b>2. Product</b>
				</h5>
			</div>
			@foreach($og_fuels as $f)
			<div class="mb-1 d-flex {{$f->access_type}}">
				<div class="pl-0 d-flex col-md-8">
					<div>
						<div style="width:50px;height:50px;
							background-color: {{$f->color}}">
						</div>
					</div>
					<div class="ml-2">
						<img style="width:50px;height:50px;
							object-fit:contain"
							src="/images/product/{{$f->id}}/thumb/{{$f->thumbnail_1}}">
					</div>
					<div class="ml-2">
						<h5 class="mb-0"><b>{{$f->name}}</b></h5>
						<h5 class="mb-0">{{$f->systemid}}</h5>
					</div>
				</div>
				<div class="col-md-4 align-self-center"></div>
			</div>
			<br/>
			@endforeach
			</div>
		</div>
	</div>
	</div>
</div>
