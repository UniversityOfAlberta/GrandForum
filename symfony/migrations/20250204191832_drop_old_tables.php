<?php

use Phinx\Migration\AbstractMigration;

class DropOldTables extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $this->dropTable('grand_graddb');
        $this->dropTable('grand_graddb_eligible');
        $this->dropTable('grand_graddb_salary_scales');
        $this->dropTable('grand_project');
        $this->dropTable('grand_project_challenges');
        $this->dropTable('grand_project_champions');
        $this->dropTable('grand_project_descriptions');
        $this->dropTable('grand_project_evolution');
        $this->dropTable('grand_project_leaders');
        $this->dropTable('grand_project_members');
        $this->dropTable('grand_project_status');
        $this->dropTable('grand_role_projects');
        $this->dropTable('grand_product_projects');
        $this->dropTable('grand_themes');
        $this->dropTable('grand_theme_leaders');
        $this->dropTable('grand_disciplines');
        $this->dropTable('grand_disciplines_map');
    }
}
