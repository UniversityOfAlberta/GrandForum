Feature: Search
    In order to search information on the forum
    As a User
    I need to be able to type into the global search and get relevant results
    
    Scenario: PNI searches for User
        Given I am logged in as "PNI.User1" using password "PNI.Pass1"
        When I fill in "globalSearchInput" with "CNI"
        Then I wait until I see "CNI User1" up to "1000"
        And I wait until I see "CNI User2" up to "1000"
        And I wait until I see "CNI User3" up to "1000"
        
    Scenario: PNI searches for a phase 2 Project
        Given I am logged in as "PNI.User1" using password "PNI.Pass1"
        When I fill in "globalSearchInput" with "Phase 2 Project"
        Then I wait until I see "Phase2Project1" up to "1000"
        And I wait until I see "Phase2Project2" up to "1000"
        And I wait until I see "Phase2BigBetProject1" up to "1000"
        
    Scenario: PNI searches for a user with accented characters
        Given I am logged in as "PNI.User1" using password "PNI.Pass1"
        When I fill in "globalSearchInput" with "User WithAccents"
        Then I wait until I see "Üšër WìthÁççénts" up to "1500"
        
    Scenario: Guest searches for HQP
        Given I am on "index.php"
        When I fill in "globalSearchInput" with "HQP"
        Then I wait "1000"
        Then I should not see "HQP User1"
        
    Scenario: Guest searches for Innactive User
        Given I am on "index.php"
        When I fill in "globalSearchInput" with "Innactive"
        Then I wait "1000"
        Then I should not see "HQP ToBeInactivated"
        
    Scenario: Guest searches for PNI
        Given I am on "index.php"
        When I fill in "globalSearchInput" with "PNI"
        Then I wait until I see "PNI User1" up to "1000"
        And I wait until I see "PNI User2" up to "1000"
        And I wait until I see "PNI User3" up to "1000"
