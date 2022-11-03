var currentSectionHref = "";
var animationTime = 250;
var animationEasingOut = 'easeInQuad';
var animationEasingIn = 'easeOutExpo';
var oldTab;

// Make sure that all the editors are actually destroyed 
// (could be remnants from the past if using the back button)
_.each(tinyMCE.editors, function(e){
    if(e != undefined){
        e.destroy();
    }
});

function revertReportAnimation(){
    if($("#reportMain").length > 0){
        var paddingHeight = parseInt($("#reportMain > div > div").css('padding-top')) + parseInt($("#reportMain > div > div").css('padding-bottom'));
        $("#reportMain > div > div").animate({
                                    'marginTop' : 0 + 'px',
                                    'height' : $("#reportMain").height() + paddingHeight + 'px',
                                    'padding-top' : 10 + 'px',
                                    'padding-bottom' : 10 + 'px'
                                 }, animationTime, animationEasingOut, function(){
                                    $("#reportMain > div > div").css('overflow-y', 'visible');
                                    $("#reportMain > div > div").css('height', '');
                                    _.defer(initResizeEvent);
                                 });
        $("#reportInstructions").animate({'opacity' : 1}, animationTime);
        $(".selectedReportTab").children("img").remove();
        $(".selectedReportTab").removeClass("selectedReportTab");
        $(oldTab).addClass("selectedReportTab");
    }
}

