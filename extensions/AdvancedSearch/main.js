// The main program flow starts here
var max_products = 100;
var upper_limit = Math.ceil(max_products / 10) * 10; // round up to next 10
var product_limit = '';

$( "#slider-range" ).slider({
range: true,
min: 0,
max: upper_limit,
values: [ 0, upper_limit ],
slide: function(event, ui) {
  $( "#amount" ).text( ui.values[0] + " - " + ui.values[1] );
},
stop: function(event, ui) {
  product_limit = "&fq=p_count:[" + ui.values[0] + " TO " + ui.values[1] + "]";
  start = 0;
  //do_solr_query();
}
});
$("#amount").text(
  $("#slider-range").slider("values", 0) 
+ " - " 
+ $("#slider-range").slider("values", 1));