<?php

use Phinx\Migration\AbstractMigration;

class NewExpertEventTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    addCustomColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Any other destructive changes will result in an error when trying to
     * rollback the migration.
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
       $table = $this->table('grand_avoid_expert_event');
       $table->addColumn('name_of_expert','text')
	    ->addColumn('expert_field', 'text')
            ->addColumn('zoomlink', 'text')
            ->addColumn('date_of_event', 'datetime')
	    ->addColumn('active', 'boolean')
            ->addColumn('date_created', 'datetime', array('default' => 'CURRENT_TIMESTAMP'))
            ->addColumn('currently_on', 'boolean')
            ->save();
    }

}
