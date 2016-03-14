Feature: User Profile
    In order to manage my profile
    As a User
    I need to be able to edit my profile

    Scenario: Editing University information
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "My Profile"
        And I press "Edit Bio"
        And I fill in "combo_title" with "Professor"
        And I fill in "combo_university" with " McGill University"
        And I fill in "combo_department" with "Computer Science"
        And I press "Save Bio"
        Then I should see "'Bio' updated successfully."
        And I should see "McGill University"
        And I should see "Computer Science"
        And I should see "Professor"
        
    Scenario: Editing University information again
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "My Profile"
        And I press "Edit Bio"
        And I fill in "combo_university" with "University of Alberta"
        And I fill in "combo_department" with "Computing Science"
        And I press "Save Bio"
        Then I should see "'Bio' updated successfully."
        And I should see "University of Alberta"
        And I should see "Computing Science"
        And I should see "Professor"
        But I should not see "McGill University"
        And I should not see "Computer Science"
        
    Scenario: Editing University info with custom University
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "My Profile"
        And I press "Edit Bio"
        And I fill in "combo_university" with "This is a new University"
        And I press "Save Bio"
        Then I should see "'Bio' updated successfully."
        And I should see "This is a new University"
        But I should not see "University of Alberta"
        
    Scenario: Editing University info with a University which has different casing
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "My Profile"
        And I press "Edit Bio"
        And I fill in "combo_university" with "university of alberta"
        And I press "Save Bio"
        Then I should see "'Bio' updated successfully."
        And I should see "University of Alberta"
        But I should not see "This is a new University"
        
    Scenario: Editing Profile text
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "My Profile"
        And I press "Edit Bio"
        And I fill in TinyMCE "public_profile" with "My Public Profile"
        And I fill in TinyMCE "private_profile" with "My Private Profile"
        And I press "Save Bio"
        Then I should see "'Bio' updated successfully."
        And I should see "My Private Profile"
        When I follow "status_logout"
        And I go to "index.php/CI:NI.User1"
        Then I should see "My Public Profile"
        
    Scenario: Checking Data Quality
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "My Profile"
        And I click "Data Quality Checks"
        Then I should see "Missing gender information"
        And I should see "Missing nationality"
        When I click "Bio"
        And I press "Edit Bio"
        And I select "Male" from "gender"
        And I select "Canadian" from "nationality"
        And I press "Save Bio"
        And I click "Data Quality Checks"
        Then I should see "No Errors"
