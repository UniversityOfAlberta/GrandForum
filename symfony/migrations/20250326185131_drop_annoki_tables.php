<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class DropAnnokiTables extends AbstractMigration
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
        $this->table('mw_an_pageratings')->drop()->update();
        $this->table('mw_an_pagestorate')->drop()->update();
        $this->table('mw_an_page_visits')->drop()->update();
        $this->table('mw_an_text_replacement')->drop()->update();
        $this->table('mw_an_upload_perm_temp')->drop()->update();
        $this->table('mw_an_vtracker_diff_results')->drop()->update();
        $this->table('grand_report_backup')->drop()->update();
    }
}
