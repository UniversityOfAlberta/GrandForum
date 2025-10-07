<?php

use Phinx\Migration\AbstractMigration;

class PersonMetrics extends AbstractMigration
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
        $table = $this->table('grand_user_metrics', array("id"=>"id"));
        $table->addColumn('user_id','integer')
              ->addColumn('gs_citation_count','integer')
              ->addColumn('gs_hindex_5_years', 'float')
              ->addColumn('gs_i10_index_5_years', 'float')
              ->addColumn('gs_hindex','float')
              ->addColumn('gs_i10_index', 'float')
              ->addColumn('change_date', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
              ->addIndex(array('user_id'))
              ->create();
              
        $table = $this->table("grand_gs_citations", array("id" => false, 'primary_key' => array('user_id', 'year')));
        $table->addColumn('user_id', 'integer', array('default' => 0, 'null' => false))
              ->addColumn('year', 'string', array('limit' => 4, 'default' => 0, 'null' => false))
              ->addColumn('count', 'integer')
              ->addColumn('change_date', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
              ->create();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
