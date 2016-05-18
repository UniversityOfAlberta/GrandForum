Feature: Threads
    In order to use the message threads
    As a valid user on the forums
    I need to be able to view/edit/add posts to threads

    Scenario: Viewing message board in tools
        Given I am logged in as "Admin.User1" using password "Admin.Pass1"
        Then I should see "Message Board"

    Scenario: Adding a new Thread to Admin Group as Admin
        Given I am logged in as "Admin.User1" using password "Admin.Pass1"
        When I follow "Message Board"
        And I press "Add Thread"
        And I fill in "title" with "New Thread By Admin.User1"
        And I select "Admin" from "roles"
        And I fill in "message" with "This is the description."
        And I press "Save Thread"
        And I wait "100"
        Then I should see "Thread has been successfully saved"

    Scenario: Viewing list of Threads as Admin
        Given I am logged in as "Admin.User1" using password "Admin.Pass1"
        When I go to "index.php/Special:MyThreads"
        Then I should see "New Thread By Admin.User1"

    Scenario: Viewing list of Threads as NI
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I go to "index.php/Special:MyThreads"
        Then I should see "No data available in table"

    Scenario: Viewing Thread as NI
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I go to "index.php/Special:MyThreads#/1"
        Then I should see "This Thread does not exist."

    Scenario: Adding a new Thread to NI Group as Admin
        Given I am logged in as "Admin.User1" using password "Admin.Pass1"
        When I follow "Message Board"
        And I press "Add Thread"
        And I fill in "title" with "New NI Thread By Admin.User1"
        And I select "CI" from "roles"
        And I fill in "message" with "This is the description."
        And I press "Save Thread"
        And I wait "100"
        Then I should see "Thread has been successfully saved"

    Scenario: Viewing list of Threads as NI
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I go to "index.php/Special:MyThreads"
        Then I should see "New NI Thread By Admin.User1"

    Scenario: Adding a new Post to NI Thread as NI
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I go to "index.php/Special:MyThreads"
        And I follow "New NI Thread By Admin.User1"
        And I wait "100"
        Then I should see "This is the description."
        And I fill in "message" with "Hello World"
        And I press "Add Reply"
        And I wait "100"
        Then I should see "Hello World"
        
    Scenario: Editing a post as NI
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I go to "index.php/Special:MyThreads"
        And I follow "New NI Thread By Admin.User1"
        And I wait "500"
        And I click by css ".edit-icon"
        And I fill in "message" with "Edited Message"
        And I press "Save"
        And I wait "100"
        Then I should see "Edited Message"
        
