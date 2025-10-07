<?php

use Phinx\Migration\AbstractMigration;

class PostingsModified extends AbstractMigration
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
        $table = $this->table("grand_news_postings");
        $table->addColumn('modified', 'timestamp', array('after' => 'created'))
              ->update();
              
        $table = $this->table("grand_event_postings");
        $table->addColumn('modified', 'timestamp', array('after' => 'created'))
              ->update();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
