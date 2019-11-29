<?php

use Phinx\Migration\AbstractMigration;

class Leverage extends AbstractMigration
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
        $table = $this->table('grand_collaborations');
        $table->addColumn('projected_cash', 'decimal', array('scale'=>2, 'precision'=>65, 'after' => 'inkind'))
              ->addColumn('projected_inkind', 'decimal', array('scale'=>2, 'precision'=>65, 'after' => 'projected_cash'))
              ->addColumn('leverage', 'boolean', array('after' => 'knowledge_user'))
              ->addIndex('leverage')
              ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
