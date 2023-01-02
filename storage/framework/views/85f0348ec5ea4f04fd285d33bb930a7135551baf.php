<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" http-equiv="Content-type" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo e(config('app.name', 'Ocosystem')); ?></title>

    <!-- Styles -->
    <link href="<?php echo e(asset('css/all.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('css/bootstrap.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('css/bootstraptabs.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('css/toastr.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('css/styles.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('css/opos_styles.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('css/jquery.dataTables.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('css/fixedColumns.dataTables.min.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('css/select.dataTables.min.css')); ?>" rel="stylesheet">
</head>
<?php echo $__env->yieldPushContent('styles'); ?>
<body>
    <?php
        $geturl = Request::getPathInfo();
        $exURL = explode("/",$geturl); 
    ?>

<?php if($exURL[1]=='opossum' && $exURL[2]=='terminal'): ?> 
<main style="margin-top:0;height: 100vh;overflow-y:auto; overflow-x:hidden;">
<?php else: ?>
<?php echo $__env->make('common.header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<main style="margin-top:0;">
<?php endif; ?>
    <div class="container-fluid gm"
         style="padding-left:20px;padding-right:20px">
        <?php echo $__env->yieldContent('content'); ?>
    </div>
</main>

<?php if($exURL[1]=='opossum' && $exURL[2]=='terminal'): ?> 
<?php else: ?>
<?php echo $__env->make('common.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php endif; ?>

<script src="<?php echo e(asset('js/jquery-3.4.1.min.js')); ?>"></script>
<script src="<?php echo e(asset('js/bootstrap.js')); ?>"></script>
<script src="<?php echo e(asset('js/jquery.inputmask.bundle.js')); ?>"></script>
<script src="<?php echo e(asset('js/jquery.dataTables.js')); ?>"></script>
<script src="<?php echo e(asset('js/toastr.js')); ?>"></script> 
<script src="<?php echo e(asset('js/button_processing.js')); ?>"></script>
<script src="<?php echo e(asset('js/dataTables.fixedColumns.js')); ?>"></script>
<script src="<?php echo e(asset('js/dataTables.buttons.js')); ?>"></script>
<script src="<?php echo e(asset('js/dataTables.flash.buttons.js')); ?>"></script>
<script src="<?php echo e(asset('js/dataTables.select.min.js')); ?>"></script>
<script src="<?php echo e(asset('js/jszip.min.js')); ?>"></script>
<script src="<?php echo e(asset('js/buttons.js')); ?>"></script>
<script src="<?php echo e(asset('js/og_buttons.js')); ?>"></script>
<script src="<?php echo e(asset('js/Chart.bundle.min.js')); ?>"></script>
<script src="<?php echo e(asset('js/custom.js')); ?>"></script>
<?php echo $__env->yieldContent('scripts'); ?>
<script type="text/javascript">

    $.ajaxSetup({
    headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),

            <?php if(isset($merchant_hash) || !empty(Request()->merchant_hash)): ?> 
            'merchant_hash': "<?php echo e(!empty(Request()->merchant_hash) ? Request()->merchant_hash:$merchant_hash); ?>",
            'X-MERCHANT-HASH': "<?php echo e(!empty(Request()->merchant_hash) ? Request()->merchant_hash:$merchant_hash); ?>" 
            <?php endif; ?>

        },
        statusCode : {
			440: function() {
			   window.location = '/'
			},
            <?php if(isset($merchant_hash) || !empty(Request()->merchant_hash)): ?> 
            200: function() {
                new_tab_event_listen()
            }
            <?php endif; ?>
        },
        async: false
    });

    toastr.options = {
        timeOut: 3,
        positionClass: "toast-top-center"
    };

    var  event_
    <?php if(isset($merchant_hash) || !empty(Request()->merchant_hash)): ?> 
    function new_tab_event_listen() {
		event_ = $("a[target=_blank]").on('click',function(e) {
            e.preventDefault()
            target = e.target
            url = $(target).attr('href');
            modified_url = modified_url_fn(url)
            myWindow = window.open(modified_url, "_blank")
		});

		event_2 = $("a[target=blank]").on('click',function(e) {
			e.preventDefault()
			target = e.target
			url = $(target).attr('href');
			modified_url = modified_url_fn(url)
			myWindow = window.open(modified_url, "_blank")
		});
    }

    $('.landing-view').on('change',function(){
        new_tab_event_listen()
    });
    $('body').on('change',function(){
        new_tab_event_listen()
    });
    $('head').on('change',function(){
        new_tab_event_listen()
    });
    $('html').on('change',function(){
        new_tab_event_listen()
    });

    function merchant_hash_value() {
        return "<?php echo e(!empty(Request()->merchant_hash) ? Request()->merchant_hash:$merchant_hash); ?>" 
    }
    new_tab_event_listen()
    <?php endif; ?>

    const modified_url_fn = function(url) {
        <?php if(isset($merchant_hash) || !empty(Request()->merchant_hash)): ?> 
            merchant_hash = "?&?&merchant_hash="+"<?php echo e(!empty(Request()->merchant_hash) ? Request()->merchant_hash:$merchant_hash); ?>" 
        <?php else: ?>
            merchant_hash = ''
        <?php endif; ?>
        modified_url = url+merchant_hash
        return modified_url
	}

	const headder_link_open = function() {
    	<?php if(isset($merchant_hash) || !empty(Request()->merchant_hash)): ?> 
			url = "/viewmerchant/<?php echo e(request()->id); ?>/processed";
		<?php else: ?>
			url = '/';
		<?php endif; ?>
		window.location = modified_url_fn(url);
	}

	const openNewTabURL = function(url) {
		console.log("URL", url);
		url = modified_url_fn(url);
		console.log("Modified Url", url);
		window.open(url);
		console.log("Opening window");
		window.focus();
	}
	
	setInterval( function() {
		if ($('body').hasClass('modal-open') == false) {
			$(".modal-backdrop").remove();
		}	
	}, 2500);

</script>

<?php echo $__env->yieldContent('js',''); ?>
</body>
</html>
<?php /**PATH E:\ocosystem\resources\views/layouts/layout.blade.php ENDPATH**/ ?>