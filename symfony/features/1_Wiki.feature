Feature: Wiki
    In order to communicate & collaborate with other people in the network
    As a User
    I need to be able to create and edit wiki pages

    Scenario: NI viewing main page
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I go to "index.php"
        Then I should see "Main Page"
        And I should not see "Permission error"

    Scenario: NI creates a wiki page
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I go to "index.php/Phase2Project1:Main"
        And I follow "Wiki"
        And I click by css "#newWikiPage"
        And I fill in "newPageTitle" with "TestWikiPage"
        And I press "Create Page"
        Then I should see "Creating Phase2Project1:TestWikiPage"
        When I fill in "wpTextbox1" with "TestText"
        And I press "Save page"
        Then I should see "TestText"
        
    Scenario: NI views list of wiki pages
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I go to "index.php/Phase2Project1:Main"
        And I follow "Wiki"
        Then I should see "TestWikiPage"
        
    Scenario: NI from other project tries to view the created wiki page
        Given I am logged in as "NI.User3" using password "NI.Pass3"
        When I go to "index.php/Phase2Project1:TestWikiPage"
        Then I should see "Permission error"
        And I should not see "TestWikiPage"
        And I should not see "TestText"
        
    Scenario: NI edits wiki page
        Given I am logged in as "NI.User2" using password "NI.Pass2"
        When I go to "index.php/Phase2Project1:TestWikiPage?action=edit"
        Then I should see "Editing Phase2Project1:TestWikiPage"
        And I fill in "wpTextbox1" with "Edited TestText"
        And I press "Save changes"
        Then I should see "Edited TestText"
        
    Scenario: Admin creating main page
        Given I am logged in as "Admin.User1" using password "Admin.Pass1"
        When I go to "index.php/Main_Page?action=edit"
        Then I should see "Creating Main Page"
        When I fill in "wpTextbox1" with "[public]Testing[/public]"
        And I press "Save page"
        Then I should see "Testing"
        
    Scenario: NI trying to edit main page (should fail)
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I go to "index.php/Main_Page?action=edit"
        Then I should see "View source for Main Page"
        
    Scenario: Admin creating a template
        Given I am logged in as "Admin.User1" using password "Admin.Pass1"
        When I go to "index.php/Template:NewTemplate"
        And I follow "create this page"
        And I fill in "wpTextbox1" with:
        """
        == Testing ==
        {{{var1}}}
        
        {{{var2}}}
        """
        And I press "Save page"
        
    Scenario: NI searches for Wiki Page
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I fill in "globalSearchInput" with "Test"
        Then I wait until I see "TestWikiPage" up to "2000"
