Feature: Wiki
    In order to communicate & collaborate with other people in the network
    As a User
    I need to be able to create and edit wiki pages

    Scenario: PNI creates a wiki page
        Given I am logged in as "PNI.User1" using password "PNI.Pass1"
        When I go to "index.php/Phase2Project1:Main"
        And I follow "Wiki"
        And I click by css "#newWikiPage"
        And I fill in "newPageTitle" with "TestWikiPage"
        And I press "Create Page"
        Then I should see "Creating Phase2Project1:TestWikiPage"
        When I fill in "wpTextbox1" with "TestText"
        And I press "Save page"
        Then I should see "TestText"
        
    Scenario: PNI views list of wiki pages
        Given I am logged in as "PNI.User1" using password "PNI.Pass1"
        When I go to "index.php/Phase2Project1:Main"
        And I follow "Wiki"
        Then I should see "TestWikiPage"
        
    Scenario: PNI from other project tries to view the created wiki page
        Given I am logged in as "PNI.User3" using password "PNI.Pass3"
        When I go to "index.php/Phase2Project1:TestWikiPage"
        Then I should see "Permission error"
        And I should not see "TestWikiPage"
        And I should not see "TestText"
        
    Scenario: PNI edits wiki page
        Given I am logged in as "PNI.User2" using password "PNI.Pass2"
        When I go to "index.php/Phase2Project1:TestWikiPage?action=edit"
        Then I should see "Editing Phase2Project1:TestWikiPage"
        And I fill in "wpTextbox1" with "Edited TestText"
        And I press "Save page"
        Then I should see "Edited TestText"
        
    Scenario: Admin editing main page
        Given I am logged in as "Admin.User1" using password "Admin.Pass1"
        When I go to "index.php/Main_Page?action=edit"
        Then I should see "Editing Main Page"
        When I fill in "wpTextbox1" with "[public]Testing[/public]"
        And I press "Save page"
        Then I should see "Testing"
        
    Scenario: PNI trying to edit main page (should fail)
        Given I am logged in as "PNI.User1" using password "PNI.Pass1"
        When I go to "index.php/Main_Page?action=edit"
        Then I should see "View source for Main Page"
        
    Scenario: Admin creating a template
        Given I am logged in as "Admin.User1" using password "Admin.Pass1"
        When I go to "index.php/Template:NewTemplate"
        And I follow "edit this page"
        And I fill in "wpTextbox1" with:
        """
        == Testing ==
        {{{var1}}}
        
        {{{var2}}}
        """
        And I press "Save page"
        
    Scenario: PNI using template editor
        Given I am logged in as "PNI.User1" using password "PNI.Pass1"
        When I go to "index.php/Phase2Project1:TestTemplate?action=createFromTemplate"
        And I select "NewTemplate" from "templateChooser"
        And I press "Create page using selected template"
        And I fill in "NewTemplate0|var1MAIN" with "VAR1"
        And I fill in "NewTemplate0|var2MAIN" with "VAR2"
        And I press "Save page"
        Then I should see "VAR1"
        And I should see "VAR2"
