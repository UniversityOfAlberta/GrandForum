<?php

use Phinx\Migration\AbstractMigration;

class PersonGsms extends AbstractMigration
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
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $table = $this->table('grand_person_gsms', array('id' => false, 'primary_key' => 'user_id'));
        $table->addColumn('user_id','integer')
              ->addColumn('gpa60','float')
              ->addColumn('gpafull','float')
              ->addColumn('gpafull_credits','integer')
              ->addColumn('notes','text')
              ->addColumn('anatomy','text')
              ->addColumn('stats','text')
              ->addColumn('degree','text')
              ->addColumn('institution','text')
              ->addColumn('failures','text')
              ->create();
    }
}
