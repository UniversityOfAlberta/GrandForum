  var calendarImagePath =  wgScriptPath + "/extensions/Calendar/images";

  // modeled after mwInsertEditButton
  function insertCalendarButton() {
        var parent = document.getElementById('toolbar');
        if (!parent) return false;
        var image = document.createElement("img");
          image.width = 23;
          image.height = 22;
	  image.className = "mw-toolbar-editbutton";
          image.src = calendarImagePath + "/addCalendar.png";
          image.border = 0;
          image.alt = "Add Calendar";
          image.title = "Add Calendar";
          image.style.cursor = "pointer";
          image.onclick = function() {
              insertTags("<calendar>", "</calendar>", "");
              return false;
          };
          image.id = "button_calendar";
          parent.appendChild(image);
        }
        
$(document).ready(insertCalendarButton);
