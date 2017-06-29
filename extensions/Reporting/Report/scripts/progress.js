function setProgress(id, percent){
    if($("#" + id).hasClass('selectedReportTab')){
        $("#reportProgressBar").width(percent + "%");
        $("#reportProgressLabel").text("Section Progress (" + percent + "%)");
        if(percent == 100 && buttonClicked){
            $("#saveDialog").dialog({
                width: 'auto',
                buttons: {
                    "Ok": function(){
                        $(this).dialog('close');
                    }
                }
            });
        }
    }
}

function updateProgress(responseStr){
    if(responseStr != undefined && responseStr.length > 0){
        // There could be an error with the save ajax request
        showConflictError(responseStr);
    }
    $.get(currentSectionHref + '&getProgress', function(response){
        for(index in response){
            var val = response[index];
            setProgress(index, val);
        }
        buttonClicked = false;
    });
}
