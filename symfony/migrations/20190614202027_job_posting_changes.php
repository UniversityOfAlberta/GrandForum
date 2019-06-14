<?php

use Phinx\Migration\AbstractMigration;

class JobPostingChanges extends AbstractMigration
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
        $table->addColumn('language', 'string', array('after' => 'rank', 'limit' => 32))
              ->addColumn('job_title_fr', 'string', array('after' => 'job_title', 'limit' => 128))
              ->addColumn('research_fields_fr', 'text', array('after' => 'research_fields'))
              ->addColumn('keywords_fr', 'text', array('after' => 'keywords'))
              ->addColumn('summary_fr', 'text', array('after' => 'summary'))
              ->addIndex('job_title_fr')
              ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
