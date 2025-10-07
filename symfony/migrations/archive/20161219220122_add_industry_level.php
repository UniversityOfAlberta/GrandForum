<?php

use Phinx\Migration\AbstractMigration;

class AddIndustryLevel extends AbstractMigration
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
        $table = $this->table('grand_contributions_partners');
        $table->addColumn('level', 'string', array('after' => 'contact', 'limit' => 64))
              ->addColumn('industry', 'string', array('after' => 'contact', 'limit' => 64))
              ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
