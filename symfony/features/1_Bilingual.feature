Feature: Bilingual
    In order to support multi languages
    As a User
    I need to be able to change my language preferences

    Scenario: Guest changing language to french
        Given I am on "index.php"
        Then I should see "Login"
        When I go to "index.php?lang=fr"
        And I go to "index.php"
        Then I should see "Connexion"

    Scenario: Guest changing language back to english
        Given I am on "index.php"
        When I go to "index.php?lang=en"
        And I go to "index.php"
        Then I should see "Login"
        
    Scenario: User changing language to french
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I go to "index.php?lang=fr"
        And I go to "index.php"
        Then I should see "Taille de la police"
        
    Scenario: User changing language back to english
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I go to "index.php?lang=en"
        And I go to "index.php"
        Then I should see "Font Size"
