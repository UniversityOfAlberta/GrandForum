<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class ElitePosting extends AbstractMigration
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
        $table = $this->table('grand_elite_postings', array('id' => 'id'));
        $table->addColumn('user_id', 'integer')
              ->addColumn('visibility', 'string', array('limit' => 16))
              ->addColumn('language', 'string', array('limit' => 32))
              ->addColumn('title', 'string', array('limit' => 300))
              ->addColumn('title_fr', 'string', array('limit' => 300))
              ->addColumn('article_link', 'string', array('limit' => 256))
              ->addColumn('start_date', 'datetime')
              ->addColumn('end_date', 'datetime')
              ->addColumn('summary', 'text')
              ->addColumn('summary_fr', 'text')
              ->addColumn('image', 'text', array('limit' => MysqlAdapter::TEXT_MEDIUM))
              ->addColumn('image_caption', 'string', array('limit' => 128))
              ->addColumn('image_caption_fr', 'string', array('limit' => 128))
              // Custom Fields
              ->addColumn('company_name', 'string', array('limit' => 128))
              ->addColumn('company_profile', 'text')
              ->addColumn('reports_to', 'string', array('limit' => 128))
              ->addColumn('based_at', 'string', array('limit' => 128))
              ->addColumn('responsibilities', 'text')
              ->addColumn('qualifications', 'text')
              ->addColumn('skills', 'text')
              // End Custom Fields
              ->addColumn('preview_code', 'string', array('limit' => 32))
              ->addColumn('created', 'timestamp', array('default' => 'CURRENT_TIMESTAMP'))
              ->addColumn('modified', 'timestamp')
              ->addColumn('deleted', 'boolean', array('default' => 0))
              ->addIndex('user_id')
              ->create();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
