<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class TaskAssginees extends AbstractMigration
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
        $table = $this->table('grand_pmm_task_assginees', array('id' => 'id'));
        $table->addColumn('task_id', 'integer')
              ->addColumn('assignee', 'integer')
              ->addColumn('status', 'string', array('limit' => 64))
              ->addIndex('task_id')
              ->addIndex('assignee')
              ->create();

        $table = $this->table('grand_pmm_task');
        $table->removeColumn('assignee')
              ->removeColumn('status')
                ->update();




    }
}
