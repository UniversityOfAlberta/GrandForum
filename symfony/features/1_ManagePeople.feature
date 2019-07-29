Feature: Manage People
    In order to edit user's roles/projects/relations/universities
    As a User I need to be able to request role/project changes
    As an Admin I need to be able to accept role/project changes

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
        
    Scenario: NI should not see Admin editing options
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "Manage People"
        Then I should not see "Sub-Roles"
        And I should not see "Project Leadership"
        And I should not see "Theme Leadership"
        
    Scenario Outline: Admin users should see Admin editing options
        Given I am logged in as <user> using password <pass>
        When I follow "Manage People"
        Then I should see "Sub-Roles"
        And I should see "Project Leadership"
        And I should see "Theme Leadership"
        
        Examples:
        | user            | pass            |
        | "Admin.User1"   | "Admin.Pass1"   |
        | "Manager.User1" | "Manager.Pass1" |
        | "Staff.User1"   | "Staff.Pass1"   |
        
    Scenario: Admin Adding PL (Make sure PL is also added to project, and subscribed to mailing list)
        Given I am logged in as "Admin.User1" using password "Admin.Pass1"
        When I follow "Manage People"
        And I fill in "Search:" with "NI User3"
        And I click by css "#editProjectLeadership"
        And I press "Add Project"
        And I select "Phase2Project5" from "name"
        And I press "Save"
        And I wait "1000"
        When I go to "index.php/Phase2Project5:Main"
        Then I should see "User3, NI"
        When I go to "index.php/CI:NI.User3?tab=projects"
        Then I should see "Phase2Project5"
        And "ni.user3@behat-test.com" should be subscribed to "test-leaders"
        
    Scenario: Admin Removing PL (Make sure that PL is also removed from the mailing list)
        Given I am logged in as "Admin.User1" using password "Admin.Pass1"
        When I follow "Manage People"
        And I fill in "Search:" with "NI User3"
        And I click by css "#editProjectLeadership"
        And I click by css "input[name=deleted]"
        And I press "Save"
        And I wait "1000"
        And "ni.user3@behat-test.com" should not be subscribed to "test-leaders"
        
    Scenario: Admin Adding TL
        Given I am logged in as "Admin.User1" using password "Admin.Pass1"
        When I follow "Manage People"
        And I fill in "Search:" with "NI User3"
        And I click by css "#editThemeLeadership"
        And I press "Add Theme"
        And I select "Theme1" from "name"
        And I press "Save"
        And I wait "1000"
        And I follow "Themes"
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
        And I wait "1000"
        And I follow "Themes"
        Then I should see "NI User4"
        
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
        And I press "Add Institution"
        And I fill in "combo_university" with "Test University"
        And I fill in "combo_department" with "Test Department"
        And I select "Graduate Student - Master's" from "position"
        And I press "Save"
        And I wait "1000"
        Then I should see "Institutions saved"
        
    Scenario: Updating University information
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "Manage People"
        And I fill in "Search:" with "HQP User4"
        And I click by css "#editUniversities"
        And I fill in "combo_department" with "Test Department Updated"
        And I press "Save"
        And I wait "1000"
        Then I should see "Institutions saved"