<?php

use Phinx\Migration\AbstractMigration;

class UserStory extends AbstractMigration
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
   	$table = $this->table('grand_user_stories', array('id'=>'rev_id'));
	if(!$table->exists()){
	    $table->addColumn('id', 'integer')
		  ->addColumn('user_id', 'integer')
                  ->addColumn('title', 'string')
		  ->addColumn('story', 'text')
		  ->addColumn('date_submitted', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
		  ->addColumn('approved', 'boolean')
		  ->addIndex(array('user_id'))
		  ->addIndex(array('id'))
                  ->addIndex(array('title'))
		  ->save();
	}     
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
