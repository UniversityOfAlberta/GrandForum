<?php

use Phinx\Migration\AbstractMigration;

class PersonLanguages extends AbstractMigration
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
        $lang_table = $this->table('grand_user_languages', array('id' => 'id'));
        if(!$lang_table->exists()){
            $lang_table->addColumn('user_id', 'integer')
                       ->addColumn('language', 'string', array('limit' => 64))
                       ->addColumn('can_read', 'boolean')
                       ->addColumn('can_write', 'boolean')
                       ->addColumn('can_speak', 'boolean')
                       ->addColumn('can_understand', 'boolean')
                       ->addColumn('can_review', 'boolean')
                       ->addIndex(array('user_id'))
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
