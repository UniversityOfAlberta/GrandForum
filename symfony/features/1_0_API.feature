Feature: API
    In order to access information from the forum
    As a User
    I need to be able to access the APIs
    As a Guest
    I need to be able to access the public APIs
    
    Scenario: Getting list of PNIs as a PNI
        Given I am logged in as "PNI.User1" using password "PNI.Pass1"
        When I go to "index.php?action=api.people/PNI"
        Then I should see "PNI.User1"
        And I should see "pni.user1@behat-test.com"
        But I should not see "CNI.User1"
        
    Scenario: Getting list of PNIs as a Guest
        Given I am on "index.php?action=api.people/PNI"
        Then I should see "PNI.User1"
        But I should not see "pni.user1@behat-test.com"
        And I should not see "CNI.User1"
        
    Scenario: Getting list of PNIs and CNIs as a PNI
        Given I am logged in as "PNI.User1" using password "PNI.Pass1"
        When I go to "index.php?action=api.people/PNI,CNI"
        Then I should see "PNI.User1"
        And I should see "CNI.User1"
        And I should see "pni.user1@behat-test.com"
        And I should see "cni.user1@behat-test.com"
        But I should not see "HQP.User1"
        
    Scenario: Getting list of PNIs and CNIs as a Guest
        Given I am on "index.php?action=api.people/PNI,CNI"
        Then I should see "PNI.User1"
        And I should see "CNI.User1"
        But I should not see "pni.user1@behat-test.com"
        And I should not see "cni.user1@behat-test.com"
        And I should not see "HQP.User1"
        
    Scenario: Getting list of HQP as a PNI
        Given I am logged in as "PNI.User1" using password "PNI.Pass1"
        When I go to "index.php?action=api.people/HQP"
        Then I should see "HQP.User1"
        And I should see "hqp.user1@behat-test.com"
        
    Scenario: Getting list of HQP as a Guest
        Given I am on "index.php?action=api.people/HQP"
        Then I should see "[]"
        But I should not see "HQP.User1"
        
    Scenario: Getting list of Projects as a PNI
        Given I am logged in as "PNI.User1" using password "PNI.Pass1"
        When I go to "index.php?action=api.project"
        Then I should see "Phase1Project1"
        
    Scenario: Getting list of Projects as a Guest
        Given I am on "index.php?action=api.project"
        Then I should see "Phase1Project1"
