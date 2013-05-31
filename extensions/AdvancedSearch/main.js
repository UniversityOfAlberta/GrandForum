// The main program flow starts here
//var max_products = 100;
var product_limit = '';
// /var page = 0;
function resetSlider(max_products){
	//var max_products = pageRouter.currentView.max_products;
	var upper_limit = Math.ceil(max_products / 10) * 10; // round up to next 10
	product_limit = '';

	$( "#slider-range" ).slider({
	range: true,
	min: 0,
	max: upper_limit,
	values: [ 0, upper_limit ],
	slide: function(event, ui) {
	  $( "#amount" ).text( ui.values[0] + " - " + ui.values[1] );
	},
	stop: function(event, ui) {
	  product_limit = "p_count:[" + ui.values[0] + " TO " + ui.values[1] + "]";
	  start = 0;
	  //do_solr_query();
	}
	});
	$("#amount").text(
	  $("#slider-range").slider("values", 0) 
	+ " - " 
	+ $("#slider-range").slider("values", 1));
}

function parseSolrResponse(){
	//console.log("parseSolrResponse!");
}

$(function() {
	$("#advanced_toggle").click(function(){
	 	$( "#facet_controls" ).toggle();
	 	if($("#advanced_toggle .ui-button-icon-primary").hasClass("ui-icon-triangle-1-s")){
	 		$("#advanced_toggle .ui-button-icon-primary").removeClass("ui-icon-triangle-1-s");
	 		$("#advanced_toggle .ui-button-icon-primary").addClass("ui-icon-triangle-1-e");
	 	}else{
	 		$("#advanced_toggle .ui-button-icon-primary").removeClass("ui-icon-triangle-1-e");
	 		$("#advanced_toggle .ui-button-icon-primary").addClass("ui-icon-triangle-1-s");
	 	}
	});
	$( "#advanced_toggle" ).button({
      icons: {
        primary: "ui-icon-triangle-1-e"
      }
    });
});