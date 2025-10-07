<?php

use Phinx\Migration\AbstractMigration;

class Freeze extends AbstractMigration
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
        $table = $this->table("grand_freeze", array("id" => "id"));
        if(!$table->exists()){
            $table->addColumn('project_id', 'integer')
                  ->addColumn('feature', 'string', array('limit' => 32))
                  ->addIndex('project_id')
                  ->addIndex('feature')
                  ->create();
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
