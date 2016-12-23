<?php

use Phinx\Migration\AbstractMigration;

class UserRequestSendEmail extends AbstractMigration
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
        $table->addColumn('wpSendEmail', 'string', array('after' => 'wpEmail', 'limit' => 64))
              ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
