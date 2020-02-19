<?php

use Phinx\Migration\AbstractMigration;

class SalaryScales extends AbstractMigration
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
        $table = $this->table('grand_salary_scales');
        $table->addColumn('min_salary_atsec3', 'integer', array('after' => 'min_salary_fso4'))
              ->addColumn('max_salary_atsec3', 'integer', array('after' => 'max_salary_fso4'))
              ->addColumn('increment_atsec3', 'integer', array('after' => 'increment_fso4'))
              ->addColumn('min_salary_atsec2', 'integer', array('after' => 'min_salary_fso4'))
              ->addColumn('max_salary_atsec2', 'integer', array('after' => 'max_salary_fso4'))
              ->addColumn('increment_atsec2', 'integer', array('after' => 'increment_fso4'))
              ->addColumn('min_salary_atsec1', 'integer', array('after' => 'min_salary_fso4'))
              ->addColumn('max_salary_atsec1', 'integer', array('after' => 'max_salary_fso4'))
              ->addColumn('increment_atsec1', 'integer', array('after' => 'increment_fso4'))
              ->save();
    }
}
