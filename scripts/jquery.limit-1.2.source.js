(function($){ 
     $.fn.extend({  
         limit: function(limit,element) {
			
			var interval, f;
			var self = $(this);
					
			$(this).focus(function(){
				interval = window.setInterval(substring,60);
			});
			
			$(this).blur(function(){
				clearInterval(interval);
				substring();
			});
			
			function substring(){ 
			    var val = $(self).val();
			    var length = 0;
			    if($(self).hasClass('autocomplete')){
			        var regex = RegExp('@\\[[^-]+-([^\\]]*)]','g');
			        length = val.replace(regex, ' ').length;
			    }
			    else{
			        length = val.length;
			    }
			    if(length > limit){
			        $(self).val($(self).val().substring(0,limit));
			    }
			    if(typeof element != 'undefined'){
				    if($(element).html() != length){
				        $(element).html((limit-length<=0)?limit:length);
				    }
				}
			}
			$(document).ready(function(){
		        substring();
		        setTimeout(substring, 100);
		        setTimeout(substring, 250);
		    });
        } 
    }); 
})(jQuery);
