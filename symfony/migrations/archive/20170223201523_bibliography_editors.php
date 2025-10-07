<?php

use Phinx\Migration\AbstractMigration;

class BibliographyEditors extends AbstractMigration
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
        $table = $this->table("grand_bibliography");
        $table->addColumn('editors', 'string', array('after' => 'person_id'))
              ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
