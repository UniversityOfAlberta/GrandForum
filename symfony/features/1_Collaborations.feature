Feature: Collaborations
    In order to be able to manage collaborations on the forum
    As a User I need to be able to create and edit my own collaborations
    As a Project/Theme Leader I need to be able to edit collaborations on my project

    Scenario: Anon trying to add a collaboration
        Given I am on "index.php/Special:CollaborationPage#/new"
        Then I should see "You are not allowed to execute the action you have requested."
        
    Scenario: PL Trying to add a new collaboration
        Given I am logged in as "PL.User2" using password "PL.Pass2"
        When I follow "Manage Collaborations"
        And I follow "Add Collaboration"
        And I fill in "title" with "New Collaboration 1"
        And I fill in "personName" with "Name"
        And I fill in "position" with "Position"
        And I select from Chosen "country" with "Canada"
        And I check "Private sector in Canada" from "sector"
        And I click by css "[name=planning]"
        And I fill in "existed" with "Yes"
        And I fill in "other" with "My Description"
        And I click by css "#projects_Phase2Project3"
        And I press "Create Collaboration"
        And I wait until I no longer see "Create Collaboration" up to "1000"
        Then I should see "New Collaboration 1"
        And I should see "Edit Collaboration"
        And I should see "Phase2Project3"
        
    Scenario: PL Trying to add a new knowledge user
        Given I am logged in as "PL.User2" using password "PL.Pass2"
        When I follow "Manage Collaborations"
        And I follow "Add Knowledge User"
        And I fill in "title" with "New Knowledge User 1"
        And I fill in "personName" with "Name"
        And I fill in "position" with "Position"
        And I select from Chosen "country" with "Canada"
        And I check "Private sector in Canada" from "sector"
        And I fill in "other" with "My Description"
        And I click by css "#projects_Phase2Project3"
        And I press "Create Knowledge User"
        And I wait until I no longer see "Create Knowledge User" up to "1000"
        Then I should see "New Knowledge User 1"
        And I should see "Edit Knowledge User"
        And I should see "Phase2Project3"
               
    Scenario: PL Trying to edit their own Collaboration
        Given I am logged in as "PL.User2" using password "PL.Pass2"
        When I follow "Manage Collaborations"
        Then I should see "New Collaboration 1"
        
    Scenario: TL Trying to edit a contribiton from their project
        Given I am logged in as "TL.User1" using password "TL.Pass1"
        When I follow "Manage Collaborations"
        Then I should see "New Collaboration 1"
        
    Scenario: Staff Should be able to see all Collaborations
        Given I am logged in as "Staff.User1" using password "Staff.Pass1"
        When I follow "Manage Collaborations"
        Then I should see "New Collaboration 1"

    Scenario: Anon trying to view Collaborations
        Given I am on "index.php/Special:CollaborationPage"
        Then I should see "You are not allowed to execute the action you have requested."
        
    Scenario: NI trying to view Collaborations
        Given I am logged in as "NI.User4" using password "NI.Pass4"
        Given I am on "index.php/Special:CollaborationPage"
        Then I should see "You are not allowed to execute the action you have requested."
        
    Scenario: Anon trying to view a specific Collaboration
        Given I am on "index.php/Special:CollaborationPage#/1"
        Then I should see "You are not allowed to execute the action you have requested."
        
    Scenario: PL Trying to delete a Collaboration
        Given I am logged in as "PL.User2" using password "PL.Pass2"
        When I follow "Manage Collaborations"
        And I follow "New Collaboration 1"
        And I accept confirmation dialogs
        And I press "Delete Collaboration"
        Then I should see "Collaboration Deleted"
        And I should not see "New Collaboration 1"
