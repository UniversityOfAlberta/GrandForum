<?php
# Calendar Extension
# Creates a calendar of the month and year.
#
# BT: Latest tested MW version: 1.15 (2010-03-22)
#
# Example Code
#
# <calendar></calendar>
# this defaults to "current month"
#
# (I guess you can still use <calendar>2005-05</calendar> as well, in that case you won't be able to jump to next mont)
#
# (extended by petervanmechelen at gmail dot com)
#
# patched for working with wikimedia 1.7.0 by Dirk Schneider <info _at_ dischneider _dot_ de>
#
# 2010 (Brendan Tansey)
# Added ability to hide or show events below the calendar.
#
# 2008-09-15 (Brendan Tansey)
# Fixed bug where day of the week does not align correctly with date
# Works with mw 1.13.1
# Added namespace extension integration
#
# 2008-06-03 (Brendan Tansey)
# Patched to work with mw 1.12.0
# Removed use of parser in parsing date string - it's unneeded and broken.
# Added toolbar button for adding a default calendar
#
# 2006-07-12
# patched to show "today" marked only in current month and current year
# patched to work with mw 1.7.0
# patched to let links to previous and next month work
#
# 2007-05-12
# patched to call the Parser in a way that works on 1.9.2+ by Nigel Gilbert <n.gilbert_at_surrey.ac.uk>
# also a number of minor changes and corrections

$egCalDefaultNamespace = 'Cal';

$wgExtensionFunctions[] = "wfCalendarExtension";
$wgExtensionCredits['parserhook'][] = array(
        'name' => 'Calendar',
        'author' => 'UofA: SERL',
        'description' => 'Display an embedded calendar on a wiki page.  Uses the  <calender> tag.  Based on [http://www.mediawiki.org/wiki/Extension:Calendar_(Shane)_extended Calendar (Shane) extended].',
        //'url' => ''
);

/** BT Edit **/
$wgHooks['BeforePageDisplay'][] = 'addCalendarButton';

function addCalendarButton(&$out){
  global $wgScriptPath;
  $out->addScript("\n         <script type='text/javascript' src='" .
		  $wgScriptPath . '/extensions/Calendar/addCalendarButton.js' . "'></script>");
  return true;
}

define("EX_CALENDAR", true);

/** End BT Edit **/

function wfCalendarExtension() {
    global $wgParser;
    $wgParser->setHook( "calendar", "createmwCalendar" );
    if (defined('EX_ACCESS_CONTROLS')){
      global $egAnnokiNamespaces, $egCalDefaultNamespace;
      if (!$egAnnokiNamespaces->isExtraNs($egCalDefaultNamespace))
	$egAnnokiNamespaces->addNewNamespace($egCalDefaultNamespace);
    }
}

# The callback function for converting the input text to HTML output
function createmwCalendar($input, $argv, &$parser){
    $parser->disableCache();
    
    $input = trim($input);
    
    /**
    * check if date in $_GET-parameter
    * fallback on default this month
    **/
        if($input=="")
        {
            if(isset($_GET['month'])&&(isset($_GET['year'])))
            {
                $input = ($_GET['month']<10?"0":"").date($_GET['month']." ".$_GET['year']);
            }
            else
            {
                $input = date("m Y");
            }
        } 

        //$ret = $parser->parse($input, $parser->getTitle(), $parser->getOptions(), false, false); 
	//$array = explode(' ', $ret->getText());        
	$array = explode(' ', $input);
	
        $month = $array[0];
        $year = $array[1];

	$mwCalendar = new mwCalendar();

        $mwCalendar->dateNow($month, $year);
        return $mwCalendar->showThisMonth($parser);
}


class mwCalendar
{
        var $cal = "CAL_GREGORIAN";
        var $format = "%Y%m%d";
        var $today;
        var $day;
        var $month;
        var $year;
        var $pmonth;
        var $pyear;
        var $nmonth;
        var $nyear;
// German weekday names
//        var $wday_names = array("Mo","Di","Mi","Do","Fr","Sa","So"); // put sunday first to change order of the days
// English weekday names
        var $wday_names = array("Sun","Mon","Tues","Wed","Thur","Fri","Sat"); // put sunday first to change order of the days
// German month names
//        var $wmonth_names = array("Januar", "Februar", "M�rz", "April", "Mai", "Juni","Juli", "August","September", "Oktober", "November", "Dezember");
//English month names
        var $wmonth_names = array("January", "February", "March", "April", "May", "June", "July", "August", "September"," October", "November", "December");

