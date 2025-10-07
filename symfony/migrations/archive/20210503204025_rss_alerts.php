<?php

use Phinx\Migration\AbstractMigration;

class RssAlerts extends AbstractMigration
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
        $table = $this->table('grand_rss_feeds', array('id' => 'id'));
        $table->addColumn('url', 'string', array('limit' => 256))
              ->create();
              
        $table = $this->table('grand_rss_articles', array('id' => 'id'));
        $table->addColumn('feed', 'integer')
              ->addColumn('rss_id', 'string', array('limit' => 128))
              ->addColumn('url', 'string', array('limit' => 1024))
              ->addColumn('title', 'string', array('limit' => 256))
              ->addColumn('date', 'datetime')
              ->addColumn('description', 'text')
              ->addColumn('people', 'text')
              ->addColumn('projects', 'text')
              ->addColumn('keywords', 'text')
              ->addIndex('feed')
              ->addIndex('rss_id')
              ->create();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
