<?php


use Phinx\Migration\AbstractMigration;

class CollabYear extends AbstractMigration
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
        $collabs = $this->table("grand_collaborations");

        $collabs->addColumn('year', 'integer', array('after'=>'organization_name'))
            ->addColumn('knowledge_user', 'boolean')
            ->addIndex('year')
            ->addIndex('knowledge_user')
            ->update();
    }
}
