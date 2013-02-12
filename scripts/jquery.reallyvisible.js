(function( $ ){

$.extend(

$.expr[":"],

{ reallyvisible: function (a) { return ($(a).is(":visible") && $(a).parents(":hidden").length == 0) } }

);

})( jQuery );
