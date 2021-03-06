<?php

use Phinx\Migration\AbstractMigration;

class IllegalAuthors extends AbstractMigration
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
        $table = $this->table('grand_illegal_authors', array('id' => 'id'));
        if(!$table->exists()){
            $table->addColumn('author', 'string', array('limit' => '256'))
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
