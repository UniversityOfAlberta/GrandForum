<?php

use Phinx\Migration\AbstractMigration;

class DiversitySurvey extends AbstractMigration
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
        $this->table('grand_diversity')
             ->addColumn('affiliation',         'string', array('after' => 'immigration', 'limit' => 50))
             ->addColumn('age',                 'string', array('after' => 'immigration', 'limit' => 50))
             ->addColumn('indigenous_apply',    'text', array('after' => 'immigration'))
             ->addColumn('true_self',           'string', array('after' => 'immigration', 'limit' => 50))
             ->addColumn('valued',              'string', array('after' => 'immigration', 'limit' => 50))
             ->addColumn('space',               'string', array('after' => 'immigration', 'limit' => 50))
             ->addColumn('respected',           'text', array('after' => 'immigration'))
             ->addColumn('least_respected',     'text', array('after' => 'immigration'))
             ->addColumn('principles',          'string', array('after' => 'immigration', 'limit' => 50))
             ->addColumn('principles_describe', 'text', array('after' => 'immigration'))
             ->addColumn('statement',           'string', array('after' => 'immigration', 'limit' => 50))
             ->addColumn('improve',             'text', array('after' => 'immigration'))
             ->addColumn('training',            'string', array('after' => 'immigration', 'limit' => 50))
             ->addColumn('prevents_training',   'text', array('after' => 'immigration'))
             ->addColumn('training_taken',      'text', array('after' => 'immigration'))
             ->addColumn('implemented',         'text', array('after' => 'immigration'))
             ->addColumn('stem',                'text', array('after' => 'immigration'))
             ->changeColumn('disability_visibility', 'text')
             ->removeColumn('birth')
             ->removeColumn('racialized')
             ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
