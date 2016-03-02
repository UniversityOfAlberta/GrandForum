<?php

use Phinx\Migration\AbstractMigration;

class MessageThread extends AbstractMigration
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
	$table = $this->table("grand_threads", array("id" => "id"));
	if(!$table->exists()){
	    $table->addColumn('user_id', 'integer')
                  ->addColumn('users', 'text')
                  ->addColumn('title', 'string', array('limit' => 64))
		  ->addColumn('date_created', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
		   ->addIndex(array('user_id'))
		   ->addIndex(array('title'))
		  ->create();
	}
        $table = $this->table("grand_posts", array("id" => "id"));
        if(!$table->exists()){
            $table->addColumn('thread_id', 'integer')
                  ->addColumn('user_id', 'integer')
                  ->addColumn('message', 'text')
                  ->addColumn('date_created', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
		   ->addIndex(array('thread_id'))
		   ->addIndex(array('user_id'))
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
