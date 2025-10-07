<?php

use Phinx\Migration\AbstractMigration;

class HqpMovedOn extends AbstractMigration
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
        $movedOn = $this->table('grand_movedOn');
        if(!$movedOn->hasColumn('effective_date')){
            $movedOn->addColumn('effective_date', 'timestamp', array('after' => 'country',
                                                                     'default' => '0000-00-00 00:00:00'))
                    ->save();
        }
        $theses = $this->table('grand_theses');
        if(!$theses->hasColumn('moved_on')){
            $theses->addColumn('moved_on', 'integer', array('after' => 'id'))
                   ->removeIndex(array('user_id', 'publication_id'))
                   ->removeColumn('date')
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
