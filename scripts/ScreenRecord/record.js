(function( $ ){

    $.fn.record = function(options) {
        var that = this;
        var convertURL = '';
        var convertSVG = false;
        
        if(options.convertSVG == true && options.convertURL != undefined){
            convertURL = options.convertURL;
            convertSVG = true;
        }
    
        this.init = function(){
            var button = $('<button onClick="return false;">Annotate</button>');
            button.click(function(e){
                that.start();
                e.stopPropagation();
            });
            button.insertBefore(this);
        }
    
        this.start = function(){
            if(convertSVG && $($("svg:visible"), that).length > 0){
                var converted = Array();
                var deferreds = Array();
                $.each($($("svg:visible"), that), function(index, value){
                    var svg = $(value).clone().wrapAll("<div/>").parent().html();
                    deferreds.push($.post(convertURL, {'svg': svg}, function(response){
                        var img = $("<img id='img" + index + "' src='data:image/png;base64, " + response + "' />");
                        var oldValue = $(value).replaceWith(img);
                        converted[index] = oldValue;
                    }));
                });
                $.when.apply(null, deferreds).done(function(){
                    that.html2canvas(function(){
                        for(cId in converted){
                            var c = converted[cId];
                            $("#img" + cId).replaceWith(c);
                        }
                    });
                });
            }
            else{
                this.html2canvas();
            }
        }
        
        this.html2canvas = function(callback){
            html2canvas(that, {
                allowTaint: true,
                taintTest: false,
                onrendered: function(canvas) {
                    $(canvas).insertAfter(that);
                    if(callback != undefined){
                        callback();
                    }
                }
            });
        }
        
        if(options == 'start'){
            this.start();
        }
        else{
            this.init();
        }
    };
  
    
    
})( jQuery );
