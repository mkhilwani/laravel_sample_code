/* form validation */
$(document).ready(function() {
    $('#mytastingnote').validate({
        ignore: [],
        rules: {
            tasting_note_added_date: {
                required: true
            },
            tasting_note: {
                required: function() {
                    CKEDITOR.instances.tasting_note.updateElement();
                }
            }
        },
        messages: {
            required: "this field is required",
            tasting_note: {
                required: "Please enter a tasting notes",
                minlength: "Tasting notes must consist of at least 12 characters"
            },
        },
        /* use below section if required to place the error*/
        errorPlacement: function(error, element) {
            if (element.attr("name") == "tasting_note") {
                error.insertBefore("textarea#tasting_note");
            } else {
                error.insertBefore(element);
            }
        }


    });

    $('#mytastingsubmit').click(function() {
        if ($("#mytastingnote").valid()) {
            /*my tasting insert here*/
            mytasting();
        }
    });
});


/*onchange scale functionality works in backend*/
$(document).ready(function() {
    $("#scale").unbind('change').bind('change', function() {
        var score = $(this).val();
        var myArray = score.split('_');
        //  alert(myArray[0]);
        $("#scales").val(score);
        if (myArray[1].indexOf('point') >= 0) {

            $("#text-score_0").show();
            $("#related_data").show();
            $("#related_data").html(myArray[0]);
            $("#star-rater_0").hide();

        }
        else {
            $("#star-rater_0").show();
            $("#text-score_0").hide();

            if (myArray[1].indexOf('3') >= 0) {
                $("#related_data").hide();
                $('#star-rater_0').raty({
                    numberMax: 3,
                    number: 100,
                    half: true,
                    click: function(score, evt) {
                        $("#star_score").val(score);
                        $("#input_score").val('');
                    }
                });
            }
            else {
                $("#related_data").hide();
                $('#star-rater_0').raty({
                    numberMax: 5,
                    number: 100,
                    half: true,
                    click: function(score, evt) {
                        $("#star_score").val(score);
                        $("#input_score").val('');
                    }
                });
            }
        }

    });
});

/*tab for my tasting notes div tab*/
$(document).ready(function() {
    $('#tastingnotes').click(function() {
        $('#add_tasting_notes').show();

    });
});

/*enter score related check in backend*/
$(document).ready(function() {
    $("#text-score_0").blur(function() {
        var textValues = $(this).val();
        var score = $("#scale").val();
        var num = new Number(textValues);
        var myArray1 = score.split('_');
        var myArray = myArray1[0].split('-');
        if (!(num >= myArray[0] && num <= myArray[1])) {
            $("#text-score_0").val('');
            BootstrapDialog.alert('Please enter score between ' + myArray1[0] + ' scores only');
        } else {

            $("#input_score").val($("#text-score_0").val());
            $("#star_score").val('');
        }

    });
});


/*tag related code in backend*/
$(function() {

    $('#tasting_tags').tagsInput({width: 'auto'});
});

/* tooltip functionality in frontend details page*/
$(document).ready(function() {

    $("#tooltip").click(function() {

        $("#tooltip").tooltip('show');

    });

});


/* Datepicker function*/
$(function() {
    $('#datetimepicker1').datetimepicker({
        format: 'YYYY-MM-DD',
        pickTime: false, use24hours: false
    });
});

/*my tasting records insert*/
function mytasting() {
    var str = $("form#mytastingnote").serialize();
    alert(str);
    $.ajax({
        type: "post",
        url: 'mytastingnote',
        dataType: "json",
        data: str,
        success: function(response) {

        },
    });
}

/* wine post category wise records listing */
$(document).ready(function() {

    $(".wine_post_category").click(function() {
        var CatId = $(this).attr("CatId");
        $.ajax({
            type: "post",
            url: '/winepost/category',
            data: {CatId: CatId},
            success: function(response) {
                $('#winepostlist').html(response.html);
            },
            error: function() {
                console.log(response);
            }
        });

    });

});

/* delete in winepost list */
$(document).on('click', '.del_link', function() {
    var id = $(this).attr("del");
    BootstrapDialog.confirm('Are you sure you want to delete this wine post?', function(result) {
        if (result) {
            window.location.href = "/winepost/delete/details/" + id;
        }
    });
});

