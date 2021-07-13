(function( $ ){
  $.fn.switcheroo = function(options) {
    this.name = options.name;
    this.id = options.id;
    this.customAllowed = options.noCustom;
    this.values = new Array();
    this.unaccentedValues = new Array();
    this.cleanIds = new Array();
    this.oldOptions = new Array();
    this.leftArray = new Array();
    this.rightArray = new Array();
    var obj = this;
    
    $.fn.getValue = function(){
        return obj.leftArray;
    }
    
    this.cleanId = function(str){
        str = str.replace(/ /g, '')
                 .replace(/'/g, '')
                 .replace(/\(/g, '')
                 .replace(/\)/g, '')
                 .replace(/\//g, '')
                 .replace(/\|/g, '')
                 .replace(/\!/g, '')
                 .replace(/\,/g, '')
                 .replace(/\:/g, '')
                 .replace(/\+/g, '')
                 .replace(/\?/g, '')
                 .replace(/\#/g, '')
                 .replace(/\$/g, '')
                 .replace(/\-/g, '')
                 .replace(/\~/g, '')
                 .replace(/\^/g, '')
                 .replace(/\`/g, '')
                 .replace(/\&amp\;/g, '')
                 .replace(/\&/g, '')
                 .replace(/\;/g, '')
                 .replace(/"/g, '');
        return str;
    }
    
    this.filterResults = function(){
        var value = $("#search" + this.id.pluralize()).val();
        var unaccentedValue = unaccentChars(value.replace(/ /g, "."));
        $.each($("#right" + this.id.pluralize()).children().not("#no" + this.id), function(index, val){
            var valSelect = val.id;
            if(!$(val).hasClass("custom")){
                obj.oldOptions[valSelect] = $(val).detach()[0];
            }
            else{
                $(val).remove();
            }
        });
        if(value == ""){
            var no = $("#no").detach();
            if(no.length > 0){
                this.oldOptions["no"] = no[0];
            }
        }
        else{
            var skip = false;
            for(i = 0; i < this.values.length; i++){
                var cleanId = this.cleanIds[i];
                var unaccentedVal = this.unaccentedValues[i];
                if(unaccentedValue == unaccentedVal){
                    skip = true;
                    if(typeof this.oldOptions[cleanId] != "undefined"){
                        $(this.oldOptions[cleanId]).attr("selected", true);
                    }
                    break;
                }
                else{
                    if(typeof this.oldOptions[cleanId] != "undefined"){
                        $(this.oldOptions[cleanId]).attr("selected", false);
                    }
                }
            }
            if(!skip){
                if(this.customAllowed){
                    var customName = document.createElement("option");
                    customName.innerHTML = "&quot;" + value + "&quot;";
                    customName.setAttribute("class", "custom");
                    customName.setAttribute("style", "font-style: italic;");
                    $(customName).attr("selected", "true");
                    $(customName).appendTo($("#right" + this.id.pluralize()));
                }
            }
        }
        var n = 0;
        var buffer = Array();
        for(i = 0; i < this.values.length; i++){
            var val = this.values[i];
            var unaccentedVal = this.unaccentedValues[i];
            var cleanId = this.cleanIds[i];
            if(unaccentedVal.indexOf(unaccentedValue) != -1){
                if(typeof this.oldOptions[cleanId] != "undefined"){
                    buffer[i] = "<option id='" + this.oldOptions[cleanId].id + "'>" + this.oldOptions[cleanId].innerHTML + "</option>";
                    this.oldOptions[cleanId] = undefined;
                }
                n++;
            }
        }
        $("#right" + this.id.pluralize()).append(buffer.join(''));
        if(n == 0){
            if(typeof this.oldOptions["no"] != "undefined"){
                this.oldOptions["no"].appendTo($("#right" + this.id.pluralize()));
            }
        }
        else{
            var no = $("#no").detach();
            if(no.length > 0){
                this.oldOptions["no"] = no;
            }
        }
    }
    
    this.placeInHidden = function(){
        var obj = this;

        $("#hidden" + this.id.pluralize()).empty();
        var values = Array();
        this.leftArray = Array();
        $.each($("#left" + this.id.pluralize()).children(), function(index, val){
            values.push('"' + val.innerHTML + '"');
            $("#hidden" + obj.id.pluralize()).append("<input class=\'auth\' type=\'hidden\' name=\'" + obj.id + "[]\' value=\'" + val.innerHTML + "\' />");
            obj.leftArray.push(val.innerHTML);
        });
        $("input[name=" + this.id + "]").val(values.join(', ')).change();
    }
    
    this.init = function(){
        var obj = this;
        $.each($("#" + this.id).children(".left").children(), function(index, value){
            $(".noshow", $(value)).replaceWith("\"");
            var val = $(value).html();
            obj.leftArray[index] = val;
        });
        $.each($("#" + this.id).children(".right").children(), function(index, value){
            $(".noshow", $(value)).replaceWith("\"");
            var val = $(value).html();
            obj.rightArray[index] = val;
        });
        this.values = this.leftArray.concat(this.rightArray);
        for(i = 0; i < this.values.length; i++){
            this.unaccentedValues[i] = unaccentChars(this.values[i].replace(/ /g, "."));
            this.cleanIds[i] = this.id + this.cleanId(this.values[i].replace(/\./g, ""));
        }
        var customMessage = "";
        if(this.customAllowed){
            customMessage = "If the " + this.name + " is not in the list, you can add a custom " + this.name + " by entering in text in the Search Bar, and selecting the first name in the list.  ";
        }
        var message = "To add " + this.name.pluralize() + ", select one or more from the right selection box, and click the '&lt;&lt;' button.  You can filter the results by entering text in the Search bar.  " + customMessage + "To re-order the " + this.name.pluralize() + " in the left selection box, select one or more " + this.name.pluralize() + " and click either the '&uarr;' or the '&darr;' buttons.  To remove " + this.name.pluralize() + ", select one or more from the left selection box, and click the '&gt;&gt;' button.";
        
        $("#" + this.id).html("<input type='hidden' value='' name='" + this.id + "' /><table style='display:none'><tr><td colspan='3' style='display:none;width:100px;'></td></tr><tr><td align=\'center\'><b>Current " + this.name.pluralize() + "</b></td><td></td><td><div style='float:right;'><b id='" + this.id + "helpCell'>?</b></div><b>Search/Add " + this.name.pluralize() + ":</b><br /><input onKeyPress=\'return disableEnterKey(event)\' id=\'search" + this.id.pluralize() + "\' style=\'width:238px;\' type=\'text\' /></td></tr>" +
                "<tr><td><div id=\'hidden" + this.id.pluralize() + "\' style=\'display:none\'></div>" +
                    "<select id=\'left" + this.id.pluralize() + "\' size=\'10\' style=\'width:250px;\' multiple>" +
                    "</select><center style='margin-top:2px;'><input type=\'button\' value=\'&uarr;\' id=\'moveUp" + this.id.pluralize() + "\' />&nbsp;<input type=\'button\' value=\'&darr;\' id=\'moveDown" + this.id.pluralize() + "\' /></center></td>" +
            "<td>" +
                "<input type=\'button\' value=\'<<\' id=\'moveLeft" + this.id.pluralize() + "\' /><br />" +
                "<br />" +
                "<input type=\'button\' value=\'>>\' id=\'moveRight" + this.id.pluralize() + "\' />" +
            "</td>" +
            "<td valign='top'>" +
            "<select id=\'right" + this.id.pluralize() + "\' size=\'10\' style=\'width:250px;\' multiple>" +
                "<option id=\'no" + this.id + "\' disabled>Search did not match any " + this.name + "</option>\n" +
            "</select></td></tr></table>");
        $("#" + this.id + 'helpCell').qtip({content: message});
        var leftBuffer = "";
        for(index in this.leftArray){
            try{
                var value = this.leftArray[index];
                if(value.indexOf("\"") == -1){
                    value = value.replace(/\./g, " ");
                }
                if(value != ""){
                    leftBuffer += "<option id=\'" + this.id + this.cleanId(value) + "\'>" + value + "</option>";
                }
            }
            catch(e){
            
            }
        }
        var rightBuffer = "";
        for(index in this.rightArray){
            try{
                var value = this.rightArray[index];
                if(value.indexOf("\"") == -1){
                    value = value.replace(/\./g, " ");
                }
                if(value != ""){
                    rightBuffer += "<option id=\'" + this.id + this.cleanId(value) + "\'>" + value + "</option>";
                }
            }
            catch(e){

            }
        }
        
        $("#left" + this.id.pluralize()).html(leftBuffer);
        $("#right" + this.id.pluralize()).html(rightBuffer);
        
        $("#" + this.id).children("table").css("display", "block");
        
        var no = $("#no" + this.id).detach();
        if(no.length > 0){
            this.oldOptions["no"] = no;
        }
    };
    
    this.init();
    this.placeInHidden();
    //this.filterResults();
    
    $("#search" + this.id.pluralize()).attr("autocomplete", "off");
    
    $("form").submit(function(){
        obj.placeInHidden();
    });
    
    $("#search" + this.id.pluralize()).keyup(function(event) {
        if(event.keyCode != 40 && event.keyCode != 38){
            obj.filterResults();
        }
    });
    
    $("#" + this.id + "help").click(function(){
        $("#" + obj.id + "helpCell").toggle();
        if($("#" + obj.id + "help").html() == "Show Help"){
            $("#" + obj.id + "help").html("Hide Help");
        }
        else if($("#" + obj.id + "help").html() == "Hide Help"){
            $("#" + obj.id + "help").html("Show Help");
        }
    });
    
    $("#moveLeft" + this.id.pluralize()).click(function(){
        $.each($("#right" + obj.id.pluralize()).children().filter(":selected"), function(index, value){
            $("#left" + obj.id.pluralize()).append($(value).detach());
            //obj.values.splice(obj.values.indexOf(value.value.replace(/ /g, ".")), 1);
        });
        obj.placeInHidden();
    });
    
    $("#moveRight" + this.id.pluralize()).click(function(){
        $.each($("#left" + obj.id.pluralize()).children().filter(":selected"), function(index, value){
            $("#right" + obj.id.pluralize()).append($(value).detach());
            //obj.values.push(value.value.replace(/ /g, "."));
        });
        obj.values.sort();
        obj.filterResults();
        obj.placeInHidden();
    });
    
    $("#moveUp" + this.id.pluralize()).click(function(){
        $.each($("#left" + obj.id.pluralize()).children().filter(":selected"), function(index, value){
            var prev = $(value).prev();
            if(prev.length > 0){
                var object = $(value).detach();
                object.insertBefore(prev);
                //obj.values.push(value.value.replace(/ /g, "."));
            }
        });
        obj.placeInHidden();
    });
    
    $("#moveDown" + this.id.pluralize()).click(function(){
        $($("#left" + obj.id.pluralize()).children().filter(":selected").get().reverse()).each(function(index, value){
            var next = $(value).next();
            if(next.length > 0){
                var object = $(value).detach();
                object.insertAfter(next);
                //obj.values.push(value.value.replace(/ /g, "."));
            }
        });
        obj.placeInHidden();
    });
    
    // Events
    $("#search" + this.id.pluralize()).keypress(function(event) {
        if(event.keyCode == 40){        //DOWN
            $.each($("#right" + obj.id.pluralize()).children(":selected").not("#no" + obj.id.pluralize()), function(index, value){
                if($(value).next().length > 0){
                    $(value).attr("selected", false);
                    $(value).next().attr("selected", true);
                }
            });
        }
        else if(event.keyCode == 38){   //UP
            $.each($("#right" + obj.id.pluralize()).children(":selected").not("#no" + obj.id.pluralize()), function(index, value){
                if($(value).prev().length > 0){
                    $(value).attr("selected", false);
                    $(value).prev().attr("selected", true);
                }
            });
        }
    });
}
})( jQuery );

function disableEnterKey(e){
    var key;
    
    if(window.event){
        key = window.event.keyCode;     //IE
    }
    else{
        key = e.which;     //firefox
    }
    if(key == 13){      // ENTER
        return false;
    }
    else{
        return true;
    }
}

function createSwitcheroos(){
    var switcheroos = Array();
    $.each($(".switcheroo"), function(index, value){
        if(!$(value).hasClass('created')){
            var s = $(this).switcheroo({name: $(value).attr("name"), 
                                        id: $(value).attr("id"), 
                                        noCustom: !$(value).hasClass('noCustom')
                                       });
            $(value).addClass('created');
            switcheroos.push(s);
            $(value).css("display", "block");
        }
    });
}

$(document).ready(function(){
    createSwitcheroos();
});
