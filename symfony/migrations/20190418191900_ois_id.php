<?php


use Phinx\Migration\AbstractMigration;

class OisId extends AbstractMigration
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
        $table = $this->table('grand_gsms', array('id' => 'id'));
        $table->addColumn('ois_id', 'string', array('after' => 'gsms_id', 'limit' => '64'))
              ->addIndex('ois_id')
              ->update();
    }
}
