Feature: Project Milestones
    A PL should be able to edit the project milestones
    and leaders of individual milestones should be able to edit those

    Scenario: PL adding a new activity
        Given I am logged in as "PL.User1" using password "PL.Pass1"
        When I am on "index.php/Phase2Project1:Main"
        And I click "Milestones"
        And I press "Edit Milestones"
        And I click "Add Activity"
        And I fill in "new_activity_title" with "My New Activity"
        And I press "Add Activity"
        And I am on "index.php/Phase2Project1:Main?tab=milestones"
        Then I should see "My New Activity"
        
    Scenario: PL adding a new milestone
        Given I am logged in as "PL.User1" using password "PL.Pass1"
        When I am on "index.php/Phase2Project1:Main?tab=milestones"
        And I press "Edit Milestones"
        And I click "Add Milestone"
        And I select "My New Activity" from "new_milestone_activity"
        And I fill in "new_milestone_title" with "My New Milestone"
        And I press "Add Milestone"
        And press "Save Milestones"
        Then I should see "'Milestones' updated successfully."
        And  I should see "My New Milestone"
        
    Scenario: PL editing a milestone
        Given I am logged in as "PL.User1" using password "PL.Pass1"
        When I am on "index.php/Phase2Project1:Main?tab=milestones"
        And I press "Edit Milestones"
        And I select "O - On Going" from "milestone_q[1][0][2014][2]"
        And I select "O - On Going" from "milestone_q[1][0][2014][3]"
        And I select "O - On Going" from "milestone_q[1][0][2014][4]"
        And I select from Chosen "\"milestone_leader[1][0][]\"" with "NI User1"
        And I press "Save Milestones"
        Then I should see "'Milestones' updated successfully."
        And I should see "NI User1"
        
    Scenario: PL editing a milestone
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I am on "index.php/Phase2Project1:Main?tab=milestones"
        And I press "Edit Milestones"
        And I select "Funders" from "milestone_end_user[1][0]"
        And I press "Save Milestones"
        Then I should see "'Milestones' updated successfully."
        And I should see "Funders"
