<?php

use Phinx\Migration\AbstractMigration;

class Diversity extends AbstractMigration
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
        $table = $this->table('grand_diversity');
        $table->addColumn('user_id', 'integer')
              ->addColumn('reason', 'text')
              ->addColumn('gender', 'text')
              ->addColumn('sexuality', 'text')
              ->addColumn('birth', 'string', array('limit' => 32))
              ->addColumn('indigenous', 'string', array('limit' => 32))
              ->addColumn('disability', 'string', array('limit' => 32))
              ->addColumn('disability_visibility', 'string', array('limit' => 32))
              ->addColumn('minority', 'string', array('limit' => 32))
              ->addColumn('race', 'text')
              ->addColumn('racialized', 'string', array('limit' => 32))
              ->addColumn('immigration', 'string', array('limit' => 128))
              ->addColumn('comments', 'text')
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
