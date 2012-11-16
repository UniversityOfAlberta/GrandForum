(function($){ 
     $.fn.extend({  
         multiLimit: function(limit,element,textareas) {
            for(i in textareas){
                var textarea = textareas[i];
                $(textarea).focus(function(){
			        interval = window.setInterval(limitChars,60);
		        });
		
		        $(textarea).blur(function(){
			        clearInterval(interval);
			        limitChars();
		        });
		    }
		    $(document).ready(function(){
		        limitChars();
		        setTimeout(limitChars, 100);
		        setTimeout(limitChars, 250);
		    });
		    
		    function limitChars(){
		        var length = 0;
		        for(i in textareas){
		            var textarea = textareas[i];
		            if($(textarea).hasClass('autocomplete')){
		                var regex = RegExp('@\\[[^-]+-([^\\]]*)]','g');
		                length += $(textarea).val().replace(regex, ' ').length;
		            }
		            else{
		                length += $(textarea).val().length;
		            }
		        }
		        if($(element).prop("tagName") == "SPAN" ||
		           $(element).prop("tagName") == "DIV"){
		            $(element).html(length);
		        }
		        changeColor(length);
		    }
		    
		    function changeColor(length){
                if(length > limit){
                    $(element).parent().addClass('inlineError');
                    $(element).parent().removeClass('inlineWarning');
                }
                else if(length == 0){
                    $(element).parent().addClass('inlineWarning');
                    $(element).parent().removeClass('inlineError');
                }
                else{
                    $(element).parent().removeClass('inlineError');
                    $(element).parent().removeClass('inlineWarning');
                }
            }
        } 
    }); 
})(jQuery);
