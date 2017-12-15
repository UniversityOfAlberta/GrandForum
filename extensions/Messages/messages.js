function closeParent(link){
    $(link).parent().slideUp(250, function(){
        $(this).remove();
    });
}

function addMessage(type, message, scroll, selector){
    if(selector == undefined){
        selector = "#wgMessages";
    }
    if($(selector + ' .' + type).length && !$(selector + ' .' + type).is(':animated')){
        $(selector + ' .' + type).append('<br />' + message);
    }
    else{
        $(selector + ' .' + type).stop();
        $(selector + ' .' + type).remove();
        $(selector).append("<div class='" + type + "' style='display:none'><span style='display:inline-block;'>" + message + "</span></div>");
        addClose($(selector + ' .' + type));
        $(selector + ' .' + type).slideDown(250);
    }
    if(scroll == true){
        var parent = $(selector).scrollParent();
        if((parent[0] != document)){
            $(parent).animate({scrollTop: $(selector).position().top}, 300);
        } else {
            $('html,body').animate({scrollTop: $(selector).position().top}, 300);
        }
    }
}

function clearMessage(type, selector){
    if(selector == undefined){
        selector = "#wgMessages";
    }
    $(selector + ' .' + type).slideUp(250, function(){
        $(this).remove();
    });
}

function addError(message, scroll, selector){
    addMessage('error', message, scroll, selector);
}

function addSuccess(message, scroll, selector){
    addMessage('success', message, scroll, selector);
}

function addWarning(message, scroll, selector){
    addMessage('warning', message, scroll, selector);
}

function addInfo(message, scroll, selector){
    addMessage('info', message, scroll, selector);
}

function addPurpleInfo(message, scroll, selector){
    addMessage('purpleInfo', message, scroll, selector);
}

function clearError(selector){
    clearMessage('error', selector);
}

function clearSuccess(selector){
    clearMessage('success', selector);
}

function clearWarning(selector){
    clearMessage('warning', selector);
}

function clearInfo(selector){
    clearMessage('info', selector);
}

function clearPurpleInfo(selector){
    clearMessage('purpleInfo', selector);
}

function clearAllMessages(selector){
    clearError(selector);
	clearSuccess(selector);
	clearWarning(selector);
	clearInfo(selector);
	clearPurpleInfo(selector);
}

function addClose(messageBox){
    $(messageBox).append("<a class='error_box_close' onClick='closeParent(this)'>X</a>");
}

$(document).ready(function(){
    addClose($('.error, .warning, .success, .info, .purpleInfo').not('.notQuitable'));
});
