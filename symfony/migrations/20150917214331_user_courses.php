<?php

use Phinx\Migration\AbstractMigration;

class UserCourses extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     *
     * Uncomment this method if you would like to use it.
     *
    public function change()
    {
    }
    */
    
    /**
     * Migrate Up.
     */
    public function up()
    {
      $table = $this->table("grand_user_courses",array("id" => "id"));
      if(!$table->exists()){
            $table->addColumn('user_id', 'integer')
		  ->addColumn('course_id', 'integer')
                  ->addIndex(array('user_id'))
                  ->addIndex(array('course_id'))
                  ->create();
      }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
