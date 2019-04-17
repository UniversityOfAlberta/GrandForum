<?php

use Phinx\Migration\AbstractMigration;

class Poll extends AbstractMigration
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
        $table = $this->table('grand_poll');
        $table->addColumn('choices', 'integer', array('after' => 'poll_name', 'default' => 1))
              ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
