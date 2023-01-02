<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<!-- CSRF Token -->
<meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

<title><?php echo e(config('app.name', 'OPOSsum')); ?></title>

<style>
body {
	margin: 0;
	font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
	color: #212529;
	text-align: left;
	background-color: #fff;
}
.bg-refund{
	color:#fff;
	background:#ff7e30;
	border-color:#ff7e30
}
.table{
	width: 100%!important;
	border-style: none;
}
.thead-dark {
	color: white;
	border-color: #343a40;
	background-color: #343a40;

}
#table-th th{
	font-size: 12px!important;
}
.text-center{
	text-align: center;
}
.text-left{
	text-align: left;
}
.text-right{
	text-align: right;
}
.table-td td{
	font-size: 12px!important;
}
p {
	margin-top: 0;
	margin-bottom: 0rem;
	font-size: 12px;
}

hr{
	margin-bottom: 0;
}
#item tr  td {
	padding-top: 6px !important;
	padding-bottom: 6px !important;
	vertical-align: middle !important;
}
#item tr th{
	font-size: 12px;
	padding-top: 8px !important;
	padding-bottom: 8px !important;
	vertical-align: middle !important;
}

td{
	border-style: none;
}
th{
	border-style: none;
}

.text-bold {
	font-weight: bold;
	font-size: 12px;
}

span {
	font-size: 12px;
}

tr td span{
	/*width: 80px !important;*/
	/*height: 60px !important;*/
	text-align: center;
	vertical-align: middle;
	font-size: 12px;
	cursor: pointer;
	padding: 10px 20px;
	color: black;
	display: inline-block;
	font-weight: 400;
	margin-top: 10px;
}
.active{
	border-radius: 10px;
	color: white;
	padding: 10px 25px;
	background-color: black;
}
.rad-info-box .heading {
	font-size: 1.2em;
	font-weight: 300;
	text-transform: uppercase;
}
</style>
</head>

<body>

	  <table border="0" style="width:100%; border-collapse: collapse" cellspacing="0" cellpadding="0">
        <tr>
            <td valign="center" rowspan="2" colspan="2">
			<b style="font-size:30px;font-weight:700;word-wrap:normal;">
			Electric Vehicle Charger
			</b>
			<br>
			<p style="font-size:18px;">
			Itemized Sales Report
			</p>
			</td>
            <td valign="bottom" colspan="3" align="right" style="font-size: 15px">
                <?php echo e($data['branch']); ?><br><?php echo e($data['systemid']); ?>

            </td>
        </tr>

        <tr>
            <td valign="bottom" colspan="5" align="right">
                <p style="font-weight: 700;font-size: 12px">
                   <?php echo e($data['startdate']); ?> - 
                   <?php echo e($data['enddate']); ?>

                </p>

            </td>
        </tr>
    </table>

    <table border="0" cellpadding="0" cellspacing="0" class="table" id="item" style="margin-top: 5px; width:100%">
        <thead class="thead-dark">
        <tr id="table-th" style="border-style: none">
            <th valign="middle" class="text-center" style="width:5%;">No</th>
            <th valign="middle" class="text-left" style="width:20%;">Product</th>
            <th valign="middle" class="text-center" style="width:10%">Price</th>
            <th valign="middle" class="text-center" style="width:10%;">Qty</th>
            <th valign="middle" class="text-center" style="width:10%;">Tax&nbsp;</th>
			<th valign="middle" class="text-center" style="width:10%;">Sales</th>
			<th valign="middle" class="text-center" style="width:10%;">Amount</th>
			<th valign="middle" class="text-center" style="width:10%;">Paid By</th>

        </tr>
        </thead>
       
            <tr class="table-td">
                <td class="text-center" style="border-style: none">
					<?php echo e("1"); ?>

				</td>
                <td class="text-left" style="border-style: none">
					<?php echo e("EV-Charger Product"); ?>

				</td>
                <td class="text-center" style="border-style: none">
                    <?php echo e("999,999.99"); ?>

                </td>
                <td style="text-align:center;border-style: none">
					<?php echo e("1"); ?>

                </td>

                <td style="text-align:center;border-style: none">
                    <?php echo e("999,999.99"); ?>

                </td>
                 <td style="text-align:center;border-style: none">
                    <?php echo e("999,999.99"); ?>

                </td>
                 <td style="text-align:center;border-style: none">
                    <?php echo e("999,999.99"); ?>

                </td>
                 <td style="text-align:center;border-style: none">
                    <?php echo e("60"); ?>

                </td>

            </tr>
        
    </table>

	
</body>
</html>
<?php /**PATH E:\ocosystem\resources\views/itemized_sales_report/evcharger_pdf.blade.php ENDPATH**/ ?>