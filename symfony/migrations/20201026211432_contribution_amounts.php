<?php

use Phinx\Migration\AbstractMigration;

class ContributionAmounts extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     *
     * Uncomment this method if you would like to use it.
     *
    public function change()
    {
    }
    */
    
    /**
     * Migrate Up.
     */
    public function up()
    {
        $inkindMap = array("none" => "None",
                           "equi" => "Equipment, Software",
                           "mate" => "Materials",
                           "logi" => "Logistical Support of Field Work",
                           "srvc" => "Provision of Services",
                           "faci" => "Use of Company Facilites",
                           "sifi" => "Salaries of Scientific Staff",
                           "mngr" => "Salaries of Managerial and Administrative Staff",
                           "trvl" => "Project-related Travel",
                           "othe" => "Other");
                            
        $cashMap = array("1a" => "Salaries: Bachelors - Canadian and Permanent Residents",
                         "1b" => "Salaries: Bachelors - Foreign",
                         "1c" => "Salaries: Masters - Canadian and Permanent Residents",
                         "1d" => "Salaries: Masters - Foreign",
                         "1e" => "Salaries: Doctorate - Canadian and Permanent Residents",
                         "1f" => "Salaries: Doctorate - Foreign",
                         "2a" => "Salaries: Post-doctoral - Canadian and Permanent residents",
                         "2b" => "Salaries: Post-doctoral - Foreign",
                         "2c" => "Salaries: Other",
                         "3"  => "Salary and benefits of incumbent (Canada Research Chairs only)",
                         "4"  => "Professional and technical services/contracts",
                         "5"  => "Equipment (incl. powered vehicles)",
                         "6"  => "Materials, supplies and other expenditures",
                         "7"  => "Travel",
                         "8"  => "Other expenditures");
                         
        $map = array("none" => "None") + 
               $inkindMap + 
               $cashMap;
    
        $table = $this->table('grand_contributions_partners');
        // Add Column
        if(!$table->hasColumn('amounts')){
            $table->addColumn('amounts', 'text', array('after' => 'type'));
        }
        $table->save();
        
        // Do Migration
        $conn = $this->getAdapter()->getConnection();
        $data = $this->fetchAll('SELECT * FROM grand_contributions_partners');
        foreach($data as $row){
            $id = $row['contribution_id'];
            $partner = $row['partner'];
            
            $type = $row['type'];
            $subtype = $row['subtype'];
            $cash = $row['cash'];
            $inkind = $row['kind'];
            
            $amounts = array();
            if($type == "cash"){
                if($subtype == "none" || $subtype == "" || !isset($cashMap[$subtype])){
                    $amounts["8"] = $cash;
                }
                else{
                    $amounts[$subtype] = $cash;
                }
            }
            else if($type == "inki"){
                if(isset($map[$subtype]) && $subtype != "none"){
                    $amounts[$subtype] = $inkind;
                }
                else{
                    $amounts["othe"] = $inkind;
                    $amounts["inkind_other"] = "None";
                }
            }
            else if($type == "caki"){
                $amounts["8"] = $cash;
                if(isset($map[$subtype]) && $subtype != "none"){
                    $amounts[$subtype] = $inkind;
                }
                else{
                    $amounts["othe"] = $inkind;
                    $amounts["inkind_other"] = "None";
                }
            }
            else {
                $amounts["none"] = $cash;
            }
            
            if(!isset($map[$subtype])){
                $amounts["othe"] = $inkind;
                $amounts["inkind_other"] = $subtype;
            }
            
            $amounts = json_encode($amounts);
            $amounts = $conn->quote($amounts);
            $partner = $conn->quote($partner);
            $this->execute("UPDATE grand_contributions_partners 
                            SET amounts = {$amounts}
                            WHERE contribution_id = '{$id}' 
                            AND partner = {$partner}");
            $this->execute("COMMIT");
        }
        
        // Delete Columns
        if($table->hasColumn('unknown')){
            $table->removeColumn('unknown');
        }
        if($table->hasColumn('subtype')){
            $table->removeColumn('subtype');
        }
        if($table->hasColumn('cash')){
            $table->removeColumn('cash');
        }
        if($table->hasColumn('kind')){
            $table->removeColumn('kind');
        }
        $table->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
