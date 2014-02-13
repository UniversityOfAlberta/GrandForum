Feature: User Profile
    In order to manage my profile
    As a User
    I need to be able to edit my profile

    Scenario: Editing University information
        Given I am logged in as "PNI.User1" using password "PNI.Pass1"
        When I follow "My Profile"
        And I press "Edit Profile"
        And I select "Professor" from "title"
        And I select "McGill University" from "university"
        And I fill in "combo_department" with "Computing Science"
        And I press "Save Profile"
        Then I should see "'Profile' updated successfully."
        And I should see "McGill University"
