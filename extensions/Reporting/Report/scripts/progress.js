function setProgress(id, percent){
    if($("#" + id).hasClass('selectedReportTab')){
        $("#reportProgressBar").width(percent + "%");
        $("#reportProgressLabel").text("Progress (" + percent + "%)");
    }
}

function updateProgress(responseStr){
    return; // disabled
    if(responseStr != undefined && responseStr.length > 0){
        // There could be an error with the save ajax request
        showConflictError(responseStr);
    }
    $.get(currentSectionHref + '&getProgress', function(response){
        for(index in response){
            var val = response[index];
            setProgress(index, val);
        }
    });
}
