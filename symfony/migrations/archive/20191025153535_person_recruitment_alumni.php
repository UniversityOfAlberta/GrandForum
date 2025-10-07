<?php

use Phinx\Migration\AbstractMigration;

class PersonRecruitmentAlumni extends AbstractMigration
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
        $table = $this->table('grand_alumni', array('id' => 'id'));
        $table->addColumn('user_id', 'integer')
              ->addColumn('recruited', 'string', array('limit' => 64))
              ->addColumn('recruited_country', 'string', array('limit' => 64))
              ->addColumn('alumni', 'string', array('limit' => 32))
              ->addColumn('alumni_country', 'string', array('limit' => 64))
              ->addColumn('alumni_sector', 'string', array('limit' => 32))
              ->addIndex('user_id')
              ->create();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
