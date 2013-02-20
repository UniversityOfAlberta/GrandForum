(function( $ ){

    $.fn.record = function(options) {
        var that = this;
        var convertURL = '';
        var delay = 10*1000;
        var maxSize = 5*1000*1000;
        var convertSVG = false;
        var interval = null;
        var recordInterval = null;
        var el;
        var selectable = false;
        var onCapture = undefined;
        var onFinishedRecord = undefined;
        var story = Array();
        var target = '';
        var oldWindowOnBeforeUnload = undefined;
        var currentSize = 0;
        
        var recordButton;
        var pickButton;
        var screenshotButton;
        var timeLeft;
        var sizeLeft;
        
        if(options.convertSVG == true && options.convertURL != undefined){
            convertURL = options.convertURL;
            convertSVG = true;
        }
        if(options.delay != undefined){
            delay = options.delay;
        }
        if(options.maxSize != undefined){
            maxSize = options.maxSize;
        }
        if(options.onCapture != undefined){
            onCapture = options.onCapture;
        }
        if(options.onFinishedRecord != undefined){
            onFinishedRecord = options.onFinishedRecord;
        }
        if(options.selectable != undefined){
            selectable = options.selectable;
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
            recordButton = $('<button onClick="return false;" style="padding:3px 10px !important;font-size:10px !important;"><span class="recordText">Record</span> <span class="record" style="font-size:12px;">●</span></button>');
            pickButton = $('<button onClick="return false;" style="padding:3px 10px !important;font-size:10px !important;">Select Element</button>');
            screenshotButton = $('<button style="padding:3px 10px !important;font-size:10px !important;" onClick="return false;">Capture (Alt+c)</button>');
            sizeLeft = $('<span style="margin-left:20px;font-size:10px;"></span><br />');
            timeLeft = $('<span style="margin-left:20px;font-size:10px;"></span>');
            
            $(window).keydown(function(e){
                if(e.altKey && e.keyCode == 67){ // Alt + c
                    that.takeScreenshot();
                }
            });
            recordButton.click(function(e){
                if(interval == null){
                    that.start();
                    e.stopPropagation();
                }
                else{
                    screenshotButton.hide();
                    pickButton.hide();
                    sizeLeft.hide();
                    timeLeft.hide();
                    that.stop();
                    if(onFinishedRecord != undefined){
                        onFinishedRecord(story.slice(0));
                    }
                    story = Array();
                    window.onbeforeunload = oldWindowOnBeforeUnload;
                    oldWindowOnBeforeUnload = undefined;
                }
            });
            pickButton.click(function(e){
                that.start();
            });
            screenshotButton.click(function(){
                that.takeScreenshot();
            });
            
            pickButton.hide();
            screenshotButton.hide();
            sizeLeft.hide();
            timeLeft.hide();
            
            recordButton.appendTo(recordDiv);
            pickButton.appendTo(recordDiv);
            screenshotButton.appendTo(recordDiv);
            sizeLeft.appendTo(recordDiv);
            timeLeft.appendTo(recordDiv);
            
            recordDiv.appendTo($(el));
        }
        
        this.takeScreenshot = function(){
            if(interval != null){
                if(delay > 0){
                    clearInterval(interval);
                }
                clearInterval(recordInterval);
                timeLeft.html('Capturing...');
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
                timeLeft.html('Next Screenshot in ' + timeTillNext + ' s');
            }
            else{
                timeLeft.empty();
            }
        }
        
        this.showSize = function(over){
            if(parseFloat(currentSize) <= maxSize && over == false){
                sizeLeft.html(currentSize + '/' + Math.round(maxSize/1000/1000) + 'MB');
                sizeLeft.css('color', '');
            }
            else{
                sizeLeft.html(currentSize + '/' + Math.round(maxSize/1000/1000) + 'MB<br />The last screenshot exceeded the size limit.  Please stop recording to start a new session.');
                sizeLeft.css('color', '#FF0000');
            }
        }
        
        this.afterStart = function(dom){
            sizeLeft.show();
            timeLeft.show();
            screenshotButton.show();
            screenshotButton.css('display', 'inline-block');
            if(selectable){
                pickButton.show();
                pickButton.css('display', 'inline-block');
            }
            target = dom;
            that.stop();
            $("span.recordText", $(that).parent()).html('Stop');
            if(delay > 0){
                interval = setInterval(that.takeScreenshot, delay);
            }
            else{
                interval = 0;
            }
            that.showSize(false);
            recordInterval = setInterval(that.recordBlink, 1000);
            if(typeof oldWindowOnBeforeUnload == 'undefined'){
                oldWindowOnBeforeUnload = window.onbeforeunload;
                window.onbeforeunload = function(){ return "You are currently recording a screen capture session.  Leaving this page will cause the session to be lost.  To save the session, press the 'Stop' button."};
            }
        }
    
        this.start = function(){
            that.currentSize = 0;
            if(selectable){
                var outline = DomOutline({onClick: that.afterStart});
                outline.start();
            }
            else{
                that.afterStart($(that));
            }
        }
        
        this.stop = function(){
            timeLeft.empty();
            sizeLeft.empty();
            if(delay > 0){
                clearInterval(interval);
            }
            interval = null;
            clearInterval(recordInterval);
            recordInterval = null;
            $("span.record", $(that).parent()).css('color', '');
            $("span.recordText", $(that).parent()).html('Record');
        }
        
        this.html2canvas = function(callback){
            html2canvas(target, {
                onrendered: function(canvas) {
                    var img = canvas.toDataURL().replace('data:image/png;base64,', '');
                    if(img != ''){
                        var data = {
                                    'url' : document.location.toString() + document.location.hash,
                                    'img' : canvas.toDataURL().replace('data:image/png;base64,', ''),
                                    'date': new Date().toJSON(),
                                    'descriptions': Array(),
                                    'transition': ''
                                   };
                        var size = JSON.stringify(story).length;
                        sizeAfter = size + JSON.stringify(data).length;
                        
                        if(sizeAfter <= maxSize){
                            story.push(data);
                            currentSize = (sizeAfter/1000/1000).toFixed(2);
                            that.showSize(false);
                        }
                        else{
                            currentSize = (size/1000/1000).toFixed(2);
                            that.showSize(true);
                        }
                    }
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