$(document).ready(function(){
    
    setUpFormSubmit();
    var timeout = null;
    var ajaxSection = null;
    var ajaxInstructions = null;
    
    $.each($("a.reportTab"), function(index, val){
        var href = $(this).attr('href');
        if($(this).hasClass('selectedReportTab')){
            currentSectionHref = href;
        }
        $(this).removeAttr('href');
        
        $(this).click(function(){
            if($(this).attr('href') != undefined){
                href = $(this).attr('href');
            }
            var selectedIndex = $(".reportTab").index($(".selectedReportTab"));
            var clickedIndex = $(".reportTab").index($(this));
            if(ajaxSection != null && selectedIndex != clickedIndex){
                ajaxSection.abort();
                $(".selectedReportTab > img").remove();
            }
            if(ajaxInstructions != null && selectedIndex != clickedIndex){
                ajaxInstructions.abort();
                $(".selectedReportTab > img").remove();
            }
            if(timeout != null){
                clearTimeout(timeout);
            }
            if(dbWritable){
                findAutosaves(updateProgress, revertReportAnimation);
                saveAll(animate, revertReportAnimation);
            }
            else{
                animate();
            }
            var paddingHeight = parseInt($("#reportMain > div > div").css('padding-top')) + parseInt($("#reportMain > div > div").css('padding-bottom'));
            var oldHeight = $("#reportMain > div").height();
            if(selectedIndex != clickedIndex){
                $("#reportMain > div").stop();
            }
            oldTab = $(".selectedReportTab");
            $(".selectedReportTab").removeClass("selectedReportTab");
            $(this).addClass("selectedReportTab");
            $(this).children("img").remove();
            if (!$.browser.msie || $.browser.version > 7){
                $(this).html("<img style='position:absolute;top:5px;left:1px;width:16px;height:16px;' src='../skins/Throbber.gif' />" + $(this).html());
            }
            $("#reportMain > div > div").off('resize');
            if(selectedIndex < clickedIndex){
                $("#reportMain > div > div").css('overflow-y', 'hidden');
                $("#reportMain > div > div").animate({
                                            'marginTop' : -$("#reportMain").height() + 'px',
                                            'height' : $("#reportMain").height()*2 + 'px',
                                            'padding-top' : 0 + 'px',
                                            'padding-bottom' : 0 + 'px'
                                         }, animationTime, animationEasingOut);
                $("#reportInstructions").animate({'opacity' : 0.01}, animationTime);
            }
            else if(selectedIndex > clickedIndex){
                $("#reportMain > div > div").css('overflow-y', 'hidden');
                $("#reportMain > div > div").animate({
                                            'marginTop' : $("#reportMain").height() + 'px',
                                            'height' : 0 + 'px',
                                            'padding-top' : 0 + 'px',
                                            'padding-bottom' : 0 + 'px'
                                         }, animationTime, animationEasingOut);
                $("#reportInstructions").animate({'opacity' : 0.01}, animationTime);
            }
            var that = this;
            function animate(responseStr){
                if(responseStr != undefined && responseStr.length > 0){
                    // There could be an error with the save ajax request
                    showConflictError(responseStr);
                    revertReportAnimation();
                    return;
                }
                ajaxSection = $.get(href + '&showSection', function(response){
                    for (edId in tinyMCE.editors){
                        var e = tinyMCE.editors[edId];
                        if(e != undefined){
                            e.destroy();
                            e.remove();
                        }
                    }
                    currentSectionHref = href;
                    $(that).children("img").remove();
                    $("#reportMain > div").stop();
                    $("#reportMain > div").html(response);
                    $("#reportMain > div .tooltip").qtip({
		                show: {
		                    delay: 500
		                },
		                hide: {
                            fixed: true,
                            delay: 300
                        }
		            });
		            $("#reportMain > div .clicktooltip").qtip({
		                show: 'click',
                        hide: 'click unfocus'
		            });
                    updateProgress();
                    var heightDifference = oldHeight - $("#reportMain > div > div").height();
					var height = Math.max(parseInt($('#outerReport').css('min-height')), $('#reportMain > div > div').height());
                    if(selectedIndex < clickedIndex){
                        $("#reportMain > div > div").css('margin-top', height + heightDifference);
                        $("#reportMain > div > div").height(0);
                        $("#reportMain > div > div").css('overflow-y', 'hidden');
                        $("#reportMain > div > div").animate({
                                                'marginTop' : 0 + 'px',
                                                'height' : height + 'px'
                                             }, animationTime, animationEasingIn, function(){
                                                                                    $("#reportMain > div > div").css('overflow-y', 'visible');
                                                                                    $("#reportMain > div > div").css('height', '');
                                                                                    _.defer(initResizeEvent);
                                                                                  });
                        $("#reportMain > div").animate({
                                                    'height' : height + paddingHeight + 'px'
                                                 }, animationTime, animationEasingIn);
                        $("#reportInstructions > div").animate({
                                                    'max-height' : height + paddingHeight + 'px'
                                                 }, animationTime, animationEasingIn);
                    }
                    else if(selectedIndex > clickedIndex){
                        $("#reportMain > div > div").css('margin-top', -height);
                        $("#reportMain > div > div").height(height*2 + heightDifference);
                        $("#reportMain > div > div").css('overflow-y', 'hidden');
                        $("#reportMain > div > div").animate({
                                                    'marginTop' : 0 + 'px',
                                                    'height' : height + 'px'
                                                 }, animationTime, animationEasingIn, function(){
                                                                                        $("#reportMain > div > div").css('overflow-y', 'visible');
                                                                                        $("#reportMain > div > div").css('height', '');
                                                                                        _.defer(initResizeEvent);
                                                                                      });
                        $("#reportMain > div").animate({
                                                    'height' : height + paddingHeight + 'px'
                                                 }, animationTime, animationEasingIn);
                        $("#reportInstructions > div").animate({
                                                    'max-height' : height + paddingHeight + 'px'
                                                 }, animationTime, animationEasingIn);
                    }
                    else{
                        $("#reportMain > div").animate({
                                                    'height' : height + paddingHeight + 'px'
                                                 }, animationTime, animationEasingIn);
                        _.defer(initResizeEvent);
                    }
                    timeout = setTimeout(function(){findAutosaves(updateProgress);}, animationTime); // Make sure that there was enough time to complete the animation, then find the new autosaves
                    setUpFormSubmit();
                    lastSaveString = $("form[name=report]").serialize();
                });
            }
            if($(this).attr("data-has-instructions") == "true"){
                ajaxInstructions = $.get(href + '&showInstructions', function(response){
                    if(response.trim() == ""){
                        $("#instructionsToggle").hide();
                        $("#reportInstructions").hide();
                    }
                    else{
                        $("#instructionsToggle").show();
                        $("#reportInstructions").show();
                    }
                    $("#reportInstructions > div > div").html("<span id='instructionsHeader'>Instructions</span>" + response);
                    $("#reportInstructions").animate({'opacity' : 1}, animationTime);
                });
            }
            else{
                $("#instructionsToggle").hide();
                $("#reportInstructions").hide();
            }
        });
    });
    
    function setUpFormSubmit(){
        $.each($("form[name=report] input[type=submit]"), function(index, value){
            $(value).click(function(){
                if(timeout != null){
                    clearTimeout(timeout);
                }
                autosaveDiv = $('.autosaveSpan');
                findAutosaves(updateProgress);
                saveAll(updateProgress);
                return false;
            });
        });
    }
});
