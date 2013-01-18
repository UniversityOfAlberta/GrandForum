function switcheroo(name, id, customAllowed){
    this.name = name;
    this.id = id;
    this.customAllowed = customAllowed;
    this.values = new Array();
    this.oldOptions = new Array();
    this.leftArray = new Array();
    this.rightArray = new Array();
    
    var obj = this;
    
    this.cleanId = function(str){
        str = str.replace(/ /g, '')
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
                 .replace(/\-/g, '');
        return str;
    }
    
    this.filterResults = function(){
        var value = $("#search" + this.id + "s").attr("value");
        $.each($("#right" + this.id + "s").children().not("#no" + this.id), function(index, val){
            var valSelect = val.id;
            if(!$(val).hasClass("custom")){
                obj.oldOptions[valSelect] = $("#" + valSelect).detach();
            }
            else{
                $(val).remove();
            }
        });
        if(value == ""){
            var no = $("#no").detach();
            if(no.length > 0){
                this.oldOptions["no"] = no;
            }
        }
        else{
            var skip = false;
            for(i = 0; i < this.values.length; i++){
                var cleanId = this.cleanId(this.id + this.values[i].replace(/\./g, ""));
                if(value.replace(/ /g, ".").toLowerCase() == this.values[i].replace(/ /g, ".").toLowerCase()){
                    skip = true;
                    if(typeof this.oldOptions[cleanId] != "undefined"){
                        this.oldOptions[cleanId].attr("selected", true);
                    }
                    break;
                }
                else{
                    if(typeof this.oldOptions[cleanId] != "undefined"){
                        this.oldOptions[cleanId].attr("selected", false);
                    }
                }
            }
            if(!skip){
                if(this.customAllowed){
                    var customName = document.createElement("option");
                    customName.innerHTML = value;
                    customName.setAttribute("class", "custom");
                    customName.setAttribute("style", "font-style: italic;");
                    $(customName).attr("selected", "true");
                    $(customName).appendTo($("#right" + this.id + "s"));
                }
            }
        }
        var n = 0;
        var buffer = Array();
        for(i = 0; i < this.values.length; i++){
            var val = this.values[i];
            var valSelect = this.cleanId(val.replace(/\./g, ""));
            if(val.replace(/ /g, ".").toLowerCase().indexOf(value.replace(/ /g, ".").toLowerCase()) != -1){
                if(typeof this.oldOptions[this.id + valSelect] != "undefined"){
                    buffer[i] = "<option id='" + $(this.oldOptions[this.id + valSelect]).attr("id") + "'>" + this.oldOptions[this.id + valSelect].html() + "</option>";
                    this.oldOptions[this.id + valSelect] = undefined;
                }
                n++;
            }
        }
        $("#right" + this.id + "s").append(buffer.join(''));
        if(n == 0){
            if(typeof this.oldOptions["no"] != "undefined"){
                this.oldOptions["no"].appendTo($("#right" + this.id + "s"));
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
        $.each($("#left" + this.id + "s").children(), function(index, val){
            $("#hidden" + obj.id + "s").append("<input class=\'auth\' type=\'hidden\' name=\'" + obj.id + "[]\' value=\'" + val.innerHTML + "\' />");
        });
    }
    
    this.init = function(){
        var obj = this;
        $.each($("#" + this.id).children(".left").children(), function(index, value){
            obj.leftArray[index] = $(value).html();
        });
        $.each($("#" + this.id).children(".right").children(), function(index, value){
            obj.rightArray[index] = $(value).html();
        });
        this.values = this.leftArray.concat(this.rightArray);
        var customMessage = "";
        if(this.customAllowed){
            customMessage = "If the " + this.name + " is not in the list, you can add a custom " + this.name + " by entering in text in the Search Bar, and selecting the first name in the list.  ";
        }
        var message = "To add " + this.name.pluralize() + ", select one or more from the right selection box, and click the '&lt;&lt;' button.  You can filter the results by entering text in the Search bar.  " + customMessage + "To re-order the " + this.name + "s in the left selection box, select one or more " + this.name.pluralize() + " and click either the '&uarr;' or the '&darr;' buttons.  To remove " + this.name.pluralize() + ", select one or more from the left selection box, and click the '&gt;&gt;' button.";
        
        $("#" + this.id).html("<table style='display:none'><tr><td colspan='3' style='display:none;width:100px;'></td></tr><tr><td align=\'center\'><b>Current " + this.name.pluralize() + "</b></td><td></td><td><div style='float:right;'><b id='" + this.id + "helpCell'>?</b></div><b>Search/Add " + this.name.pluralize() + ":</b><br /><input onKeyPress=\'return disableEnterKey(event)\' id=\'search" + this.id + "s\' style=\'width:100%;\' type=\'text\' /></td></tr>" +
                "<tr><td><div id=\'hidden" + this.id + "s\' style=\'display:none\'></div>" +
                    "<select id=\'left" + this.id + "s\' size=\'10\' style=\'width:250px;\' multiple>" +
                    "</select><center><input type=\'button\' value=\'&uarr;\' id=\'moveUp" + this.id + "s\' />&nbsp;<input type=\'button\' value=\'&darr;\' id=\'moveDown" + this.id + "s\' /></center></td>" +
            "<td>" +
                "<input type=\'button\' value=\'<<\' id=\'moveLeft" + this.id + "s\' /><br />" +
                "<br />" +
                "<input type=\'button\' value=\'>>\' id=\'moveRight" + this.id + "s\' />" +
            "</td>" +
            "<td valign='top'>" +
            "<select id=\'right" + this.id + "s\' size=\'10\' style=\'width:250px;\' multiple>" +
                "<option id=\'no" + this.id + "\' disabled>Search did not match any " + this.name + "</option>\n" +
            "</select></td></tr></table>");
        $("#" + this.id + 'helpCell').qtip({content: message});
        var leftBuffer = "";
        for(index in this.leftArray){
            try{
                var value = this.leftArray[index].replace(/\./g, " ");
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
                var value = this.rightArray[index].replace(/\./g, " ");
                if(value != ""){
                    rightBuffer += "<option id=\'" + this.id + this.cleanId(value) + "\'>" + value + "</option>";
                }
            }
            catch(e){

            }
        }
        
        $("#left" + this.id + "s").html(leftBuffer);
        $("#right" + this.id + "s").html(rightBuffer);
        
        $("#" + this.id).children("table").css("display", "block");
        
        var no = $("#no" + this.id).detach();
        if(no.length > 0){
            this.oldOptions["no"] = no;
        }
    };
    
    this.init();
    //this.filterResults();
    
    $("#search" + this.id + "s").attr("autocomplete", "off");
    
    $("form").submit(function(){
        obj.placeInHidden();
    });
    
    $("#search" + this.id + "s").keyup(function(event) {
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
    
    $("#moveLeft" + this.id + "s").click(function(){
        $.each($("#right" + obj.id + "s").children().filter(":selected"), function(index, value){
            $("#left" + obj.id + "s").append($(value).detach());
            obj.values.splice(obj.values.indexOf(value.value.replace(/ /g, ".")), 1);
        });
    });
    
    $("#moveRight" + this.id + "s").click(function(){
        $.each($("#left" + obj.id + "s").children().filter(":selected"), function(index, value){
            $("#right" + obj.id + "s").append($(value).detach());
            obj.values.push(value.value.replace(/ /g, "."));
        });
        obj.values.sort();
        obj.filterResults();
    });
    
    $("#moveUp" + this.id + "s").click(function(){
        $.each($("#left" + obj.id + "s").children().filter(":selected"), function(index, value){
            var prev = $(value).prev();
            if(prev.length > 0){
                var object = $(value).detach();
                object.insertBefore(prev);
                //obj.values.push(value.value.replace(/ /g, "."));
            }
        });
    });
    
    $("#moveDown" + this.id + "s").click(function(){
        $($("#left" + obj.id + "s").children().filter(":selected").get().reverse()).each(function(index, value){
            var next = $(value).next();
            if(next.length > 0){
                var object = $(value).detach();
                object.insertAfter(next);
                //obj.values.push(value.value.replace(/ /g, "."));
            }
        });
    });
    
    // Events
    $("#search" + this.id + "s").keypress(function(event) {
        if(event.keyCode == 40){        //DOWN
            $.each($("#right" + obj.id + "s").children(":selected").not("#no" + obj.id + "s"), function(index, value){
                if($(value).next().length > 0){
                    $(value).attr("selected", false);
                    $(value).next().attr("selected", true);
                }
            });
        }
        else if(event.keyCode == 38){   //UP
            $.each($("#right" + obj.id + "s").children(":selected").not("#no" + obj.id + "s"), function(index, value){
                if($(value).prev().length > 0){
                    $(value).attr("selected", false);
                    $(value).prev().attr("selected", true);
                }
            });
        }
    });
}

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
            var s = new switcheroo($(value).attr("name"), $(value).attr("id"), !$(value).hasClass('noCustom'));
            $(value).addClass('created');
            switcheroos.push(s);
            $(value).css("display", "block");
        }
    });
}

$(document).ready(function(){
    createSwitcheroos();
});