        function mwCalendar()
        {
                $this->day = "1";
                $today = "";
                $month = "";
                $year = "";
                $pmonth = "";
                $pyear = "";
                $nmonth = "";
                $nyear = "";
        }


        function dateNow($month,$year)
        {
                $this->month = $month;
                $this->year = $year;
                $this->today = strftime("%d",time());
                $this->pmonth = $this->month - 1;
                $this->pyear = $this->year - 1;
                $this->nmonth = $this->month + 1;
                $this->nyear = $this->year + 1;
        }

        function daysInMonth($month,$year)
        {
                if (empty($year))
                {
                        $year = mwCalendar::dateNow("%Y");
                }
                if (empty($month))
                {
                        $month = mwCalendar::dateNow("%m");
                }
                if($month == "2")
                {
                        if(mwCalendar::isLeapYear($year))
                        {
                                return 29;
                        }
                        else
                        {
                                return 28;
                        }
                }
                else if ($month == "4" || $month == "6" || $month == "9" || $month == "11")
                {
                        return 30;
                }
                else
                {
                        return 31;
                }
        }

        function isLeapYear($year)
        {
            return (($year % 4 == "0" && $year % 100 != "0") || $year % 400 == "0");
        }

        function dayOfWeek($month,$year)
        { 
        $weekday_no = date("w", strtotime("1 ".$this->wmonth_names[$month - 1]." $year"));
        //if ($weekday_no == 0) return 6; //BT removed
        //else 
	  return $weekday_no; // - 1; //BT removed
              }

