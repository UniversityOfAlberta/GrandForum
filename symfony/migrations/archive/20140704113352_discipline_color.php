<?php

use Phinx\Migration\AbstractMigration;

class DisciplineColor extends AbstractMigration
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
        $table = $this->table('grand_disciplines');
        if(!$table->hasColumn('color')){
            $table->addColumn('color', 'string', array('limit' => 16, 
                                                      'after' => 'discipline'))
                  ->save();
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
