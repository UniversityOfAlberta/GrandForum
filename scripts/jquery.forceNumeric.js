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
    var regex = new RegExp('^[0-9,]*\.?[0-9]{0,' + options.decimals + '}$');
     return this.each(function () {
        var lastValue = $(this).val();
        
        var validateMax = function(target){
            if($(target).val() == ""){
                lastValue = options.min;
                return;
            }
            if(!(regex.test('' + $(target).val() + ''))){
                $(target).val(lastValue);
                return;
            }
            var minVal = Math.min(options.max, $(target).val().replace(/,/g, ''));
            
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
        
        var validateMin = function(target){
            if($(target).val() == ""){
                lastValue = options.min;
                return;
            }
            if(!(regex.test('' + $(target).val() + ''))){
                $(target).val(lastValue);
                return;
            }
            var maxVal = Math.max(options.min, $(target).val().replace(/,/g, ''));
            
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
                    validateMax(e.target);
                }
            });
            $(this).change(function(e){
                if(checkArrows(e)){
                    validateMax(e.target);
                }
            });
            validateMax(this);
        }
        if(options.min != ""){
            $(this).keyup(function(e){
                if(checkArrows(e)){
                    validateMin(e.target);
                }
            });
            $(this).change(function(e){
                if(checkArrows(e)){
                    validateMax(e.target);
                }
            });
            validateMin(this);
        }
        if(!(regex.test($(this).val()))){
            $(this).val(options.min);
            lastValue = options.min;
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
