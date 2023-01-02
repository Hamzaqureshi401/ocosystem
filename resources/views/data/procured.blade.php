@extends('layouts.layout')

@section('content')
<style>
.btn-link:hover{
	text-decoration:none;
}

.btn-link:focus{
	text-decoration:none;
}
</style>

<div id="landing-view">
	<div class="row py-2"
		style="vertical-align:middle;padding-top:0 !important;padding-bottom:0 !important;margin-bottom:0;height:75px">
		<div class="col-md-6" style="display:flex;align-items:center">
			<h2 class="mb-0">Procured Inventory</h2>
			<p></p>
		</div>
	</div>
	<div class="table-responsive" style="overflow-x: hidden;">
		<table class="table table-bordered" id="consignment_tbl">
			<thead>
				<tr class="thead-dark">
					<th class="text-center" style="width:25px;">No</th>
					<th class="text-center" style="width:25px;">Product&nbsp;ID</th>
					<th class="text-left"   style="width:1000px;">Product&nbsp;Name</th>
					<th class="text-left"   style="width:25px;">Procured&nbsp;Qty</th>
					<th class="text-center" style="width:25px;">Trigger</th>
					<th class="text-center" style="width:25px;">Sales</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td class="text-center">1</td>
					<td class="text-center">0000000359</td>
					<td class="text-left" style="padding-left:18px;"><a href="javascript:void(0)" class="btn-link os-linkcolor">Radio</a></td>
					<td class="text-center">300</td>
					<td class="text-center">
						<a href="javascript:void(0)" data-toggle="modal" data-target=".triggerModal" class="btn-link os-linkcolor">200</a>
					</td>
					<td class="text-center">
						<a href="javascript:void(0)" class="btn-link os-linkcolor">30,000</a>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>

<!-- trigger modal pop up starts here-->
<div class="modal fade triggerModal" tabindex="-1" role="dialog"
	aria-hidden="true">
	<div style="width:300px" class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-body">
			<form>
			<div class="mb-0 form-group">
				<input type="text" class="text-center form-control"
				style="outline:none;" Placeholder="" name="">
			</div>
			</form>
			</div>
		</div>
	</div>
</div>
<!-- trigger modal pop up ends here-->
@endsection

@section('scripts')
@include('settings.buttonpermission')

<script>
$(document).ready( function () {
	$("main").css('height','');
	$("main").css('min-height','400px');

	$('#consignment_tbl').DataTable();
});
</script>
@endsection
