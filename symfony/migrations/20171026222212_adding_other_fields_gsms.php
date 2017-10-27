<?php

use Phinx\Migration\AbstractMigration;

class AddingOtherFieldsGsms extends AbstractMigration
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
     *
    public function change()
    {

    }*/
    public function change()
    {
       $table = $this->table('grand_gsms');
       $table->addColumn('cs_app','string')
             ->addColumn('academic_year', 'string')
             ->addColumn('term', 'string')
             ->addColumn('subplan_name', 'string')
             ->addColumn('program', 'string')
             ->addColumn('degree_code', 'string')
             ->addColumn('admission_program_name', 'string')
             ->addColumn('submitted_date','timestamp', array('default' => 'CURRENT_TIMESTAMP'))
             ->addColumn('folder', 'string')
             ->addColumn('department_gpa', 'float')
             ->addColumn('department_gpa_scale', 'float')
             ->addColumn('department_normalized_gpa', 'float')
             ->addColumn('fgsr_gpa', 'float')
             ->addColumn('fgsr_gpa_scale', 'float')
             ->addColumn('fgsr_normalized_gpa', 'float')
             ->addColumn('funding_note', 'string')
             ->addColumn('department_decision', 'string')
             ->addColumn('fgsr_decision', 'string')
             ->addColumn('decision_response', 'string')
             ->addColumn('general_notes', 'string')
             ->save();
    }
}
