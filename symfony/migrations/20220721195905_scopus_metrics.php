<?php

use Phinx\Migration\AbstractMigration;

class ScopusMetrics extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $table = $this->table('grand_user_gsmetrics');
        $table->addColumn('scopus_document_count','integer', array('after' => 'i10_index'))
              ->addColumn('scopus_cited_by_count', 'integer', array('after' => 'scopus_document_count'))
              ->addColumn('scopus_citation_count', 'integer', array('after' => 'scopus_cited_by_count'))
              ->addColumn('scopus_h_index','float', array('after' => 'scopus_citation_count'))
              ->addColumn('scopus_coauthor_count', 'integer', array('after' => 'scopus_h_index'))
              ->save();
    }
}
