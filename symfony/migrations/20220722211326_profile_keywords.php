<?php

use Phinx\Migration\AbstractMigration;

class ProfileKeywords extends AbstractMigration
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
        $table = $this->table('grand_person_keywords', array('id' => 'id'));
        $table->addColumn('user_id', 'integer')
              ->addColumn('keyword', 'string', array('limit' => 64) )
              ->addIndex('user_id')
              ->addIndex('keyword')
              ->create();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
