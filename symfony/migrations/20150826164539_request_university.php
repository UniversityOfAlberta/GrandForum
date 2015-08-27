<?php

use Phinx\Migration\AbstractMigration;

class RequestUniversity extends AbstractMigration
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
        $table = $this->table('grand_user_request');
        $table->addColumn('university', 'string', array('after' => 'wpNS', 'limit' => '256'))
              ->addColumn('department', 'string', array('after' => 'university', 'limit' => '256'))
              ->addColumn('position', 'string', array('after' => 'department', 'limit' => '256'))
              ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
