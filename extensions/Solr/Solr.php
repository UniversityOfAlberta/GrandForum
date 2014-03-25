<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['Solr'] = 'Solr';
$wgExtensionMessagesFiles['Solr'] = $dir . 'Solr.i18n.php';
$wgSpecialPageGroups['Solr'] = 'network-tools';

// SQL tables
$sqlTables = array("milestone" => "grand_milestones",
                   "posting" => "grand_postings",
                   "product" => "grand_products",
                   "project" => "grand_project_descriptions",
                   "report" => "grand_report_blobs" );

// SQL Primary Keys
$sqlPrimaryKeys = array("milestone" => "id",
                        "posting" => "id",
                        "product" => "id",
                        "project" => "id",
                        "report" => "blob_id" );


// SQL table field names to be retrieved
// Legend for secondary searches:  label | table name | id field | target field
$milestoneFields = array("project_id" => "Project | grand_project | id | name",
												 "title" => "Title",
												 "status" => "Status",
												 "description" => "Description",
												 "assessment" => "Assessment",
												 "edited_by" => "Editor | mw_user | user_id | user_name.blob",
												 "start_date" => "Start Date",
												 "end_date" => "End Date");

$postingFields = array("title" => "Title",
                       "descr" => "Description");

$productFields = array("title" => "Title",
											 "status" => "Status",
											 "authors.blob" => "Authors",
											 "data.blob" => ""); // uses blob field names

$projectFields = array("full_name" => "Title",
                       "description" => "Description");

$reportFields = array("edited_by" => "Editor | mw_user | user_id | user_name.blob",
											"user_id" => "User | mw_user | user_id | user_name.blob",
											"proj_id" => "Project | grand_project | id | name",
											"changed" => "Date",
											"data.blob" => ""); // uses blob field names


$resultFields = array("milestone" => $milestoneFields,
									  	"posting" => $postingFields,
											"product" => $productFields,
											"project" => $projectFields,
											"report" => $reportFields );



function runSolr($par) {
	Solr::run($par);
}

class Solr extends SpecialPage {

	function __construct() {
		wfLoadExtensionMessages('Solr');
		SpecialPage::SpecialPage('Solr', HQP.'+', true, 'runSolr');
	}

