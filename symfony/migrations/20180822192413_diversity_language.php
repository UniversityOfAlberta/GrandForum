<?php

use Phinx\Migration\AbstractMigration;

class DiversityLanguage extends AbstractMigration
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
        $table->addColumn('language', 'string', array('length' => 2, 'after' => 'user_id'))
              ->update();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
