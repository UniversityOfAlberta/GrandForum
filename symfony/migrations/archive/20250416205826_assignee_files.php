<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

final class AssigneeFiles extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        $table = $this->table('grand_pmm_task_assginees');
        $table->addColumn('filename', 'string', array('limit' => 128))
              ->addColumn('type', 'string', array('limit' => '64'))
              ->addColumn('data', 'text', array('limit' => MysqlAdapter::TEXT_MEDIUM))
              ->update();

    }
}
