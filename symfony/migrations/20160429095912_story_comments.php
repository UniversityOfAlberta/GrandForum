<?php

use Phinx\Migration\AbstractMigration;

class StoryComments extends AbstractMigration
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
        $table = $this->table("grand_story_comments", array("id" => "id"));
        if(!$table->exists()){
            $table->addColumn('story_id', 'integer')
		  ->addColumn('parent_id', 'integer')
                  ->addColumn('user_id', 'integer')
                  ->addColumn('message', 'text')
                  ->addColumn('date_created', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
                   ->addIndex(array('story_id'))
                   ->addIndex(array('user_id'))
		   ->addIndex(array('parent_id'))
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
