jQuery.fn.forceNumeric = function (options) {
    if(_.isUndefined(options.min)){
        options.min = "";
    }
    if(_.isUndefined(options.max)){
        options.max = "";
    }
    if(_.isUndefined(options.decimals)){
        options.decimals = 0;
    }
    var regex = new RegExp('^[0-9,-]*\.?[0-9]{0,' + options.decimals + '}$');
     return this.each(function () {
        var lastValue = $(this).val();
        
        var validateMax = function(target, checkMax){
            if($(target).val() == ""){
                lastValue = options.min;
                return;
            }
            if(!(regex.test('' + $(target).val() + ''))){
                if(_.isNumber(parseFloat($(target).val()))) {
                    $(target).val(Number(Math.round($(target).val()+'e'+options.decimals)+'e-'+options.decimals));
                } else {
                    $(target).val(lastValue);
                }
                return;
            }
            var minVal = $(target).val().replace(/,/g, '');
            if(checkMax){
                minVal = Math.min(options.max, minVal);
            }
            
            if(_.isNaN(minVal)){
                $(target).val(lastValue);
                return;
            }
            if($(target).val() != minVal){
                $(target).val(minVal);
            }
            if(options.includeCommas == true){
                $(target).val($(target).val().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,"));
            }
            lastValue = $(target).val();
        }
        
        var validateMin = function(target, checkMin){
            if($(target).val() == ""){
                lastValue = options.min;
                return;
            }
            if(!(regex.test('' + $(target).val() + ''))){
                if(_.isNumber(parseFloat($(target).val()))) {
                    $(target).val(Number(Math.round($(target).val()+'e'+options.decimals)+'e-'+options.decimals));
                } else {
                    $(target).val(lastValue);
                }
                return;
            }
            var maxVal = $(target).val().replace(/,/g, '');
            if(checkMin) {
                maxVal = Math.max(options.min, maxVal);
            }
            
            if(_.isNaN(maxVal)){
                $(target).val(lastValue);
                return;
            }
            if($(target).val() != maxVal){
                $(target).val(maxVal);
            }
            if(options.includeCommas == true){
                $(target).val($(target).val().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,"));
            }
            lastValue = $(target).val();
        }
        
        var checkArrows = function(e){
            var key = e.which || e.keyCode;
            if (!e.shiftKey && !e.altKey && !e.ctrlKey &&
                key == 37 || key == 39){
                return false;
            }
            return true;
        };
        
        if(options.max != ""){
            $(this).keyup(function(e){
                if(checkArrows(e)){
                    validateMax(e.target, false);
                }
            });
            $(this).change(function(e){
                validateMax(e.target, true);
            });
            validateMax(this, true);
        }
        if(options.min != ""){
            $(this).keyup(function(e){
                if(checkArrows(e)){
                    validateMin(e.target, false);
                }
            });
            $(this).change(function(e){
                validateMin(e.target, true);
            });
            validateMin(this, true);
        }
        $(this).change(function(e) { 
            if(!(regex.test($(e.target).val()))){
                if(_.isNumber(parseFloat($(e.target).val()))) {
                    $(e.target).val(Number(Math.round($(e.target).val()+'e'+options.decimals)+'e-'+options.decimals));
                } else {
                    $(e.target).val(options.min);
                }
                lastValue = $(e.target).val();
            }
        });
        if(!(regex.test($(this).val()))){
            if(_.isNumber(parseFloat($(this).val()))) {
                $(this).val(Number(Math.round($(this).val()+'e'+options.decimals)+'e-'+options.decimals));
            } else {
                $(this).val(options.min);
            }
            lastValue = $(this).val();
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
