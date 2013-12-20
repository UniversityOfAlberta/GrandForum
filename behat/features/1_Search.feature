Feature: Search
    In order to search information on the forum
    As a User
    I need to be able to type into the global search and get relevant results
    
    Scenario: PNI searches for User
        Given I am logged in as "PNI.User1" using password "PNI.Pass1"
        When I fill in "globalSearchInput" with "CNI"
        And I wait "1000"
        Then I should see "CNI User1"
        And I should see "CNI User2"
        And I should see "CNI User3"
        
    Scenario: PNI searches for a phase 2 Project
        Given I am logged in as "PNI.User1" using password "PNI.Pass1"
        When I fill in "globalSearchInput" with "Phase 2 Project"
        And I wait "1000"
        Then I should see "Phase2Project1"
        And I should see "Phase2Project2"
        And I should see "Phase2Project3"
        
    Scenario: PNI searches for a user with accented characters
        Given I am logged in as "PNI.User1" using password "PNI.Pass1"
        When I fill in "globalSearchInput" with "User WithAccents"
        And I wait "1000"
        Then I should see "Üšër WìthÁççénts"
