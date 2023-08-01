var autosaveDiv = null;
var sTimeout = null;
var sInterval = 60*1000;
var autosaveEnabled = true;
var nextSave = sInterval/1000;

var lastSaveString = "";

function Autosave(value){
    if(autosaveDiv == null){
        if($("#" + $(this.value).attr("id") + "auto").length == 0){
            $(value).html("<div id='" + $(this.value).attr("id") + "auto' style='color:#222222;text-shadow:1px 1px 0px #FFFFFF;padding:5px;background:#EAEAEA;border:1px solid #AAAAAA;display:none; right:25px;position:absolute;'><b>Saving</b></div>" + $(value).html());
        }
    }
    else{
        $.each($(autosaveDiv), function(index, val){
            if($("." + $(this.value).attr("id") + "auto").length == 0){
                $(val).html("<div class='" + $(this.value).attr("id") + "auto' style='color:#222222;text-shadow:1px 1px 0px #FFFFFF;padding:5px;background:#EAEAEA;border:1px solid #AAAAAA;display:none;'><b>Saving</b></div>");
            }
        });
    }
    this.value = value;
    this.auto = $("." + $(this.value).attr("id") + "auto");
    this.isAutoSave = true;
    var obj = this;
    
    // Submits the form, using an ajax call.
    this.save = function(fn, failFn){
        var button = $('[type=submit]', this.value);
        $(button).prop('disabled', true);
        var dataStr = $(this.value).serialize() + "&" + encodeURIComponent($(button).attr("name")) + "=" + encodeURIComponent($(button).val());
        var url = $(this.value).attr("action");
        this.auto.stop();
        this.auto.css("opacity", 100);
        this.auto.css("display", "inline");
        //$('#submit_throbber').css('display', 'inline-block');
        this.auto.html("<b>Saving</b>&nbsp;<img width='16' height='16' src='../skins/Throbber.gif' />");
        if(dataStr == lastSaveString){
            obj.auto.html("<b><en>Saved</en><fr>Enregistré</fr></b>");
            obj.auto.fadeOut(2500);
            $(button).removeAttr('disabled');
            //$('#submit_throbber').css('display', 'none');
            if(fn != null){
                fn();
            }
            return;
        }
        
        $.ajax({
            type: "POST",
            url: url,
            data: dataStr + "&oldData=" + encodeURIComponent(lastSaveString) + '&user=' + wgUserName,
            success: function (data) {
                if(data != undefined && data.length > 0){
                    // Do not change the lastSaveString values which have conflicts
                    for(index in data){
                        var postId = data[index].postId;
                        var value = data[index].value;
                        var oldValue = data[index].oldValue;
                        dataStr = dataStr.replace(postId + '=', 'old' + '=');
                        dataStr += '&' + postId + '=' + encodeURIComponent(oldValue).replace(/%20/g, '+');
                    }
                }
                lastSaveString = dataStr;
                obj.auto.html("<b><en>Saved</en><fr>Enregistré</fr></b>");
                obj.auto.fadeOut(2500);
                $(button).removeAttr('disabled');
                $('#submit_throbber').css('display', 'none');
                clearError();
                if(fn != null){
                    fn(data);
                }
            },
            error: function(data){
                obj.auto.html("<b>Error Saving</b>");
                clearError();
                addError('There was an error saving this page.  Please verify that you are logged in, and not impersonating anyone.');
                $(button).removeAttr('disabled');
                $('#submit_throbber').css('display', 'none');
                if(failFn != null){
                    failFn(data);
                }
            }
        });
    }
}

var autosaves = Array();

function saveAll(fn, failFn){
    var count = 0;
    for(i in autosaves){
        var autosave = autosaves[i];
        if(typeof autosave == 'object'){
            count++;
            autosave.save(fn, failFn);
        }
    }
    if(count == 0){
        if(fn != null){
            fn();
        }
        return false;
    }
    return true;
}

saveAll = _.throttle(saveAll, 1000);

function saveAllAutosaves(fn, failFn){
    var count = 0;
    for(i in autosaves){
        var autosave = autosaves[i];
        if(typeof autosave == 'object'){
            if(autosave.isAutoSave && autosaveEnabled){
                count++;
                autosave.save(fn, failFn);
            }
        }
    }
    if(count == 0){
        if(fn != null){
            fn();
        }
        return false;
    }
    return true;
}

$(document).ready(function(){
    findAutosaves();
    setInterval(function(){
        nextSave--;
    }, 1000);
});

function findAutosaves(fn, failFn){
    // Go throught the document and construct Autosaves
    autosaves = Array();
    $.each($(".autosave"), function(index, value){
        autosaves.push(new Autosave(value));
    });
    $.each($(".noautosave"), function(index, value){
        var auto = new Autosave(value);
        auto.isAutoSave = false;
        autosaves.push(auto);
    });
    if(sTimeout != null){
        clearInterval(sTimeout);
    }
    sTimeout = setInterval(function() {
        saveAllAutosaves(fn, failFn);
        nextSave = sInterval/1000;
    }, sInterval);
    nextSave = sInterval/1000;
}


