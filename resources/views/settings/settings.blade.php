<div class="col align-self-end"
	style="width:100%;padding-left: 0px;padding-right: 0px;">
</div>

<div class="clearfix"></div>
<div class="tab">
	<button class="tablinks tabcorners"
		style="width:150px;"
		id="General" onclick="openTab('General')">
		General
	</button>
	<!--
	<button class="tablinks tabcorners "
		style="width:150px;"
		id="Target" onclick="openTab('Target')">
		Target
	</button>
	<button class="tablinks tabcorners"
		style="width:150px;"
		id="Campaign" onclick="openTab('Campaign')">
		Campaign
	</button>
	-->
	<button class="tablinks tabcorners"
		style="width:190px;"
		id="HeaderFooter" onclick="openTab('HeaderFooter')">
		Header & Footer
	</button>
	<!--
 	<button class="tablinks tabcorners"
		style="width:150px;"
		id="function" onclick="openTab('Function')">
		Function
	</button>
	-->
	<button class="tablinks tabcorners"
			style="width:150px;"
			id="function" onclick="openTab('Barcode')">
		Barcode
	</button>

	<button class="tablinks tabcorners"
			style="width:210px;"
			id="function" onclick="openTab('Loyalty_programme')">
			Loyalty Programme
	</button>

</div>
<div id='tabData'></div>

<style>
    .tabcontent,.tab {border:unset;}
	.tab{
		background:white; 
		/*
		border-bottom: 1px solid #d0d0d0;
		*/
	}

	.tabcorners {
		border-top-right-radius:10px;
		border-top-left-radius:10px;
	}

    .tab button.active {
		background-color: #fff;/*#e7e7e7;*/
	/*	border-top: #000 1px solid;
		border-left: #000 1px solid;*/
		border: 1px solid #aaa;
		border-bottom: unset;
		font-weight: 700;
		border-right: 0px solid #aaa;
		cursor: pointer !important;
		font-size: 19px;
		border-top-left-radius:10px;
		border-top-right-radius:10px;
	}

	.tab button {
		font-size: 19px;
		border-left: 1px solid #aaa;
		border-top: 1px solid #aaa;
		border-top-left-radius:10px;
		border-top-right-radius:10px;
		padding-top:8px;
		padding-bottom:10px;
		background-color:#f0f0f0;
	}

	.tab button:last-child { border-right:1px solid #aaa;}

	#tabData {margin-left:5px;}
	.modal-body > .form-group {margin-bottom:0!important;}
</style>

<script type="text/javascript">
	function openTab(tabName) {
		var route = null;

		//Tab routes here
		switch (tabName) {
			case 'General':
				route = '{{route('landing.ajax.generalSetting')}}';		
				break;
			case 'Target':
				route = '{{route('landing.ajax.target')}}';		
				break;
			case 'Campaign':
				route = '{{route('landing.ajax.campaign')}}';		
				break;
			case 'HeaderFooter':
				route = '{{route('landing.ajax.HeaderFooter')}}';
				break;
 			case 'Function':
				route = '{{route('landing.ajax.function')}}';
				break;
			case 'Barcode':
				route = '{{route('landing.ajax.Barcode')}}';
				break;
			case 'Loyalty_programme':
				route = '{{route('landing.ajax.loyalty_programme')}}';
				break;
			default:
				route = null;
		}

		if (route != null) {
		   $.ajax({
                url: route,
                type: 'get',
                success: function (response) {
                	$( ".tab > button" ).removeClass("active");
					$( "#"+tabName ).addClass( "active" );
                	$('#tabData').html(response);
                },
                error: function (e) {
                    console.log('Error:'+e);
                }
			});
		} else {
			console.log('Invalid tab');
		}
	}
</script>
<br/><br/><br/>
