<?php

use Phinx\Migration\AbstractMigration;

class UniversityShortLength extends AbstractMigration
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
        $table = $this->table('grand_universities');
        $table->changeColumn('university_name', 'string', array('limit' => 256))
              ->changeColumn('short_name', 'string', array('limit' => 256))
              ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
