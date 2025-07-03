<?php

use Phinx\Migration\AbstractMigration;

class CreateAvoidResourceTable extends AbstractMigration
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
     *    addCustomColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Any other destructive changes will result in an error when trying to
     * rollback the migration.
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
	public function change()
{
   $table = $this->table('grand_avoid_resources');
   $table->addColumn('ParentAgency','text')
        ->addColumn('PublicName_Program', 'text')
        ->addColumn('ResourceAgencyNum', 'text')
        ->addColumn('AgencyDescription', 'text')
        ->addColumn('HoursOfOperation', 'text')
        ->addColumn('Eligibility', 'text')
        ->addColumn('LanguagesOffered', 'text')
        ->addColumn('LanguagesOfferedList', 'text')
        ->addColumn('ApplicationProcess', 'text')
        ->addColumn('Coverage', 'text')
        ->addColumn('CoverageAreaText', 'text')
        ->addColumn('PhysicalAddress1', 'text')
        ->addColumn('PhysicalAddress2', 'text')
        ->addColumn('PhysicalCity', 'text')
        ->addColumn('PhysicalCounty', 'text')
        ->addColumn('PhysicalStateProvince', 'text')
        ->addColumn('PhysicalPostalCode', 'text')
        ->addColumn('MailingAttentionName', 'text')
        ->addColumn('MailingAddress1', 'text')
        ->addColumn('MailingAddress2', 'text')
        ->addColumn('MailingCity', 'text')
        ->addColumn('MailingStateProvince', 'text')
        ->addColumn('MailingPostalCode', 'text')
        ->addColumn('DisabilitiesAccess', 'text')
        ->addColumn('Phone1Name', 'text')
        ->addColumn('Phone1Number', 'text')
        ->addColumn('Phone1Description', 'text')
        ->addColumn('PhoneNumberBusinessLine', 'text')
        ->addColumn('PhoneTollFree', 'text')
        ->addColumn('PhoneFax', 'text')
        ->addColumn('EmailAddressMain', 'text')
        ->addColumn('WebsiteAddress', 'text')
        ->addColumn('Custom_Facebook', 'text')
        ->addColumn('Custom_Instagram', 'text')
        ->addColumn('Custom_LinkedIn', 'text')
        ->addColumn('Custom_Twitter', 'text')
        ->addColumn('Custom_YouTube', 'text')
        ->addColumn('Categories', 'text')
        ->addColumn('LastVerifiedOn', 'text')
        ->addColumn('Split', 'text')
        ->addColumn('PublicName', 'text')
        ->addColumn('Category', 'text')
        ->addColumn('SubCategory', 'text')
        ->addColumn('SubSubCategory', 'text')
        ->addColumn('TaxonomyTerms', 'text')
        ->save();
    }
}
