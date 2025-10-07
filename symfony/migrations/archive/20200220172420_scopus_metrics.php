<?php

use Phinx\Migration\AbstractMigration;

class ScopusMetrics extends AbstractMigration
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
        $table = $this->table('grand_user_metrics');
        $table->addColumn('scopus_document_count','integer', array('after' => 'gs_i10_index'))
              ->addColumn('scopus_cited_by_count', 'integer', array('after' => 'scopus_document_count'))
              ->addColumn('scopus_citation_count', 'integer', array('after' => 'scopus_cited_by_count'))
              ->addColumn('scopus_h_index','float', array('after' => 'scopus_citation_count'))
              ->addColumn('scopus_coauthor_count', 'integer', array('after' => 'scopus_h_index'))
              ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
