var elementStates = new Array();

/** 
 * Show or hide a <div>.
 * @param {String} layer_ref The id of the div to show or hide.
 * @param {String} defaultState Can be 'block' or 'none'.  This is the way the div is set up in the HTML initially.
 */
function showhide(layer_ref, defaultState) {
  var state = defaultState; //Default state
  
  if (elementStates[layer_ref] == undefined){
    elementStates[layer_ref] = state;   
  }
  else{
    state = elementStates[layer_ref];
  }
    
  if (state == 'block') {
    state = 'none';
  }
  else {
    state = 'block';
  }
  
  elementStates[layer_ref] = state;
  
  if (document.all) {
     //IS IE 4 or 5 (or 6 beta)
    eval( "document.all." + layer_ref + ".style.display = state");
  }
  if (document.layers) {
     //IS NETSCAPE 4 or below
    document.layers[layer_ref].display = state;
  }
  if (document.getElementById &&!document.all) {
      hza = document.getElementById(layer_ref);
      hza.style.display = state;
  }
} 
