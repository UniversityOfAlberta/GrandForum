<?php

use Phinx\Migration\AbstractMigration;

class CrmProjects extends AbstractMigration
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
        $table = $this->table('grand_crm_projects', array('id' => 'id'));
        $table->addColumn('contact_id', 'integer')
              ->addColumn('project_id', 'integer')
              ->addIndex('contact_id')
              ->addIndex('project_id')
              ->create();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