/***
 * Data Append in winepost list page
 * Date : 7th October 
 * Ashish Ranpara
 */
$(window).scroll(function() {
     var divHeight = $('.userwinepostlist').height();
    // alert("divHeight"+divHeight);
//    if($(window).scrollTop() + $(window).height() > $(document).height() -100){
  if ($(window).scrollTop() == $(document).height() - $(window).height()) {
    // if ($(window).scrollTop() == divHeight - 100) {  
        //alert("scrool top="+$(document).height()+"wh="+$(window).height());
        //for listing
        var LastDiv = $(".winepost:last"); /* get the last div of the dynamic content using ":last" */
        var LastId = $(".winepost:last").attr("id"); /* get the id of the last div */

        //for search
        var categoryAppend = $(".cat_search:last"); /* get the last div of the dynamic content using ":last" */
        var catId = $(".cat_search:last").attr("id"); /* get the id of the last div */
        var wineId = $(".cat_search:last").attr("wid");

        
        if (LastId != '' && LastId != null) {
           var statusId = $("#nomore").attr("status");
            if (statusId > 0) {
            // var ValueToPass = "lastid=" + LastId + "&tagid=" + tid; /* create a variable that containing the url parameters which want to post to getdata.php file */
            $.ajax({
                type: "POST",
                url: "/winepost/winepostlists",
                data: {lastId: LastId},
                cache: false,
                success: function(response) {
                    console.log(response);
                    $("#loader").html("");
                   if (response.error == "false") {
                        LastDiv.after(response.html);
                    }else {
                            $("#more").html("");
                            $("#more").removeClass("alert alert-success");
                            $("#nomore").addClass("alert alert-danger");
                            $("#nomore").html('No more records');
                            $("#nomore").attr("status", response.status)
                        }
                }
            });
          }
        }
        else if (catId != '' && catId != null) {
            var statusId = $("#nomore").attr("status");
            if (statusId > 0) {
                //  alert("test"+LastId);
                // var ValueToPass = "lastid=" + LastId + "&tagid=" + tid; /* create a variable that containing the url parameters which want to post to getdata.php file */
                $.ajax({
                    type: "POST",
                    url: "/winepost/categorylist",
                    data: {CatId: catId, wineId: wineId},
                    cache: false,
                    success: function(response) {
                        console.log(response);
                        $("#loader").html("");
                        if (response.error == "false") {
                            categoryAppend.after(response.html);
                        } else {
                            $("#more").html("");
                            $("#more").removeClass("alert alert-success");
                            $("#nomore").addClass("alert alert-danger");
                            $("#nomore").html('No more records');
                            $("#nomore").attr("status", response.status)
                        }
                    }
                });
            }
        }
    }
});

//$('.loadMore').click(function () {
//    $('.loadMore').fadeOut(200);
//    $('.container').append(content); // PUT YOUR AJAX REQUEST HERE INSTEAD
//});




//
// $(document).ready(function() {
//    $(window).scroll(function() { /* window on scroll run the function using jquery and ajax */
//        var divHeight = $('.userwinepostlist').height();
//
//        var WindowHeight = $(window).height(); /* get the window height */
//        if ($(window).scrollTop() == divHeight - 100) {           
//            /* check is that user scrolls down to the bottom of the page */
//            alert("in");
//            $("#loader").html("<img src='assets/images/image.gif' alt='loading'/>"); /* displa the loading content */
//
//            //for tag wise listing
//            var LastDiv = $(".winepost:last"); /* get the last div of the dynamic content using ":last" */
//            var LastId = $(".winepost:last").attr("id"); /* get the id of the last div */
//
//           
//
//            if (LastId != '' && LastId != null) {
//                var tid = $('#tagid').val();
//                alert("test"+LastId);
//               // var ValueToPass = "lastid=" + LastId + "&tagid=" + tid; /* create a variable that containing the url parameters which want to post to getdata.php file */
//                $.ajax({
//                    type: "POST",
//                    url: "/winepost/getlisting",
//                    data: {lastId:LastId},
//                    cache: false,
//                    success: function(html) {
//                        $("#loader").html("");
//                        if (html) {
//                            LastDiv.after(html);
//                        }
//                    }
//                });
//            }
//           
//            return false;
//        }
//        
//        return false;
//    });
//});
