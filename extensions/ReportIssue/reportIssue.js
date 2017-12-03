$(document).ready(function(){
    
    var dataToSend = {};
    
    $("div#side").append("<div id='reportIssue'><button>Report Issue</button><span class='throbber' style='display:none;'></span></div>");
    
    $("div#reportIssueDialog").dialog({
        autoOpen: false,
        width: 400,
        buttons: {
            "Submit": function(){
                dataToSend.comments = $("div#reportIssueDialog textarea").val();
                $.post(wgServer + wgScriptPath + '/index.php?action=reportIssue', dataToSend, $.proxy(function(response){
                    $(this).dialog('close');
                }, this));
            },
            "Cancel": function(){
                $(this).dialog('close');
            }
        }
    });
   
    $("div#reportIssue button").click(function(){
        $("div#reportIssue .throbber").show();
        html2canvas(document.body, {
            onrendered: function(canvas) {
                dataToSend = {
                    img: canvas.toDataURL(),
                    url: document.location.toLocaleString(),
                    browser: navigator.userAgent,
                    comments: ''
                };
                $("div#reportIssueDialog").dialog('open');
                $("div#reportIssue .throbber").hide();
            }
        });
    });
    
});
