<?php

use Phinx\Migration\AbstractMigration;

class GrantContributions extends AbstractMigration
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
        $table = $this->table('grand_grant_contributions', array('id' => false, 'primary_key' => array('grant_id', 'contribution_id')));
        if(!$table->exists()){
            $table->addColumn('grant_id','integer')
                  ->addColumn('contribution_id', 'integer')
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
