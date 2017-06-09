Feature: Bibliographies
    In order to manage bibliographies to the forum
    As a User
    I need to be able to view/add/edit/delete bibliographies

    Scenario: Adding a new Bibliography
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "Add Bibliography"
        And I fill in "title" with "New Bibliography 1"
        And I fill in "description" with "This is a description 1"
        And I press "Create Bibliography"
        And I wait "100"
        Then I should see "New Bibliography"

    Scenario: Adding a Product in a new Bibliography
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "Add Bibliography"
        And I fill in "title" with "New Bibliography 2"
        And I fill in "description" with "This is a description 2"
        When I drag an element from "products" with id "1" from "sortable2" to "sortable1"
        And I press "Create Bibliography"
        And I wait "1000"
        Then I should see "Product 1."

    Scenario: Remove a Product in an existing Bibliography
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I go to "index.php/Special:BibliographyPage"
        And I wait "100"
        When I follow "New Bibliography 2"
        And I press "Edit Bibliography"
        And I fill in "title" with "Updated Bibliography 2"
        When I drag an element from "products" with id "1" from "sortable1" to "sortable2"
        And I press "Save Bibliography"
        And I wait "1000"
        Then I should not see "Product 1."

    Scenario: Adding multiple Products in an existing Bibliography
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I go to "index.php/Special:BibliographyPage"
        And I wait "100"
        When I follow "Updated Bibliography 2"
        And I press "Edit Bibliography"
        When I drag an element from "products" with id "2" from "sortable2" to "sortable1"
        When I drag an element from "products" with id "1" from "sortable2" to "sortable1"
        And I press "Save Bibliography"
        And I wait "1000"
        Then I should see "Product 2."
        And I should see "Product 1."

    Scenario: Adding an Editor in a new Bibliography
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "Add Bibliography"
        And I fill in "title" with "New Bibliography 3"
        And I fill in "description" with "This is a description 3"
        When I drag an element from "editors" with id "2" from "sortable2" to "sortable1"
        And I press "Create Bibliography"
        And I wait "1000"
        Then I should see "Manager User1"

    Scenario: Remove an Editor in an existing Bibliography
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I go to "index.php/Special:BibliographyPage"
        And I wait "100"
        When I follow "New Bibliography 3"
        And I press "Edit Bibliography"
        And I fill in "title" with "Updated Bibliography 3"
        When I drag an element from "editors" with id "2" from "sortable1" to "sortable2"
        And I press "Save Bibliography"
        And I wait "1000"
        Then I should not see "Manager User1"

    Scenario: Adding multiple Editors in an existing Bibliography
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I go to "index.php/Special:BibliographyPage"
        And I wait "100"
        When I follow "Updated Bibliography 3"
        And I press "Edit Bibliography"
        When I drag an element from "editors" with id "1" from "sortable2" to "sortable1"
        When I drag an element from "editors" with id "2" from "sortable2" to "sortable1"
        And I press "Save Bibliography"
        And I wait "1000"
        Then I should see "Manager User1"

    Scenario: Adding a comment to an existing Bibliography
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I go to "index.php/Special:BibliographyPage"
        And I wait "100"
        When I follow "Updated Bibliography 3"
        And I wait "100"
        And I fill in TinyMCE "message" with "My first comment"
        And I press "Add Reply"
        Then I should see "My first comment"

    Scenario: Modify a comment from an existing Bibliography
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I go to "index.php/Special:BibliographyPage"
        And I wait "100"
        When I follow "Updated Bibliography 3"
        And I wait "500"
        And I click by css ".edit-icon"
        And I fill in TinyMCE "message" with "Edited Message"
        And I press "Save"
        And I wait "100"
        Then I should see "Edited Message"

    Scenario: Deleting a comment from an existing Bibliography
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I go to "index.php/Special:BibliographyPage"
        And I wait "100"
        When I follow "Updated Bibliography 3"
        And I wait "100"
        And I click by css ".delete-icon"
        And I wait "100"
        Then I should not see "Edited Message"