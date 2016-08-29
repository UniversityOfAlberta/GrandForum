Feature: Threads
    In order to use the message threads
    As a valid user on the forums
    I need to be able to view/edit/add posts to threads

    Scenario: Viewing message board in tools
        Given I am logged in as "Admin.User1" using password "Admin.Pass1"
        Then I should see "Ask an Expert"

    Scenario: Adding a new Thread to Admin Group as Admin
        Given I am logged in as "Admin.User1" using password "Admin.Pass1"
        When I follow "Ask an Expert"
        And I press "Ask an Expert"
        And I fill in "title" with "New Thread By Admin.User1"
        And I select "Clinical" from "category"
        And I select "All Experts" from "visibility"
        And I fill in "message" with "This is the description."
        And I press "Save"
        And I wait "100"
        Then I should see "Thread has been successfully saved"

    Scenario: Viewing list of Threads as Admin
        Given I am logged in as "Admin.User1" using password "Admin.Pass1"
        When I go to "index.php/Special:MyThreads"
        Then I should see "New Thread By Admin.User1"

    Scenario: Adding a new Thread to Admin Group as Admin
        Given I am logged in as "Admin.User1" using password "Admin.Pass1"
        When I follow "Ask an Expert"
        And I press "Ask an Expert"
        And I fill in "title" with "New Thread By Admin.User1"
        And I select "Clinical" from "category"
        And I select "Chosen Experts" from "visibility"
	And I wait "100"
