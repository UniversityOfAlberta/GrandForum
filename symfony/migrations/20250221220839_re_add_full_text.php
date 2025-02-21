<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ReAddFullText extends AbstractMigration
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
        $this->execute("ALTER TABLE `grand_names_cache` ADD FULLTEXT (`name`)");
        $this->execute("ALTER TABLE `mw_searchindex` ADD FULLTEXT (`si_title`)");
        $this->execute("ALTER TABLE `mw_searchindex` ADD FULLTEXT (`si_text`)");
    }
}
