<?php

use Phinx\Migration\AbstractMigration;

class GsmsAdditional extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(){
        $conn = $this->getAdapter()->getConnection();
        $fields = array('applicant_number',
                        'status',
                        'gender',
                        'date_of_birth',
                        'country_of_birth',
                        'country_of_citizenship',
                        'program_name',
                        'applicant_type',
                        'education_history',
                        'department',
                        'epl_test',
                        'epl_score',
                        'epl_listen',
                        'epl_write',
                        'epl_read',
                        'epl_speaking',
                        'cs_app',
                        'academic_year',
                        'term',
                        'subplan_name',
                        'program',
                        'degree_code',
                        'admission_program_name',
                        'folder',
                        'department_gpa',
                        'department_gpa_scale',
                        'department_normalized_gpa',
                        'fgsr_gpa',
                        'fgsr_gpa_scale',
                        'fgsr_normalized_gpa',
                        'funding_note',
                        'department_decision',
                        'fgsr_decision',
                        'decision_response',
                        'general_notes');
                        
        $stmt = $this->query("SELECT * 
                              FROM grand_gsms");
        $rows = $stmt->fetchAll();
        foreach($rows as $row){
            $additional = @unserialize($row['additional']);
            if($additional === false){
                $additional = json_decode($row['additional'], true);
            }
            foreach($fields as $field){
                if($row[$field] !== '' && 
                   $row[$field] !== 0 &&
                   $row[$field] !== "0" &&
                   $row[$field] !== "0000-00-00 00:00:00"){
                    $additional[$field] = str_replace(" 00:00:00", "", $row[$field]);
                }
            }
            $serialized = $conn->quote(json_encode($additional));
            $this->execute("UPDATE grand_gsms
                            SET additional = {$serialized}
                            WHERE id = '{$row['id']}'");
        }
        
        
        $stmt = $this->query("SELECT * 
                              FROM grand_gsms_2017");
        $rows = $stmt->fetchAll();
        foreach($rows as $row){
            $row['additional'] = preg_replace('!s:(\d+):"(.*?)";!se', "'s:'.strlen('$2').':\"$2\";'", $row['additional']);
            $additional = @unserialize($row['additional']);
            if($additional === false){
                $additional = json_decode($row['additional'], true);
            }
            foreach($fields as $field){
                if($row[$field] !== '' && 
                   $row[$field] !== 0 &&
                   $row[$field] !== "0" &&
                   $row[$field] !== "0000-00-00 00:00:00"){
                    $additional[$field] = str_replace(" 00:00:00", "", $row[$field]);
                }
            }
            $serialized = $conn->quote(json_encode($additional));
            $this->execute("UPDATE grand_gsms_2017
                            SET additional = {$serialized}
                            WHERE id = '{$row['id']}'");
        }
        $table = $this->table('grand_gsms');
        foreach($fields as $field){
            $table->removeColumn($field);
        }
        $table->save();
        
        $table = $this->table('grand_gsms_2017');
        foreach($fields as $field){
            $table->removeColumn($field);
        }
        $table->save();
        
        $this->table('grand_person_gsms')->drop();
    }
}