	function run(){
	    global $wgUser, $wgOut, $wgServer, $wgScriptPath, 
             $sqlTables, $resultFields, $sqlPrimaryKeys;

      if(isset($_GET['type'])){
        $type = $_GET['type'];
        $query = $_GET['query'];
        $ids = explode(",",$_GET['id']); // 0:field_name  1:key
        if($type == "product"){
          Solr::getProducts($ids);
        }
        else if($type == "project"){
          Solr::getProjects($ids, $query);
        }
        else if($type == "milestone"){
          Solr::getMilestones($ids, $query);
        }
        else if($type == "posting"){
          Solr::getPostings($ids);
        }
        else{
          header('Content-Type: text/json');
          print json_encode(array());
          exit;
        }
      }

      $sqlTables = json_encode($sqlTables);
      $sqlPrimaryKeys = json_encode($sqlPrimaryKeys);
      $resultFields = json_encode($resultFields);

      $script =<<<EOF
        <script type="text/javascript">

          // Write PHP arrays
          var sqlTables = {$sqlTables};
          var sqlPrimaryKeys = {$sqlPrimaryKeys};
          var resultFields = {$resultFields};

          
          $(document).ready(function(){
						$("#query").focus();
          });

					$("#searchForm").submit(function() {
							return false;
					});


          $("#query").on("keypress", function(e){
            if (e.which == 13){
							e.preventDefault();
              query = $("#query").val();
              url_solr = "{$wgServer}{$wgScriptPath}/extensions/Solr/curl.php?query=(" + query + ")"

							$.getJSON(url_solr,
								function(data) {
                  $(".results").hide();
									$.each(data, function(key, val) { walkJsonTree(key, val) });
							});

							e.preventDefault();
							return null;
            }
          });

          
					$("#b_search").on("click", function() {
						var press = jQuery.Event("keypress");
						press.which = 13;
						$("#query").trigger(press);
            return false;
					});

          var datatable_refs = new Array();
          datatable_refs['product'] = null;
          datatable_refs['project'] = null;
          datatable_refs['milestone'] = null;
          datatable_refs['posting'] = null;

          function populateTable(data, type){
            table = "";
            count = 0;
            $.each(data, function(row_i, row){
              table += "<tr>";
              $.each(row, function(col_i, col){
                table += "<td>"+col+"</td>";
              })
              table += "</tr>";
              count++;
            });

            if(count > 0){
              $("#"+type+"_results").show();
              if(datatable_refs[type] !== null){
                datatable_refs[type].fnDestroy();
              }
              $("#"+type+"_results #"+type+"sTable tbody").html(table);
              datatable_refs[type] = $("#"+type+"_results #"+type+"sTable").dataTable({
                'aLengthMenu': [[-1], ['All']],
                "bFilter": false
              });
            }
            else{
              $("#"+type+"_results").hide();
            }
          }
          
          function fetchData(ids, type){
            if(ids.length == 0){
              return;
            }

            query = $("#query").val();
            ids = ids.join(',');
            var url_sql = "{$wgServer}{$wgScriptPath}/index.php/Special:Solr?type="+type+"&query="+query+"&id=" + ids;
                
            $.ajax({
              url: url_sql, 
              success: function(data){ 
                populateTable(data, type);
              }, 
              async: false 
            }); 
          }

          function walkJsonTree(key, val) {
            if(key == 'response' && val.numFound > 0){
              docs = val.docs;
              products = new Array();
              projects = new Array();
              milestones = new Array();
              postings = new Array();
              $.each(docs, function(ind, pair){
                $.each(pair, function(type_id, id){
                  if(type_id == 'product_id'){
                    products.push(id);
                  }
                  else if(type_id == 'project_id'){
                    projects.push(id);
                  }
                  else if(type_id == 'milestone_id'){
                    milestones.push(id);
                  }
                  else if(type_id == 'posting_id'){
                    postings.push(id);
                  }
                  
                });
              });
              
              fetchData(products, 'product');
              fetchData(projects, 'project');
              fetchData(milestones, 'milestone');
              fetchData(postings, 'posting');
            }
          }

        </script>
EOF;

      $wgOut->addScript($script);

	    $wgOut->addHTML('
        <form id=searchForm>
          <input type="text" size=70 id="query" />
          <input type=button value=Search name="b_search" id="b_search" />
        </form>
        <br/><br/>
			');

	    $wgOut->addHTML('
        <style type="text/css">
          #product_results, #project_results, #milestone_results{
            padding: 20px 0;
          }
        </style>
        <div id="results">

            <div id="product_results" class="results" style="display:none;">
            <h3>Products</h3>
            <table class="indexTable dataTable" frame="box" rules="all" id="productsTable">
            <thead>
            <tr>
            <th width="20%">Date</th>
            <th>Category/Type</th>
            <th>Title</th>
            <th>Authors</th>
            <th>Projects</th>
            </tr>
            </thead>
            <tbody></tbody>
            </table>
            </div>

            <div id="project_results" class="results" style="display:none;">
            <h3>Projects</h3>
            <table class="indexTable dataTable" frame="box" rules="all" id="projectsTable">
            <thead>
            <tr>
            <th>Category/Type</th>
            <th>Title</th>
            <th>Authors</th>
            </tr>
            </thead>
            <tbody></tbody>
            </table>
            </div>

            <div id="milestone_results" class="results" style="display:none;">
            <h3>Project Milestones</h3>
            <table class="indexTable dataTable" frame="box" rules="all" id="milestonesTable">
            <thead>
            <tr>
            <th>Project</th>
            <th width="12%">Start/End Date</th>
            <th width="20%">Title</th>
            <th>Description</th>
            </tr>
            </thead>
            <tbody></tbody>
            </table>
            </div>

            <div id="posting_results" class="results" style="display:none;">
            <h3>Postings</h3>
            <table class="indexTable dataTable" frame="box" rules="all" id="postingsTable">
            <thead>
            <tr>
            <th>Title</th>
            <th width="12%">Start/End Date</th>
            <th width="20%">URL</th>
            <th>Description</th>
            </tr>
            </thead>
            <tbody></tbody>
            </table>
            </div>
        </div>
			');

  }

  static function getProducts($ids){
    global $wgServer, $wgScriptPath;

    $products = array();
    foreach($ids as $id){
      $product = Paper::newFromId($id);
      
      $date = $product->getDate();
      $cat = $product->getCategory();
      $type = $product->getType();
      $cat_type = $cat ." / ". $type;
      $title = "<a href='{$wgServer}{$wgScriptPath}/index.php/{$cat}:{$id}'>". $product->getTitle() ."</a>";
      //$status = $product->getStatus();
     
      $auths = $product->getAuthors();
      $authors = array();
      foreach($auths as $auth){
        $authors[] = "<a href='". $auth->getUrl() ."'>". $auth->getNameForForms() ."</a>";
      }
      $authors = implode(', ', $authors);

      $projs = $product->getProjects();
      $projects = array();
      foreach($projs as $proj){
        $projects[] = $proj->getName();
      }
      $projects = implode(', ', $projects);
      //$data = $product->getData();

      $products[] = array('date'=>$date, 'cat_type'=>$cat_type, 'title'=>$title, 'authors'=>$authors, 'projects'=>$projects);
    }
    header('Content-Type: text/json');
    echo json_encode($products);
    exit;
  }

  static function getProjects($ids, $q){
    global $wgServer, $wgScriptPath, $wgUser;
    $me = Person::newFromId($wgUser->getId());

    $projects = array();
    foreach($ids as $id){
      $query = "SELECT project_id FROM grand_project_descriptions WHERE id = {$id}";
      $qres = DBFunctions::execSQL($query);
      if(count($qres) > 0 && isset($qres[0]['project_id'])){
        $project_id = $qres[0]['project_id'];
      }
      else{
        break;
      }

      $project = Project::newFromId($project_id);
      if(!$me->isMemberOf($project) ||  !$me->isRole(STAFF)){
        continue;
      }
      $name = $project->getName();
      $type = $project->getType();
      $status = $project->getStatus();
      $type = $type .'/'.$status;
      $description = $project->getDescription();
      $description = preg_replace(
          '/.*?\s(.{0,50})(\b'.$q.'\b)(.{0,50})\s.*?/',
          '$1<span style="display:inline-block; background-color:yellow;">$2</span>$3',
          $description
      );
      $matches = array();
      preg_match_all('/.*?\s(.{0,50})(\b'.$q.'\b)(.{0,50})\s.*?/', $description, $matches);
      
      if(count($matches)>0){
        $description = '... '. implode(' ... ', $matches[0]) .' ...';
      }else{
        $description = substr($description, 0, 250);
      }
     
      //$themes = $project->getThemes();
      $title = "<a href='{$wgServer}{$wgScriptPath}/index.php/{$name}:Main'>". $name ."</a>";
      //echo $description ."<br>";

      $projects[$project_id] = array('title'=>$title, 'type'=>$type, 'description'=>$description);
    }
    header('Content-Type: text/json');
    echo json_encode($projects);
    exit;
  }

  static function getMilestones($ids, $q){
    global $wgServer, $wgScriptPath, $wgUser;
    $me = Person::newFromId($wgUser->getId());

    $milestones = array();
    foreach($ids as $id){
      $milestone = Milestone::newFromIndex($id);
      $real_m_id = $milestone->getMilestoneId();
      $project = $milestone->getProject();

      if(!$me->isMemberOf($project) || !$me->isRole(STAFF)){
        continue;
      }
      
      $project_name = $project->getName();
      $project_name = "<a href='{$wgServer}{$wgScriptPath}/index.php/{$project_name}:Main'>". $project_name ."</a>";

      $title = $milestone->getTitle();
      $description = $milestone->getDescription();
      $description = preg_replace(
          '/.*\s?(.{0,50})(\b'.$q.'\b)(.{0,50})\s?.*?/',
          '$1<span style="display:inline-block; background-color:yellow;">$2</span>$3',
          $description
      );
      $matches = array();

      preg_match_all('/.*\s?(.{0,50})(\b'.$q.'\b)(.{0,50})\s?.*?/i', $description, $matches);
      
      if(count($matches)>0){
        $description = '... '. implode(' ... ', $matches[0]) .' ...';
      }else{
        $description = substr($description, 0, 250);
      }

      //print_r($matches);
      $start_date = $milestone->getStartDate();
      $end_date = $milestone->getEndDate();
      $date = substr($start_date, 0, 10) ." / ". substr($end_date, 0, 10);

      $milestones[$real_m_id] = array('project_name'=>$project_name, 'date'=>$date, 'title'=>$title, 'description'=>$description);
    }

    header('Content-Type: text/json');
    echo json_encode($milestones);
    exit;
  }

  static function getPostings($ids){
    global $wgServer, $wgScriptPath, $wgUser;
    $me = Person::newFromId($wgUser->getId());

    $postings = array();
    foreach($ids as $id){
      $query = "SELECT id, title, start, end, url, descr FROM grand_postings WHERE id = {$id}";
      $qres = DBFunctions::execSQL($query);
     
      if(count($qres) == 0){
        break;
      }

      $title = $qres[0]['title'];
      $start = $qres[0]['start'];
      $end = $qres[0]['end'];
      $start_end = $start ." - ".$end;
      $url = $qres[0]['url'];
      $descr = $qres[0]['descr'];
  
      $postings[] = array('title'=>$title, 'start_end'=>$start_end, 'url'=>$url, 'description'=>$descr);
      
    }
    header('Content-Type: text/json');
    echo json_encode($postings);
    exit;
  }

}

?>
