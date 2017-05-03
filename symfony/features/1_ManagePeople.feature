Feature: Manage People
    In order to edit user's roles/projects/relations/universities
    As a User I need to be able to request role/project changes
    As an Admin I need to be able to accept role/project changes

    Scenario: Staff Making HQP a candidate
        Given I am logged in as "Staff.User1" using password "Staff.Pass1"
        When I follow "Edit Roles"
        And I select "HQP User4" from "names"
        And I press "Next"
        And I follow "SubRolesTab"
        And I check "candidate"
        And I press "Submit Request"
        Then I should see "is now a candidate user"
        And "hqp.user4@behat-test.com" should not be subscribed to "test-hqps"
        
    Scenario: Staff Making Candidate-HQP a full HQP
        Given I am logged in as "Staff.User1" using password "Staff.Pass1"
        When I follow "Edit Roles"
        And I select "HQP User4" from "names"
        And I press "Next"
        And I follow "SubRolesTab"
        And I uncheck "candidate"
        And I press "Submit Request"
        Then I should see "is now a full user"
        And "hqp.user4@behat-test.com" should be subscribed to "test-hqps"
        
    Scenario: NI trying to Edit Sub-Roles (should not be able to)
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        Then I should not see "Edit Roles"

    Scenario: NI Editing HQP's projects
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "Manage People"
        And I fill in "Search:" with "HQP User1"
        And I click by css "#editProjects"
        And I select "Phase2Project2" from "name"
        And I press "Save"
        And I wait "1000"
        Then I should see "Projects saved"
        
    Scenario: NI Inactivating HQP
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        And "hqp.tobeinactivated@behat-test.com" should be subscribed to "test-hqps"
        When I follow "Manage People"
        And I fill in "Search:" with "HQP ToBeInactivated"
        And I click by css "#editRoles"
        And I wait "500"
        And I fill in "endDate" with ""
        And I click by css ".ui-state-active"
        And I press "Save"
        And I wait "1000"
        Then I should see "Roles saved"
        And "hqp.tobeinactivated@behat-test.com" should not be subscribed to "test-hqps"
        
    Scenario: Admin Adding PL (Make sure PL is also added to project, and subscribed to mailing list)
        Given I am logged in as "Admin.User1" using password "Admin.Pass1"
        When I follow "Edit Roles"
        And I select "NI User3" from "names"
        And I press "Next"
        And I follow "LeadershipTab"
        And I check "pl_Phase2Project5"
        And I press "Submit Request"
        Then I should see "is now a project leader of Phase2Project5"
        When I go to "index.php/Phase2Project5:Main"
        Then I should see "User3, NI"
        When I go to "index.php/CI:NI.User3?tab=projects"
        Then I should see "Phase2Project5"
        And "ni.user3@behat-test.com" should be subscribed to "test-leaders"
        
    Scenario: Admin Removing PL (Make sure that PL is also removed from the mailing list)
        Given I am logged in as "Admin.User1" using password "Admin.Pass1"
        When I follow "Edit Roles"
        And I select "NI User3" from "names"
        And I press "Next"
        And I follow "LeadershipTab"
        And I uncheck "pl_Phase2Project5"
        And I press "Submit Request"
        Then I should see "is no longer a project leader of Phase2Project5"
        And "ni.user3@behat-test.com" should not be subscribed to "test-leaders"
        
    Scenario: Adding Supervises relations
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "Manage People"
        And I press "Edit Existing Member"
        And I select "HQP User4" from "select"
        And I click "Add"
        And I wait "1000"
        And I fill in "Search:" with "HQP User4"
        And I click by css "#editRelations"
        And I press "Add Relationship"
        And I select "Supervises" from "type"
        And I press "Save"
        And I wait "1000"
        Then I should see "Relations saved"
        
    Scenario: Removing Supervises relations
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "Manage People"
        And I fill in "Search:" with "HQP User4"
        And I click by css "#editRelations"
        And I wait "500"
        And I fill in "endDate" with ""
        And I click by css ".ui-state-active"
        And I press "Save"
        And I wait "1000"
        Then I should see "Relations saved"
        
    Scenario: Adding Works With relations
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "Manage People"
        And I fill in "Search:" with "NI User2"
        And I click by css "#editRelations"
        And I press "Add Relationship"
        And I select "Works With" from "type"
        And I press "Save"
        And I wait "1000"
        Then I should see "Relations saved"
        
    Scenario: Adding University information
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "Manage People"
        And I fill in "Search:" with "HQP User4"
        And I click by css "#editUniversities"
        And I press "Add University"
        And I fill in "combo_university" with "Test University"
        And I fill in "combo_department" with "Test Department"
        And I select "Graduate Student - Master's" from "position"
        And I press "Save"
        And I wait "1000"
        Then I should see "Universities saved"
        
    Scenario: Updating University information
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "Manage People"
        And I fill in "Search:" with "HQP User4"
        And I click by css "#editUniversities"
        And I fill in "combo_department" with "Test Department Updated"
        And I press "Save"
        And I wait "1000"
        Then I should see "Universities saved"
