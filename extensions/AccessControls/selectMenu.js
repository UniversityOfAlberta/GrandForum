var dualList = null; //an instance of the FilterableDualList class but it has to be initialized after the form containing the lists

//various hacks are needed to make this browser work
var isIE = (navigator.appName == "Microsoft Internet Explorer");

//IE doesn't have Array.indexOf...
if(!Array.indexOf){
	    Array.prototype.indexOf = function(obj){
	        for(var i=0; i<this.length; i++){
	            if(this[i]==obj){
	                return i;
	            }
	        }
	        return -1;
	    }
	}

function setDisabledColors() {
	//make the disabled items at least appear grey in IE (they can still be clicked but not moved)
	var optionElements = document.getElementsByTagName('option');
	for (var i = 0; i < optionElements.length; i++) {
		if (optionElements[i].getAttribute("disabled")) {
			optionElements[i].style.color = "#CCC";
		}
	}
	
}
		
/* class CustomListBox */
	function CustomListBox(listElement, userNamespaces, collisions) {
		this.list = listElement; /* HTMLSelectElement */	
		this.allElements = new Array();
		this.currentFilter = "";
		this.userNamespaces = userNamespaces;
		var options = this.list.options;
		for (var i = 0; i < options.length; i++) {
			this.allElements.push(options[i]);
		}
		
		this.collisions = collisions;
		this.getSelected = getSelected;	
		this.add = add;	
		this.findLeastBigger = findLeastBigger;
		this.findLeastBiggerInAll = findLeastBiggerInAll;
		this.remove = remove;
		this.setSelectedAll = setSelectedAll;
		this.applyFilter = applyFilter;
		this.passCurrentFilter = passCurrentFilter;
		this.addItem = addItem;
		this.setUserNSVisible = setUserNSVisible;
		this.prepareForSubmit = prepareForSubmit;
		
		if (userNamespaces != null) {
			this.userNSVisible = false;
			this.applyFilter("");
		}
	}

	function getSelected() {
		var options = this.list.options;
		var ret = new Array();
		for (var i = 0; i < options.length; i++) {
			if (options[i].selected) {
				ret.push(options[i]);
			}
		}
		return ret;
	}
	
	function addItem(item, requestedPosition) {
		//TODO this does not work for the user rights manager which uses ns ids for the option id (so it ends up being sorted by the key of the ns rather than the name)
		if (this.collisions != null && this.collisions.indexOf(item.id) != -1) {
			item.style.color = 'red';
		}
		if (isIE && requestedPosition == null) {
			this.list.add(item); //IE will not accept add(item, null)
		}
		else
			this.list.add(item, requestedPosition);	
		
		
	}
	
	function add(items) {
		for (var i = 0; i < items.length; i++) {
			var item = items[i];
			if (this.passCurrentFilter(item)) {			
				var preceding = this.findLeastBigger(item);
				this.addItem(item, preceding);
			}

			var index = this.findLeastBiggerInAll(item);
			this.allElements.splice(index, 0, item);
		}
		
	}
	
	function remove(items) {
		var options = this.list.options;
		for (var i = 0; i < items.length; i++) {
			this.list.remove(options.namedItem(items[i].id).index);
			var index = -1;
			
			//TODO: make this more efficient 
			for (var k = 0; k < this.allElements.length; k++) {
				if (items[i].id == this.allElements[k].id) {
					index = k;
					break;
				}
			}
			
			if (index != -1)
				this.allElements.splice(index, 1);
		}
	}
	
	//finds the smallest item that is bigger than the given item
	function findLeastBigger(item) {
		//TODO: make this more efficient
		var options = this.list.options;
		for (var i = 0; i < options.length; i++) {
			//TODO: use alphanumeric comparison in stead
			if (options[i].value > item.value) {
				//the HTMLSelectElement.add() in IE needs an index and in every other browser - the Option object
				if (isIE)
					return i;
				else
					return options[i];
			}
		}
		return null;
	}
	
	//TODO combine with findLeastBigger
	function findLeastBiggerInAll(item) {
		var options = this.allElements;
		for (var i = 0; i < options.length; i++) {
			if (options[i].value > item.value) {
				return i;
			}
		}
		return options.length;
	}
	
	function setUserNSVisible(visible) {
		this.userNSVisible = visible;
		this.applyFilter(this.currentFilter);
	}
	
	function passCurrentFilter(item) {
		var passesStringFilter = false
		var passesUserNSFilter = false;
		
		if (this.currentFilter == "" || item.innerHTML.toLowerCase().indexOf(this.currentFilter.toLowerCase()) >= 0) {
			passesStringFilter = true;
		}
		
		if (this.userNamespaces == null || this.userNSVisible || this.userNamespaces.indexOf(item.value) == -1) {
			passesUserNSFilter = true;
		}
		
		return (passesStringFilter && passesUserNSFilter);
	}
		
	function setSelectedAll(selected) {
		var options = this.list.options;
		
		for (var i = 0; i < options.length; i++) {
			options[i].selected = selected;	
		}	
	}
	
	function applyFilter(filter) {
		 
		 this.currentFilter = filter;
		 this.list.options.length = 0;
		 for (var i = 0; i < this.allElements.length; i++) {
		 	if (this.passCurrentFilter(this.allElements[i]))
		 		this.addItem(this.allElements[i], null);
		 }
	}
	
	function prepareForSubmit() {
		if (this.collisions != null) {
			var collisionsInList = new Array();
			for (var i = 0; i < this.allElements.length; i++) {
				if (this.collisions.indexOf(this.allElements[i].id) != -1) {
					if (collisionsInList.length == 2) {
  						collisionsInList.push("...");
  						break;
  					}
					collisionsInList.push(this.allElements[i].id);
				}
			}
			if (collisionsInList.length > 0) {
				if (!confirm("The following pages already exist in the namespace: " + collisionsInList + " (marked in red)\n" +
				"Do you wish to overwrite them?")) {
					return false;
				}
			}
		}
		this.userNSVisible = true;
		this.applyFilter("");
		this.setSelectedAll(true);
		return true;
	}
	


