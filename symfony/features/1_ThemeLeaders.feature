Feature: Theme Leaders
    In order to see information about the projects that are in the themes that I lead
    As a Theme Leader
    I need to be able to see the full information about those projects as if I were a member

    Scenario: TL views Project Page of a Project that has a challenge which is led by TL
        Given I am logged in as "TL.User1" using password "TL.Pass1"
        And I go to "index.php/Phase2Project3:Main"
        Then I should see "Main"
        And I should see "Sub-Projects"
        And I should see "Dashboard"
        And I should see "Visualizations"
        And I should see "Wiki"
        
    Scenario: TL views Project Page of a Project that does not have a challege which is led by TL
        Given I am logged in as "TL.User1" using password "TL.Pass1"
        And I go to "index.php/Phase2Project1:Main"
        Then I should see "Main"
        And I should not see "Sub-Projects"
        And I should see "Dashboard"
        And I should see "Visualizations"
        And I should not see "Wiki"
        
    Scenario: TL views Sub-Project Page of a Project that has a challenge which is led by TL
        Given I am logged in as "TL.User1" using password "TL.Pass1"
        And I go to "index.php/Phase2Project3SubProject1:Main"
        Then I should see "Main"
        And I should see "Dashboard"
        And I should see "Visualizations"
        And I should see "Wiki"    
    
    Scenario: TL views Sub-Project Page of a Project that does not have a challege which is led by TL
        Given I am logged in as "TL.User1" using password "TL.Pass1"
        And I go to "index.php/Phase2Project1SubProject1:Main"
        Then I should see "Main"
        And I should see "Dashboard"
        And I should see "Visualizations"
        And I should not see "Wiki"
        
    Scenario: TL views the listing of their themes
        Given I am logged in as "TL.User1" using password "TL.Pass1"
        And I follow "My Profile"
        And I click "Projects"
        Then I should see "Theme1 (lead)"
