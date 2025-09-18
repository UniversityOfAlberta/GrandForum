<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddCascadeToTasks extends AbstractMigration
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
        $table = $this->table('grand_pmm_task_assignees');
        $table->changeColumn('task_id', 'integer', [
                  'signed' => false,
              ])
              ->addForeignKey('task_id', 'grand_pmm_task', 'id', [
                  'delete' => 'CASCADE',
              ])
              ->addIndex(['task_id', 'assignee'], ['unique' => true])
              ->update();
    }
}
