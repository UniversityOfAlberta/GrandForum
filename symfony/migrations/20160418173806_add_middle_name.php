<?php

use Phinx\Migration\AbstractMigration;

class AddMiddleName extends AbstractMigration
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
        $table->addColumn('wpFirstName', 'string', array('after' => 'wpRealName', 'limit' => 120))
              ->addColumn('wpMiddleName', 'string', array('after' => 'wpFirstName', 'limit' => 120))
              ->addColumn('wpLastName', 'string', array('after' => 'wpRealName', 'limit' => 120))
              ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }

}
