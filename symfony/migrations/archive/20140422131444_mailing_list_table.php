<?php

use Phinx\Migration\AbstractMigration;

class MailingListTable extends AbstractMigration
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
        $table = $this->table('wikidev_projects_rules', array('id' => true, 'primary_key' => array('id')));
        $table->addColumn('type', 'string', array('limit' => 32))
              ->addColumn('project_id', 'integer')
              ->addColumn('value', 'string', array('limit' => 64))
              ->create();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
