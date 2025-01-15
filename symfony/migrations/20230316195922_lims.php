<?php

use Phinx\Migration\AbstractMigration;

class LIMS extends AbstractMigration
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
        $table = $this->table('grand_lims_contact', array('id' => 'id'));
        $table->addColumn('title', 'string', array('limit' => 256))
              ->addColumn('owner', 'integer')
              ->addColumn('details', 'text')
              ->addIndex('title')
              ->addIndex('owner')
              ->create();
              
        $table = $this->table('grand_lims_opportunity', array('id' => 'id'));
        $table->addColumn('contact', 'integer')
              ->addColumn('owner', 'integer')
              ->addColumn('user_type', 'string', array('limit' => 64))
              ->addColumn('description', 'text')
              ->addColumn('category', 'string', array('limit' => 64))
              ->addIndex('contact')
              ->addIndex('owner')
              ->create();
              
        $table = $this->table('grand_lims_task', array('id' => 'id'));
        $table->addColumn('opportunity', 'integer')
              ->addColumn('assignee', 'integer')
              ->addColumn('task', 'text')
              ->addColumn('due_date', 'datetime')
              ->addColumn('comments', 'text')
              ->addColumn('status', 'string', array('limit' => 64))
              ->addIndex('opportunity')
              ->addIndex('assignee')
              ->create();
              
        $table = $this->table('grand_lims_projects', array('id' => 'id'));
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
