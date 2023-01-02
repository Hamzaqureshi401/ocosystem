@if($msg == 'confirmation')
<div class="modal fade" id="showFinancialModal"
	tabindex="-1" role="dialog" aria-labelledby="staffNameLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered  mw-75 w-50" role="document">
		<div class="modal-content modal-inside bg-greenlobster" >
			<div class="modal-body text-center">
				<h5 class="modal-title text-white" id="statusModalLabel">
				Do you want add {{date('Y',strtotime($startingDate))}} as your Financial Year?
				</h5>
			</div>
			<div class="modal-footer"
				style="border-top:0 none; padding-left: 0px; padding-right: 0px;">
				<div class="row"
					style="width: 100%; padding-left: 0px; padding-right: 0px;">
					<div class="col col-m-12 text-center">
						<button type="button"
							class="btn btn-primary primary-button"
							style="color:#fff !Important;"
							data-dismiss="modal">
							Confirm
						</button>
					</div>
				</div>

				<form id="status-form" action="{{ route('logout') }}"
					method="POST" style="display: none;">
					@csrf
				</form>
			</div>
		</div>
	</div>
</div>

@elseif($msg == 'Overide')
<div class="modal fade" id="showFinancialModal"
	tabindex="-1" role="dialog" aria-labelledby="staffNameLabel"
	aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered  mw-75 w-50" role="document">
		<div class="modal-content modal-inside bg-greenlobster">
			<div class="modal-body text-center">
				<h5 class="modal-title text-white" id="statusModalLabel"
				style="padding: 15px;">Warning: The Financial Year only can be adjusted when there is no single piece of information keyed into the system, once there is a first occurrence, it can’t be changed and will be automated.
				</h5>
			</div>
			<form id="status-form" action="{{ route('logout') }}"
				method="POST" style="display: none;">
				@csrf
			</form>
		</div>
	</div>
</div>

<script type="text/javascript">
$('#date_end').html("-{{date('dMy',strtotime($FY))}}");
setTimeout(function(){
	$("#FYModal").modal('hide');
	$('.modal-backdrop').remove();
	overideFY();
},2500);
</script>

@elseif($msg == 'msg_dialog')
<div class="modal fade" id="showFinancialModal"  tabindex="-1" role="dialog"
	aria-labelledby="" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered  mw-75 w-50"
		role="document">
		<div class="modal-content modal-inside bg-greenlobster">
			<div style="border:0" class="modal-header">&nbsp;</div>
			<div class="modal-body text-center">
				<h5 class="modal-title text-white" id="statusModalLabel">
				{{$text}}
				</h5>
			</div>
			<div style="border:0" class="modal-footer">&nbsp;</div>
		</div>
	</div>
</div>

<style>
.btn {color: #fff !Important;}
</style>

<script type="text/javascript">
	
$('#FYModal').on('hidden.bs.modal', function (e) {
	$('.modal-backdrop').remove();
});

setTimeout(function() {
	$("#FYModal").modal('hide');
	reset_dialog();
	
	$('.modal-backdrop').click(function(){
		$('.modal-backdrop').remove();
	});
	openTab('General');
},2500)
</script>
@endif

