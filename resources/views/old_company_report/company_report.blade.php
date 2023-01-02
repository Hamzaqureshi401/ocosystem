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
.modal {
    text-align: center;
}

@media screen and (min-width: 768px) {
	.modal:before {
		display: inline-flex;
		vertical-align: middle;
		content: " ";
		height: 100%;
	}
}

.modal-dialog {
	display: inline-flex;
	text-align: left;
	vertical-align: middle;
	width: 500px;
}
.modal-add-style {
        text-decoration: underline blue;
        cursor: pointer;
    }

.tabcontent {
    border: unset !important;
}
</style>

<div id="landing-content" style="width: 100%">
    <div class="clearfix"></div>

    <div class="tab">
        <button class="tablinks tabcorners" style="width:150px;" onclick="openTab(event); directoryView()">Directory</button>
    </div>
 <div id="response_data_directory" class="tabcontent p-0"></div>
    <!--- End of staff -->

<script>

function directoryView() {
    $.ajax({
        url: "{{route('report.ajax.directory_list')}}",
        type: 'GET',
        dataType: "html",
        success: function (response) {
            $("#response_data_directory").html(response);
        },
        error: function (e) {
            console.log('error', e);
        }
    });
}

function openTab(evt) {
    var target = evt.target;
    document.getElementById('response_data_directory').style.display = " block";
	$( ".tab > button" ).removeClass("active");
    $( target ).addClass( "active" );
}


</script>

</div>