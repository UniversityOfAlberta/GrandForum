<?php


use Phinx\Migration\AbstractMigration;

class SopAdditional extends AbstractMigration
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
    public function change()
    {
        $conn = $this->getAdapter()->getConnection();
        $fields = array('content',
                        'sentiment_val',
                        'sentiment_type',
                        'readability_score',
                        'reading_ease',
                        'ari_grade',
                        'ari_age',
                        'colemanliau_grade',
                        'colemanliau_age',
                        'dalechall_index',
                        'dalechall_grade',
                        'dalechall_age',
                        'fleschkincaid_grade',
                        'fleschkincaid_age',
                        'smog_grade',
                        'smog_age',
                        'errors',
                        'sentlen_ave',
                        'wordletter_ave',
                        'min_age',
                        'word_count',
                        'reviewer',
                        'emotion_stats',
                        'personality_stats');
        // CURRENT
        $rows = $this->fetchAll("SELECT user_id, ".implode(",", $fields)."
                                 FROM grand_sop");
        foreach($rows as $row){
            $gsms = $this->fetchRow("SELECT * 
                                     FROM grand_gsms
                                     WHERE user_id = {$row['user_id']}");
            $additional = json_decode($gsms['additional'], true);
            foreach($fields as $field){
                if(($field == "emotion_stats" ||
                    $field == "personality_stats") &&
                   $row[$field] != ""){
                    $obj = unserialize($row[$field]);
                    $additional[$field] = $obj;
                }
                else if($row[$field] !== '' && 
                   $row[$field] !== 0 &&
                   $row[$field] !== "0" &&
                   $row[$field] !== "0000-00-00 00:00:00"){
                    $additional[$field] = str_replace(" 00:00:00", "", $row[$field]);
                }
            }
            $serialized = $conn->quote(json_encode($additional));
            $this->execute("UPDATE grand_gsms
                            SET additional = {$serialized}
                            WHERE id = '{$gsms['id']}'");
        }
        
        // 2017
        $rows = $this->fetchAll("SELECT user_id, ".implode(",", $fields)."
                                 FROM grand_sop_2017");
        foreach($rows as $row){
            $gsms = $this->fetchRow("SELECT * 
                                     FROM grand_gsms_2017
                                     WHERE user_id = {$row['user_id']}");
            $additional = json_decode($gsms['additional'], true);
            foreach($fields as $field){
                if(($field == "emotion_stats" ||
                    $field == "personality_stats") &&
                   $row[$field] != ""){
                    $obj = unserialize($row[$field]);
                    $additional[$field] = $obj;
                }
                else if($row[$field] !== '' && 
                   $row[$field] !== 0 &&
                   $row[$field] !== "0" &&
                   $row[$field] !== "0000-00-00 00:00:00"){
                    $additional[$field] = str_replace(" 00:00:00", "", $row[$field]);
                }
            }
            $serialized = $conn->quote(json_encode($additional));
            $this->execute("UPDATE grand_gsms_2017
                            SET additional = {$serialized}
                            WHERE id = '{$gsms['id']}'");
        }
        
        // 2018
        $rows = $this->fetchAll("SELECT user_id, ".implode(",", $fields)."
                                 FROM grand_sop_2018");
        foreach($rows as $row){
            $gsms = $this->fetchRow("SELECT * 
                                     FROM grand_gsms_2018
                                     WHERE user_id = {$row['user_id']}");
            $additional = json_decode($gsms['additional'], true);
            foreach($fields as $field){
                if(($field == "emotion_stats" ||
                    $field == "personality_stats") &&
                   $row[$field] != ""){
                    $obj = unserialize($row[$field]);
                    $additional[$field] = $obj;
                }
                else if($row[$field] !== '' && 
                   $row[$field] !== 0 &&
                   $row[$field] !== "0" &&
                   $row[$field] !== "0000-00-00 00:00:00"){
                    $additional[$field] = str_replace(" 00:00:00", "", $row[$field]);
                }
            }
            $serialized = $conn->quote(json_encode($additional));
            $this->execute("UPDATE grand_gsms_2018
                            SET additional = {$serialized}
                            WHERE id = '{$gsms['id']}'");
        }
    }
}
