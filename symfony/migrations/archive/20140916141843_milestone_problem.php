<?php

use Phinx\Migration\AbstractMigration;

class MilestoneProblem extends AbstractMigration
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
        $milestones = $this->table('grand_milestones');
        if(!$milestones->hasColumn('problem')){
            $milestones->addColumn('problem', 'text', array('after' => 'status'))
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
