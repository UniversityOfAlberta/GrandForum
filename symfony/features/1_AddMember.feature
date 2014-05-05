Feature: AddMember
    In order to be able to add people to the forum
    As a User I need to be able to request users
    As an Admin I need to be able to accept users

    Scenario: HQP trying to request a user (should not be allowed to)
        Given I am logged in as "HQP.User1" using password "HQP.Pass1"
        Then I should not see "Add Member"
        When I go to "index.php/Special:AddMember"
        Then I should see "Permission Error"

    Scenario: PNI Requesting a user
        Given I am logged in as "PNI.User1" using password "PNI.Pass1"
        When I follow "Add Member"
        And I fill in "first_name_field" with "New"
        And I fill in "last_name_field" with "User"
        And I fill in "email_field" with "new.user@behat-test.com"
        And I check "role_field_HQP"
        And I check "project_field_Phase2Project1"
        And I check "project_field_Phase2Project1SubProject1"
        And I press "Submit Request"
        Then I should see "User Creation Request Submitted"
    
    Scenario: Admin Accepting request
        Given I am logged in as "Admin.User1" using password "Admin.Pass1"
        When I follow "status_notifications"
        And I follow "User Creation Request"
        And I press "Accept"
        Then I should see "User created successfully"
        And "new.user@behat-test.com" should be subscribed to "test-hqps"
        And unsubscribe "new.user@behat-test.com" from "test-hqps"
        
    Scenario: PNI Requesting an already existing user
        Given I am logged in as "PNI.User1" using password "PNI.Pass1"
        When I follow "Add Member"
        And I fill in "first_name_field" with "Already"
        And I fill in "last_name_field" with "Existing"
        And I fill in "email_field" with "already.existing@behat-test.com"
        And I check "role_field_HQP"
        And I press "Submit Request"
        Then I should see "The user name must not be an already existing Person"
        
    Scenario: PNI Requesting a user with no email
        Given I am logged in as "PNI.User1" using password "PNI.Pass1"
        When I follow "Add Member"
        And I fill in "first_name_field" with "Test"
        And I fill in "last_name_field" with "User"
        And I check "role_field_HQP"
        And I press "Submit Request"
        Then I should see "The field 'Email' must not be empty"
        
    Scenario: PNI Requesting a user with no roles
        Given I am logged in as "PNI.User1" using password "PNI.Pass1"
        When I follow "Add Member"
        And I fill in "first_name_field" with "Test"
        And I fill in "last_name_field" with "User"
        And I fill in "email_field" with "test.user@behat-test.com"
        And I press "Submit Request"
        Then I should see "The field 'Roles' must not be empty"
        
    Scenario: PNI Requesting a user with accents in name and email
        Given I am logged in as "PNI.User1" using password "PNI.Pass1"
        When I follow "Add Member"
        And I fill in "first_name_field" with "Ààè"
        And I fill in "last_name_field" with "Öå"
        And I fill in "email_field" with "Ààè.Öå@behat-test.com"
        And I check "role_field_HQP"
        And I press "Submit Request"
        Then I should see "User Creation Request Submitted"
        
    Scenario: Admin Ignoring request
        Given I am logged in as "Admin.User1" using password "Admin.Pass1"
        When I follow "status_notifications"
        And I follow "User Creation Request"
        And I press "Ignore"
        Then I should not see "Ààè.Öå"
        
