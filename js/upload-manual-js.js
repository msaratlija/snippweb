
$(document).ready(function(){

    
    new Clipboard('#copy-button');
    $('#fileToUpload').on('change', function(){
        $("#upload-file-info").val($(this).val().replace(/^.*\\/, ""));
        $("#submit-button").fadeIn(250);
    });

    $('#new-upload').on('click', function(){
        $("#preview").empty();
        $("#progress-bar").hide();
        $("#successful-upload").hide();
        $("#preview").hide();
        $('#image_upload_form').slideDown();

    });


    var bar = $('.bar');
    var percent = $('.percent');

    $("#image_upload_form").on('submit',(function(e){
        e.preventDefault();
        $("#preview").show();
        $("#progress-bar").show();
            $.ajax({
            url: "image-upload.php",
            type: "POST",
            data: new FormData(this),
            dataType: 'json',
            contentType: false,
            cache: false,
            processData:false,

            beforeSend: function() {
                bar.css('background-color', 'lightgreen');
                $(".progress").show();
                var percentVal = '0%';
                bar.width(percentVal);
                percent.html(percentVal);
            },

            xhr: function() {
                var myXhr = $.ajaxSettings.xhr();
                if(myXhr.upload){
                    myXhr.upload.addEventListener('progress',progress, false);
                }
                return myXhr;
            },

            success: function(data){
                if(data.uploadOk == 1){
                    $("#successful-upload").show();
                    $('#image_upload_form').slideUp();
                    $("#url-file-info").val(data.image_url);
                    $("#preview").html(data.image_html);
                    
                }else{
                    $("#preview").html(data.error_message);
                    bar.css('background-color', 'red');
                    percent.html("Error");
                }
                console.log(data);
            },
            error: function(data, error, xhr){
                $("#preview").html("Sorry, your file was not uploaded.");
                bar.css('background-color', 'red');
                percent.html("Error");
                console.log(data);
                console.log(error);
            }
         	        
        });
    }));

function progress(e){

    if(e.lengthComputable){
        var max = e.total;
        var current = e.loaded;
        var Percentage = Math.floor((current * 100)/max);
        var percentVal = Percentage + '%';
        bar.width(percentVal);
        percent.html(percentVal);

        if(Percentage >= 100)
        {
           // process completed  
        }
    }  
}
   
});



