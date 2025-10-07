<?php

use Phinx\Migration\AbstractMigration;

class PersonProfileFields extends AbstractMigration
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
        $table = $this->table('mw_user');
        if(!$table->hasColumn('first_name')){
            $table->addColumn('first_name', 'string', array('limit' => 256,
                                                            'after' => 'user_real_name'))
                  ->addColumn('middle_name', 'string', array('limit' => 256,
                                                             'after' => 'first_name'))
                  ->addColumn('last_name', 'string', array('limit' => 256,
                                                           'after' => 'middle_name'))
                  ->addColumn('prev_first_name', 'string', array('limit' => 256,
                                                                 'after' => 'last_name'))
                  ->addColumn('prev_last_name', 'string', array('limit' => 256,
                                                                'after' => 'prev_first_name'))
                  ->addColumn('honorific', 'string', array('limit' => 16,
                                                           'after' => 'prev_last_name'))
                  ->addColumn('language', 'string', array('limit' => 32,
                                                          'after' => 'honorific'))
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
