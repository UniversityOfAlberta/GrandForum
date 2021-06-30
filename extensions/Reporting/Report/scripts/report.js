function initResizeEvent(){
    var fn = function(){
        var paddingHeight = parseInt($('#reportMain > div > div').css('padding-top')) + parseInt($('#reportMain > div > div').css('padding-bottom'));
        $('#reportMain > div').height($('#reportMain > div > div').height() + paddingHeight);
        $('#reportInstructions > div').css('max-height', $('#reportMain > div').height());
    }
    $('#reportMain > div > div').resize(fn);
    fn();
}

setInterval(initResizeEvent, 50);

function setAutosave(enabled){
    if(enabled){
        autosaveEnabled = true;
    }
    else{
        autosaveEnabled = false;
    }
}

function toggleFullscreenAfterAnimation(){
    $('#outerReport').toggleClass('outerReportCustom');
    $('#reportInstructions').toggleClass('reportInstructionsCustom');
    $('#instructionsToggle').toggleClass('instructionsToggleCustom');
    if($('#instructionsToggle').hasClass('instructionsToggleCustom')){
        $("html").css('min-width', '');
        $('#bodyContent').toggleClass('bodyContentCustom');
        $('.instructionsToggleCustom').css('min-height', $(window).height() + 'px');
        $('.instructionsToggleCustom').height($(window).height());
        $('.outerReportCustom #reportMain > div').css('min-height', $(window).height() + 'px');
        $('.outerReportCustom #reportMain > div').height($(window).height());
        $(window).resize(function(){
            var paddingHeight = parseInt($('#reportMain > div > div').css('padding-top')) + parseInt($('#reportMain > div > div').css('padding-bottom'));
            $('.instructionsToggleCustom').css('min-height', $(window).height() + 'px');
            $('.instructionsToggleCustom').height($(window).height());
            $('#reportMain > div').height(Math.max($(window).height(), $('#reportMain > div > div').height() + paddingHeight));
            $('#reportMain > div').css('min-height', Math.max($(window).height(), parseInt($('#reportMain > div > div').height() + paddingHeight)) + 'px');
        });
    }
    else{
        $(window).off('resize');
        setMinWidth();
        var paddingHeight = parseInt($('#reportMain > div > div').css('padding-top')) + parseInt($('#reportMain > div > div').css('padding-bottom'));
        var height = $('#outerReport #reportMain > div > div').height() + paddingHeight;
        $('#instructionsToggle').css('min-height', '');
        $('#instructionsToggle').height("100%");
        $('#reportInstructions > div').height("100%");
        $('#outerReport #reportMain > div').css('min-height', '');
        $('#outerReport #reportMain > div').height(height);
        $('#bodyContent').css('bottom', '')
                         .css('z-index', '1');
        if($.browser.msie){
            // IE doesn't render the bodyContent correctly until it is repainted, so force a Repaint
            document.getElementById('mBody').style.display = 'none';
            document.getElementById('mBody').style.display = 'block';
        }
        $('#bodyContent').toggleClass('bodyContentNoRoundedShadow');
    }
}

