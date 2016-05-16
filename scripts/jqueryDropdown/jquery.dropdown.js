(function( $ ){

  $.fn.dropdown = function(options) {
    $.fn.imgToggle = function(){
        if($("span.dropdown", $(this)).hasClass('down')){
            this.imgUp();
        } 
        else{
            this.imgDown();
        }
    }
    
    $.fn.imgUp = function(){
        $("span.dropdown", $(this)).removeClass('down');
        $("span.dropdown", $(this)).addClass('up');
        $("span.dropdown", $(this)).html("&#x25B2;");
    }
    
    $.fn.imgDown = function(){
        $("span.dropdown", $(this)).removeClass('up');
        $("span.dropdown", $(this)).addClass('down');
        $("span.dropdown", $(this)).html("&#x25BC;");
    }
    
    $(this).addClass('dropdown');
    
    var title = (typeof options.title != 'undefined') ? options.title : 'DropDown';
    var width = (typeof options.width != 'undefined') ? options.width : '150px';
    
    var lis = $('li', $(this));
    $(lis).removeClass('selected');
    $(lis).addClass('action');
    $(lis).css('display', 'block');
    $('a', $(lis)).removeClass('highlights-tab');
    $(lis).addClass('highlights-background-hover');
    $(this).append("<div class='actions' />");
    var divActions = $('div.actions' ,$(this));
    $(lis).appendTo($(divActions));
    $(this).append("<li class='actions'><a>" + title + "<span class='dropdown down' style='margin-left:5px;'>&#x25BC;</span></a></li>");
    $(this).imgDown();
    var that = this;
    $('li.actions > a', $(this)).width($(divActions).width()-10); 
    var unHoverTimeout = null;
    $('li.actions > a', $(this)).addClass('highlights-tab');
    $('li.actions', $(this)).mouseenter(function(e){
        if(unHoverTimeout != null){
            clearTimeout(unHoverTimeout);
            unHoverTimeout = null;
        }
        else{
            $('li.actions', $(that)).click();
        }
    });
    
    $('li.actions', $(this)).mouseleave(function(e){
        if(unHoverTimeout != null){
            clearTimeout(unHoverTimeout);
            unHoverTimeout = null;
        }
        else if(divActions.is(":visible")){
            unHoverTimeout = setTimeout(function(){$('li.actions', $(that)).click();}, 75);
        }
    });
    
    divActions.hover(function(e){
        if(unHoverTimeout != null){
            clearTimeout(unHoverTimeout);
            unHoverTimeout = null;
        }
        else if(divActions.is(":visible")){
            unHoverTimeout = setTimeout(function(){$('li.actions', $(that)).click();}, 75);
        }
    });
    
    $('li.actions', $(this)).click(function(e){
        unHoverTimeout=null;
        
        var tabWidth = $(this).width() + 
                       parseInt($(this).css('padding-left')) + 
                       parseInt($(this).css('padding-right')) +
                       parseInt($(this).css('borderLeftWidth')) + 
                       parseInt($(this).css('borderRightWidth'));
        var divWidth = $(divActions).width();
        var documentWidth = $(document).width();

        if(divActions.is(":visible")){
            $(divActions).fadeOut(100);
            if(!$(this).hasClass('selected2')){
                $(this).removeClass('selected');
            }
        }
        else{
            
            divActions.css('opacity', 1);
            unHoverTimeout=setTimeout($.proxy(function(){
                if($(this).hasClass('selected')){
                    $(this).addClass('selected2');
                }
                $(this).addClass('selected');
                $(divActions).fadeIn(100);
                unHoverTimeout=null;
                $('div.actions').parent().css('z-index', 999);
                divActions.parent().css('z-index', 1000);
                if($(divActions).offset().left + divWidth + 5 >= $(window).width()){
                    var shiftAmount = ($(window).width() - ($(divActions).offset().left + divWidth + 10));
                }
            }, this), 75);   
        }
        e.stopPropagation();
    });
  };
})( jQuery );
