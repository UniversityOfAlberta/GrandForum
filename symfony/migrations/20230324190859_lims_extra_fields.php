<?php

use Phinx\Migration\AbstractMigration;

class LimsExtraFields extends AbstractMigration
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
        $table = $this->table('grand_lims_opportunity');
        $table->addColumn('satisfaction', 'string', array('limit' => 3, 'after' => 'category'))
              ->addColumn('responded', 'string', array('limit' => 8, 'after' => 'category'))
              ->addColumn('surveyed', 'string', array('limit' => 8, 'after' => 'category'))
              ->addColumn('products', 'text', array('after' => 'category'))
              ->update();
              
        $table = $this->table('grand_lims_task');
        $table->addColumn('date', 'datetime', array('after' => 'status'))
              ->update();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
