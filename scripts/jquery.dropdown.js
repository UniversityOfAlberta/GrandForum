(function( $ ){

  $.fn.dropdown = function(options) {
    
    $.fn.imgToggle = function(){
        var selected = '';
        if($('li.actions', $(this)).hasClass('selected')){
            selected = '_selected';
        }
        if($("img.dropdown", $(this)).attr('src') == '../skins/down' + selected + '.png'){
            this.imgUp();
        } 
        else{
            this.imgDown();
        }
    }
    
    $.fn.imgUp = function(){
        var selected = '';
        if($('li.actions', $(this)).hasClass('selected')){
            selected = '_selected';
        }
        $("img.dropdown", $(this)).attr('src', '../skins/up' + selected + '.png');
    }
    
    $.fn.imgDown = function(){
        var selected = '';
        if($('li.actions', $(this)).hasClass('selected')){
            selected = '_selected';
        }
        $("img.dropdown", $(this)).attr('src', '../skins/down' + selected + '.png');
    }
    
    $(this).addClass('dropdown');
    
    var title = (typeof options.title != 'undefined') ? options.title : 'DropDown';
    var width = (typeof options.width != 'undefined') ? options.width : '150px';
    
    var lis = $('li', $(this));
    $(lis).removeClass('selected');
    $(lis).addClass('action');
    $(lis).css('display', 'block');
    
    $(this).append("<div class='actions' />");
    var divActions = $('div.actions' ,$(this));
    $(divActions).css('min-width', width);
    
    $(divActions).css('right', '1px');
    $(lis).appendTo($(divActions));
    $(this).append("<li class='actions'><a>" + title + "<img class='dropdown' style='margin-left:5px;' /></a></li>");
    $(this).imgDown();
    $(divActions).append("<img class='dropdowntop' src='../skins/dropdowntop.png' />");
    var dropdownTop = $('.dropdowntop', $(this));
    var that = this;
    
    $('li.actions', $(this)).click(function(e){
        $("div.actions").not(divActions).fadeOut(250); // Remove all other dropdowns
        $("ul.dropdown").not(that).imgDown();
        
        var tabWidth = $(this).width() + 
                       parseInt($(this).css('padding-left')) + 
                       parseInt($(this).css('padding-right')) +
                       parseInt($(this).css('border-width'))*2;
        var divWidth = $(divActions).width();
        var documentWidth = $(document).width();
        
        $(dropdownTop).css('position', 'absolute');
        $(dropdownTop).css('top', -5);
        $(divActions).fadeToggle(250);
        $(that).imgToggle();
        $(divActions).css('right', tabWidth - $(divActions).width());
        $(dropdownTop).css('right', Math.ceil((tabWidth - 7)/2) - (tabWidth - $(divActions).width()));
        if($(divActions).offset().left + divWidth + 5 >= $(window).width()){
            var shiftAmount = ($(window).width() - ($(divActions).offset().left + divWidth + 10));
            $(divActions).css('right', (tabWidth - $(divActions).width()) - shiftAmount);
            $(dropdownTop).css('right', Math.ceil((tabWidth - 7)/2) - (tabWidth - $(divActions).width()) + shiftAmount);
        }
        e.stopPropagation();
    });
    $(document).click(function(){
        $(divActions).fadeOut(250);
        $(that).imgDown();
    });
  };
})( jQuery );
