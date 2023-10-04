<?php

use Phinx\Migration\AbstractMigration;

class RoleProjects extends AbstractMigration
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
        $table = $this->table('grand_role_projects', array('id' => false, 'primary_key' => array('role_id', 'project_id')));
        if(!$table->exists()){
            $table->addColumn('role_id', 'integer', array('default' => 0, 'null' => false))
                  ->addColumn('project_id', 'integer', array('default' => 0, 'null' => false))
                  ->create();
        }
        
        $table = $this->table('grand_role_request');
        $table->addColumn('role_projects', 'text', array('after' => 'role'))
              ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
