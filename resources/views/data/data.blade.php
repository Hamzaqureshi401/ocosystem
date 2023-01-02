<style>
    .tabcontent{
        background-color: white;
    }
    .tab {
        border: unset;
    }

    .tab {
        background: white;
    }

    .tabcorners {
        border-top-right-radius: 10px;
        border-top-left-radius: 10px;
    }

    .tab button.active {
        background-color: #fff;
        border: 1px solid #aaa;
        border-bottom: unset;
        font-weight: 700;
        border-right: 0px solid #aaa;
        cursor: pointer !important;
        font-size: 19px;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
    }

    .tab button {
        font-size: 19px;
        border-left: 1px solid #aaa;
        border-top: 1px solid #aaa;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
        padding-top: 8px;
        padding-bottom: 10px;
        background-color: #f0f0f0;
    }

    .tab button:last-child {
        border-right: 1px solid #aaa;
    }

    #tabData {
        margin-left: 5px;
    }
    
.float-btn-right{
    float:right;
    /* width:77px;
    height:77px; */
    /* border-radius:5px; */
    /* text-align:center;
    box-sizing:border-box; */
    /* padding:5px;
    margin:10px 5px 10px 5px; */
}

.mg-top-15{
    margin-top:25px;
}

.bg-bluecrab{
    border-radius:5px;
    margin:auto;
    width:25px;
    height:25px;
    display:block;
    cursor: pointer;
}

.btn-link:hover{
    text-decoration:none;
}

.btn-link:focus{
    text-decoration:none;
}
</style>
<div id="landing-content" style="width: 100%">
    <div class="clearfix"></div>

    <div class="tab">
        <button class="tablinks tabcorners" style="width:150px;" onclick="openTab(event,'supplier')" id="supplierTabLink">Supplier</button>
		<button class="tablinks tabcorners"  style="width: 150px;" onclick="openTab(event,'dealer')" id="dealerTabLink">Dealer</button>
    </div>
    <p id="type_merchant" style="display: none;"></p>
    <div id="data-tab-contents"></div>
    <style>
        .modal-add-style {
            text-decoration: underline blue;
            cursor: pointer;
        }

        .tabcontent {
            border: unset !important;
        }

        .modal-lg{
            width:100%;
        }

    </style>

</div>
<script>
    $(document).ready(function() {
        $("main").css('height','');
        $("main").css('min-height','400px');
    });
function openTab(evt, cityName) {
    var i, tabcontent, tablinks;
    // tabcontent = document.getElementsByClassName("tabcontent");
    // for (i = 0; i < tabcontent.length; i++) {
    //     tabcontent[i].style.display = "none";
    // }
    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }
    $('#type_merchant').html(cityName);
    //document.getElementById(cityName).style.display = " block";
    evt.currentTarget.className += " active";
}

$('#supplierTabLink').click(function(){
    $.ajax({
		url: "{{route('data.ajax.supplier')}}",
		type: 'GET',
		dataType: "html",
		success: function (response) {
			$("#data-tab-contents").html(response);
		},
		error: function (e) {
			console.log('error', e);
		}
	});
})

$('#dealerTabLink').click(function(){
    $.ajax({
		url: "{{route('data.ajax.dealer')}}",
		type: 'GET',
		dataType: "html",
		success: function (response) {
			$("#data-tab-contents").html(response);
		},
		error: function (e) {
			console.log('error', e);
		}
	});
})
    // $(document).ready( function () {
    //     $('#supplierDataTbl').DataTable();
    // } );
</script>

