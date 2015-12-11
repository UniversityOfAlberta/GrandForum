jQuery.fn.forceNumeric = function (options) {
    
     return this.each(function () {
        var validateMax = function(target){
            if($(target).val() == ""){
                return;
            }
            $(target).val(Math.min(options.max, $(target).val().replace(/,/g, '')));
            if(options.includeCommas == true){
                $(target).val($(target).val().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,"));
            }
        }
        
        var validateMin = function(target){
            if($(target).val() == ""){
                return;
            }
            $(target).val(Math.max(options.min, $(target).val().replace(/,/g, '')));
            if(options.includeCommas == true){
                $(target).val($(target).val().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,"));
            }
        }
        
        if(options.max != undefined && options.max != ""){
            $(this).keyup(function(e){
                validateMax(e.target);
            });
            validateMax(this);
        }
        if(options.min != undefined && options.min != ""){
            $(this).keyup(function(e){
                validateMin(e.target);
            });
            validateMin(this);
        }
        if(!(/^[0-9,]+$/.test($(this).val()))){
            $(this).val("");
        }
        $(this).keydown(function (e) {
             var key = e.which || e.keyCode;

             if (!e.shiftKey && !e.altKey && !e.ctrlKey &&
             // numbers   
                 key >= 48 && key <= 57 ||
             // Numeric keypad
                 key >= 96 && key <= 105 ||
             // comma, period and minus, . on keypad
                key == 190 || key == 188 || key == 109 || key == 110 ||
             // Backspace and Tab and Enter
                key == 8 || key == 9 || key == 13 ||
             // Home and End
                key == 35 || key == 36 ||
             // left and right arrows
                key == 37 || key == 39 ||
             // Del and Ins
                key == 46 || key == 45)
                 return true;

             return false;
         });
     });
}
