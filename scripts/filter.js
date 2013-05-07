String.prototype.regexIndexOf = function(regex, startpos) {
    try {
        var indexOf = this.substring(startpos || 0).search(regex);
        return (indexOf >= 0) ? (indexOf + (startpos || 0)) : indexOf;
    } catch(e){
        return -1;
    }
}

function filterResults(value){
    if(typeof value != 'undefined'){
        var value = value.replace(/ /g, '|');
        $.each($("tr[name=search]"), function(index, val){
            if(val.id.replace(/\./g, " ").toLowerCase().regexIndexOf(value.toLowerCase()) != -1 || $(val).attr("class").toLowerCase().regexIndexOf(value.toLowerCase()) != -1){
                $(val).show();
            }
            else{
                $(val).hide();
            }
        });
    }
}

$(document).ready(function(){
    filterResults($("#search").val());
    $("#search").attr("autocomplete", "off");
});
