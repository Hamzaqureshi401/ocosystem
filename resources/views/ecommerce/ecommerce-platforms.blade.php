
<div class="modal fade show" id="platform" role="dialog"
	style="display: block;" aria-modal="true">

	<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
		<!-- Modal content-->
		<div class="modal-content bg-greenlobster" style="padding: 9;">
			<div style="padding-top:10px;padding-bottom:10px"
				class="modal-header">
				<h3 style="margin-bottom:4px">Platform</h3>
			</div>

			<div class="modal-body">
				<div id="modalplatforms">
				@foreach($platforms as $platform)
					<h5 style="cursor:pointer" onclick="selectplatform({{$platform->id}})" data-id="{{$platform->id}}" 
						class="platfotmlist pllist @if($platform->exists) pl_selected @else pl_notselected @endif" id="platform{{$platform->id}}"
						
						>{{$platform->platform}}
					</h5>
				@endforeach
				</div>
			</div>
		</div>
	</div>
</div>

