if (!$.browser.msie || $.browser.version > 7){
$(document).ready(function(){
    $("#instructionsToggle").draggable({axis: "x", iframeFix: true });
    
    var lastWidth = $("#reportMain").width();
    var lastIWidth = $("#reportInstructions").width();
    
    $("#reportInstructions > div").css('max-height', $("#reportMain > div").height());
    
    $("#instructionsToggle").bind( "dragstart", function(event, ui) {
        lastWidth = $("#reportMain").width();
        lastIWidth = $("#reportInstructions").width();
    });
    
    $("#instructionsToggle").bind( "dragstop", function(event, ui) {
        lastWidth = $("#reportMain").width();
        lastIWidth = $("#reportInstructions").width();
    });
    
    $("#reportMain").width(10000);
    $("#reportMain").css('max-width', 10000 + 'px');
    
    $("#instructionsToggle").bind("drag", function(event, ui) {
        document.body.style.cursor="ew-resize";
        var difference = ui.originalPosition.left - ui.position.left;
        var newIWidth = Math.max(0, lastIWidth + difference);
        var snapAmount = null;
        if(newIWidth - 230 <= 20 && newIWidth - 230 >= -20){
            snapAmount = newIWidth - 230;
        }
        var minimum = 0;
        if($.browser.msie){
            minimum = 1;
        }
        var startWidth = $("#reportMain").width();
        $("#reportMain").width(Math.max(minimum, lastWidth + ui.position.left + snapAmount));
        $("#reportMain").css('max-width', Math.max(minimum, lastWidth + ui.position.left + snapAmount));
        var endWidth = Math.max(minimum, lastWidth + ui.position.left + snapAmount);
        if(endWidth > 1){
            if(snapAmount == null){
                $("#reportInstructions").css('min-width', Math.max(0, lastIWidth - ui.position.left));
                $("#reportInstructions").css('max-width', Math.max(0, lastIWidth - ui.position.left));
                $("#reportInstructions").width(Math.max(0, lastIWidth - ui.position.left));
                $("#reportInstructions > div").css('max-height', $("#reportMain > div").height());
            }
            else{
                $("#reportInstructions").css('min-width', Math.max(0, 230));
                $("#reportInstructions").css('max-width', Math.max(0, 230));
                $("#reportInstructions").width(Math.max(0, 230));
                $("#reportInstructions > div").css('max-height', $("#reportMain > div").height());
            }
            $("#reportMain").width(10000);
            $("#reportMain").css('max-width', 10000 + 'px');
        }
        if(snapAmount == null){
            if(difference > 0)
                ui.position.left = -1;
            else ui.position.left = 1;
        }
        else{
            ui.position.left = 0;
        }
    });
    
    $("#instructionsToggle").bind("dragstop", function(event, ui) {
        document.body.style.cursor="default";
    });
});
}
else{
    $(document).ready(function(){
        $("#instructionsToggle").html("");
    });
}
