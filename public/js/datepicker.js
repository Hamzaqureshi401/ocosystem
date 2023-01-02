store_date = dateToYMDEmpty(new Date());
$("#trans_start_date").val(store_date);
$("#trans_end_date").val(store_date);
$("#rev_start_date").val(store_date);
$("#rev_end_date").val(store_date);

var StartDate = "";
var EndDate = "";

localStorage.removeItem("startTransDate")
localStorage.removeItem("endTransDate")
localStorage.removeItem("startRevDate")
localStorage.removeItem("endRevDate")

function dateToYMDEmpty(date) {
   
	var strArray=['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
	var d = date.getDate();
	var m = strArray[date.getMonth()];
	var y = date.getFullYear().toString().substr(-2);
    // alert(m)
	var currentHours = date.getHours();
	return '' + (d <= 9 ? '0' + d : d) + '' + m + '' + y + '';
}

var start_date_dialog = osmanli_calendar;
var completion_date_dialog = osmanli_calendar;
var terminal_date= osmanli_calendar.MIN_DATE;


// showTransStartDate
function showTransStartDate(e) {
    // alert("yessss")
    sessionStorage.removeItem("modalTrue");
    sessionStorage.setItem("modalTrue",'showTransStartDate');

    date = new Date();
    start_date_dialog.MAX_DATE = date;
    start_date_dialog.DAYS_DISABLE_MIN = "ON";
    start_date_dialog.DAYS_DISABLE_MAX = "ON";
    //start_date_dialog.MIN_DATE = new Date();
    $('.next-month').off();
    $('.prev-month').off();

    $('.prev-month').click(function () {start_date_dialog.pre_month()});
    $('.next-month').click(function () {start_date_dialog.next_month()});

    start_date_dialog.CURRENT_DATE = new Date();

    if(localStorage.getItem("startTransDate")===null)
    {
       
         start_date_dialog.SELECT_DATE = new Date()
    } else{
        var loclaaa=  localStorage.getItem("startTransDate");
        // console.log( loclaaa)
        // start_date_dialog.CURRENT_DATE =new Date(localStorage.getItem("startTransDate"));
        start_date_dialog.SELECT_DATE = new Date(localStorage.getItem("startTransDate"))
        start_date_dialog.CURRENT_DATE = new Date(localStorage.getItem("startTransDate"))


        
        // console.log(start_date_dialog.SELECT_DATE)
    }
        var date =   start_date_dialog.SELECT_DATE.getDate();
        const monthNames = ["January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"
        ];
        var month  =  monthNames[start_date_dialog.SELECT_DATE .getMonth()];
        var year = start_date_dialog.SELECT_DATE.getFullYear();
        var select_moth_year  =  month+" "+year
        var date =   start_date_dialog.SELECT_DATE.getDate();
        sessionStorage.setItem("date_check",date);
        sessionStorage.setItem("select_moth_year",select_moth_year);


    if(date == 1){
        start_date_dialog.CURRENT_DATE.setDate(4)
        start_date_dialog.ON_SELECT_FUNC = function(){
            var date = osmanli_calendar.SELECT_DATE;
            localStorage.setItem("startTransDate",date)
            var start_date = dateToYMDEmpty(date);
            // console.log(start_date)
            
            // localStorage.setItem("sTransDate",start_date)
    
            $("#trans_start_date").val(start_date);
            jQuery('#showDateModal').modal('hide');
        }

    }else{
        start_date_dialog.ON_SELECT_FUNC = function(){
            var date = osmanli_calendar.SELECT_DATE;
            localStorage.setItem("startTransDate",date)
            var start_date = dateToYMDEmpty(date);
            console.log(start_date)
            
            // localStorage.setItem("sTransDate",start_date)
    
            $("#trans_start_date").val(start_date);
            jQuery('#showDateModal').modal('hide');
        }
    }

    
    start_date_dialog.init()
    if(date == 1){
        var table_data =  $(".date_table tbody tr").eq(1)
        table_data.children('td').each(function(){
        var data = $(this).html();
            if(data== 1){
                $(this).addClass("selected_date")   

            }
        })
    }
    jQuery('#showDateModal').modal('show');

}
//end showTransStartDate
var EndDate = new Date();

// showTransEndDate
function showTransEndDate(e) {

    // $("#trans_end_date").val("nb");

    sessionStorage.removeItem("modalTrue");
    sessionStorage.setItem("modalTrue",'showTransEndDate');

    date = new Date();
    start_date_dialog.MAX_DATE = date;
    start_date_dialog.DAYS_DISABLE_MIN = "ON";
    start_date_dialog.DAYS_DISABLE_MAX = "ON";
    //start_date_dialog.MIN_DATE = new Date();
    $('.next-month').off();
    $('.prev-month').off();

    $('.prev-month').click(function () {start_date_dialog.pre_month()});
    $('.next-month').click(function () {start_date_dialog.next_month()});

    start_date_dialog.CURRENT_DATE = new Date();

    if(localStorage.getItem("endTransDate")===null)
    {
        // alert("end session has no  data")
       
         start_date_dialog.SELECT_DATE = new Date()
    } else{
        // alert("end session has data")
        var loclaaa=  localStorage.getItem("endTransDate");
        // console.log( loclaaa)
        // start_date_dialog.CURRENT_DATE =new Date(localStorage.getItem("startTransDate"));
        start_date_dialog.SELECT_DATE = new Date(localStorage.getItem("endTransDate"))
        start_date_dialog.CURRENT_DATE = new Date(localStorage.getItem("endTransDate"))


        
        // console.log(start_date_dialog.SELECT_DATE)
    }
        var date =   start_date_dialog.SELECT_DATE.getDate();
        const monthNames = ["January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"
        ];
        var month  =  monthNames[start_date_dialog.SELECT_DATE .getMonth()];
        var year = start_date_dialog.SELECT_DATE.getFullYear();
        var select_moth_year  =  month+" "+year
        var date =   start_date_dialog.SELECT_DATE.getDate();
        sessionStorage.setItem("date_check",date);
        sessionStorage.setItem("select_moth_year",select_moth_year);


    if(date == 1){
        // alert("date 1 if")
        start_date_dialog.CURRENT_DATE.setDate(4)
        start_date_dialog.ON_SELECT_FUNC = function(){
            var date = osmanli_calendar.SELECT_DATE;
            
            localStorage.setItem("endTransDate",date)
            var start_date = dateToYMDEmpty(date);
            // console.log(start_date)
            
            // localStorage.setItem("sTransDate",start_date)
    
            $("#trans_end_date").val(start_date);
            jQuery('#showDateModal').modal('hide');
        }

    }else{
        // alert("date 1 else")

        start_date_dialog.ON_SELECT_FUNC = function(){
            var date = osmanli_calendar.SELECT_DATE;
            
            localStorage.setItem("endTransDate",date)
            var start_date = dateToYMDEmpty(date);
            // console.log(start_date)
            
            // localStorage.setItem("sTransDate",start_date)
    
            $("#trans_end_date").val(start_date);
            jQuery('#showDateModal').modal('hide');
        }
    }

    
    start_date_dialog.init()
    if(date == 1){
        var table_data =  $(".date_table tbody tr").eq(1)
        table_data.children('td').each(function(){
        var data = $(this).html();
            if(data== 1){
                $(this).addClass("selected_date")   

            }
        })
    }
    jQuery('#showDateModal').modal('show');

}

//end showTransEndDate
// var EndDate = new Date();



// showRevEndDate
function showRevEndDate(e) {
  
    // $("#trans_end_date").val("nb");

    sessionStorage.removeItem("modalTrue");
    sessionStorage.setItem("modalTrue",'endRevDate');

    date = new Date();
    start_date_dialog.MAX_DATE = date;
    start_date_dialog.DAYS_DISABLE_MIN = "ON";
    start_date_dialog.DAYS_DISABLE_MAX = "ON";
    //start_date_dialog.MIN_DATE = new Date();
    $('.next-month').off();
    $('.prev-month').off();

    $('.prev-month').click(function () {start_date_dialog.pre_month()});
    $('.next-month').click(function () {start_date_dialog.next_month()});

    start_date_dialog.CURRENT_DATE = new Date();

    if(localStorage.getItem("endRevDate")===null)
    {
        // alert("end session has no  data")
       
         start_date_dialog.SELECT_DATE = new Date()
    } else{
        // alert("end session has data")
        var loclaaa=  localStorage.getItem("endRevDate");
        // console.log( loclaaa)
        // start_date_dialog.CURRENT_DATE =new Date(localStorage.getItem("startTransDate"));
        start_date_dialog.SELECT_DATE = new Date(localStorage.getItem("endRevDate"))
        start_date_dialog.CURRENT_DATE = new Date(localStorage.getItem("endRevDate"))


        
        // console.log(start_date_dialog.SELECT_DATE)
    }
        var date =   start_date_dialog.SELECT_DATE.getDate();
        const monthNames = ["January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"
        ];
        var month  =  monthNames[start_date_dialog.SELECT_DATE .getMonth()];
        var year = start_date_dialog.SELECT_DATE.getFullYear();
        var select_moth_year  =  month+" "+year
        var date =   start_date_dialog.SELECT_DATE.getDate();
        sessionStorage.setItem("date_check",date);
        sessionStorage.setItem("select_moth_year",select_moth_year);


    if(date == 1){
        // alert("date 1 if")
        start_date_dialog.CURRENT_DATE.setDate(4)
        start_date_dialog.ON_SELECT_FUNC = function(){
            var date = osmanli_calendar.SELECT_DATE;
            
            localStorage.setItem("endRevDate",date)
            var start_date = dateToYMDEmpty(date);
            // console.log(start_date)
            
            // localStorage.setItem("sTransDate",start_date)
    
            $("#rev_end_date").val(start_date);
            jQuery('#showDateModal').modal('hide');
        }

    }else{
        // alert("date 1 else")

        start_date_dialog.ON_SELECT_FUNC = function(){
            var date = osmanli_calendar.SELECT_DATE;
            
            localStorage.setItem("endRevDate",date)
            var start_date = dateToYMDEmpty(date);
            // console.log(start_date)
            
            // localStorage.setItem("sTransDate",start_date)
    
            $("#rev_end_date").val(start_date);
            jQuery('#showDateModal').modal('hide');
        }
    }

    
    start_date_dialog.init()
    if(date == 1){
        var table_data =  $(".date_table tbody tr").eq(1)
        table_data.children('td').each(function(){
        var data = $(this).html();
            if(data== 1){
                $(this).addClass("selected_date")   

            }
        })
    }
    jQuery('#showDateModal').modal('show');

}
// showRevEndDate


// showRevStartDate
function showRevStartDate(e) {
  
    // $("#trans_end_date").val("nb");

    sessionStorage.removeItem("modalTrue");
    sessionStorage.setItem("modalTrue",'startRevDate');

    date = new Date();
    start_date_dialog.MAX_DATE = date;
    start_date_dialog.DAYS_DISABLE_MIN = "ON";
    start_date_dialog.DAYS_DISABLE_MAX = "ON";
    //start_date_dialog.MIN_DATE = new Date();
    $('.next-month').off();
    $('.prev-month').off();

    $('.prev-month').click(function () {start_date_dialog.pre_month()});
    $('.next-month').click(function () {start_date_dialog.next_month()});

    start_date_dialog.CURRENT_DATE = new Date();

    if(localStorage.getItem("startRevDate")===null)
    {
        // alert("end session has no  data")
       
         start_date_dialog.SELECT_DATE = new Date()
    } else{
        // alert("end session has data")
        var loclaaa=  localStorage.getItem("startRevDate");
        // console.log( loclaaa)
        // start_date_dialog.CURRENT_DATE =new Date(localStorage.getItem("startTransDate"));
        start_date_dialog.SELECT_DATE = new Date(localStorage.getItem("startRevDate"))
        start_date_dialog.CURRENT_DATE = new Date(localStorage.getItem("startRevDate"))


        
        // console.log(start_date_dialog.SELECT_DATE)
    }
        var date =   start_date_dialog.SELECT_DATE.getDate();
        const monthNames = ["January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"
        ];
        var month  =  monthNames[start_date_dialog.SELECT_DATE .getMonth()];
        var year = start_date_dialog.SELECT_DATE.getFullYear();
        var select_moth_year  =  month+" "+year
        var date =   start_date_dialog.SELECT_DATE.getDate();
        sessionStorage.setItem("date_check",date);
        sessionStorage.setItem("select_moth_year",select_moth_year);


    if(date == 1){
        // alert("date 1 if")
        start_date_dialog.CURRENT_DATE.setDate(4)
        start_date_dialog.ON_SELECT_FUNC = function(){
            var date = osmanli_calendar.SELECT_DATE;
            
            localStorage.setItem("startRevDate",date)
            var start_date = dateToYMDEmpty(date);
            // console.log(start_date)
            
            // localStorage.setItem("sTransDate",start_date)
    
            $("#rev_start_date").val(start_date);
            jQuery('#showDateModal').modal('hide');
        }

    }else{
        // alert("date 1 else")

        start_date_dialog.ON_SELECT_FUNC = function(){
            var date = osmanli_calendar.SELECT_DATE;
            
            localStorage.setItem("startRevDate",date)
            var start_date = dateToYMDEmpty(date);
            // console.log(start_date)
            
            // localStorage.setItem("sTransDate",start_date)
    
            $("#rev_start_date").val(start_date);
            jQuery('#showDateModal').modal('hide');
        }
    }

    
    start_date_dialog.init()
    if(date == 1){
        var table_data =  $(".date_table tbody tr").eq(1)
        table_data.children('td').each(function(){
        var data = $(this).html();
            if(data== 1){
                $(this).addClass("selected_date")   

            }
        })
    }
    jQuery('#showDateModal').modal('show');

}
// showRevStartDate
function extractExcel(isExcel){
 
    if(isExcel==1){
        var startDate = $("#trans_start_date").val();
        var endDate = $("#trans_end_date").val();

        data ="startDate="+startDate+"&endDate="+endDate+"&istab=transaction"
        location.assign($Route+"/get-generate-excel?"+data);        

        // httpExcel("#trans_start_date","#trans_end_date","transaction")
    }
    else if(isExcel==2){
        var startDate = $("#rev_start_date").val();
        var endDate = $("#rev_end_date").val();

        data ="startDate="+startDate+"&endDate="+endDate+"&istab=revenue"
        location.assign($Route+"/get-generate-excel?"+data);        

    }
    else if(isExcel==3){
        data ="istab=uptime"
        location.assign($Route+"/get-generate-excel?"+data);        

    }
    

}

function httpExcel(from,to,istab){
// alert("helo")

    if(istab===2)
    {
        data ="istab="+istab
        location.assign($Route+"/get-generate-excel?"+data);        
 
    }else{
        var startDate = $(from).val();
        var endDate = $(to).val();

        data ="startDate="+startDate+"&endDate="+endDate+"&istab="+istab
        location.assign($Route+"/get-generate-excel?"+data);        
    }

    
    // $.ajax({
    //     url: $Route+"/get-generate-excel",
    //     data:data,
    //     success:function(response){
            // location.assign($Route+"/get-generate-excel");            
    //     }
    // })
    // .done(downloadFile);

}

function downloadFile(response) {
    var blob = new Blob([response], {type: 'application/octetstream'})
    // window.location.href=$Route+"/"+response;
     var url = URL.createObjectURL(blob);
    
    location.assign(url);
}

$(".modal-body div:first").on("click" , function(){

    var change_month_year = $(".modal-body div:first .col-md-8 .month-year h3").html()
    var select_moth_year = sessionStorage.getItem("select_moth_year");
    var date = sessionStorage.getItem("date_check");
   
    if(date == 1){
        if(change_month_year  == select_moth_year ){
        var table_data =  $(".date_table tbody tr").eq(1)
        table_data.children('td').each(function(){
            var data = $(this).html();
                if(data== 1){
                    $(this).addClass("selected_date")   
   
                }
            })
    
        }else{
            $(".selected_date").removeClass("selected_date")

        }
    }
  
})