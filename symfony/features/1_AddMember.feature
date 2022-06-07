Feature: AddMember
    In order to be able to add people to the forum
    As a User I need to be able to request users
    As an Admin I need to be able to accept users

    Scenario: Anon trying to create an account (should be disabled)
        Given I am on "index.php/Special:Userlogin/signup"
        Then I should see "Permission error"
        
    Scenario: Anon trying to create an account 2 (should be disabled)
        Given I am on "index.php?title=Special:UserLogin&action=submitlogin&type=login&returnto=Help%3AContents&type=signup"
        Then I should see "Permission error"

    Scenario: HQP trying to request a user (should not be allowed to)
        Given I am logged in as "HQP.User1" using password "HQP.Pass1"
        Then I should not see "Add Member"
        When I go to "index.php/Special:AddMember"
        Then I should see "Permission Error"

    Scenario: NI Requesting a user
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "Add Member"
        And I fill in "first_name_field" with "New"
        And I fill in "last_name_field" with "User"
        And I fill in "email_field" with "new.user@behat-test.com"
        And I check "role_field_HQP"
        And I check "project_field_Phase2Project1"
        And I check "project_field_Phase2Project1SubProject1"
        And I fill in "combo_position_field0" with "My Position"
        And I select "Canadian" from "nationality_field"
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
        
    Scenario: Staff Requesting a candidate user
        Given I am logged in as "Staff.User1" using password "Staff.Pass1"
        When I follow "Add Member"
        And I fill in "first_name_field" with "New"
        And I fill in "last_name_field" with "Candidate"
        And I fill in "email_field" with "new.candidate@behat-test.com"
        And I check "role_field_HQP"
        And I check "project_field_Phase2Project1"
        And I check "project_field_Phase2Project1SubProject1"
        And I fill in "combo_position_field0" with "My Position"
        And I select "Canadian" from "nationality_field"
        And I check "Yes" from "cand_field"
        And I press "Submit Request"
        Then I should see "User Creation Request Submitted"
        
    Scenario: Admin Accepting request
        Given I am logged in as "Admin.User1" using password "Admin.Pass1"
        When I follow "status_notifications"
        And I follow "User Creation Request"
        And I press "Accept"
        Then I should see "User created successfully"
        And "new.candidate@behat-test.com" should not be subscribed to "test-hqps"
        
    Scenario: NI Requesting another user (will get a warning)
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "Add Member"
        And I fill in "first_name_field" with "New"
        And I fill in "last_name_field" with "Users"
        And I fill in "email_field" with "new.users@behat-test.com"
        And I check "role_field_HQP"
        And I check "project_field_Phase2Project1"
        And I check "project_field_Phase2Project1SubProject1"
        And I fill in "combo_position_field0" with "My Position"
        And I select "Canadian" from "nationality_field"
        And I press "Submit Request"
        Then I should see "The name provided is similar to the following person"
        And I press "Yes"
        Then I should see "User Creation Request Submitted"
        
    Scenario: Staff Accepting request
        Given I am logged in as "Staff.User1" using password "Staff.Pass1"
        When I follow "status_notifications"
        And I follow "User Creation Request"
        And I press "Accept"
        Then I should see "User created successfully"
        And "new.users@behat-test.com" should be subscribed to "test-hqps"
        And unsubscribe "new.users@behat-test.com" from "test-hqps"
        
    Scenario: NI Requesting an already existing user
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "Add Member"
        And I fill in "first_name_field" with "Already"
        And I fill in "last_name_field" with "Existing"
        And I fill in "email_field" with "already.existing@behat-test.com"
        And I check "role_field_HQP"
        And I press "Submit Request"
        Then I should see "The user name must not be an already existing Person"
        
    Scenario: NI Requesting a user with no email
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "Add Member"
        And I fill in "first_name_field" with "Test"
        And I fill in "last_name_field" with "User"
        And I check "role_field_HQP"
        And I press "Submit Request"
        Then I should see "The field 'Email' must not be empty"
        
    Scenario: NI Requesting a user with no roles
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "Add Member"
        And I fill in "first_name_field" with "Test"
        And I fill in "last_name_field" with "User"
        And I fill in "email_field" with "test.user@behat-test.com"
        And I press "Submit Request"
        Then I should see "The field 'Roles' must not be empty"
        
    Scenario: NI Requesting a user with accents in name and email
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "Add Member"
        And I fill in "first_name_field" with "Ààè"
        And I fill in "last_name_field" with "Öå"
        And I fill in "email_field" with "Ààè.Öå@behat-test.com"
        And I check "role_field_HQP"
        And I fill in "combo_position_field0" with "My Position"
        And I select "Canadian" from "nationality_field"
        And I press "Submit Request"
        Then I should see "User Creation Request Submitted"
        
    Scenario: Admin Ignoring request
        Given I am logged in as "Admin.User1" using password "Admin.Pass1"
        When I follow "status_notifications"
        And I follow "User Creation Request"
        And I press "Ignore"
        Then I should not see "Ààè.Öå"
        
    Scenario: Staff Requesting a user with no roles
        Given I am logged in as "Staff.User1" using password "Staff.Pass1"
        When I follow "Add Member"
        And I fill in "first_name_field" with "Test"
        And I fill in "last_name_field" with "UserNoRoles"
        And I fill in "email_field" with "test.usernoroles@behat-test.com"
        And I press "Submit Request"
        Then I should not see "The field 'Roles' must not be empty"
