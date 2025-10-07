<?php

use Phinx\Migration\AbstractMigration;

class Journals extends AbstractMigration
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
        $journal_table = $this->table('grand_journals', array('id' => 'id'));
        if(!$journal_table->exists()){
            $journal_table->addColumn('year', 'integer')
                          ->addColumn('short_title', 'string', array('limit' => 64) )
                          ->addColumn('iso_abbrev', 'string', array('limit' => 128 ) )
                          ->addColumn('title', 'string', array('limit' => 256) )
                          ->addColumn('issn', 'string', array('limit' => 9) )
                          ->addColumn('eissn', 'string', array('length' => 9))
                          ->addColumn('description', 'string', array('limit' => 128) )
                          ->addColumn('ranking_numerator', 'integer')
                          ->addColumn('ranking_denominator', 'integer')
                          ->addColumn('impact_factor', 'decimal', array('precision' => 8, 'scale' => 5) )
                          ->addColumn('cited_half_life', 'decimal', array('precision' => 8, 'scale' => 5) )
                          ->addColumn('eigenfactor', 'decimal', array('precision' => 8, 'scale' => 5) )
                          ->addIndex('issn')
                          ->addIndex('eissn')
                          ->create();
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
