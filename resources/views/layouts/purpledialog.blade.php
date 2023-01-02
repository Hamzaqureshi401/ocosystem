<div class="modal fade" id="msgModalPurpleLayout"  tabindex="-1"
     role="dialog" aria-labelledby="staffNameLabel"
     aria-hidden="true" style="text-align: center;">

    <div class="modal-dialog modal-dialog-centered  mw-75 w-50"
         role="document">
        <div class="modal-content modal-inside bg-purplelobster"
			style="width: 100%;
				 background-color: {{@$color}} !important" >
			<div class="modal-header" style="border:0">&nbsp;</div>
            <div class="modal-body text-center">
                <h5 class="modal-title text-white"
					id="statusModalLabel">{!! $msg !!}
				</h5>
            </div>
            <div class="modal-footer"
                 style="border-top:0;padding-left:0;padding-right:0;">
				 &nbsp;
            </div>
        </div>
    </div>
</div>

<style>
/* .btn {color: #fff !Important;} */
</style>

<script type="text/javascript">
    $("#msgModalPurpleLayout").modal('show');

    $('#editUserModal').on('hidden.bs.modal', function (e) {
        $('.modal-backdrop').remove();
    });

    $('.modal-backdrop').click(function(){
        $('.modal-backdrop').remove();
    });

    setTimeout(function() {
        $("#msgModalPurpleLayout").modal('hide');
        $('.modal-backdrop').remove();
    },2500)
</script>
