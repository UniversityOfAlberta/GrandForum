<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class NewsPost extends AbstractMigration
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
        $table = $this->table('grand_news_post', array('id' => 'id'));
        $table->addColumn('translated_id', 'integer')
              ->addColumn('user_id', 'integer')
              ->addColumn('visibility', 'string', array('limit' => 16))
              ->addColumn('language', 'string', array('limit' => 32))
              ->addColumn('title', 'string', array('limit' => 70))
              ->addColumn('article_link', 'string', array('limit' => 256))
              ->addColumn('posted_date', 'datetime')
              ->addColumn('summary', 'text')
              ->addColumn('author', 'string', array('limit' => 128))
              ->addColumn('source_name', 'string', array('limit' => 128))
              ->addColumn('source_link', 'string', array('limit' => 256))
              ->addColumn('image', 'text', array('limit' => MysqlAdapter::TEXT_MEDIUM))
              ->addColumn('image_caption', 'string', array('limit' => 128))
              ->addColumn('created', 'timestamp', array('default' => 'CURRENT_TIMESTAMP'))
              ->addColumn('deleted', 'boolean', array('default' => 0))
              ->addIndex('user_id')
              ->addIndex('translated_id')
              ->create();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
