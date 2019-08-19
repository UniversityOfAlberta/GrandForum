<?php

use Phinx\Migration\AbstractMigration;

class JobPostingPreviewCode extends AbstractMigration
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
        $table = $this->table('grand_job_postings');
        $table->addColumn('preview_code', 'string', array('limit' => 32, 'after' => 'summary_fr'))
              ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
