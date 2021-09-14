<?php

use Phinx\Migration\AbstractMigration;

class EliteFields extends AbstractMigration
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
        $this->table('grand_elite_postings')
             ->addColumn('training', 'text', array('after' => 'based_at'))
             ->addColumn('positions', 'text', array('after' => 'skills'))
             ->addColumn('level', 'string', array('limit' => 128, 'after' => 'skills'))
             ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
