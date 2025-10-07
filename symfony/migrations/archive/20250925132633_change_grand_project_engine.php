<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ChangeGrandProjectEngine extends AbstractMigration
{
    public function up(): void
    {
        $this->execute('ALTER TABLE grand_project ENGINE=InnoDB;');
    }

    public function down(): void
    {
        $this->execute('ALTER TABLE grand_project ENGINE=MyISAM;');
    }
}
