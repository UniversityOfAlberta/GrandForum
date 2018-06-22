<?php

use Phinx\Migration\AbstractMigration;

class UpdateRelations extends AbstractMigration
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
        $this->execute('UPDATE grand_relations SET type = "Supervises" WHERE type = "Works With"');
        $this->execute('UPDATE grand_relations SET type = "Supervisory-Committee member" WHERE type = "Supervisory Committee"');
        $this->execute('UPDATE grand_relations SET type = "Examining-Committee member" WHERE type = "Examiner"');
        $this->execute('UPDATE grand_relations SET type = "Examining-Committee chair" WHERE type = "Committee Chair"');
    }
}
