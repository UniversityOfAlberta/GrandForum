Feature: Project/Theme Leaders
    In order to see information about the projects that are in the themes that I lead
    As a Theme Leader
    I need to be able to see the full information about those projects as if I were a member

    Scenario Outline: Leader views Project Page of a Project that has a challenge which is led by Leader
        Given I am logged in as <user> using password <pass>
        And I go to "index.php/Phase2Project3:Main"
        Then I should see "Main"
        And I should see "Dashboard"
        And I should see "Visualizations"
        And I should see "Wiki"
        And I should see "Edit Main"
        
        Examples:
        | user       | pass       |
        | "PL.User2" | "PL.Pass2" |
        | "TL.User1" | "TL.Pass1" |
        | "TC.User1" | "TC.Pass1" |
        
    Scenario Outline: Leader views Project Page of a Project that does not have a challege which is led by Leader
        Given I am logged in as <user> using password <pass>
        And I go to "index.php/Phase2Project1:Main"
        Then I should see "Main"
        And I should see "Dashboard"
        And I should see "Visualizations"
        And I should not see "Wiki"
        And I should not see "Edit Main"
        
        Examples:
        | user       | pass       |
        | "PL.User2" | "PL.Pass2" |
        | "TL.User1" | "TL.Pass1" |
        | "TC.User1" | "TC.Pass1" |
        
    Scenario Outline: Leader tries to update long description of a Project that has a challenge which is led by Leader
        Given I am logged in as <user> using password <pass>
        And I go to "index.php/Phase2Project3:Main"
        And I click "Description"
        And I press "Edit Description"
        And I fill in TinyMCE "long_description" with <text>
        And I press "Save Description"
        Then I should see "'Description' updated successfully."
        And I should see <text>
        
        Examples:
        | user       | pass       | text                       |
        | "PL.User2" | "PL.Pass2" | "PL.User2 was here (long)" |
        | "TL.User1" | "TL.Pass1" | "TL.User1 was here (long)" |
        | "TC.User1" | "TC.Pass1" | "TC.User1 was here (long)" |
        
    Scenario Outline: LLeader tries to short description of a Project that has a challenge which is led by Leader
        Given I am logged in as <user> using password <pass>
        And I go to "index.php/Phase2Project3:Main"
        And I press "Edit Main"
        And I fill in TinyMCE "description" with <text>
        And I press "Save Main"
        Then I should see "'Main' updated successfully."
        And I should see <text>
        
        Examples:
        | user       | pass       | text                        |
        | "PL.User2" | "PL.Pass2" | "PL.User2 was here (short)" |
        | "TL.User1" | "TL.Pass1" | "TL.User1 was here (short)" |
        | "TC.User1" | "TC.Pass1" | "TC.User1 was here (short)" |
        
    Scenario Outline: Leader views Sub-Project Page of a Project that has a challenge which is led by Leader
        Given I am logged in as <user> using password <pass>
        And I go to "index.php/Phase2Project3SubProject1:Main"
        Then I should see "Main"
        And I should see "Dashboard"
        And I should see "Visualizations"
        And I should see "Wiki"
        
        Examples:
        | user       | pass       |
        | "PL.User2" | "PL.Pass2" |
        | "TL.User1" | "TL.Pass1" |
        | "TC.User1" | "TC.Pass1" |  
    
    Scenario Outline: Leader views Sub-Project Page of a Project that does not have a challege which is led by Leader
        Given I am logged in as <user> using password <pass>
        And I go to "index.php/Phase2Project1SubProject1:Main"
        Then I should see "Main"
        And I should see "Dashboard"
        And I should see "Visualizations"
        And I should not see "Wiki"
        
        Examples:
        | user       | pass       |
        | "PL.User2" | "PL.Pass2" |
        | "TL.User1" | "TL.Pass1" |
        | "TC.User1" | "TC.Pass1" |
        
    Scenario Outline: Leader views the listing of their themes
        Given I am logged in as <user> using password <pass>
        And I follow "My Profile"
        And I click "Projects"
        Then I should see <text>
        
        Examples:
        | user       | pass       | text                                 |
        | "PL.User2" | "PL.Pass2" | "Phase 2 Project 3 (Phase2Project3)" |
        | "TL.User1" | "TL.Pass1" | "Theme 1 (Theme1) (lead)"            |
        | "TC.User1" | "TC.Pass1" | "Theme 1 (Theme1) (coord)"           |
