<?php

use Phinx\Migration\AbstractMigration;

class UniversityCoordinates extends AbstractMigration
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
        if(!$table->hasColumn('latitude')){
            $table->addColumn('latitude', 'string', array('limit' => 32, 
                                                      'after' => 'university_name'))
                  ->save();
        }
        if(!$table->hasColumn('longitude')){
            $table->addColumn('longitude', 'string', array('limit' => 32,
                                                       'after' => 'latitude'))
                  ->save();
        }
        if(!$table->hasColumn('color')){
            $table->addColumn('color', 'string', array('limit' => '32',
                                                   'after' => 'longitude'))
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
