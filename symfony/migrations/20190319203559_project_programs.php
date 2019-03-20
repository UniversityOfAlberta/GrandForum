<?php

use Phinx\Migration\AbstractMigration;

class ProjectPrograms extends AbstractMigration
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
        $addr_table = $this->table('grand_project_programs', array('id' => 'id'));
        if(!$addr_table->exists()){
            $addr_table->addColumn('proj_id', 'integer')
                       ->addColumn('name', 'string', array('limit' => 128))
                       ->addColumn('url', 'string', array('limit' => 256))
                       ->addIndex(array('proj_id'))
                       ->create();
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
