<?php

use Phinx\Migration\AbstractMigration;

class JobPostings extends AbstractMigration
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
        $table = $this->table('grand_job_postings', array('id' => 'id'));
        $table->addColumn('user_id', 'integer')
              ->addColumn('visibility', 'string', array('limit' => 16))
              ->addColumn('job_title', 'string', array('limit' => 128))
              ->addColumn('deadline_type', 'string', array('limit' => 16))
              ->addColumn('deadline_date', 'datetime')
              ->addColumn('start_date_type', 'string', array('limit' => 32))
              ->addColumn('start_date', 'datetime')
              ->addColumn('tenure', 'string', array('limit' => 8))
              ->addColumn('rank', 'string', array('limit' => 16))
              ->addColumn('rank_other', 'string', array('limit' => 32))
              ->addColumn('position_type', 'string', array('limit' => 32))
              ->addColumn('research_fields', 'text')
              ->addColumn('keywords', 'text')
              ->addColumn('contact', 'string', array('limit' => 128))
              ->addColumn('source_link', 'string', array('limit' => 256))
              ->addColumn('summary', 'text')
              ->addColumn('created', 'timestamp', array('default' => 'CURRENT_TIMESTAMP'))
              ->addColumn('deleted', 'boolean', array('default' => 0))
              ->addIndex(array('user_id'))
              ->addIndex(array('job_title'))
              ->create();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
