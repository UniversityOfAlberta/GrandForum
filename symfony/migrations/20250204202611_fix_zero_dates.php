<?php

use Phinx\Migration\AbstractMigration;

class FixZeroDates extends AbstractMigration
{

    private function fixTableCol($table, $col, $default='NULL'){
        $type = "DATETIME";
        if($default == "CURRENT_TIMESTAMP"){
            $type = "TIMESTAMP";
        }
        $this->execute("ALTER TABLE `$table` 
                        CHANGE `$col` `$col` $type NULL DEFAULT $default");
    
        $this->execute("UPDATE `$table`
                        SET `$col` = NULL
                        WHERE `$col` = '0000-00-00 00:00:00'
                           OR `$col` = '0000-00-00 00:00:00'
                           OR `$col` = ''");
    }

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
        $this->fixTableCol('grand_grants', 'start_date');
        $this->fixTableCol('grand_grants', 'end_date');
        $this->execute('ALTER TABLE grand_gs_citations DROP PRIMARY KEY');
        $this->execute('ALTER TABLE `grand_gs_citations` ADD `id` INT NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`)');
        $this->fixTableCol('grand_gs_citations', 'year');
        $this->fixTableCol('grand_keywords', 'start_date');
        $this->fixTableCol('grand_keywords', 'end_date');
        $this->fixTableCol('grand_movedOn', 'effective_date');
        $this->fixTableCol('grand_movedOn', 'date_created', 'CURRENT_TIMESTAMP');
        $this->fixTableCol('grand_movedOn', 'date_changed');
        $this->fixTableCol('grand_new_grants', 'installment');
        $this->fixTableCol('grand_notifications', 'time');
        $this->fixTableCol('grand_pdf_index', 'last_download');
        $this->fixTableCol('grand_pdf_index', 'created');
        $this->fixTableCol('grand_personal_fec_info', 'date_of_phd');
        $this->fixTableCol('grand_personal_fec_info', 'date_of_appointment');
        $this->fixTableCol('grand_personal_fec_info', 'date_assistant');
        $this->fixTableCol('grand_personal_fec_info', 'date_associate');
        $this->fixTableCol('grand_personal_fec_info', 'date_professor');
        $this->fixTableCol('grand_personal_fec_info', 'date_probation1');
        $this->fixTableCol('grand_personal_fec_info', 'date_probation2');
        $this->fixTableCol('grand_personal_fec_info', 'date_tenure');
        $this->fixTableCol('grand_personal_fec_info', 'date_retirement');
        $this->fixTableCol('grand_personal_fec_info', 'date_last_degree');
        $this->fixTableCol('grand_personal_fec_info', 'date_fso2');
        $this->fixTableCol('grand_personal_fec_info', 'date_fso3');
        $this->fixTableCol('grand_personal_fec_info', 'date_fso4');
        $this->fixTableCol('grand_personal_fec_info', 'date_atsec1');
        $this->fixTableCol('grand_personal_fec_info', 'date_atsec2');
        $this->fixTableCol('grand_personal_fec_info', 'date_atsec3');
        $this->fixTableCol('grand_personal_fec_info', 'date_ats_anniversary');
        $this->fixTableCol('grand_products', 'date');
        $this->fixTableCol('grand_products', 'date_changed', 'CURRENT_TIMESTAMP');
        $this->fixTableCol('grand_products', 'acceptance_date');
        $this->fixTableCol('grand_products', 'date_created');
        $this->fixTableCol('grand_product_histories', 'created', 'CURRENT_TIMESTAMP');
        $this->fixTableCol('grand_product_histories', 'updated');
        $this->fixTableCol('grand_relations', 'start_date');
        $this->fixTableCol('grand_relations', 'end_date');
        $this->fixTableCol('grand_report_blobs', 'changed');
        $this->fixTableCol('grand_roles', 'start_date');
        $this->fixTableCol('grand_roles', 'end_date');
        $this->fixTableCol('grand_user_addresses', 'start_date');
        $this->fixTableCol('grand_user_addresses', 'end_date');
        $this->fixTableCol('grand_user_gsmetrics', 'start_date');
        $this->fixTableCol('grand_user_gsmetrics', 'change_date', 'CURRENT_TIMESTAMP');
        $this->fixTableCol('grand_user_metrics', 'acm_start_date');
        $this->fixTableCol('grand_user_metrics', 'acm_end_date');
        $this->fixTableCol('grand_user_metrics', 'change_date', 'CURRENT_TIMESTAMP');
        $this->fixTableCol('grand_user_telephone', 'start_date');
        $this->fixTableCol('grand_user_telephone', 'end_date');
        $this->fixTableCol('grand_user_university', 'start_date');
        $this->fixTableCol('grand_user_university', 'end_date');
        $this->fixTableCol('mw_user', 'profile_start_date');
        $this->fixTableCol('mw_user', 'profile_end_date');
        $this->fixTableCol('phinxlog', 'end_time');
    }
}
