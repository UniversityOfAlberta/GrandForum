function closeParent(link){
    $(link).parent().slideUp(250, function(){
        $(this).remove();
    });
}

function addMessage(type, message, scroll){
    if($('#wgMessages .' + type).length && !$('#wgMessages .' + type).is(':animated')){
        $('#wgMessages .' + type).append('<br />' + message);
    }
    else{
        $('#wgMessages .' + type).stop();
        $('#wgMessages .' + type).remove();
        $('#wgMessages').append("<div class='" + type + "' style='display:none'><span style='display:inline-block;'>" + message + "</span></div>");
        addClose($('#wgMessages .' + type));
        $('#wgMessages .' + type).slideDown(250);
    }
    if(scroll == true){
        $('html,body').animate({scrollTop: $('#wgMessages').offset().top}, 250);
    }
}

function clearMessage(type){
    $('#wgMessages .' + type).slideUp(250, function(){
        $(this).remove();
    });
}

function addError(message, scroll){
    addMessage('error', message, scroll);
}

function addSuccess(message, scroll){
    addMessage('success', message, scroll);
}

function addWarning(message, scroll){
    addMessage('warning', message, scroll);
}

function addInfo(message, scroll){
    addMessage('info', message, scroll);
}

function addPurpleInfo(message, scroll){
    addMessage('purpleInfo', message, scroll);
}

function clearError(){
    clearMessage('error');
}

function clearSuccess(){
    clearMessage('success');
}

function clearWarning(){
    clearMessage('warning');
}

function clearInfo(){
    clearMessage('info');
}

function clearPurpleInfo(){
    clearMessage('purpleInfo');
}

function clearAllMessages(){
    clearError();
	clearSuccess();
	clearWarning();
	clearInfo();
	clearPurpleInfo();
}

function addClose(messageBox){
    $(messageBox).append("<a class='error_box_close' onClick='closeParent(this)'>X</a>");
}

$(document).ready(function(){
    addClose($('.error, .warning, .success, .info, .purpleInfo').not('.notQuitable'));
});
