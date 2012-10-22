<?php
//require_once('FeatureRequestViewer.php');
$dir = dirname(__FILE__) . '/';
$wgSpecialPages['SociQL'] = 'Queries'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['SociQL'] = $dir . 'Queries.i18n.php';
$wgSpecialPageGroups['SociQL'] = 'sociql-tools';

function runQueries($par) {
  Queries::run($par);
}

class Queries extends SpecialPage{

	function Queries() {
		wfLoadExtensionMessages('SociQL');
		SpecialPage::SpecialPage("SociQL", MANAGER, true, 'runQueries');
	}

	function run($par){
		global $wgServer, $wgScriptPath;
                include_once("db.inc.php");
                include_once("SociQL.php");

                SociQL::setDialect("MySQL");
                
		global $wgOut, $wgUser;

                $query = null;
                if (isset($_GET["txt_query"])) {
                    $query = $_GET['txt_query'];
                }

                $wgOut->addScript(SociQL::getModelScript());
				

                SociQL::getFacebookInitialization();

                $wgOut->addHTML("<form method='get' name='frm_query' action='$wgServer$wgScriptPath/index.php/Special:SociQL'>
                                    <table width='100%' border='0' cellspacing='0' cellpadding='0'>
                                      <tr>
                                            <td width='77%' valign='top'>
                                                    <table width='98%' border='0' cellspacing='0' cellpadding='0'>
                                                      <tr>
                                                            <td><br />Query:<br />
                                                                    <textarea name='txt_query' cols='10' rows='4'>".stripslashes($query)."</textarea>
                                                                    <br />
                                                                    <input name='btn_submit' type='submit' id='btn_submit' value='Submit' /></td>
                                                      </tr>
                                                      <tr>
                                                            <td class='small'>");

                //$wgOut->addHTML("<textarea cols='10' rows='4' $table</textarea>");
                $wgOut->addHTML("<br/><br/>");
                
                $wgOut->addWikiText(SociQL::getResult($query, "WIKI"));

                $wgOut->addHTML('                           </td>
                                              </tr>
                                            </table>
                                    </td>
                                    <td width="23%" valign="top">
                                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                      <tr>
                                                            <td align="center" height="35">

                                                            </td>
                                                      </tr>
                                                      <tr>
                                                            <td>
                                                            <script>
                                                            var query1 = "SELECT a1.nationality, a1.position, a1.type, a1.university, a1.username, a1.tomap \nFROM user a1";
                                                            var query2 = "SELECT a1.nationality, a1.position, a1.type, a1.university, a1.username, a1.tomap \nFROM user a1 \nWHERE a1.username><\"Eleni\"";
                                                            var query3 = "SELECT a1.username, a2.name, a1.nationality, a1.position, a1.twitter, a1.type, a1.university, a1.tomap \nFROM user a1, role a2 \nWHERE hasRole(a1,a2)";
                                                            var query4 = "SELECT a1.username, a2.name, a1.nationality, a1.position, a1.twitter, a1.type, a1.university, a1.tomap \nFROM user a1, role a2 \nWHERE hasRole(a1,a2) AND a2.name=\"HQP\"";
								    var query5 = "SELECT a1.username, a2.name, a1.nationality, a1.gender, a1.position, a1.twitter, a1.type, a1.university, a1.tomap \nFROM user a1, role a2 \nWHERE hasRole(a1,a2) AND a2.name=\"HQP\" AND a1.gender=\"Female\"";
                                                            var query6 = "SELECT m1.id, m1.group, m1.title, m1.assessment, m1.description, m1.endDate, m1.startDate, m1.status \nFROM milestone m1 \nWHERE m1.description><\"sensors\"";
								    var query7 = "SELECT m1.id, m1.group, m1.title, m1.assessment, m1.description, m1.endDate, m1.startDate, m1.status \nFROM milestone m1 \nWHERE m1.group=\"8\"";
								    var query8 = "SELECT a1.id, a1.name \nFROM project a1";
								    var query9 = "SELECT a1.id, a1.name, a2.group, a2.id, a2.status, a2.title \nFROM project a1, milestone a2 \nWHERE hasMilestone(a1,a2) ";
								    var query10 = "SELECT a1.name, a2.title \nFROM project a1, milestone a2 \nWHERE hasMilestone(a1,a2) AND a1.name=\"MEOW\"";
								    var query11 = "SELECT a1.id, a1.name, a2.group, a2.id, a2.status, a2.title \nFROM project a1, milestone a2 \nWHERE hasMilestone(a1,a2) AND a1.id=\"140\" AND a2.group=\"7\"";
								    var query12 = "SELECT a1.id, a1.amount, a1.year \nFROM budget a1";
								    var query13 = "SELECT a1.id, a1.amount, a1.year \nFROM budget a1 WHERE a1.amount=\"15\"";
								    var query14 = "SELECT a1.id, a1.year, a1.amount, a2.full_name, a2.name \nFROM budget a1, project a2 \nWHERE allocated(a2,a1)";
								    var query15 = "SELECT a1.id, a1.year, a1.amount, a2.full_name, a2.name \nFROM budget a1, project a2 \nWHERE allocated(a2,a1) AND a2.name><\"DINS\"";
								    var query16 = "SELECT a1.id, a1.year, a1.amount, a2.full_name, a2.name \nFROM budget a1, project a2 \nWHERE allocated(a2,a1) AND a1.year><\"2010\"";
								    </script>
                                                            <div style="background-color:#EEEEEE;"><strong>Examples</strong></div>
                                                            <div id="examples" style="height:150px; overflow:auto;">
                                                            <span class="Estilo2"><a href="#" onClick="javascript: document.frm_query.txt_query.value = query1">Example 1</a>: Simple users selection<br>
                                                            <a href="#" onClick="javascript: document.frm_query.txt_query.value = query2">Example 2</a>: Users with a name filter<br>
                                                            <a href="#" onClick="javascript: document.frm_query.txt_query.value = query3">Example 3</a>: User selection with role <br>
                                                            <a href="#" onClick="javascript: document.frm_query.txt_query.value = query4">Example 4</a>: Users with a role filter <br>
								    <a href="#" onClick="javascript: document.frm_query.txt_query.value = query5">Example 5</a>: Users with two filter <br>
								    <a href="#" onClick="javascript: document.frm_query.txt_query.value = query6">Example 6</a>: Milestiones with description filter<br>
								    <a href="#" onClick="javascript: document.frm_query.txt_query.value = query7">Example 7</a>: Milestone with its revisions<br>
								    <a href="#" onClick="javascript: document.frm_query.txt_query.value = query8">Example 8</a>: Simple projects selection<br>
								    <a href="#" onClick="javascript: document.frm_query.txt_query.value = query9">Example 9</a>: All the projects with all the milestiones<br>
								    <a href="#" onClick="javascript: document.frm_query.txt_query.value = query10">Example 10</a>: Single project with its milestiones<br>
								    <a href="#" onClick="javascript: document.frm_query.txt_query.value = query11">Example 11</a>: Single project and milestone evolution<br>
								    <a href="#" onClick="javascript: document.frm_query.txt_query.value = query12">Example 12</a>: Simple Budget Selection<br>
								    <a href="#" onClick="javascript: document.frm_query.txt_query.value = query13">Example 13</a>: Budget with an amount filter<br>
								    <a href="#" onClick="javascript: document.frm_query.txt_query.value = query14">Example 14</a>: All the projects with all the allocated budgets<br>
								    <a href="#" onClick="javascript: document.frm_query.txt_query.value = query15">Example 15</a>: Single project with its allocated budgets<br>
								    <a href="#" onClick="javascript: document.frm_query.txt_query.value = query16">Example 16</a>: Project - Budget filtered by year<br>
                                                            </span>
                                                            </div>
                                                            <br />
                                                            </td>
                                                      </tr>
                                                      <tr>
                                                            <td valign="top">');

                $wgOut->addHTML(SociQL::getModelTree());

                $wgOut->addHTML('                           </td>
                                                      </tr>
                                                      </table>
                                                    </td>
                                              </tr>
                                            </table>
											

                                        </form>');
				
				$wgOut->addScript("<script type=\"text/javascript\" src=\"http://maps.googleapis.com/maps/api/js?sensor=false\"></script>");
			    
				$wgOut->addHTML("<br><button id='map_button'>Show Map Results</button>
									<div id=\"map_canvas\" style=\"width:80%; height:300px;display:none;\"></div>");
				$wgOut->addScript(SociQL::getMap());
                
	}

	function parse($text){
		$text = str_replace("'", "&#39;", $text);
		$text = str_replace("\"", "&quot;", $text);
		return $text;
	}

}

function compareScore($a, $b) {
    if ($a['score'] < $b['score']) {
        return 1;
    } else if ($a['score'] > $b['score']) {
        return -1;
    } else {
        return 0;
    }
}
?>
