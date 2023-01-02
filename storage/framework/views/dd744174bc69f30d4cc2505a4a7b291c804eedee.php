<?php $__env->startSection('content'); ?>
    <div id="landing-view">

    </div>
<?php $__env->stopSection(); ?>


<?php $__env->startSection('scripts'); ?>
<!-- redirect to mobile when page expires -->
<!---button permission---->
<?php echo $__env->make('settings.buttonpermission', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\ocosystem\resources\views/landing/landing.blade.php ENDPATH**/ ?>