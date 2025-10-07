<?php

use Phinx\Migration\AbstractMigration;

class UserPronouns extends AbstractMigration
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
        $this->table('mw_user')
             ->addColumn('user_pronouns', 'string', array('limit' => 32, 'after' => 'user_gender'))
             ->addColumn('user_ethnicity', 'string', array('limit' => 64, 'after' => 'user_disability_status'))
             ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
