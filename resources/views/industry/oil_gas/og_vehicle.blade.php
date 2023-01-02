@extends('industry.oil_gas.og_oilgas')

@section('content_landing')
<style>
.butns{
	display: none
}

th, td{
	vertical-align: middle !important;
}

td{
	text-align: center;
}

.slim_cell {
	padding-top: 2px !important;
	padding-bottom: 2px !important;
}

#masterBackBar {
	position:relative;
	z-index:0;
	height: 30px;
}

#myProgress {
	position:absolute;
	z-index:1;
	width: 100%;
	height: 30px;
	background-color: #ddd;
	color: black;
	padding-top: 2px;
	text-align: right;
	padding-right:10px;
}

#myBar {
	position:absolute;
	z-index:2;
	width: 10%;
	opacity:50%;
	height: 30px;
	text-align: center;
}
</style>
	
<div class="d-flex" style="width: 100%;">
	<div style="padding-left:0" class="col align-self-center col-md-4">
		<h2 class="mb-0">Central Administration: Vehicle</h2>
	</div>

	<div style="padding-left:0" class="col align-self-center col-md-8">
		<div class="row align-items-center">
		<div class="col-md-6">
	{{--		<div class="mb-1" style="display:flex">
				<div style="width:50px;height:50px;opacity:50%;background-color: green"></div>
				<div class="ml-1">
					<img style="object-fit:contain;width:50px;height:50px"
						src="/images/ron95.jpeg"/>
				</div>
				<div>
					<h5 class="mb-0 ml-2"><b>RON95</b></h5>
					<h5 class="mb-0 ml-2">103000000841</h5>
				</div>
			</div>

			<div class="mb-1" style="display:flex">
				<div style="width:50px;height:50px;opacity:50%;background-color: yellow"></div>
				<div class="ml-1">
					<img style="object-fit:contain;width:50px;height:50px"
						src="/images/ron97.jpeg"/>
				</div>
				<div>
					<h5 class="mb-0 ml-2"><b>RON97 Ultimate Edition Premium</b></h5>
					<h5 class="mb-0 ml-2">103000000495</h5>
				</div>
			</div>

			<div class="mb-1" style="display:flex">
				<div style="width:50px;height:50px;opacity:50%;background-color: red"></div>
				<div class="ml-1">
					<img style="object-fit:contain;width:50px;height:50px"
						src="/images/diesel.png"/>
				</div>
				<div>
					<h5 class="mb-0 ml-2"><b>Diesel</b></h5>
					<h5 class="mb-0 ml-2">103000000884</h5>
				</div>
			</div>--}}
		</div>
		<div class="col-md-3 pb-0" style="align-self:flex-end">
			<h5 class="mb-0"><b>Deliveryman</b></h5>
			<h5 class="mb-0">James Lim</h5>
			<h5 class="mb-0">1030000000542</h5>
		</div>
		<div class="col-md-2 pb-0" style="align-self:flex-end">
			<h5 class="mb-0"><b>Number Plate</b></h5>
			<h5 class="mb-0">BXK 1234 </h5>
			<h5 class="mb-0">Owned</h5>
		</div>

		<div class="col-md-1 pb-0 pr-0"
			style="float:right;align-self:flex-end">
			<button class="btn btn-success bg-guide sellerbutton"
				style="padding-left:0;padding-right:0;float:right;
					margin: 0px 0 5px 0;"
				 data-toggle="modal" data-target="#colorGuide"
				id="og_guide">Guide
			</button>
		</div>
		</div>
	</div>
</div>

<div style="padding-left:0;padding-right:0;padding-top:0" class="col-sm-12">
	<table id="ca_vehicleTable"
		class="table table-bordered dataTable no-footer">
		<thead class="thead-dark">
			<tr>
				<th class="text-center" style="width:30px">No</th>
				<th class="text-center" style="width:130px">Delivery&nbsp;Order&nbsp;ID</th>
				<th class="text-center" style="width:auto">1</th>
				<th class="text-center" style="width:auto">2</th>
				<th class="text-center" style="width:auto">3</th>
				<th class="text-center" style="width:auto">4</th>
				<th class="text-center" style="width:auto">5</th>
				<th class="text-center" style="width:auto">6</th>
				<th class="text-center" style="width:auto">7</th>
				<th class="text-center" style="width:auto">8</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>1</td>
				<td>106000000439</td>
				<td class="slim_cell">
					<div id="masterBackBar">
						<div id="myBar"
							style="background-color:green;width:10%;">
						</div>
						<div id="myProgress">
							<span id="c1">100/1000</span>&nbsp;&ell;
						</div>
					</div>
				</td>

				<td class="slim_cell">
					<div id="masterBackBar">
						<div id="myBar"
							style="background-color:green;width:20%;">
						</div>
						<div id="myProgress">
							<span id="c2">200/1000</span>&nbsp;&ell;
						</div>
					</div>
				</td>

				<td class="slim_cell">
					<div id="masterBackBar">
						<div id="myBar"
							style="background-color:green;width:30%;">
						</div>
						<div id="myProgress">
							<span id="c3">300/1000</span>&nbsp;&ell;
						</div>
					</div>
				</td>
			
				<td class="slim_cell">
					<div id="masterBackBar">
						<div id="myBar"
							style="background-color:yellow;width:40%;">
						</div>
						<div id="myProgress">
							<span id="c4">400/1000</span>&nbsp;&ell;
						</div>
					</div>
				</td>

				<td class="slim_cell">
					<div id="masterBackBar">
						<div id="myBar"
							style="background-color:yellow;width:50%;">
						</div>
						<div id="myProgress">
							<span id="c5">500/1000</span>&nbsp;&ell;
						</div>
					</div>
				</td>

				<td class="slim_cell">
					<div id="masterBackBar">
						<div id="myBar"
							style="background-color:yellow;width:60%;">
						</div>
						<div id="myProgress">
							<span id="c6">600/1000</span>&nbsp;&ell;
						</div>
					</div>
				</td>

				<td class="slim_cell">
					<div id="masterBackBar">
						<div id="myBar"
							style="background-color:red;width:70%;">
						</div>
						<div id="myProgress">
							<span id="c7">700/1000</span>&nbsp;&ell;
						</div>
					</div>
				</td>

				<td class="slim_cell">
					<div id="masterBackBar">
						<div id="myBar"
							style="background-color:red;width:80%;">
						</div>
						<div id="myProgress">
							<span id="c8">800/1000</span>&nbsp;&ell;
						</div>
					</div>
				</td>
			</tr>
		</tbody>
	</table>
</div>


{!!$color_guide!!}
@endsection
@section('js')
<script>
ca_vehicleTable = $('#ca_vehicleTable').DataTable({
    order:[],

    "columnDefs": [
    ]
});

</script>
@endsection