/* class FilterableDualList */
function FilterableDualList(removable, available, userNamespaces, collisions) {
	this.listBoxes = new Array();
	this.listBoxes['removable'] = new CustomListBox(removable, userNamespaces, collisions);
	this.listBoxes['available'] = new CustomListBox(available, userNamespaces, null);
	this.userNamespaces = userNamespaces;
	
	this.moveOptions = moveOptions;
	this.applyFilterToList = applyFilterToList;
	this.setUserNSVisible = setAllUserNSVisible;
	this.prepareForSubmit = prepareAllForSubmit;
}

function moveOptions(from, to) {
	var selected = this.listBoxes[from].getSelected();
	var selectedNotDisabled = new Array();

	for (var i = 0; i < selected.length; i++) {
		if (!selected[i].getAttribute("disabled")) {
			selected[i].setAttribute("style", "");
			//IE simply ignores the disabled attribute, so here we make sure disabled items do not get moved
			selectedNotDisabled.push(selected[i]);

		}
	}
	

	this.listBoxes[from].remove(selectedNotDisabled);
	this.listBoxes[to].add(selectedNotDisabled);
	this.listBoxes[from].setSelectedAll(false);
	this.listBoxes[to].setSelectedAll(false);
}

function applyFilterToList(listName, filter) {
	this.listBoxes[listName].applyFilter(filter);
}

function setAllUserNSVisible(visible) {
	
	this.listBoxes['removable'].setUserNSVisible(visible);
	this.listBoxes['available'].setUserNSVisible(visible);
}

function prepareAllForSubmit() {
	return this.listBoxes['removable'].prepareForSubmit();
}



function handleEnter(event, listName, filterText) {
  var keyCode = event.keyCode ? event.keyCode : event.which ? event.which : event.charCode;
  if (keyCode == 13) {
  	dualList.applyFilterToList(listName, filterText);
  	return false;
  }
  return true; 
}

