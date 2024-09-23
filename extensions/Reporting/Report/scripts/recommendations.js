$(document).ready(function(){
    $('#chairTable tbody tr').each(function(i, tr){
        $('.tenure select', tr).change(function(){
            var value = $(this).val();
            if(value == "i recommend that an appointment with tenure be offered" ||
               value == "i recommend tenure as per clause 12.17 (special recommendation for tenure)" ||
               value == "i recommend that continuing appointment be offered" ||
               value == "already has tenure"){
                $('.promotion select option', tr).each(function(){
                    if($(this).val() != "n/a"){
                        $(this).show();
                    }
                });
            }
            else{
                $('.promotion select option', tr).each(function(){
                    if($(this).val() != "n/a"){
                        $(this).hide();
                    }
                });
            }
        }).change();
        $('.promotion select', tr).change(function(){
            var value = $(this).val();
            if(value.indexOf('i recommend promotion') != -1){
                // Show all options
                $(".increment option", tr).show();
            }
            else{
                // Hide options after PTC
                var foundPTC = false;
                var PTC = null;
                $(".increment option", tr).each(function(){
                    if(foundPTC && $(this).val() != "0A" && 
                                   $(this).val() != "0B" && 
                                   $(this).val() != "0C" && 
                                   $(this).val() != "0D"){
                        // PTC was already found, so hide anything after it
                        $(this).hide();
                        if($(this).is(":selected")){
                            // Something higher than the PTC was selected, so unselect it
                            $(this).prop("selected", false);
                            if(PTC != null){
                                // Now select the PTC
                                $(PTC).prop("selected", true);
                            }
                        }
                    }
                    if($(this).val().indexOf("PTC") != -1){
                        foundPTC = true;
                        PTC = this;
                    }
                });
            }
        }).change();
        
        if($(".promotion").length == 0){
            // Hide options after PTC
            var foundPTC = false;
            var PTC = null;
            $(".increment option", tr).each(function(){
                if(foundPTC && $(this).val() != "0A" && 
                               $(this).val() != "0B" && 
                               $(this).val() != "0C" && 
                               $(this).val() != "0D"){
                    // PTC was already found, so hide anything after it
                    $(this).hide();
                    if($(this).is(":selected")){
                        // Something higher than the PTC was selected, so unselect it
                        $(this).prop("selected", false);
                        if(PTC != null){
                            // Now select the PTC
                            $(PTC).prop("selected", true);
                        }
                    }
                }
                if($(this).val().indexOf("PTC") != -1){
                    foundPTC = true;
                    PTC = this;
                }
            });
        }
    });
});
