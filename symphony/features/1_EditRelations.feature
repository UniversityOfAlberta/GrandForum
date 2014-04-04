Feature: EditRelations
    In order to edit my relations
    As a User
    I need to be able to move people in/out of my relations
    
    Scenario: Adding Supervises relations
        Given I am logged in as "PNI.User1" using password "PNI.Pass1"
        When I follow "Edit Relations"
        And I select "HQP User4" from "righthqps"
        And I press "<<"
        And I press "Save Relations"
        
    Scenario: Adding Supervises relations
        Given I am logged in as "PNI.User1" using password "PNI.Pass1"
        When I follow "Edit Relations"
        And I select "HQP User4" from "lefthqps"
        And I press ">>"
        And I press "Save Relations"
