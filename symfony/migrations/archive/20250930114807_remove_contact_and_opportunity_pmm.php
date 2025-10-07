<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RemoveContactAndOpportunityPmm extends AbstractMigration
{
    public function up(): void
    {
        $this->table('grand_pmm_contact')->drop()->save();
        $this->table('grand_pmm_opportunity')->drop()->save();
    }

    public function down(): void
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
    }
}
