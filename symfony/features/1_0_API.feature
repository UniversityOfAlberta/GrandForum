Feature: API
    In order to access information from the forum
    As a User
    I need to be able to access the APIs
    As a Guest
    I need to be able to access the public APIs
    
    Scenario: Getting list of NIs as a NI
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I go to "index.php?action=api.people/NI"
        Then I should see "NI.User1"
        And I should see "ni.user1@behat-test.com"
        
    Scenario: Getting list of NIs as a Guest
        Given I am on "index.php?action=api.people/NI"
        Then I should see "NI.User1"
        But I should not see "ni.user1@behat-test.com"
               
    Scenario: Getting list of HQP as a NI
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I go to "index.php?action=api.people/HQP"
        Then I should see "HQP.User1"
        And I should see "hqp.user1@behat-test.com"
        
    Scenario: Getting list of HQP as a Guest
        Given I am on "index.php?action=api.people/HQP"
        Then I should not see "HQP.User1"
        
    Scenario: Getting list of Projects as a NI
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I go to "index.php?action=api.project"
        Then I should see "Phase1Project1"
        
    Scenario: Getting list of Projects as a Guest
        Given I am on "index.php?action=api.project"
        Then I should see "Phase1Project1"
