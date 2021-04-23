<?php

use Phinx\Migration\AbstractMigration;

class GrantScientificTitle extends AbstractMigration
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
        $table = $this->table('grand_grants');
        if(!$table->hasColumn('scientific_title')){
            $table->addColumn('scientific_title', 'string', array('after' => 'title', 'limit' => 256))
                  ->save();
        }
        
        $rows = $this->fetchAll("SELECT * FROM `grand_grants`");
        foreach($rows as $row){
            $exploded = explode("\n", $row['description']);
            $description = $exploded[0];
            if(strlen($description) > 256){
                $description = trim(substr($description, 0, 253))."...";
            }
            $scientific_title = $description;
            if($row['role'] != ""){
                // Probably created automatically
                if($row['description'] == ""){
                    $scientific_title = $row['title'];
                }
            }
            else {
                // Probably created manually
                if($row['title'] != ""){
                    $scientific_title = $row['title'];
                }
            }
            $scientific_title = str_replace("'", "\\'", $scientific_title);
            $this->execute("UPDATE `grand_grants` SET `scientific_title` = '$scientific_title' WHERE id = '{$row['id']}'");
        }
    }
}
