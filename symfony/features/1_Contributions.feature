Feature: Contributions
    In order to be able to manage contributions on the forum
    As a User I need to be able to create and edit my own contributions
    As a Project/Theme Leader I need to be able to edit contributions on my project

    Scenario: Anon trying to add a contribution
        Given I am on "index.php/Special:Contributions#/new"
        Then I should see "You do not have permissions to view this page"
        
    Scenario: NI Trying to add a new contribution
        Given I am logged in as "NI.User4" using password "NI.Pass4"
        When I follow "Manage Contributions"
        And I follow "Add Contribution"
        And I fill in "name" with "New Contribution 1"
        And I select "NI User1" from "rightpeople"
        And I press "<<"
        And I click by css "#projects_Phase2Project3"
        And I press "Add Partner"
        And I fill in "partners_0_name" with "Google"
        And I select "Cash" from "partners_0_type"
        And I fill in "partners_0_amounts_1a" with "1000"
        And I click by css "body"
        And I press "Create Contribution"
        Then I should see "New Contribution 1"
        And I should see "Edit Contribution"
        And I should see "Salaries: Bachelors - Canadian and Permanent Residents"
        And I should see "1,000"
        And I should see "NI User1"
        And I should see "Google"
        And I should see "Phase2Project3"
        
    Scenario: PL Trying to view the Contributions of an NI project member
        Given I am logged in as "PL.User2" using password "PL.Pass2"
        When I follow "Manage Contributions"
        And I follow "New Contribution 1"
        Then I should see "Phase2Project3"
        
    Scenario: NI Trying to create a contribution with no partners (should be allowed)
        Given I am logged in as "NI.User4" using password "NI.Pass4"
        When I follow "Manage Contributions"
        And I follow "Add Contribution"
        And I fill in "name" with "New Contribution 2"
        And I select "NI User1" from "rightpeople"
        And I press "<<"
        And I click by css "body"
        And I press "Create Contribution"
        Then I should see "New Contribution 2"
        And I should see "Edit Contribution"
        
    Scenario: NI Trying to edit their own contribution
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "Manage Contributions"
        Then I should see "New Contribution 1"
        
    Scenario: PL Trying to edit a contribiton from their project
        Given I am logged in as "PL.User2" using password "PL.Pass2"
        When I follow "Manage Contributions"
        Then I should see "New Contribution 1"
        
    Scenario: TL Trying to edit a contribiton from their project
        Given I am logged in as "TL.User1" using password "TL.Pass1"
        When I follow "Manage Contributions"
        Then I should see "New Contribution 1"
        
    Scenario: NI Trying to edit someone else's contribution
        Given I am logged in as "NI.User2" using password "NI.Pass2"
        When I follow "Manage Contributions"
        Then I should not see "New Contribution 1"
        
    Scenario: HQP Trying to add a new contribution
        Given I am logged in as "HQP.User2" using password "HQP.Pass2"
        When I follow "Manage Contributions"
        And I follow "Add Contribution"
        And I fill in "name" with "New Contribution 2"
        And I select "NI User2" from "rightpeople"
        And I press "<<"
        And I click by css "#projects_Phase2Project3"
        And I press "Add Partner"
        And I fill in "partners_0_name" with "Google"
        And I select "Cash" from "partners_0_type"
        And I fill in "partners_0_amounts_1b" with "1000"
        And I click by css "body"
        And I press "Create Contribution"
        Then I should see "New Contribution 2"
        And I should see "Edit Contribution"
        And I should see "Salaries: Bachelors - Foreign"
        And I should see "1,000"
        And I should see "NI User2"
        And I should see "Google"
        And I should see "Phase2Project3"
        
    Scenario: NI Trying to edit a contribution that they belong to
        Given I am logged in as "NI.User2" using password "NI.Pass2"
        When I follow "Manage Contributions"
        Then I should see "New Contribution 2"
        
    Scenario: NI Trying to edit their HQP's contribution
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "Manage Contributions"
        Then I should see "New Contribution 2"
        
    Scenario: Staff Should be able to see all contributions
        Given I am logged in as "Staff.User1" using password "Staff.Pass1"
        When I follow "Manage Contributions"
        Then I should see "New Contribution 1"
        And I should see "New Contribution 2"
        
    Scenario: NI Trying to edit a contribution that they do not belong to
        Given I am logged in as "NI.User3" using password "NI.Pass3"
        When I follow "Manage Contributions"
        Then I should not see "New Contribution 2"
        
    Scenario: Anon trying to view contributions
        Given I am on "index.php/Special:Contributions"
        Then I should see "You do not have permissions to view this page"
        
    Scenario: Anon trying to view a specific contribution
        Given I am on "index.php/Special:Contributions#/1"
        Then I should see "You do not have permissions to view this page"
        
    Scenario: NI Trying to delete a contribution
        Given I am logged in as "NI.User4" using password "NI.Pass4"
        When I follow "Manage Contributions"
        And I follow "New Contribution 1"
        And I accept confirmation dialogs
        And I press "Delete Contribution"
        Then I should see "The Contribution New Contribution 1 was deleted sucessfully"
        And I reload the page
        And I wait "500"
        Then I should see "This Contribution does not exist"
