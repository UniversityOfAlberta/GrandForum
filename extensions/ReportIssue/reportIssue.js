$(document).ready(function(){
    
    var dataToSend = {};
    
    $("div#header").append("<div id='reportIssue'><button>Report Issue</button><span class='throbber' style='display:none;'></span></div>");
    
    $("div#reportIssueDialog").dialog({
        autoOpen: false,
        width: 400,
        buttons: {
            "Submit": function(){
                dataToSend.comments = $("div#reportIssueDialog textarea").val();
                dataToSend.email = $("div#reportIssueDialog input[name=email]").val();
                $.post(wgServer + wgScriptPath + '/index.php?action=reportIssue', dataToSend, $.proxy(function(response){
                    $(this).dialog('close');
                    clearSuccess();
                    addSuccess('The issue has been reported.');
                }, this));
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
            //$("div#reportIssueDialog img").remove();
            //$("div#reportIssueDialog").append('<img src="' + canvas.toDataURL() + '" />');
            $("div#reportIssueDialog").dialog('open');
            $("div#reportIssue .throbber").hide();
        });
    });
    
});
