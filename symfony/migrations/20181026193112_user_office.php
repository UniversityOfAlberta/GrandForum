<?php

use Phinx\Migration\AbstractMigration;

class UserOffice extends AbstractMigration
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
        $table = $this->table('mw_user');
        $table->addColumn('user_office', 'string', array('length' => 32, 'after' => 'user_linkedin'))
              ->update();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
