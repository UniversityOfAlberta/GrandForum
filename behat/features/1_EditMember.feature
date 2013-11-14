Feature: EditMember

    Scenario: PNI Editing HQP's projects
        Given I am logged in as "PNI.User1" using password "PNI.Pass1"
        When I follow "Edit Member"
        And I select "HQP User1" from "names"
        And I press "Next"
        And I follow "ProjectsTab"
        And I check "p_wpNS_Phase2Project2"
        And I press "Submit Request"
        Then I should see "+Phase2Project2"
        
    Scenario: Admin Accepting request
        Given I am logged in as "Admin.User1" using password "Admin.Pass1"
        When I follow "lnk-notifications"
        And I follow "User Role Request"
        And I press "Accept"
        Then I should see "added to Phase2Project2"
