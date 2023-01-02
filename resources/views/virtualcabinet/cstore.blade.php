<style>
	th {
    background-color: black;
    color: white;
} 
</style>
<div class="text-center" style="margin-bottom:75px;">
	<div class="col-md-6 text-center" style="display:flex;align-items:center" >
		<h2 class="mb-0 text-center">C-Store</h2>
	</div>
</div>
<div class="table-responsive" style="overflow-x: hidden;">
	<table class="table table-bordered " id="cstore_tbl">
	<thead>
	<tr class="bg-data">
		<th class="text-center" style="width:30px">No</th>
		<th class="text-left" style="width:auto;">Product&nbsp;</th>
		<th class="text-center" style="width:100px;">Price</th>
		<th class="text-center"   style="width:100px">Qty</th>
		<th class="text-center" style="width:100px;">Tax</th>
		<th class="text-center" style="width:100px;">Sales</th>
		<th class="text-center" style="width:100px;">Amount</th>
		<th class="text-center" style="width:100px;">Pay&nbsp;By</th>
	</tr>
	</thead>
		<tr>
			<td class="text-center">{{ "1" }}</td>
			<td class="text-left">{{ "C-Store Product" }}</td>
			<td class="text-center">{{ "999,999.99" }}</td>
			<td class="text-center">{{ "1" }}</td>
			<td class="text-center">{{ "999,999.99" }}</td>
			<td class="text-center">{{ "999,999.99" }}</td>
			<td class="text-center">{{ "999,999.99" }}</td>
			<td class="text-center">{{ "60" }}</td>
		</tr>
	<tbody>

	</tbody>

	</table>
</div>


<!-- blue crab modal pop up ends here-->

<style>
table.dataTable thead th, table.dataTable thead td { border: none !important}
</style>
<script>
$(document).ready(function() {
    $('#cstore_tbl').dataTable({
        "aLengthMenu": [[10, 50, 75, -1], [10, 25, 50, 100]],
        "iDisplayLength": 10,
        'aoColumnDefs': [{
        'bSortable': false,
        'aTargets': ['nosort']
    }]
    });
} );
</script>

