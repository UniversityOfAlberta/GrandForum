<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class LinkTasksToProject extends AbstractMigration
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
        $table = $this->table('grand_pmm_task');
        $table->addColumn('project_id', 'integer', ['null' => false])
            ->addIndex('project_id')
            ->update();

        $this->execute("
            UPDATE grand_pmm_task t
            INNER JOIN grand_pmm_opportunity o ON t.opportunity = o.id
            INNER JOIN grand_pmm_contact c ON o.contact = c.id
            INNER JOIN grand_project p ON c.project_id = p.id
            SET t.project_id = p.id
        ");

        $table->changeColumn('project_id', 'integer', ["null" => false])
            ->addForeignKey('project_id','grand_project','id', [
                'delete'=> 'CASCADE'
                ]
            )
            ->update();
    }
}
