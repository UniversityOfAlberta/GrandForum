<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddNeedsReviewerValidation extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('grand_pmm_task');
        $table->addColumn('needs_reviewer_validation', 'boolean', ['default' => true, 'null' => false])
              ->update();
    }
}
