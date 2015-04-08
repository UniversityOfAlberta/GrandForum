Feature: Products
    In order to manage products to the forum
    As a User
    I need to be able to view/add/edit/delete products

    Scenario: Adding a new Publication
        Given I am logged in as "PNI.User1" using password "PNI.Pass1"
        When I follow "Manage Products"
        And I press "Add Product"
        And I fill in "title" with "New Publication"
        And I fill in "description" with "This is a description"
        And I select "Publication" from "category"
        And I select "Proceedings Paper" from "type"
        And I click by css "#projects_Phase2Project1"
        And I press "Save Product"
        Then I should see "The Product has been saved sucessfully"
        
    Scenario: Viewing list of Publications
        Given I am logged in as "PNI.User1" using password "PNI.Pass1"
        When I go to "index.php/Special:Products#/Publication"
        Then I should see "New Publication"
        
    Scenario: Viewing list of Publications as guest
        Given I am on "index.php"
        And I go to "index.php/Special:Products#/Publication"
        Then I should see "No data available in table"
        
    Scenario: Viewing Publication as guest (should get error)
        Given I am on "index.php/Special:Products#/Publication/1"
        Then I should see "This Product does not exist"
        
    Scenario: Changing the permissions of a product
        Given I am logged in as "PNI.User1" using password "PNI.Pass1"
        When I go to "index.php/Special:Products#/Publication"
        And I follow "New Publication"
        And I press "Edit Publication"
        And I select "Public" from "access"
        And I press "Save Publication"
        Then I should see "New Publication"
        
    Scenario: Viewing list of Publications as guest
        Given I am on "index.php"
        And I go to "index.php/Special:Products#/Publication"
        Then I should see "New Publication"
        
    Scenario: Viewing Publication as guest (should get error)
        Given I am on "index.php/Special:Products#/Publication/1"
        Then I should see "New Publication"
    
    Scenario: Editing a Publication
        Given I am logged in as "PNI.User1" using password "PNI.Pass1"
        When I go to "index.php/Special:Products#/Publication"
        And I follow "New Publication"
        And I press "Edit Publication"
        And fill in "description" with "This is an edited description"
        And I press "Save Publication"
        Then I wait "1000"
        Then I should see "This is an edited description"
        
    Scenario: Deleting a Publication
        Given I am logged in as "PNI.User1" using password "PNI.Pass1"
        When I go to "index.php/Special:Products#/Publication"
        And I follow "New Publication"
        And I press "Delete Publication"
        Then I should see "The Publication New Publication was deleted sucessfully"
        And I reload the page
        Then I should see "This publication has been deleted, and will not show up anywhere else on the forum"
        When I go to "index.php/Special:Products#/Publication"
        Then I should not see "New Publication"
