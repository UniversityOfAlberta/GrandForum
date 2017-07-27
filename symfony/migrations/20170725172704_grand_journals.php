<?php

use Phinx\Migration\AbstractMigration;

class GrandJournals extends AbstractMigration
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
        $journal_table = $this->table('grand_journals', array('id' => 'id'));
        if(!$journal_table->exists()){
            $journal_table->addColumn('year', 'integer')
                          ->addColumn('short_title', 'string', array('limit' => 64) )
                          ->addColumn('iso_abbrev', 'string', array('limit' => 128 ) )
                          ->addColumn('title', 'string', array('limit' => 256) )
                          ->addColumn('issn', 'string', array('limit' => 9) )
                          ->addColumn('description', 'string', array('limit' => 128) )
                          ->addColumn('ranking_numerator', 'integer')
                          ->addColumn('ranking_denominator', 'integer')
                          ->addColumn('impact_factor', 'decimal', array('precision' => 8, 'scale' => 5) )
                          ->addColumn('cited_half_life', 'decimal', array('precision' => 8, 'scale' => 5) )
                          ->addColumn('eigenfactor', 'decimal', array('precision' => 8, 'scale' => 5) )
                          ->create();
        }
    }
    
}
