var currentSectionHref = "";

$(document).ready(function(){
    var animationTime = 250;
    var animationEasingOut = 'easeInQuad';
    var animationEasingIn = 'easeOutExpo';
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
            findAutosaves(updateProgress);
            saveAll(animate);
            var paddingHeight = parseInt($("#reportMain > div > div").css('padding-top')) + parseInt($("#reportMain > div > div").css('padding-bottom'));
            var oldHeight = $("#reportMain > div").height();
            if(selectedIndex != clickedIndex){
                $("#reportMain > div").stop();
            }
            var oldTab = $(".selectedReportTab");
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
                    $("#reportMain > div > div").animate({
                                                'marginTop' : 0 + 'px',
                                                'height' : $("#reportMain").height() + paddingHeight + 'px',
                                                'padding-top' : 10 + 'px',
                                                'padding-bottom' : 10 + 'px'
                                             }, animationTime, animationEasingOut);
                    $("#reportInstructions").animate({'opacity' : 1}, animationTime);
                    $(".selectedReportTab").children("img").remove();
                    $(".selectedReportTab").removeClass("selectedReportTab");
                    $(oldTab).addClass("selectedReportTab");
                    return;
                }
                ajaxSection = $.get(href + '&showSection', function(response){
                    currentSectionHref = href;
                    $(that).children("img").remove();
                    $("#reportMain > div").stop();
                    $("#reportMain > div").html(response);
                    $("#reportMain > div .tooltip").qtip();
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
                                                                                    initResizeEvent();
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
                                                                                        initResizeEvent();
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
                        initResizeEvent();
                    }
                    timeout = setTimeout(function(){findAutosaves(updateProgress);}, 2500); // Make sure that there was enough time to complete the animation, then find the new autosaves
                    setUpFormSubmit();
                    lastSaveString = $("form[name=report]").serialize();
                });
            }
            ajaxInstructions = $.get(href + '&showInstructions', function(response){
                $("#reportInstructions > div > div").html("<span id='instructionsHeader'>Instructions</span>" + response);
                $("#reportInstructions").animate({'opacity' : 1}, animationTime);
            });
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