function toggleFullscreen(){
    var paddingHeight = parseInt($('#reportMain > div > div').css('padding-top')) + parseInt($('#reportMain > div > div').css('padding-bottom'));
    var easing = 'swing';
    var animationTime = 250;
    if(sideToggled == 'out'){
        var left = $('#side').width() + 30 + parseInt($('body').css('margin-left'));
    }
    else{
        left = 20;
    }
    var right = parseInt($('body').css('margin-left')) + 30;
    var top = $('#header').height() + 
              parseInt($('#header').css('margin-top')) + 
              parseInt($('#header').css('margin-bottom')) + 
              parseInt($('#header').css('padding-bottom')) + 
              parseInt($('#header').css('padding-top')) + 
              parseInt($('body').css('margin-top')) + 25;
    var bottom = $(window).height() - $('#bodyContent').height() - top - 14;
    if(!$('#instructionsToggle').hasClass('instructionsToggleCustom')){
        $('#bodyContent').toggleClass('bodyContentNoRoundedShadow');
        $('#bodyContent').css('position', 'absolute');
        $('#bodyContent').css('top', top + 'px');
        $('#bodyContent').css('right', right + 'px');
        $('#bodyContent').css('bottom', bottom + 'px');
        $('#bodyContent').css('left', left + 'px');
        $('#bodyContent').css('width', 'auto');
        $('#bodyContent').css('z-index', 1001);
        $('#reportMain').animate({
                                    'width' : '10000px',
                                    'max-width' : '10000px'
                                 }, animationTime, easing);
        $('#bodyContent').animate({
                                    'top' : 0,
                                    'left' : 0,
                                    'right' : 0,
                                    'bottom' : 0,
                                    'z-index' : 1001
                                  }, animationTime, easing, toggleFullscreenAfterAnimation);
        var divHeight = ($('#reportMain > div > div').height() + paddingHeight);
        if(divHeight > $(window).height()){
            $('#reportMain > div').animate({
                                        'height' : divHeight + 'px',
                                     }, animationTime, easing);
            $('#instructionsToggle').animate({
                                    'height' : divHeight + 'px'
                                  }, animationTime, easing);
            $('#reportInstructions > div').animate({
                                    'max-height' : divHeight + 'px'
                                  }, animationTime, easing);
        }
        else{
            $('#instructionsToggle').animate({
                                    'height' : $(window).height() + 'px'
                                  }, animationTime, easing);
            $('#reportInstructions > div').animate({
                                    'max-height' : $(window).height() + 'px'
                                  }, animationTime, easing);
        }
    }
    else{
        var paddingHeight = parseInt($('#reportMain > div > div').css('padding-top')) + parseInt($('#reportMain > div > div').css('padding-bottom'));
        var height = Math.max(parseInt($('#reportMain').css('min-height')), $('#outerReport #reportMain > div > div').height() + paddingHeight);
        bottom = $(window).height() - height - 14 - top;
        $('#bodyContent').toggleClass('bodyContentCustom');
        $('#bodyContent').css('position', 'absolute');
        $('#bodyContent').css('top', 0);
        $('#bodyContent').css('right', 0);
        $('#bodyContent').css('bottom', 0);
        $('#bodyContent').css('left', 0);
        $('#bodyContent').css('width', 'auto');
        $('#bodyContent').animate({
                                    'top' : top + 'px',
                                    'left' : left + 'px',
                                    'right' : right + 'px',
                                    'bottom' : bottom + 'px'
                                  }, animationTime, easing, toggleFullscreenAfterAnimation);
    }
}

function saveBackup(download){
    if(download == undefined || download != false){
        download = 'true';
    }
    else {
        download = 'false'
    }
    findAutosaves(updateProgress);
    saveAll(function(){
        updateProgress();
        var newUrl = $(location).attr('href') + '&saveBackup' + '&download=' + download;
        if(download != 'false'){
            window.location = newUrl;
        }
    });
}

function toggleBackup(){
    if (!$.browser.msie || $.browser.version > 7){
        $("#backupTable").slideToggle(200);
    }
    else{
        $("#backupTable").slideToggle(0);
    }
}

function showConflictError(data){
    alert("Some of the fields could not save.  A change was made to at least one of the fields after this section was loaded.  This may have been caused by multiple browser sessions being opened at the same time.  Please go and close any other browser or tabs that you have open to the " + networkName + " reporting page.\n\nAfter clicking 'Ok', you will be returned to the conflicting section and the conflicting fields will be highlighted.  Any field which was not conflicting, has been saved already.");
    for(index in data){
        var postId = data[index].postId;
        var value = _.unescape(data[index].value);
        var oldValue = _.unescape(data[index].oldValue);
        var postValue = _.unescape(data[index].postValue);
        var diff = _.unescape(data[index].diff);
        var obj = $("[name=" + postId + "]:last");
        var isRadio = false;
        
        if($(obj).attr('type') == 'radio'){
            obj = $(obj).parent();
            isRadio = true;
        }
        $(obj).css('background-color', '#FEB8B8')
              .css('color', '#D50013');
        $("#" + postId + "conflictDetails").remove();
        $("#" + postId + "conflictLink").remove();
        $(obj).after("<div id='" + postId + "conflictLink'><a style='cursor:pointer;' onClick='$(\"#" + postId + "conflictDetails\").dialog(\"open\");'><b>View Conflict Information</b></a><div title='Conflict Information' style='display:none;' id='" + postId + "conflictDetails'><ins>My Version</ins><br /><del>Server Version</del><br /><br /><hr />" + diff + "<hr /><br /><button id='" + postId + "conflictMine'>Keep My Version</button> <button id='" + postId + "conflictServer'>Keep Server Version</button></div></div>");
        $("#" + postId + "conflictDetails").dialog({autoOpen: false,height: '480', width: '640'});
        
        $("#" + postId + "conflictMine").click({"postId"  : postId,
                                                "value"   : postValue, 
                                                "obj"     : obj,
                                                "isRadio" : isRadio}, resolveConflictMine);
        
        $("#" + postId + "conflictServer").click({"postId"  : postId,
                                                 "value"   : value, 
                                                 "obj"     : obj,
                                                 "isRadio" : isRadio}, resolveConflictServer);
    }
}

