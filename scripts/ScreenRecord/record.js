(function( $ ){

    $.fn.record = function(options) {
        var that = this;
        var convertURL = '';
        var delay = 10*1000;
        var convertSVG = false;
        var interval = null;
        var recordInterval = null;
        var el;
        var onCapture = undefined;
        var onFinishedRecord = undefined;
        var story = Array();
        var target = '';
        
        var recordButton;
        var screenshotButton;
        var timeLeft;
        
        if(options.convertSVG == true && options.convertURL != undefined){
            convertURL = options.convertURL;
            convertSVG = true;
        }
        if(options.delay != undefined){
            delay = options.delay;
        }
        if(options.onCapture != undefined){
            onCapture = options.onCapture;
        }
        if(options.onFinishedRecord != undefined){
            onFinishedRecord = options.onFinishedRecord;
        }
        if(options.el != undefined){
            el = $(options.el);
        }
        else{
            el = $(this).parent();
        }
        var timeTillNext = parseInt(delay/1000);
    
        this.init = function(){
            var recordDiv = $("<div class='record'>");
            recordDiv.css('padding', '2px');
            recordButton = $('<button onClick="return false;" style="padding:3px 10px !important;font-size:10px !important;">Record <span class="record" style="font-size:12px;">●</span></button>');
            screenshotButton = $('<button class="takeScreenshot" style="padding:3px 10px !important;font-size:10px !important;" onClick="return false;">Capture (Alt+c)</button>');
            timeLeft = $('<span class="timeLeft" style="margin-left:20px;font-size:10px;"></span>');
            
            $(window).keydown(function(e){
                if(e.altKey && e.keyCode == 67){ // Alt + c
                    that.takeScreenshot();
                }
            });
            recordButton.click(function(e){
                if(interval == null){
                    $("span.record", $(that).parent()).css('color', '#FF0000');
                    $(screenshotButton).show();
                    that.start();
                    e.stopPropagation();
                }
                else{
                    $(screenshotButton).hide();
                    that.stop();
                    if(onFinishedRecord != undefined){
                        onFinishedRecord(story.slice(0));
                    }
                    story = Array();
                }
            });
            $(screenshotButton).click(function(){
                that.takeScreenshot();
            });
            
            screenshotButton.hide();
            
            recordButton.appendTo(recordDiv);
            screenshotButton.appendTo(recordDiv);
            timeLeft.appendTo(recordDiv);
            
            recordDiv.appendTo($(el));
        }
        
        this.takeScreenshot = function(){
            if(interval != null){
                if(delay > 0){
                    clearInterval(interval);
                }
                clearInterval(recordInterval);
                $(timeLeft).html('Capturing...');
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
                        that.html2canvas(function(canvas){
                            converted.forEach(function(c, cId){
                                $("#img" + cId).replaceWith(c);
                            });
                            if(onCapture != undefined){
                                onCapture(canvas);
                            }
                        });
                    });
                }
                else{
                    that.html2canvas(function(canvas){
                        if(onCapture != undefined){
                            onCapture(canvas);
                        }
                    });
                }
                if(delay > 0){
                    interval = setInterval(that.takeScreenshot, delay);
                    timeTillNext = parseInt(delay/1000);
                }
                else{
                    interval = 0;
                }
                recordInterval = setInterval(that.recordBlink, 1000);
            }
        }
        
        this.recordBlink = function(){
            if($("span.record", $(that).parent()).css('color') == "rgb(255, 0, 0)"){
                $("span.record", $(that).parent()).css('color', '');
            }
            else{
                $("span.record", $(that).parent()).css('color', '#FF0000');
            }
            if(delay > 0){
                timeTillNext--;
                $(timeLeft).html('Next Screenshot in ' + timeTillNext + ' s');
            }
            else{
                $(timeLeft).empty();
            }
        }
    
        this.start = function(){
            var outline = DomOutline({onClick: function(dom){
                    target = dom;
                    that.stop();
                    if(delay > 0){
                        interval = setInterval(that.takeScreenshot, delay);
                    }
                    else{
                        interval = 0;
                    }
                    recordInterval = setInterval(this.recordBlink, 1000);
                    that.takeScreenshot();
                }
            });
            outline.start();
        }
        
        this.stop = function(){
            $(timeLeft).empty();
            if(delay > 0){
                clearInterval(interval);
            }
            interval = null;
            clearInterval(recordInterval);
            recordInterval = null;
            $("span.record", $(that).parent()).css('color', '');
        }
        
        this.html2canvas = function(callback){
            html2canvas(target, {
                onrendered: function(canvas) {
                    var data = {
                                'url' : document.location.toString(),
                                'img' : canvas.toDataURL().replace('data:image/png;base64,', ''),
                                'date': new Date().toJSON()
                               };
                    story.push(data);
                    $(that).append(canvas);
                    if(callback != undefined){
                        callback(canvas);
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
