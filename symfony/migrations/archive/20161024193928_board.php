<?php

use Phinx\Migration\AbstractMigration;

class Board extends AbstractMigration
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
        $table = $this->table("grand_boards", array("id" => "id"));
        if(!$table->exists()){
            $table->addColumn('title', 'string', array('limit' => 64))
                  ->addColumn('description', 'text')
                  ->create();
        }
        
        $table = $this->table("grand_threads");
        $table->addColumn('board_id', 'integer', array('after' => 'id'))
              ->addIndex(array('board_id'));
        $table->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
