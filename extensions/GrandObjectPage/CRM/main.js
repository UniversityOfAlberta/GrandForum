// The main program flow starts here

// if not a reload or navigating through history.
if ((performance.navigation.type != 1) && (performance.navigation.type != 2)){
	// if we are unloading
	var bool_value = localStorage.getItem("UNLOADING") == "true" ? true : false
	if (bool_value) {
		localStorage.setItem("UNLOADING", false);
		localStorage.setItem("BIB", null);
		localStorage.setItem("PRODUCT", 0);
	}
}

$(window).on('beforeunload', function(e){
	localStorage.setItem("UNLOADING", true);
});
