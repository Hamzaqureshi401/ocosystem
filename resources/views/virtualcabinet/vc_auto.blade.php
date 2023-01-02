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
    width:77px;
    height:77px;
    border-radius:5px;
    text-align:center;
    box-sizing:border-box;
    padding:5px;
    margin:10px 5px 10px 5px;
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
<div id="landing-content" style="width: 100%">
    <div class="clearfix"></div>

    <div class="tab">
    	@foreach ($FYData as $f)
        <button class="tablinks tabcorners" style="width:150px;"
			onclick="openTab(event,'{{$f->id}}')" id="1TabLink">
			{{$loop->index + 1 }}</button>
		@endforeach
    </div>
    <div id="virtualcabinet-tab-contents"></div>

</div>
<script>
function openTab(evt,id) {
    var i, tabcontent, tablinks;

    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }
    evt.currentTarget.className += " active";
    openFY(id);
}

	function openFY(id) {
		url = "{{route('virtualcabinet.ajax.auto',['id'=>'id'])}}".replace('id',id)
		console.log(url)
    $.ajax({
		url: "{{route('virtualcabinet.ajax.auto',['id'=>'id'])}}".replace('id',id),
		type: 'GET',
		dataType: "html",
		success: function (response) {
			$("#virtualcabinet-tab-contents").html(response);
		},
		error: function (e) {
			console.log('error', e);
		}
	});
}


</script>

