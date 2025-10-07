<?php

use Phinx\Migration\AbstractMigration;

class EliteMessage extends AbstractMigration
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
             ->changeColumn('visibility', 'string', array('limit' => 32))
             ->addColumn('comments', 'text', array('after' => 'skills'))
             ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
