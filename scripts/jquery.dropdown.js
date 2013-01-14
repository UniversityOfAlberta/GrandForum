(function( $ ){

  $.fn.dropdown = function(options) {
    
    var title = (typeof options.title != 'undefined') ? options.title : 'DropDown';
    var width = (typeof options.width != 'undefined') ? options.width : '150px';
    
    var lis = $('li', $(this));
    $(lis).addClass('action');
    $(lis).css('display', 'block');
    
    $(this).append("<div class='actions' />");
    var divActions = $('div.actions' ,$(this));
    $(divActions).css('min-width', width);
    
    $(divActions).css('right', '1px');
    $(lis).appendTo($(divActions));
    $(this).append("<li class='actions'><a>" + title + "</a></li>");
    
    $(divActions).append("<img class='dropdowntop' src='../skins/dropdowntop.png' />");
    var dropdownTop = $('.dropdowntop', $(this));
    var that = this;
    $('li.actions', $(this)).click(function(e){
        $("div.actions").not(divActions).fadeOut(250); // Remove all other dropdowns
        e.stopPropagation();
        
        var tabWidth = $(this).width() + parseInt($(this).css('padding-left')) + parseInt($(this).css('padding-right'));
        var divWidth = $(divActions).width();
        var documentWidth = $(document).width();
        
        $(divActions).css('right', -tabWidth + 1);
        $(dropdownTop).css('position', 'absolute');
        $(dropdownTop).css('top', -5);
        $(dropdownTop).css('right', Math.ceil((tabWidth - 7)/2) + tabWidth);
        $(divActions).fadeToggle(250);
        console.log($(divActions).offset().left + divWidth);
        if($(divActions).offset().left + divWidth + 5 >= $(window).width()){
            var shiftAmount = ($(window).width() - ($(divActions).offset().left + divWidth + 10));
            $(divActions).css('right', -tabWidth + 1 - shiftAmount);
            $(dropdownTop).css('right', Math.ceil((tabWidth - 7)/2) + tabWidth + shiftAmount);
        }
        
    });
    $(document).click(function(){
        $(divActions).fadeOut(250);
    });
  };
})( jQuery );
