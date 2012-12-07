function closeParent(link){
    $(link).parent().fadeOut(250, function(){
        $(this).remove();
    });
}

function addMessage(type, message){
    if($('#wgMessages .' + type).length && !$('#wgMessages .' + type).is(':animated')){
        $('#wgMessages .' + type).append('<br />' + message);
    }
    else{
        $('#wgMessages .' + type).stop();
        $('#wgMessages .' + type).remove();
        $('#wgMessages').append("<div class='" + type + "' style='display:none'><span style='display:inline-block;'>" + message + "</span></div>");
        addClose($('#wgMessages .' + type));
        $('#wgMessages .' + type).fadeIn(300);
    }
}

function clearMessage(type){
    $('#wgMessages .' + type).fadeOut(250, function(){
        $(this).remove();
    });
}

function addError(message){
    addMessage('error', message);
}

function addSuccess(message){
    addMessage('success', message);
}

function addWarning(message){
    addMessage('warning', message);
}

function addInfo(message){
    addMessage('info', message);
}

function addPurpleInfo(message){
    addMessage('purpleInfo', message);
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

function addClose(messageBox){
    $(messageBox).append("<a class='error_box_close' onClick='closeParent(this)'>X</a>");
}

$(document).ready(function(){
    addClose($('.error, .warning, .success, .info, .purpleInfo').not('.notQuitable'));
});
