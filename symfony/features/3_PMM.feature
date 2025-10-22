Feature: PMM Task Management
    In order to manage tasks for a project
    As a User
    I need to be able to create, edit, and view tasks according to my role

Scenario: A Project Leader can create a new task
    Given I am logged in as "PL.User4" using password "PL.Pass4"
    When I go to "index.php/Phase1Project1:Main"
    Then I should see "Main"
    And I click "Activities"
    And I wait "5000"
    And I press "Edit"
    Then I wait until I see "Add Task" up to "5000"
    And I press "Add Task"
    And I fill in "task" with "New task for HQP User1"
    And I press "Save"
    Then I wait "5000"
    And I should see "New task for HQP User1"

Scenario: A guest user cannot see the Activities tab
    When I go to "index.php/Phase1Project1:Main"
    And I should not see "Activities"

Scenario: A logged-in user who is not a project member cannot see the Activities tab
    Given I am logged in as "PL.User1" using password "PL.Pass1"
    When I go to "index.php/Phase1Project1:Main"
    And I should not see "Activities"

Scenario: A Project Leader can assign a project member to a task
    Given I am logged in as "PL.User4" using password "PL.Pass4"
    When I go to "index.php/Phase1Project1:Main"
    And I click "Activities"
    And I wait "5000"
    And I should see "New task for HQP User1"
    And I press "Edit"
    And I wait "5000"
    And I select from Chosen "assignees" with "HQP User1"
    And I press "Save"
    And I wait "5000"
    Then I should see "New task for HQP User1"
    And I should see "HQP User1"

Scenario: A Project Leader can assign a reviewer to a task
    Given I am logged in as "PL.User4" using password "PL.Pass4"
    When I go to "index.php/Phase1Project1:Main"
    And I click "Activities"
    And I wait "5000"
    Then I should see "New task for HQP User1"
    And I press "Edit"
    And I wait "5000"

    Then I should see "Change Status"
    When I press "Change Status"
    And I wait "5000"
    Then I should see "Change Task Status"

    And I select "HQP User4" from "displayReviewers_25_id"
    And I wait "1000"
    And I close dialog "#change-status-modal"

    And I press "Save"
    And I wait "5000"

    Then I should see "Check Status"
    When I press "Check Status"
    And I wait "5000"
    Then I should see "HQP User4"

Scenario: An assignee can change the status of their assigned task
    Given I am logged in as "HQP.User1" using password "HQP.Pass1"
    When I go to "index.php/Phase1Project1:Main"
    And I click "Activities"
    And I wait "5000"
    Then I should see "New task for HQP User1"
    And I press "Edit"
    And I wait "5000"
    Then I should see "Change Status"
    When I press "Change Status"
    And I wait "5000"
    Then I should see "Change Task Status"
    
    And I select "Done" from "displayStatuses_25"
    And I wait "1000"
    And I close dialog "#change-status-modal"    
    Then I should see "Pending Review"

    And I press "Save"
    And I wait "5000"

    Then I should see "Check Status"
    When I press "Check Status"
    And I wait "5000"
    Then I should see "Done"

Scenario: A Reviewer can change status of a task
    Given I am logged in as "HQP.User4" using password "HQP.Pass4"
    When I go to "index.php/Phase1Project1:Main"
    And I click "Activities"
    And I wait "5000"
    Then I should see "New task for HQP User1"
    And I should see "Pending Review"
    And I press "Edit"
    And I wait "5000"

    Then I should see "Change Status"
    When I press "Change Status"
    And I wait "5000"
    Then I should see "Change Task Status"

    And I select "Closed" from "displayStatuses_25"
    And I wait "1000"
    And I close dialog "#change-status-modal"    
    Then I should see "Completed"

    And I press "Save"
    And I wait "5000"
    Then I should see "Completed"
