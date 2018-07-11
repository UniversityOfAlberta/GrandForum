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
    });
});
