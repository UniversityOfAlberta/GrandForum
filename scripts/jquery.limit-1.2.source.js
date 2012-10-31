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
			    var regex = RegExp('@\\[[^-]+-([^\\]]*)]','g');
			    var length = val.replace(regex, ' ').length;
			    if(length > limit){
			        $(self).val($(self).val().substring(0,limit));
			    }
			    if(typeof element != 'undefined'){
				    if($(element).html() != length){
				        $(element).html((limit-length<=0)?limit:length);
				    }
				}
			}
			substring();
        } 
    }); 
})(jQuery);
