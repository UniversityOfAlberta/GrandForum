/**
 * Hides the edit box on the Edit page.
 */
function hideEditBox(){
  showhide('wpTextbox1', 'block');
  showhide('toolbar', 'block');
}

/**
 * Hides the currently shown element and shows the other, copying the value from the currently shown element to the soon-to-be-shown element.
 * @param {String} element1ID The id of one of the elements to swap.
 * @param {String} element2ID The id of the other element to swap.
 */
function swapElements(element1ID, element2ID){
  element1 = document.getElementById(element1ID);
  element2 = document.getElementById(element2ID);
  
  if (element1 == null || element2 == null)
    return;
      
  if (element1.style.display == 'block'){
    //element2.value = element1.value;
    element1.style.display = 'none';
    element2.style.display = 'block';
  }
  else{
    //element1.value = element2.value;
    element2.style.display = 'none';
    element1.style.display = 'block';
  }
}

/**
 * Copies the value from the 'from' element to the value of the 'to' element.
 * @param {String} fromID The id of the element from which the value will be copied.
 * @param {String} toID The id of the element to which the value will be copied.
 */
function copyText(fromID, toID){
  elementFrom = document.getElementById(fromID);
  elementTo = document.getElementById(toID);
  
  if (elementFrom == null || elementTo == null)
    return;
  
  elementTo.value = elementFrom.value;
}