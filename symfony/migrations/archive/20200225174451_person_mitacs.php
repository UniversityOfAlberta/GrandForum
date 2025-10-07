<?php

use Phinx\Migration\AbstractMigration;

class PersonMitacs extends AbstractMigration
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
        $table = $this->table('mw_user', array('id' => 'id'));
        $table->addColumn('user_mitacs', 'string', array('after' => 'user_ecr', 'limit' => 128))
              ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
