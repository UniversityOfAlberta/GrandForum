Feature: Contributions
    In order to be able to add/edit contributions on the forum
    As a User I need to be able to create and edit my own contributions
    As a Project/Theme Leader I need to be able to edit contributions on my project

    Scenario: Anon trying to add a contribution
        Given I am on "index.php/Special:AddContributionPage"
        Then I should see "Permission error"
        
    Scenario: NI Trying to add a new contribution
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "Add/Edit Contribution"
        And I fill in "title" with "New Contribution 1"
        And I press "Create"
        And I select "NI User1" from "rightresearchers"
        And I press "<<"
        And I follow "Add Partner"
        And I fill in "partners[0]" with "Google"
        And I select "Cash" from "type[0]"
        And I fill in "cash[0]" with "1000"
        And I click by css "#projects_Phase2Project3"
        And I press "Create Contribution"
        Then I should see "Contribution: New Contribution 1"
        And I should see "Edit Contribution"
        
    Scenario: NI Trying to edit their own contribution
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "Add/Edit Contribution"
        And I fill in "title" with "New Contribution 1"
        And I wait "1000"
        Then I should see "New Contribution 1"
        
    Scenario: PL Trying to edit a contribiton from their project
        Given I am logged in as "PL.User2" using password "PL.Pass2"
        When I follow "Add/Edit Contribution"
        And I fill in "title" with "New Contribution 1"
        And I wait "1000"
        Then I should see "New Contribution 1"
        
    Scenario: TL Trying to edit a contribiton from their project
        Given I am logged in as "TL.User1" using password "TL.Pass1"
        When I follow "Add/Edit Contribution"
        And I fill in "title" with "New Contribution 1"
        And I wait "1000"
        Then I should see "New Contribution 1"
        
    Scenario: NI Trying to edit someone else's contribution
        Given I am logged in as "NI.User2" using password "NI.Pass2"
        When I follow "Add/Edit Contribution"
        And I fill in "title" with "New Contribution 1"
        And I wait "1000"
        Then I should not see "New Contribution 1"
        
    Scenario: HQP Trying to add a new contribution
        Given I am logged in as "HQP.User1" using password "HQP.Pass1"
        When I follow "Add/Edit Contribution"
        And I fill in "title" with "New Contribution 2"
        And I press "Create"
        And I select "HQP User1" from "rightresearchers"
        And I press "<<"
        And I select "NI User2" from "rightresearchers"
        And I press "<<"
        And I follow "Add Partner"
        And I fill in "partners[0]" with "Google"
        And I select "Cash" from "type[0]"
        And I fill in "cash[0]" with "1000"
        And I click by css "#projects_Phase2Project3"
        And I press "Create Contribution"
        Then I should see "Contribution: New Contribution 2"
        And I should see "Edit Contribution"
        
    Scenario: NI Trying to edit a milestone that they belong to
        Given I am logged in as "NI.User2" using password "NI.Pass2"
        When I follow "Add/Edit Contribution"
        And I fill in "title" with "New Contribution 2"
        And I wait "1000"
        Then I should see "New Contribution 2"
        
    Scenario: NI Trying to edit their HQP's contribution
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "Add/Edit Contribution"
        And I fill in "title" with "New Contribution 2"
        And I wait "1000"
        Then I should see "New Contribution 2"
