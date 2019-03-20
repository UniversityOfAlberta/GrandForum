<?php

use Phinx\Migration\AbstractMigration;

class ProjectContact extends AbstractMigration
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
        $table = $this->table('grand_project_addresses');
        $table->rename('grand_project_contact')
              ->addColumn('phone', 'string', array('limit' => 32))
              ->addColumn('fax', 'string', array('limit' => 32))
              ->addColumn('email', 'string', array('limit' => 64))
              ->addColumn('twitter', 'string', array('limit' => 128))
              ->addColumn('facebook', 'string', array('limit' => 128))
              ->addColumn('linkedin', 'string', array('limit' => 128))
              ->addColumn('youtube', 'string', array('limit' => 128))
              ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