        function showThisMonth(&$parser)
        {
                global $wgScript;
                $viewEvents = "";
                $lastyear = ($this->month==1?$this->year - 1:$this->year);
                $nextyear = ($this->month==12?$this->year + 1:$this->year);
                $lastmonth = ($this->month==1? 12 : $this->month - 1);
                $nextmonth = ($this->month==12? 1 : $this->month + 1);
                $currentpage = "http://".$_SERVER['SERVER_NAME'].(strpos($_SERVER['REQUEST_URI'],"?")?substr($_SERVER['REQUEST_URI'],0,strpos($_SERVER['REQUEST_URI'],"?")+1):$_SERVER['REQUEST_URI']."?");
                $params = explode("&",$_SERVER['QUERY_STRING']);
                for($i=0;$i<count($params);$i++)
                {
                    if((substr($params[$i],0,5)!="month") && (substr($params[$i],0,4)!="year"))
                    {
                        $currentpage .= $params[$i]."&";
                    }
                }
                if (strpos($currentpage,'action=purge')) {
                        $purge="";
                } else {
                        $purge="action=purge&";
                }
                $a_lastmonth = $currentpage.$purge."month=".$lastmonth."&year=".$lastyear;
                $a_nextmonth = $currentpage.$purge."month=".$nextmonth."&year=".$nextyear;

                $output = '<table cellpadding="4" cellspacing="0" class="calendar">';
                $output .= '<tr><td><a href="'.$a_lastmonth.'"><</a></td><td colspan="5" class="cal-header"><center>'. $this->wmonth_names[$this->pmonth] . " " .$this->year .'</cen
ter></td><td><a href="'.$a_nextmonth.'">></a></td></tr>';
                $output .= '<tr style="background:#EEEEEE;">';
                for($i=0;$i<7;$i++)
                        $output .= '<td>'. $this->wday_names[$i]. '</td>';
                $output .= '</tr>';
                $wday = mwCalendar::dayOfWeek($this->month,$this->year);
                $no_days = mwCalendar::daysInMonth($this->month,$this->year);
                $count = 1;
                $output .= '<tr>';
                for($i=1;$i<=$wday;$i++)
                {
                    $output .= '<td> </td>';
                    $count++;
                }
                /**
                * every day is edit link to that day
                **/
                for($i=1;$i<=$no_days;$i++)
                {
                    $dayNr = ($i<10?"0".$i:$i);
		    $articleName= $this->year."-".$this->month."-".$dayNr;
		    
		    //VG Edit - make the article for the date be of the namespace of the article where the calendar is being shown
		    global $wgTitle, $wgExtraNamespaces, $egCalDefaultNamespace; 
		    if ($wgTitle->getNamespace() >= 100)
		      $articleName = $wgTitle->getNsText() . ":$articleName";
		    //end VG edit
		    else if (in_array($egCalDefaultNamespace, $wgExtraNamespaces)){ //BT Edit - if Cal namespace exists and page is not in custom namespace, use Cal.
		      $articleName = "$egCalDefaultNamespace:$articleName";
		    }
		    //End BT edit
  
		      $alinkedit = "http://".$_SERVER['SERVER_NAME'].$wgScript."?title=".$articleName /*."&action=edit"*/;

                    $wl_title = Title::newFromText ( $articleName );
                    
                    if ($wl_title !== NULL && $wl_title->exists())
                    {
                        $thisDayExists = true;
                        $alinkeditstyle = 'style="text-decoration:underline;"';
                        $existsStyle =  "background-color:#FFCC66;";

                        // contents of event goes under the calendar...
                        
                        /*
                         * (VG) Quick workaround for a bug in the Article class that always loads "oldid"
                         * regardless of what page the Article object is for!!
                         */
                        global $wgRequest;
                        $oldidParam = $wgRequest->getText('oldid');
                        unset($wgRequest->data['oldid']);
                        
                        $article = new Article ( $wl_title );
                        $ret = $parser->parse($article->getContent(), $parser->getTitle(), $parser->getOptions(), true, false);
                        $articleContent = $ret->getText();
                        
                        $wgRequest->data['oldid'] = $oldidParam;
                        
                        $viewEvents .= "<div style=\"padding:10px; display:none;\" id=\"calViewEvents\"><a href=\"http://".$_SERVER['SERVER_NAME'].$wgScript."/$articleName\">".$articleName."</a><br />".$articleContent.'</br></div>';
                    }
                    else
                    {
                        $thisDayExists = false;
                        $alinkeditstyle = '';
                        $existsStyle =  "";
                    }
                    if($count > 6)
                    {
                        if(($i == $this->today) && ($this->month == strftime("%m",time())) && ($this->year == strftime("%Y",time())))
                        {
                            $output .= '<td style="text-align:right; font-weight:bold; background-color:#CCFFCC;"><a href="'.$alinkedit.'" '.$alinkeditstyle.'>' . $i . '</a></td></tr>';
                        }
                        else
                        {
                                $output .= '<td style="text-align:right;' . $existsStyle .'"><a href="'.$alinkedit.'" '.$alinkeditstyle.'>' . $i . '</a></td></tr>';
                        }
                          $count = 0;
                    }
                    else
                    {
                        if(($i == $this->today) && ($this->month == strftime("%m",time())) && ($this->year == strftime("%Y",time())))
                      {
                        $output .= '<td style="text-align=right; font-weight:bold; background-color:#CCFFCC;"><a href="'.$alinkedit.'" '.$alinkeditstyle.'>' . $i . '</a></td>';
                      }
                      else
                      {
                         $output .= '<td style="text-align:right;' . $existsStyle . '"><a href="'.$alinkedit.'" '.$alinkeditstyle.'>' . $i . '</a></td>';
                      }
                    }
                    $count++;
                }
                if ($count> 1) for($i=$count;$i<=7;$i++)
                {
                    $output .= "<td> </td>";
                }
		//BT: Edit
                $output .= '</tr>';
		$output .= '<tr><td colspan="7" style="text-align:right">';
        	$output .= '[<a href="#" onclick="showhide(\'calViewEvents\', \'hidden\');">show/hide events</a>]<br>';
		$output .= '</td></tr></table>';
		//BT: End edit
	
		/**
                * Show events for this month
                **/
                $output .= $viewEvents;


                return $output;
        }
}
?>
