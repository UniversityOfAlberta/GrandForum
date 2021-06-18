Feature: User Profile
    In order to manage my profile
    As a User
    I need to be able to edit my profile

    Scenario: Guest accessing profile of External
        Given I am on "index.php/External:External.User1"
        Then I should not see "Edit Bio"

    Scenario: Editing University information
        Given I am logged in as "NI.User2" using password "NI.Pass2"
        When I follow "My Profile"
        And I press "Edit Bio"
        And I press "Add Institution"
        And I fill in "combo_university" with "Test University"
        And I fill in "combo_department" with "Test Department"
        And I select "Graduate Student - Master's" from "position"
        And I press "Save Bio"
        And I wait until I see "'Bio' updated successfully." up to "2000"
        Then I should see "Test University"
        And I should see "Test Department"
        And I should see "Graduate Student - Master's"
        
    Scenario: Updating University information
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "My Profile"
        And I press "Edit Bio"
        And I fill in "combo_department" with "Test Department Updated"
        And I press "Save Bio"
        And I wait until I see "'Bio' updated successfully." up to "2000"
        Then I should see "Test Department Updated"
        
    Scenario: Editing Profile text
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "My Profile"
        And I press "Edit Bio"
        And I fill in TinyMCE "public_profile" with "My Public Profile"
        And I fill in TinyMCE "private_profile" with "My Private Profile"
        And I press "Save Bio"
        And I wait until I see "'Bio' updated successfully." up to "2000"
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
        And I wait until I see "'Bio' updated successfully." up to "2000"
        And I click "Data Quality Checks"
        Then I should see "No Errors"
