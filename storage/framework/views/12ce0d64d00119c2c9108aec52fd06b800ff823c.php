
<!-- Modal Logout-->
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog"
	style="padding-right:0 !important"
	aria-labelledby="logoutModalLabel"
	aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered  mw-75 w-50" role="document">
        <div class="modal-content modal-inside bg-greenlobster">
            <div style="border:0" class="modal-header"></div>
            <div class="modal-body text-center">
                <h5 class="modal-title text-white" id="logoutModalLabel">
				Do you really want to logout?
				</h5>
            </div>
            <div class="modal-footer"
			style="border-top:0 none; padding-left: 0px; padding-right: 0px;">
                <div class="row"
					style="width: 100%; padding-left: 0px;
					padding-top:15px !important;padding-bottom:15px !important;
					padding-right: 0px;">
                    <div class="col col-m-12 text-center">
                        <a class="btn btn-primary" href="<?php echo e(route('logout')); ?>"
							style="width:100px"
							onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            Yes
                        </a>
                        <button type="button" class="btn btn-danger"
							data-dismiss="modal" style="width:100px">No
                        </button>
                    </div>
                </div>

                <form id="logout-form" action="<?php echo e(route('logout')); ?>"
					method="POST" style="display: none;">
                    <?php echo csrf_field(); ?>
                </form>
            </div>
        </div>
    </div>
</div>


<footer class="footer">
    <div class="container">
        <span class="text-muted">
        </span>
    </div>
</footer>
<?php /**PATH E:\ocosystem\resources\views/common/footer.blade.php ENDPATH**/ ?>