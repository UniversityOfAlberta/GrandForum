Feature: Search
    In order to search information on the forum
    As a User
    I need to be able to type into the global search and get relevant results
    
    Scenario: NI searches for User
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I fill in "globalSearchInput" with "NI"
        Then I wait until I see "NI User1" up to "5000"
        And I wait until I see "NI User2" up to "5000"
        And I wait until I see "NI User3" up to "5000"
        
    Scenario: NI searches for a phase 2 Project
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I fill in "globalSearchInput" with "Phase 2 Project"
        Then I wait until I see "Phase2Project1" up to "5000"
        And I wait until I see "Phase2Project2" up to "5000"
        And I wait until I see "Phase2BigBetProject1" up to "5000"
        
    Scenario: NI searches for a user with accented characters
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I fill in "globalSearchInput" with "User WithAccents"
        Then I wait until I see "Üšër WìthÁççénts" up to "5000"
        
    Scenario: Guest searches for HQP
        Given I am on "index.php"
        When I fill in "globalSearchInput" with "HQP"
        Then I wait "5000"
        Then I should not see "HQP User1"
        
    Scenario: Guest searches for Innactive User
        Given I am on "index.php"
        When I fill in "globalSearchInput" with "Innactive"
        Then I wait "5000"
        Then I should not see "HQP ToBeInactivated"
