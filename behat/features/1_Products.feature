Feature: Products

    Scenario: Adding a new Publication
        Given I am logged in as "PNI.User1" using password "PNI.Pass1"
        When I follow "Add/Edit Publication"
        And I click by css "#addpublication"
        And I fill in "title" with "New Publication"
        And I press "Create"
        And I select "PNI User1" from "rightauthors"
        And I press "moveLeftauthors"
        And I fill in "description" with "This is a description"
        And I select "Proceedings Paper" from "type"
        And I select "January" from "month"
        And I select "4" from "day"
        And I select "2013" from "year"
        And I check "projects_Phase2Project1"
        And I press "Create Publication"
        Then I should see "New Publication"
        And I should see "Jan 4, 2013"
        And I should see "Phase2Project1"
        
    Scenario: Viewing list of Publications
        Given I am logged in as "PNI.User1" using password "PNI.Pass1"
        When I go to "index.php/Special:Products#/Publication"
        Then I should see "New Publication"
        
    Scenario: Viewing list of Publications as guest (should get permission error)
        Given I am on "index.php"
        And I go to "index.php/Special:Products#/Publication"
        Then I should see "Permission error"
        
    Scenario: Viewing Publication as guest (should get permission error)
        Given I am on "index.php/Publication:1"
        Then I should see "Permission error"
        
    Scenario: Editing a Publication
        Given I am logged in as "PNI.User1" using password "PNI.Pass1"
        When I go to "index.php/Special:Products#/Publication"
        And I follow "New Publication"
        And I press "Edit Publication"
        And fill in "description" with "This is an edited description"
        And I press "Save Publication"
        Then I should see "This is an edited description"
        
    Scenario: Deleting a Publication
        Given I am logged in as "PNI.User1" using password "PNI.Pass1"
        When I go to "index.php/Special:Products#/Publication"
        And I follow "New Publication"
        And I press "Delete Publication"
        And I press "Yes"
        Then I should see "The Publication New Publication was Deleted"
        And I reload the page
        Then I should see "This publication has been deleted, and will not show up anywhere else on the forum"
        When I go to "index.php/Special:Products#/Publication"
        Then I should not see "New Publication"
