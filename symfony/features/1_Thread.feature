Feature: Threads
    In order to use the message threads
    As a valid user on the forums
    I need to be able to view/edit/add posts to threads

    Scenario: Viewing message board in tools
        Given I am logged in as "Admin.User1" using password "Admin.Pass1"
        Then I should see "Message Boards"

    Scenario: Adding a new Thread to Admin Group as Admin
        Given I am logged in as "Admin.User1" using password "Admin.Pass1"
        When I follow "Message Boards"
        And I follow "General"
        And I press "Add Thread"
        And I fill in "title" with "New Thread By Admin.User1"
        And I select "Admin" from "roles"
        And I fill in TinyMCE "message" with "This is the description."
        And I press "Save Thread"
        And I wait until I see "Thread has been successfully saved" up to "1000"
        Then I should see "Thread has been successfully saved"

    Scenario: Viewing list of Threads as Admin
        Given I am logged in as "Admin.User1" using password "Admin.Pass1"
        When I go to "index.php/Special:MyThreads"
        And I follow "General"
        Then I should see "New Thread By Admin.User1"

    Scenario: Viewing list of Threads as NI
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I go to "index.php/Special:MyThreads"
        And I follow "General"
        Then I should see "No data available in table"

    Scenario: Viewing Thread as NI
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I go to "index.php/Special:MyThreads#/1/4"
        Then I should see "This Thread does not exist."

    Scenario: Adding a new Thread to NI Group as Admin
        Given I am logged in as "Admin.User1" using password "Admin.Pass1"
        When I follow "Message Boards"
        And I follow "General"
        And I press "Add Thread"
        And I fill in "title" with "New NI Thread By Admin.User1"
        And I select "CI" from "roles"
        And I fill in TinyMCE "message" with "This is the description."
        And I press "Save Thread"
        And I wait until I see "Thread has been successfully saved" up to "1000"
        Then I should see "Thread has been successfully saved"

    Scenario: Viewing list of Threads as NI
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I go to "index.php/Special:MyThreads"
        And I follow "General"
        Then I should see "New NI Thread By Admin.User1"

    Scenario: Adding a new Post to NI Thread as NI
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I go to "index.php/Special:MyThreads"
        And I follow "General"
        And I follow "New NI Thread By Admin.User1"
        And I wait until I see "Reply to Thread" up to "1000"
        Then I should see "This is the description."
        And I fill in TinyMCE "message" with "Hello World"
        And I press "Add Reply"
        And I wait until I see "From: NI User1" up to "1000"
        Then I should see "Hello World"
        
    Scenario: Editing a post as NI
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I go to "index.php/Special:MyThreads"
        And I follow "General"
        And I follow "New NI Thread By Admin.User1"
        And I wait until I see "Reply to Thread" up to "1000"
        And I click by css ".edit-icon"
        And I fill in TinyMCE "message" with "Edited Message"
        And I press "Save"
        And I wait until I no longer see "Save" up to "1000"
        Then I should see "Edited Message"
        
