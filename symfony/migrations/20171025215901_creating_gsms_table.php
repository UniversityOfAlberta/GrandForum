<?php

use Phinx\Migration\AbstractMigration;

class CreatingGsmsTable extends AbstractMigration
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
    public function up(){
        $table = $this->table('grand_gsms', array('id' => 'id'));
        $table->addColumn('user_id','integer')
              ->addColumn('gsms_id','string')
              ->addColumn('student_id','integer')
              ->addColumn('applicant_number','integer')
              ->addColumn('status','string')
              ->addColumn('gender','string', 'string', array('limit' => 12))
              ->addColumn('date_of_birth','timestamp', array('default' => '0000-00-00 00:00:00'))
              ->addColumn('country_of_birth','string')
              ->addColumn('country_of_citizenship','string')
              ->addColumn('program_name','string')
              ->addColumn('applicant_type','string')
              ->addColumn('education_history','string')
              ->addColumn('department','string')
              ->addColumn('epl_test','string')
              ->addColumn('epl_score','string')
              ->addColumn('epl_listen','string')
              ->addColumn('epl_write','string')
              ->addColumn('epl_read','string')
              ->addColumn('epl_speaking', 'string')
              ->addColumn('additional','text')
              ->addIndex(array('user_id'))
              ->addIndex(array('gsms_id'))
              ->addIndex(array('student_id'))
              ->addIndex(array('applicant_number'))
              ->create();
    }
}
