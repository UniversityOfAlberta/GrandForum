Feature: Manage People
    In order to edit user's roles/projects/relations/universities
    As a User I need to be able to request role/project changes
    As an Admin I need to be able to accept role/project changes
        
    Scenario: Checking Admin allowedRoles
        Given I am logged in as "Admin.User1" using password "Admin.Pass1"
        When I log "allowedRoles" I should see "PL"
        When I log "allowedRoles" I should see "Staff"
        When I log "allowedRoles" I should see "Manager"
        When I log "allowedRoles" I should see "Admin"
        
    Scenario: Checking Manager allowedRoles
        Given I am logged in as "Manager.User1" using password "Manager.Pass1"
        When I log "allowedRoles" I should see "PL"
        When I log "allowedRoles" I should see "Staff"
        When I log "allowedRoles" I should see "Manager"
        When I log "allowedRoles" I should not see "Admin"
        
    Scenario: Checking Staff allowedRoles
        Given I am logged in as "Staff.User1" using password "Staff.Pass1"
        When I log "allowedRoles" I should see "PL"
        When I log "allowedRoles" I should see "Staff"
        When I log "allowedRoles" I should not see "Manager"
        When I log "allowedRoles" I should not see "Admin"
        
    Scenario: Checking PL allowedRoles and allowedProjects
        Given I am logged in as "PL.User1" using password "PL.Pass1"
        When I log "allowedRoles" I should see "CI"
        When I log "allowedRoles" I should see "AR"
        When I log "allowedRoles" I should see "PS"
        When I log "allowedRoles" I should not see "PL"
        When I log "allowedProjects" I should see "Phase2Project1"
        When I log "allowedProjects" I should not see "Phase2Project2"
        
    Scenario: Checking NI allowedRoles
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I log "allowedRoles" I should see "HQP"
        When I log "allowedRoles" I should see "External"
        When I log "allowedRoles" I should see "AR"
        When I log "allowedRoles" I should see "CI"
        When I log "allowedRoles" I should not see "PL"
        
    Scenario: Checking HQP allowedRoles
        Given I am logged in as "HQP.User1" using password "HQP.Pass1"
        When I log "allowedRoles" I should see "HQP"
        When I log "allowedRoles" I should not see "External"
        When I log "allowedRoles" I should not see "PS"
        
    Scenario: Checking HQP-Candidate allowedRoles
        Given I am logged in as "HQP-Candidate.User1" using password "HQP-Candidate.Pass1"
        When I log "allowedRoles" I should not see "HQP"
        
    Scenario: Checking Inactive allowedRoles
        Given I am logged in as "Inactive.User1" using password "Inactive.Pass1"
        When I log "allowedRoles" I should not see "HQP"

    Scenario: PL adding NI to project
        Given I am logged in as "PL.User1" using password "PL.Pass1"
        When I follow "Manage People"
        And I press "Edit Existing Member"
        And I select from Chosen "select" with "NI User5"
        And I click "Add"
        And I wait until I see "NI User5" up to "1000"
        And I fill in "Search:" with "NI User5"
        And I click by css "#editRoles"
        And I select "Phase2Project1" from "selectedProject"
        And I click by css "#addProject"
        And I press "Save"
        And I wait until I see "Roles saved" up to "1000"
        When I go to "index.php/Phase2Project1:Main"
        Then I should see "User5, NI"

    Scenario: Staff Making HQP a candidate
        Given I am logged in as "Staff.User1" using password "Staff.Pass1"
        When I follow "Manage People"
        And I fill in "Search:" with "HQP User4"
        And I click by css "input[name=candidate]"
        And I wait "100"
        And I reload the page
        And I click "People"
        And I wait "100"
        And I follow "Candidates"
        Then I should see "User4, HQP"
        And "hqp.user4@behat-test.com" should not be subscribed to "test-hqps"
        
    Scenario: Staff Making Candidate-HQP a full HQP
        Given I am logged in as "Staff.User1" using password "Staff.Pass1"
        When I follow "Manage People"
        And I fill in "Search:" with "HQP User4"
        And I click by css "input[name=candidate]"
        And I wait "100"
        And I reload the page
        And I click "People"
        And I wait "100"
        And I follow "HQP"
        Then I should see "User4, HQP"
        And "hqp.user4@behat-test.com" should be subscribed to "test-hqps"
        
    Scenario: NI trying to Edit Sub-Roles (should not be able to)
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "Manage People"
        Then I should not see "Sub-Roles"

    Scenario: NI Editing HQP's projects
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "Manage People"
        And I fill in "Search:" with "HQP User1"
        And I click by css "#editRoles"
        And I select "Phase2Project2" from "selectedProject"
        And I click by css "#addProject"
        And I press "Save"
        And I wait until I see "Roles saved" up to "1000"
        Then I should see "Roles saved"
        When I go to "index.php/Phase2Project2:Main"
        Then I should see "User1, HQP"
        
    Scenario: NI Inactivating HQP
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        And "hqp.tobeinactivated@behat-test.com" should be subscribed to "test-hqps"
        When I follow "Manage People"
        And I fill in "Search:" with "HQP ToBeInactivated"
        And I click by css "#editRoles"
        And I wait until I see "Highly Qualified Person" up to "1000"
        And I fill in "endDate" with ""
        And I click by css ".ui-state-active"
        And I press "Save"
        And I wait until I see "Roles saved" up to "1000"
        Then I should see "Roles saved"
        And "hqp.tobeinactivated@behat-test.com" should not be subscribed to "test-hqps"
    
    Scenario: NI Re-Activating HQP
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        And "hqp.tobeinactivated@behat-test.com" should not be subscribed to "test-hqps"
        When I follow "Manage People"
        And I fill in "Search:" with "HQP ToBeInactivated"
        And I click by css "#editRoles"
        And I wait until I see "Highly Qualified Person" up to "1000"
        And I click by css "#infinity"
        And I press "Save"
        And I wait until I see "Roles saved" up to "1000"
        Then I should see "Roles saved"
        And "hqp.tobeinactivated@behat-test.com" should be subscribed to "test-hqps"
    
    Scenario: NI Inactivating HQP Again
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        And "hqp.tobeinactivated@behat-test.com" should be subscribed to "test-hqps"
        When I follow "Manage People"
        And I fill in "Search:" with "HQP ToBeInactivated"
        And I click by css "#editRoles"
        And I wait until I see "Highly Qualified Person" up to "1000"
        And I click by css "input[name=endDate]"
        And I click by css ".ui-datepicker-next"
        And I click by css ".ui-datepicker-calendar .ui-state-default"
        And I press "Save"
        And I wait until I see "Roles saved" up to "1000"
        Then I should see "Roles saved"
        And "hqp.tobeinactivated@behat-test.com" should not be subscribed to "test-hqps"
        
    Scenario: NI should not see Admin editing options
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "Manage People"
        Then I should not see "Sub-Roles"
        And I should not see "Theme Leadership"
        
    Scenario Outline: Admin users should see Admin editing options
        Given I am logged in as <user> using password <pass>
        When I follow "Manage People"
        Then I should see "Sub-Roles"
        And I should see "Theme Leadership"
        
        Examples:
        | user            | pass            |
        | "Admin.User1"   | "Admin.Pass1"   |
        | "Manager.User1" | "Manager.Pass1" |
        | "Staff.User1"   | "Staff.Pass1"   |
        
    Scenario: Admin Adding PL (Make sure PL is also added to project, and subscribed to mailing list)
        Given I am logged in as "Admin.User1" using password "Admin.Pass1"
        When I follow "Manage People"
        And I fill in "Search:" with "PL User3"
        And I click by css "#editRoles"
        And I press "Add Role"
        And I select "PL" from "name"
        Then I should see "There should be a project associated with this role"
        And I select "Phase2Project5" from "selectedProject"
        And I click by css "#addProject"
        Then I should not see "There should be a project associated with this role"
        And I press "Save"
        And I wait until I see "Roles saved" up to "1000"
        When I go to "index.php/Phase2Project5:Main"
        Then I should see "User3, PL"
        When I go to "index.php/PL:PL.User3?tab=projects"
        Then I should see "Phase2Project5"
        And "pl.user3@behat-test.com" should be subscribed to "test-leaders"
        
    Scenario: Admin Removing PL (Make sure that PL is also removed from the mailing list)
        Given I am logged in as "Admin.User1" using password "Admin.Pass1"
        When I follow "Manage People"
        And I fill in "Search:" with "PL User3"
        And I click by css "#editRoles"
        And I click by css "input[name=deleted]"
        And I press "Save"
        And I wait until I see "Roles saved" up to "1000"
        And "ni.user3@behat-test.com" should not be subscribed to "test-leaders"
        
    Scenario: Admin Adding TL
        Given I am logged in as "Admin.User1" using password "Admin.Pass1"
        When I follow "Manage People"
        And I fill in "Search:" with "NI User3"
        And I click by css "#editThemeLeadership"
        And I press "Add Theme"
        And I select "Theme1" from "name"
        And I press "Save"
        And I wait until I see "Themes saved" up to "1000"
        And I go to "index.php/NETWORK:Themes_II"
        Then I should see "NI User3"
        
    Scenario: Admin Adding TC
        Given I am logged in as "Admin.User1" using password "Admin.Pass1"
        When I follow "Manage People"
        And I fill in "Search:" with "NI User4"
        And I click by css "#editThemeLeadership"
        And I press "Add Theme"
        And I select "Theme1" from "name"
        And I click by css "input[name=coordinator]"
        And I press "Save"
        And I wait until I see "Themes saved" up to "1000"
        And I go to "index.php/NETWORK:Themes_II"
        Then I should see "NI User4"
        
    Scenario: Adding Supervises relations
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "Manage People"
        And I press "Edit Existing Member"
        And I select from Chosen "select" with "HQP User4"
        And I click "Add"
        And I wait until I see "HQP User4" up to "1000"
        And I fill in "Search:" with "HQP User4"
        And I click by css "#editRelations"
        And I press "Add Relationship"
        And I select "Supervises" from "type"
        And I press "Save"
        And I wait until I see "Relations saved" up to "1000"
        Then I should see "Relations saved"
        
    Scenario: Removing Supervises relations
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "Manage People"
        And I fill in "Search:" with "HQP User4"
        And I click by css "#editRelations"
        And I wait until I see "Edit Relations" up to "1000"
        And I fill in "endDate" with ""
        And I click by css ".ui-state-active"
        And I press "Save"
        And I wait until I see "Relations saved" up to "1000"
        Then I should see "Relations saved"
        
    Scenario: Adding Works With relations
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "Manage People"
        And I fill in "Search:" with "NI User2"
        And I click by css "#editRelations"
        And I press "Add Relationship"
        And I select "Works With" from "type"
        And I press "Save"
        And I wait until I see "Relations saved" up to "1000"
        Then I should see "Relations saved"
        
    Scenario: Adding University information
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "Manage People"
        And I fill in "Search:" with "HQP User4"
        And I click by css "#editUniversities"
        And I press "Add Institution"
        And I fill in "combo_university" with "Test University"
        And I fill in "combo_department" with "Test Department"
        And I select "Graduate Student - Master's" from "position"
        And I press "Save"
        And I wait until I see "Institutions saved" up to "1000"
        Then I should see "Institutions saved"
        
    Scenario: Updating University information
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "Manage People"
        And I fill in "Search:" with "HQP User4"
        And I click by css "#editUniversities"
        And I fill in "combo_department" with "Test Department Updated"
        And I press "Save"
        And I wait until I see "Institutions saved" up to "1000"
        Then I should see "Institutions saved"
