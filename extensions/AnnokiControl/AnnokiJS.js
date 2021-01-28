
////////////////////////////////////////////////////
//edits for annokiblooms
var ids=new Array('timeline','spidermap','topicmap','storyevolution','flowchart', 'hierarchy');

//annokiaddcombobox for annokiblooms
function setSelectedToolbar(index){

	if(index == 1){
	id = ids[0];}
	else if(index == 2){
	id = ids[1];}
	else if(index == 3){
	id = ids[2];}
	else if(index == 4){
	id = ids[3];}
	else if(index == 5){
	id = ids[4];}
	else if(index == 6){
	id = ids[5];}
	else{
	id = 0;
	}
	hideallids();
	showdiv(id);
}

function hideallids(){
	//loop through the array and hide each element by id
	for (var i=0;i<ids.length;i++){
		hidediv(ids[i]);
	}		  
}

function hidediv(id) {
	//safe function to hide an element with a specified id
	if (document.getElementById) { // DOM3 = IE5, NS6
	
		document.getElementById(id).style.display = 'none';
	}
	else {
		if (document.layers) { // Netscape 4
			document.id.display = 'none';
		}
		else { // IE 4
			document.all.id.style.display = 'none';
		}
	}
}

function showdiv(id) {
	//safe function to show an element with a specified id
	
	if(id == 0){
	}
	else{	  
	if (document.getElementById) { // DOM3 = IE5, NS6
		document.getElementById(id).style.display = 'block';
	}
	else {
		if (document.layers) { // Netscape 4
			document.id.display = 'block';
		}
		else { // IE 4
			document.all.id.style.display = 'block';
		}
	}
	}
}

function addButtonNew(imageFile, speedTip, tagOpen, tagClose, sampleText, num, type) {
	
	imageFile=escapeQuotesHTML(imageFile);
	speedTip=escapeQuotesHTML(speedTip);
	tagOpen=escapeQuotes(tagOpen);
	tagClose=escapeQuotes(tagClose);
	sampleText=escapeQuotes(sampleText);
	var mouseOver="";
	var barType = "";
	var graphType ="";

	// we can't change the selection, so we show example texts
	// when moving the mouse instead, until the first button is clicked
	if(!document.selection && !is_gecko) {
		// filter backslashes so it can be shown in the infobox
		var re=new RegExp("\\\\n","g");
		tagOpen=tagOpen.replace(re,"");
		tagClose=tagClose.replace(re,"");
		mouseOver = "onMouseover=\"if(!noOverwrite){document.infoform.infobox.value='"+tagOpen+sampleText+tagClose+"'};\"";
	}
	if(num == 0){
	if(type == 0){
	barType ="extraTools";
	graphType = "Extra Tools:     ";
	}
	else if(type == 1){
	barType ="fcTools";
	graphType = "Flow Chart Tools:     ";
	}
	else if(type == 4){
	barType ="hTools";
	graphType = "Hierarchy Tools:     ";
	}
	else if(type == 2){
	barType ="tlTools";
	graphType = "Time Line Tools:     ";
	}
	else if(type == 3){
	barType ="tmTools";
	graphType = "Topic Map Tools:     ";
	}
	else if(type == 5){
	barType ="smTools";
	graphType = "Spider Map Tools:     ";
	}
	else if(type == 6){
	barType ="seTools";
	graphType = "Story Evolution Tools:     ";
	}
	else{
	barType ="mwTools";
	graphType = "Annoki Tools:     ";
	}
	mouseClick = "onClick=\"showText('barType',text);\"";
	document.write(graphType);
	}

	document.write("<a href=\"javascript:insertTags('"+tagOpen+"','"+tagClose+"','"+sampleText+"');\">");
	document.write("<img width=\"23\" height=\"22\" src=\""+imageFile+"\" border=\"0\" alt=\""+speedTip+"\" title=\""+speedTip+"\""+mouseOver+">");
	document.write("</a>");

return

}


//end annokiblooms edits
////////////////////////////////////////

///////////////////////////////////////
// ANNOKI EDIT

function editFormReturnKeyValuePairsToMainWindow(key, val){
	(document.editform.annokikey.value = key);
	(document.editform.annokivalue.value = val);
}

function transferKeyValuePairs(selectBoxId,targetBoxId){
	var box = document.getElementById(selectBoxId);
	var size = box.options.length;
	var newkv = '';
	for(var i=0;i<size;i++){
		if(box.options[i].selected){
			newkv += (', '+box.options[i].value);
			box.options[i].selected = false;
			
		}
	}
	var targetbox = document.getElementById(targetBoxId);
	if (targetbox.value.indexOf(newkv) == -1){
	    targetbox.value += newkv;
	}
	return false;
}

function transferCourseTypePairs(selectBoxId,targetBoxId){
	var box = document.getElementById(selectBoxId);
	var size = box.options.length;
	var newkv = '';
	for(var i=0;i<size;i++){
		if(box.options[i].selected){
			newkv += (', '+box.options[i].value);
			box.options[i].selected = false;
			
		}
	}
	var targetbox = document.getElementById(targetBoxId);
	if (targetbox.value.indexOf(newkv) == -1){
            targetbox.value += newkv;
        }
	return false;
}

function transferGroups(selectBoxId,targetBoxId){
	var sourceBox = document.getElementById(selectBoxId);
	var size = sourceBox.options.length;
	var newkv = '';
	for(var i=0;i<size;i++){
		if(sourceBox.options[i].selected){
			newkv += (sourceBox.options[i].value+',');
			sourceBox.options[i].selected = false;
			
		}
	}
	var targetbox = document.getElementById(targetBoxId);
	if (targetbox.value.indexOf(newkv) == -1){
            targetbox.value += newkv;
        }
	return false;
}

// END ANNOKI EDIT 
//////////////////////////////////////////////////////
