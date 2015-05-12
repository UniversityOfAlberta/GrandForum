Feature: EditRelations
    In order to edit my relations
    As a User
    I need to be able to move people in/out of my relations
    
    Scenario: Adding Supervises relations
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "Edit Relations"
        And I select "HQP User4" from "righthqps"
        And I press "moveLefthqps"
        And I press "Save Relations"
        
    Scenario: Removing Supervises relations
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "Edit Relations"
        And I select "HQP User4" from "lefthqps"
        And I press "moveRighthqps"
        And I press "Save Relations"
        
    Scenario: Adding Works With relations
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "Edit Relations"
        And I follow "Works With"
        And I select "NI User1" from "rightcoworkers"
        And I press "moveLeftcoworkers"
        And I press "Save Relations"
