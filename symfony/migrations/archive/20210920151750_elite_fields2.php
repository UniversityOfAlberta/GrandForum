<?php

use Phinx\Migration\AbstractMigration;

class EliteFields2 extends AbstractMigration
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
             ->addColumn('phone', 'string', array('limit' => 32, 'after' => 'based_at'))
             ->addColumn('email', 'string', array('limit' => 64, 'after' => 'based_at'))
             ->addColumn('contact', 'string', array('limit' => 128, 'after' => 'based_at'))
             ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
