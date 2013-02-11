(function( $ ){

    $.fn.record = function(options) {
        var that = this;
        var convertURL = '';
        var delay = 10*1000;
        var convertSVG = false;
        var interval = null;
        var recordInterval = null;
        
        if(options.convertSVG == true && options.convertURL != undefined){
            convertURL = options.convertURL;
            convertSVG = true;
        }
        if(options.delay != undefined){
            delay = options.delay;
        }
    
        this.init = function(){
            var button = $('<button onClick="return false;">Record <span class="record" style="font-size:15px;">●</span></button>');
            var button2 = $('<button class="takeScreenshot" onClick="return false;" disabled="disabled">Take Screenshot (Alt+r)</button>');
            $(window).keydown(function(e){
                if(e.altKey && e.keyCode == 82){ // Alt + r
                    that.takeScreenshot();
                }
            });
            button.click(function(e){
                if(interval == null){
                    $("span.record", $(that).parent()).css('color', '#FF0000');
                    that.start();
                    e.stopPropagation();
                }
                else{
                    that.stop();
                }
            });
            button.insertBefore(this);
            button2.insertBefore(this);
        }
        
        this.takeScreenshot = function(){
            if(interval != null){
                clearInterval(interval);
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
                            converted.forEach(function(c, cId){
                                $("#img" + cId).replaceWith(c);
                            });
                        });
                    });
                }
                else{
                    that.html2canvas();
                }
                interval = setInterval(that.takeScreenshot, delay);
            }
        }
    
        this.start = function(){
            that.stop();
            $(".takeScreenshot", $(that).parent()).removeAttr("disabled");
            $(".takeScreenshot", $(that).parent()).on("click", function(){
                that.takeScreenshot();
            });
            recordInterval = setInterval(function(){
                if($("span.record", $(that).parent()).css('color') == "rgb(255, 0, 0)"){
                    $("span.record", $(that).parent()).css('color', '');
                }
                else{
                    $("span.record", $(that).parent()).css('color', '#FF0000');
                }
            }, 1000);
            interval = setInterval(that.takeScreenshot, delay);
            that.takeScreenshot();
        }
        
        this.stop = function(){
            $(".takeScreenshot", $(that).parent()).attr("disabled", "disabled");
            $(".takeScreenshot", $(that).parent()).off("click");
            clearInterval(interval);
            clearInterval(recordInterval);
            interval = null;
            recordInterval = null;
            $("span.record", $(that).parent()).css('color', '');
        }
        
        this.html2canvas = function(callback){
            html2canvas(that, {
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
