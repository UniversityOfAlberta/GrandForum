Feature: User Profile

    Scenario: Editing University information
        Given I am logged in as "PNI.User1" using password "PNI.Pass1"
        When I follow "My Profile"
        And I follow "Contact"
        And I press "Edit Contact"
        And I select "Professor" from "title"
        And I select "McGill University" from "university"
        And I fill in "combo_department" with "Computing Science"
        And I press "Save Contact"
        Then I should see "'Contact' updated successfully."
        And I should see "McGill University"
