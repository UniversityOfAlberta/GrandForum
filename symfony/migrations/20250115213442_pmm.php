<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

final class Pmm extends AbstractMigration
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
        $table = $this->table('grand_pmm_contact', array('id' => 'id'));
        $table->addColumn('title', 'string', array('limit' => 256))
              ->addColumn('owner', 'integer')
              ->addColumn('project_id', 'integer')
              ->addColumn('details', 'text')
              ->addIndex('title')
              ->addIndex('owner')
              ->addIndex('project_id')
              ->create();
              
        $table = $this->table('grand_pmm_opportunity', array('id' => 'id'));
        $table->addColumn('contact', 'integer')
              ->addColumn('owner', 'integer')
              ->addColumn('description', 'text')
              ->addIndex('contact')
              ->addIndex('owner')
              ->create();
              
        $table = $this->table('grand_pmm_task', array('id' => 'id'));
        $table->addColumn('opportunity', 'integer')
              ->addColumn('assignee', 'integer')
              ->addColumn('task', 'text')
              ->addColumn('due_date', 'datetime')
              ->addColumn('comments', 'text')
              ->addColumn('status', 'string', array('limit' => 64))
              ->addIndex('opportunity')
              ->addIndex('assignee')
              ->create();

        $table = $this->table('grand_pmm_files', array('id' => 'id'));
        $table->addColumn('opportunity_id', 'integer')
            ->addColumn('filename', 'string', array('limit' => 128))
            ->addColumn('type', 'string', array('limit' => 64))
            ->addColumn('data', 'text', array('limit' => MysqlAdapter::TEXT_MEDIUM))
            ->addIndex('opportunity_id')
            ->create();
    }
}
