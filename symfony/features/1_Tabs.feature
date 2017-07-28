Feature: Tabs
    In order to discover pages on the forum
    As a User
    I need to be able to see structured tabs on the Forum
    
    @grand
    Scenario: NI viewing top level tabs
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        Then I should see "GRAND"
        And I should see "My Profile"
        And I should see "My Projects"
        And I should see "My Reports"
        And I should see "My Archive"
        
    @grand
    Scenario: NI viewing GRAND sub-tabs
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "GRAND"
        Then I should see "Projects"
        And I should see "People"
        And I should see "Products"
        And I should see "Themes"
        And I should see "Visualizations"
    
    @grand
    Scenario: NI viewing My Profile
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "My Profile"
        Then the url should match ".*NI:NI.User1"
    
    @grand
    Scenario: NI viewing My MailingLists
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "My Mailing Lists"
        Then the url should match ".*Special:MyMailingLists"
    
    @grand
    Scenario: NI viewing My Reports sub-tabs
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "My Reports"
        Then I should see "NI"
    
    @grand
    Scenario: HQP viewing My Reports sub-tabs
        Given I am logged in as "HQP.User1" using password "HQP.Pass1"
        When I follow "My Reports"
        Then I should see "HQP"
    
    @grand
    Scenario: PL viewing My Reports sub-tabs
        Given I am logged in as "PL.User1" using password "PL.Pass1"
        When I follow "My Reports"
        Then I should see "NI"
        And I should see "Phase2Project1"
