$(document).ready(function(){
    
    var dataToSend = {};
    var selector = (showSideBar) ? "div#side" : "#header ul";

    $(selector).append("<div id='reportIssue'><button><span class='en'>Report Issue</span><span class='fr' style='font-size:0.85em;line-height:1em;'>Signaler un problème</span></button><span class='throbber' style='display:none;'></span></div>");
    if(isExtensionEnabled("ContactUs")){
        $(selector).append("<div id='contactUs'><button><span class='en'>Contact Us</span><span class='fr'>Contactez-nous</span></button></div>");
    }
    
    if(!showSideBar){
        $("#reportIssue, #contactUs").css("display", "inline-block")
                                     .css("margin-right", "10px")
                                     .css("margin-top", "8px")
                                     .css("float", "right");
    }
    
    if(networkName != "AI4Society"){
        $("#topic").append("<option></option>");
        $("#topic").val('');
        $("#topic").closest("tr").hide();
        $("#topic_other").show();
        $("#topic_other td").first().text("Subject:");
        $("#contactFile").hide();
    }
    
    $("div#contactUsDialog input[type=file]").change(function(e){
        var file = e.target.files[0];
        var reader = new FileReader();
        reader.addEventListener("load", function() {
            if(file.size > 1024*1024*5){
                $('#fileSizeError').show();
                dataToSend.fileObj = null;
                delete dataToSend.fileOb;
            }
            else{
                $('#fileSizeError').hide();
                var fileObj = {
                    filename: file.name,
                    type: file.type,
                    data: reader.result
                };
                fileObj.filename = file.name;
                dataToSend.fileObj = fileObj;
            }
        });
        if(file != undefined){
            reader.readAsDataURL(file);
        }
    });
    
    $("div#reportIssueDialog").dialog({
        autoOpen: false,
        width: '35em',
        buttons: {
            "Submit": function(){   
                dataToSend.first_name = $("div#reportIssueDialog input[name=first_name]").val();
                dataToSend.last_name = $("div#reportIssueDialog input[name=last_name]").val();
                dataToSend.email = $("div#reportIssueDialog input[name=email]").val();
                dataToSend.comments = $("div#reportIssueDialog textarea").val();
                $.post(wgServer + wgScriptPath + '/index.php?action=reportIssue', dataToSend, function(response){
                    $(this).dialog('close');
                    clearSuccess();
                    addSuccess('The issue has been reported.');
                }.bind(this));
            },
            "Cancel": function(){
                $(this).dialog('close');
            }
        }
    });
    
    $("div#contactUsDialog").dialog({
        autoOpen: false,
        width: '35em',
        buttons: {
            "Submit": function(){
                dataToSend.topic = $("div#contactUsDialog #topic").val();
                if(dataToSend.topic == "Other" && $("div#contactUsDialog #topicOther").val().trim() != ""){
                    dataToSend.topic += ": " + $("div#contactUsDialog #topicOther").val().trim();
                }
                if(dataToSend.topic == ""){
                    dataToSend.topic = $("div#contactUsDialog #topicOther").val().trim();
                }
                dataToSend.first_name = $("div#contactUsDialog input[name=first_name]").val();
                dataToSend.last_name = $("div#contactUsDialog input[name=last_name]").val();
                dataToSend.email = $("div#contactUsDialog input[name=email]").val();
                dataToSend.comments = $("div#contactUsDialog textarea").val();
                $.post(wgServer + wgScriptPath + '/index.php?action=reportIssue', dataToSend, function(response){
                    $(this).dialog('close');
                    clearSuccess();
                    addSuccess('Your message has been sent to support.');
                }.bind(this));
            },
            "Cancel": function(){
                $(this).dialog('close');
            }
        }
    });
    
    $("div#helpDialog").dialog({
        autoOpen: false,
        width: '35em',
        buttons: {
            "Submit": function(){
                dataToSend.first_name = $("div#helpDialog input[name=first_name]").val();
                dataToSend.last_name = $("div#helpDialog input[name=last_name]").val();
                dataToSend.email = $("div#helpDialog input[name=email]").val();
                dataToSend.phone = $("div#helpDialog input[name=phone]").val();
                dataToSend.comments = $("div#helpDialog textarea").val();
                $.post(wgServer + wgScriptPath + '/index.php?action=reportIssue', dataToSend, function(response){
                    $(this).dialog('close');
                    clearSuccess();
                    addSuccess('Your message has been sent to support.');
                }.bind(this));
            },
            "Cancel": function(){
                $(this).dialog('close');
            }
        }
    });
   
    $("div#reportIssue button").click(function(){
        $("div#reportIssue .throbber").show();
        html2canvas(document.body).then(function(canvas) {
            dataToSend = {
                img: canvas.toDataURL(),
                url: document.location.toLocaleString(),
                browser: navigator.userAgent,
                comments: '',
                email: ''
            };
            $("div#reportIssueDialog").dialog('open');
            $("div#reportIssue .throbber").hide();
            $(window).resize();
        });
    });
    
    $("div#contactUs button").click(function(){
        dataToSend = {
            topic: '',
            comments: '',
            email: '', 
            fileObj: {}
        };
        $("div#contactUsDialog").dialog('open');
        $("div#contactUsDialog input[type=file]").change();
        $(window).resize();
    });
    
    $("#helpButton").click(function(){
        dataToSend = {
            phone: '',
            comments: '',
            email: ''
        };
        $("div#helpDialog").dialog('open');
        $(window).resize();
    });
    
    $("div#contactUsDialog #topic").change(function(){
        if($("#topic").val() == "Other" || $("#topic").val() == ""){
            $("#topic_other").show();
        }
        else{
            $("#topic_other").hide();
        }
    }).change();
    
    $(window).resize(function(){
        var desiredWidth = "35em";
        if(window.matchMedia('(max-width: 767px)').matches){
            desiredWidth = $(window).width()*0.99;
        }
        else if(window.matchMedia('(max-width: 1024px)').matches){
            desiredWidth = "35em";
        }
        
        if($('#helpDialog, #contactUsDialog, #reportIssueDialog').is(':visible')){
            $('#helpDialog, #contactUsDialog, #reportIssueDialog').dialog({
                width: desiredWidth
            });
            $('#helpDialog, #contactUsDialog, #reportIssueDialog').dialog({
                position: { 'my': 'center', 'at': 'center' }
            });
        }
    }).resize();
    
});
