<?php

use Phinx\Migration\AbstractMigration;

class SopMigrations extends AbstractMigration
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
     
    public function change()
    {

    }*/
    public function up(){
        $table = $this->table("grand_sop", array("id" => "id"));
        if(!$table->exists()){
            $table->addColumn('user_id', 'integer')
                  ->addColumn('content', 'text')
                  ->addColumn('date_created', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
		  ->addColumn('sentiment_val','string', array('limit' => 100))
		  ->addColumn('sentiment_type', 'string', array('limit' => 100))
		  ->addColumn('readability_score', 'float')
		  ->addColumn('reading_ease','float')
		  ->addColumn('ari_grade','float')
		  ->addColumn('ari_age','float')
		  ->addColumn('colemanliau_grade','float')
		   ->addColumn('colemanliau_age','float')
		 ->addColumn('dalechall_index','float')
		 ->addColumn('dalechall_grade','float')
                 ->addColumn('dalechall_age','float')
		 ->addColumn('fleschkincaid_grade','float')
		  ->addColumn('fleschkincaid_age','float')
		 ->addColumn('smog_grade','float')
		 ->addColumn('smog_age','float')
		 ->addColumn('errors','integer')
                 ->addColumn('sentlen_ave','integer')
                 ->addColumn('wordletter_ave','integer')
                 ->addColumn('min_age','integer')
                 ->addColumn('word_count','integer')
                  ->addIndex(array('user_id'))
                  ->create();
        }
        $table = $this->table("grand_sop_annotation", array("id" => "id"));
        if(!$table->exists()){
            $table->addColumn('user_id', 'integer')
                  ->addColumn('sop_id', 'integer')
                  ->addColumn('content', 'text')
                  ->addColumn('annotator_id', 'text')
                  ->addColumn('date_updated', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
                  ->addColumn('schema_version', 'text')
                  ->addColumn('uri', 'text')
                  ->addColumn('text', 'text')
                  ->addColumn('quote', 'text')
                  ->addColumn('ranges', 'text')
                  ->addColumn('consumer', 'text')
                  ->addColumn('tags', 'text')
                  ->addColumn('permissions', 'text')
                   ->addIndex(array('user_id'))
                  ->create();
        }
    }
    public function down(){

    }
}
