<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['Solr'] = 'Solr';
$wgExtensionMessagesFiles['Solr'] = $dir . 'Solr.i18n.php';
$wgSpecialPageGroups['Solr'] = 'grand-tools';

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
		SpecialPage::SpecialPage('Solr', MANAGER.'+', true, 'runSolr');
	}
	
	function run(){
	    global $wgUser, $wgOut, $wgServer, $wgScriptPath, 
             $sqlTables, $resultFields, $sqlPrimaryKeys;

      $sqlTables = json_encode($sqlTables);
      $sqlPrimaryKeys = json_encode($sqlPrimaryKeys);
      $resultFields = json_encode($resultFields);

      $script =<<<EOF
        <script type="text/javascript">

          // Write PHP arrays
          var sqlTables = '{$sqlTables}';
          var sqlPrimaryKeys = '{$sqlPrimaryKeys}';
          var resultFields = '{$resultFields}';

          br = "<br/>\\n";

          $(document).ready(function(){

						$("#query").focus();
            $('#resultsTable').dataTable({
                "bPaginate": false,
                "bLengthChange": false,
                "bFilter": false,
                /*"aaSorting": [[0,'asc']],
                "aoColumns": [
                    null,
                    null,
                    { "bSortable": false }
                ]*/
            });
          });

					$("#searchForm").submit(function() {
							return false;
					});


          $("#query").live("keypress", function(e){
            if (e.which == 13){
							e.preventDefault();
              query = $("#query").val();

              url_solr = "{$wgServer}{$wgScriptPath}/extensions/Solr/curl.php" 
                  + "?query=(" + query + ")"

							$.getJSON(url_solr,
								function(data) {
									$("#results").text("");
									$.each(data, function(key, val) { walkJsonTree(key, val) });
							});

							e.preventDefault();
							return null;
            }
          });


					$("#b_search").live("click", function() {
						var press = jQuery.Event("keypress");
						press.which = 13;
						$("#query").trigger(press);
            return false;
					});


          function addResultHeader(label, value){
            $("#resultsTable tbody").append("<tr><td>" + label + "</td></td>" + value + "</td></tr>");
          }

 
          function addResultRow(label, value){
            $("#resultsTable tbody").append(
                   "<tr class=solr_row>" 
                   + "<td class=solr_row_label>" + label + "</td>" 
                   + "<td class=solr_row_value>" + value + "</td>"
                   + "</tr> \\n");
          }


          function getAllInQuotes(blob){
            output = [];
            idx = 0;
            while (idx < blob.length){ 
              start = blob.indexOf('"', idx) + 1;
              if (start == 0)
                break;
              end  = blob.indexOf('"', start + 1);
              bit = blob.substring(start, end);
              output.push(bit.replace(/\./g," "));
              idx = end + 1;
            } 
            return output.join(", ");
          }


          function getBlobWithLabels(blob, type){
            // Odd strings are keys, evens are vals
            labels = [];
            values = [];
            idx = 0;
            isLabel = true;
            while (idx < blob.length){ 
              start = blob.indexOf('"', idx) + 1;
              if (start == 0)
                break;
              end  = blob.indexOf('"', start);
              bit = blob.substring(start, end);
//alert(blob.length+"  "+ start +" "+ end +"  "+ bit);
              if (isLabel){
                // Blob field label: To title case, remove underscores
                bit = (bit.charAt(0).toUpperCase() + bit.substring(1)).replace("_"," ");
								labels.push(bit);
								isLabel = false;

              } else {
								values.push(bit);
								isLabel = true;
              }
              idx = end + 1;
            } 
            output = [];
            for (i = 0; i < labels.length; i++){
              output.push(addResultRow(labels[i], values[i]));
            } 
            return output.join("\\n");
          }


					function getSqlParams(type, id){
            prefix = "&id=" + sqlPrimaryKeys[type]+","+id + "&fields=";
					  bits = []; 
             
            fields = resultFields[type];
						for (var key in fields){
						  bits.push(key);
						}
						return prefix + bits.join(",");
          }


          function printResults(key, val, type) {
	          fields = resultFields[type];
	          output = ""; 

	          $.each(fields, function(field){
	            isFound = false;

	            // Blob: last 5 chars are ".blob"
	            if (field.length > 5 && field.substring(0, field.length - 5) == key){
	              
	              if (key == "authors"){ 
                  output = getAllInQuotes(val);
                  isFound = true; 
                }

                if (key == "data"){
                  output = getBlobWithLabels(val, type);
                  if (output.length)
										isFound = true; 
                }

	            } else if (key == field){
                output = val;
                isFound = true;
              } 

							if (isFound){
								if (output == null || output == "")
									output = "n/a";
								var label = fields[field];

								if(!label.length && key == "data") // blob (see defs at top)
									$("#results").append(output); 

								else {
									// SECONDARY SQL QUERY
									if (label.indexOf("|") > 0){
										var bits = label.split("|");
										label = bits[0].trim(); 
                    if (output < 1){ // "output" is the foreign key
                      output = "id " + output + ": no such record";

                    } else {
											var table = bits[1].trim();
											var table_id_label = bits[2].trim(); 
											var id = "&id=" + table_id_label + "," + output;
											var table_target_field = "&fields=" + bits[3].trim();
											var url_sql = "{$wgServer}{$wgScriptPath}/extensions/Solr/sql.php"
																		+ "?table=" + table + id + table_target_field;

											$.ajax({
												url: url_sql, 
												success: function(data){ 
													$.each($.parseJSON(data), function(key, val){ 
														output = val; 
													});
												}, 
												async: false 
											}); 
									  }
									}

									$("#results").append(addResultRow(label, output));
								} 
              }
	          }); 
          } // printResults


					function walkJsonTree(key, val) {
						if (val instanceof Object) {
              //$("#results").append("<b>" + key + "</b>" + br);
							$.each(val, function(key, val) {
									walkJsonTree(key, val)
							});

						} else {
							if (key.match(/_id$/)){
							  // Get result type
							  type = key.split("_")[0];

	              addResultHeader(type, val);

                // PRIMARY SQL QUERY
							  var table = sqlTables[type];
							  var params = getSqlParams(type, val);
                var url_sql = "{$wgServer}{$wgScriptPath}/extensions/Solr/sql.php"
                              + "?table=" + table + params;

							  $.ajax({
                  url: url_sql, 
                  success: function(data){ 
                    $.each($.parseJSON(data), function(key, val){ printResults(key, val, type) });
                  }, 
                  async: false 
                }); 

							} else {
                //$("#results").append(key + "  " + val + br);
							  if (key == "numFound"){
									//$("#resultsTable tbody").append("<tr><td>" + key + "</td><td>" + val + "</td></tr>");
								} 
							}
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
        <div id="results">
             <table class="indexTable dataTable" cellspacing="1" cellpadding="3" frame="box" rules="all" id="resultsTable">
             <thead>
             <tr><td width="20%">Content Type</td><td>Excerpt</td></tr>
             </thead>
             <tbody>
             <tr><td></td><td>Empty</td></tr>
             </tbody>
             </table>
        </div>
			');

  } // run()

}

?>
