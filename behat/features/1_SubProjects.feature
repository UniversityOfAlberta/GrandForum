Feature: Sub Projects

    Scenario: PL Adding a sub-project
        Given I am logged in as "PL.User1" using password "PL.Pass1"
        When I follow "My Projects"
        And I follow "Sub-Projects"
        And I press "New Sub-Project"
        And I fill in "new_acronym" with "NewSubProject"
        And I fill in "new_full_name" with "New Sub Project"
        And I fill in "combo_new_pl" with "PNI.User1"
        And I fill in "new_description" with "New Sub Project Description"
        And I press "Create Sub-Project"
        Then I should see "The Sub-Project was created successfully"
        When I follow "Sub-Projects"
        Then I should see "NewSubProject"
        
    Scenario: PL Adding a duplicate sub-project
        Given I am logged in as "PL.User1" using password "PL.Pass1"
        When I follow "My Projects"
        And I follow "Sub-Projects"
        And I press "New Sub-Project"
        And I fill in "new_acronym" with "NewSubProject"
        And I fill in "new_full_name" with "New Sub Project"
        And I press "Create Sub-Project"
        Then I should see "The field 'Acronym' must not be an already existing Project"
        
    Scenario: PL editing sub-project description
        Given I am logged in as "PL.User1" using password "PL.Pass1"
        When I go to "index.php/NewSubProject:Main"
        And I press "Edit Main"
        And I fill in "fullName" with "New Sub Project Edited 1"
        And I fill in "description" with "Last edited by PL.User1"
        And I press "Save Main"
        Then I should see "'Main' updated successfully"
        And I should see "New Sub Project Edited 1"
        And I should see "Last edited by PL.User1"
        
    Scenario: Sub-PL editing sub-project description and title
        Given I am logged in as "PNI.User1" using password "PNI.Pass1"
        When I go to "index.php/NewSubProject:Main"
        And I press "Edit Main"
        And I fill in "fullName" with "New Sub Project Edited 2"
        And I fill in "description" with "Last edited by PNI.User1"
        And I press "Save Main"
        Then I should see "'Main' updated successfully"
        And I should see "New Sub Project Edited 2"
        And I should see "Last edited by PNI.User1"
