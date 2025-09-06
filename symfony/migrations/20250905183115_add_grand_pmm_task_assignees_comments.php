<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddGrandPmmTaskAssigneesComments extends AbstractMigration
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
        $table = $this->table('grand_pmm_task_assignees_comments');
        $table->addColumn('task_id', 'integer', ['null' => false])
              ->addColumn('assignee_id', 'integer', ['null' => false])
              ->addColumn('sender_id', 'integer', ['null' => false])
              ->addColumn('comment', 'text', ['null' => false])
              ->addColumn('status', 'string', ['limit' => 50, 'null' => false, 'default' => 'active'])
              ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])

              ->addIndex('task_id')
              ->addIndex('assignee_id')
              ->create();
    }
}
