<?php

use Phinx\Migration\AbstractMigration;

class DropTables extends AbstractMigration
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
        $this->dropTable('grand_allocations');
        $this->dropTable('grand_conference_attendance');
        $this->dropTable('grand_contributions');
        $this->dropTable('grand_contributions_partners');
        $this->dropTable('grand_contributions_projects');
        $this->dropTable('grand_ethics');
        $this->dropTable('grand_feature_votes');
        $this->dropTable('grand_hqp_months');
        $this->dropTable('grand_list_request');
        $this->dropTable('grand_loi');
        $this->dropTable('grand_materials');
        $this->dropTable('grand_materials_keywords');
        $this->dropTable('grand_materials_people');
        $this->dropTable('grand_materials_projects');
        $this->dropTable('grand_partners');
        $this->dropTable('grand_poll');
        $this->dropTable('grand_poll_collection');
        $this->dropTable('grand_poll_groups');
        $this->dropTable('grand_poll_options');
        $this->dropTable('grand_poll_votes');
        $this->dropTable('grand_product_citations');
        $this->dropTable('grand_recorded_images');
        $this->dropTable('grand_recordings');
        $this->dropTable('grand_reporting_year_ticket');
        $this->dropTable('grand_researcher_cv');
        $this->dropTable('grand_review_results');
        $this->dropTable('grand_travel_forms');
        $this->dropTable('wikidev_messages');
        $this->dropTable('wikidev_projects');
        $this->dropTable('wikidev_projects_rules');
        $this->dropTable('wikidev_unsubs');
        $this->dropTable('sociql_actor');
        $this->dropTable('sociql_map_actors');
        $this->dropTable('sociql_ontology_ent');
        $this->dropTable('sociql_ontology_prop');
        $this->dropTable('sociql_ontology_rel');
        $this->dropTable('sociql_property');
        $this->dropTable('sociql_relation');
        $this->dropTable('sociql_requiredprop');
        $this->dropTable('sociql_requiredset');
        $this->dropTable('sociql_same');
        $this->dropTable('sociql_site');
        $this->dropTable('survey_events');
        $this->dropTable('survey_results');
        $this->dropTable('mw_virtu_auth_ids');
        $this->dropTable('mw_virtu_experience');
        $this->dropTable('mw_virtu_pub_auths');
    }
}
