Feature: User Profile
    In order to manage my profile
    As a User
    I need to be able to edit my profile

    Scenario: Editing University information
        Given I am logged in as "PNI.User1" using password "PNI.Pass1"
        When I follow "My Profile"
        And I press "Edit Profile"
        And I fill in "combo_title" with "Professor"
        And I select "McGill University" from "university"
        And I fill in "combo_department" with "Computer Science"
        And I press "Save Profile"
        Then I should see "'Profile' updated successfully."
        And I should see "McGill University"
        And I should see "Computer Science"
        And I should see "Professor"
        
    Scenario: Editing University information again
        Given I am logged in as "PNI.User1" using password "PNI.Pass1"
        When I follow "My Profile"
        And I press "Edit Profile"
        And I select "University of Alberta" from "university"
        And I fill in "combo_department" with "Computing Science"
        And I press "Save Profile"
        Then I should see "'Profile' updated successfully."
        And I should see "University of Alberta"
        And I should see "Computing Science"
        And I should see "Professor"
        But I should not see "McGill University"
        And I should not see "Computer Science"
        
    Scenario: Editing Profile text
        Given I am logged in as "PNI.User1" using password "PNI.Pass1"
        When I follow "My Profile"
        And I press "Edit Profile"
        And I fill in "public_profile" with "My Public Profile"
        And I fill in "private_profile" with "My Private Profile"
        And I press "Save Profile"
        Then I should see "'Profile' updated successfully."
        And I should see "My Private Profile"
        When I follow "status_logout"
        And I go to "index.php/PNI:PNI.User1"
        Then I should see "My Public Profile"