function resolveConflictMine(event){
    var postId = event.data.postId;
    var value = event.data.value;
    var obj = event.data.obj;
    var isRadio = event.data.isRadio;
    
    $(obj).css('background-color', '#FFFFFF')
          .css('color', '#000000');
    if(!isRadio){
        $("[name=" + postId + "]:last").val(value);
    }
    $("#" + postId + "conflictDetails").remove();
    $("#" + postId + "conflictLink").remove();
    $(obj).after("<input type='hidden' name='" + postId + "_ignoreConflict' value='true' />");
}

function resolveConflictServer(event){
    var postId = event.data.postId;
    var value = event.data.value;
    var obj = event.data.obj;
    var isRadio = event.data.isRadio;
    
    $(obj).css('background-color', '#FFFFFF')
          .css('color', '#000000');
    if(isRadio){
        $.each($("[name=" + postId + "]"), function(ind, val){
            if($(val).val() == value){
                $(val).attr('checked', true);
            }
        });
    }
    else{
        $("[name=" + postId + "]:last").val(value);
    }
    $("#" + postId + "conflictDetails").remove();
    $("#" + postId + "conflictLink").remove();
}

$(document).ready(function(){
    var paddingHeight = parseInt($('#reportMain > div > div').css('padding-top')) + parseInt($('#reportMain > div > div').css('padding-bottom'));
    $('#reportMain').children('div').height($('#reportMain > div > div').height() + paddingHeight);
    jQuery.resizeY.delay = 16;
    initResizeEvent();
    $("input[name=toggleFullscreen]").removeAttr('checked');
    $("input[name=toggleFullscreen]").change(function(){
        toggleFullscreen();
    });
    $("form[name=report]").serialize();
    if($.cookie("autosave") == "off"){
        $("input[name=autosave]").filter('[value=off]').attr('checked', true);
        setAutosave(false);
    }
    else{
        $("input[name=autosave]").filter('[value=on]').attr('checked', true);
        setAutosave(true);
    }
    $("input[name=autosave]").change(function(){
        if($(this).val() == 'on'){
            setAutosave(true);
            $.cookie("autosave", "on", { expires: 31 });
        }
        else{
            setAutosave(false);
            $.cookie("autosave", "off", { expires: 31 });
        }
    });
    
    $('#reportMain').on('change', 'input, select, textarea, button', function() {
        saveAll(function(){
            updateProgress();
        });
    });
    
    $('.hiddenFile').change(function(evt){
        var fileName = $('.hiddenFile').val().split('\\');
        $("#fileName").html(fileName[fileName.length-1]);
        $("#dialog-confirm").dialog("destroy");
        $("#dialog-confirm").dialog({
			resizable: false,
			draggable: false,
			modal: true,
			minWidth:500, 
			buttons: {
				"Yes": function(){
				    $(this).dialog("close");
				    findAutosaves(updateProgress);
                    saveAll(function(){
                        $("#backupForm").submit();
                        updateProgress();
                    });
				},
				"No": function(){
					$(this).dialog("close");
					$('#resetBackup').click();
				}
			}
		});
		$(".ui-dialog-buttonset button").removeClass("ui-widget").removeClass("ui-state-default").removeClass("ui-corner-all").removeClass("ui-button-text-only").removeClass("ui-state-hover");
    });
});
