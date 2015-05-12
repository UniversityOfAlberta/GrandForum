@grand
Feature: GRAND Reporting
    In order to report on the forum
    As a User
    I need to be able to access the relevant reports and edit the fields in the report

    Scenario: HQP attempts to view an NI report
        Given I am logged in as "HQP.User1" using password "HQP.Pass1"
        When I go to "index.php/Special:Report?report=NIReport"
        Then I should see "Permission error"
        
    Scenario: HQP attempts to view a PL report
        Given I am logged in as "HQP.User1" using password "HQP.Pass1"
        When I go to "index.php/Special:Report?report=ProjectFinalReport&project=Phase1Project1"
        Then I should see "Permission error"
        
    Scenario: HQP attempts to their HQP report
        Given I am logged in as "HQP.User1" using password "HQP.Pass1"
        When I go to "index.php/Special:Report?report=HQPReport"
        Then I should not see "Permission error"
        
    Scenario: NI attempts to view an HQP report
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I go to "index.php/Special:Report?report=HQPReport"
        Then I should see "Permission error"
        
    Scenario: NI (who is not a PL) attempts to view a PL report
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I go to "index.php/Special:Report?report=ProjectFinalReport&project=Phase1Project1"
        Then I should see "Permission error"
    
    Scenario: NI attempts to view their NI report
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I go to "index.php/Special:Report?report=NIReport"
        Then I should not see "Permission error"
        
    Scenario: PL attempts to view a PL report which they are not a PL of
        Given I am logged in as "PL.User1" using password "PL.Pass1"
        When I go to "index.php/Special:Report?report=ProjectFinalReport&project=Phase1Project1"
        Then I should see "Permission error"
        
    Scenario: PL attempts to view their PL report
        Given I am logged in as "PL.User1" using password "PL.Pass1"
        When I go to "index.php/Special:Report?report=ProjectReport&project=Phase2Project1"
        Then I should not see "Permission error"

    Scenario: HQP edits and saves their report
        Given I am logged in as "HQP.User1" using password "HQP.Pass1"
        When I follow "My Reports"
        And I click by css "#Report"
        And I wait until I see "Excellence of the Research Program: How my research contributes to the Network" up to "5000"
        And I click "Excellence of the Research Program: How my research contributes to the Network"
        And I fill in "Report_Ia_head_person17_project0_milestone0_OR_person17_project0_milestone0_ProjectLimit_Ia_person17_project0_milestone0_projects_person17_project212_milestone0_Ia" with "lorem ipsum"
        And I press "Save"
        And I click "Dashboard"
        And I wait until I see "Projects:" up to "5000"
        And I click by css "#Report"
        And I wait until I see "Excellence of the Research Program: How my research contributes to the Network" up to "5000"
        And I click "Excellence of the Research Program: How my research contributes to the Network"
        Then I should see "lorem ipsum"
        
    Scenario: NI edits one of their HQP's reports
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "My Reports"
        And I go to "index.php/Special:Report?report=HQPReport&impersonate=HQP.User1"
        Then I should see "is currently viewing the forum as HQP User1"
        When I click by css "#Report"
        And I wait until I see "Excellence of the Research Program: How my research contributes to the Network" up to "5000"
        And I click "Excellence of the Research Program: How my research contributes to the Network"
        And I fill in "Report_Ia_head_person17_project0_milestone0_OR_person17_project0_milestone0_ProjectLimit_Ia_person17_project0_milestone0_projects_person17_project212_milestone0_Ia" with "edited by supervisor"
        And I press "Save"
        And I click "Dashboard"
        And I wait until I see "Projects:" up to "5000"
        And I click by css "#Report"
        And I wait until I see "Excellence of the Research Program: How my research contributes to the Network" up to "5000"
        And I click "Excellence of the Research Program: How my research contributes to the Network"
        Then I should see "edited by supervisor"       
        
    Scenario: NI Uploads a budget within limits (with BigBet Project)
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "My Reports"
        And I click "Budget"
        And I wait until I see "Budget Justification" up to "5000"
        And I switch to iframe "budget"
        And I attach the file "NI.User1_valid.xls" to "budget"
        And I press "Upload"
        And I switch to iframe "budget"
        Then I should see "$115000"
        And I should not see "is greater than the maximum"
        
    Scenario: NI Uploads a budget which is over the limits
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "My Reports"
        And I click "Budget"
        And I wait until I see "Budget Justification" up to "5000"
        And I switch to iframe "budget"
        And I attach the file "NI.User1_invalid.xls" to "budget"
        And I press "Upload"
        And I switch to iframe "budget"
        Then I should see "$66000"
        And I should see "is greater than the maximum $65000"
        
    Scenario: NI Uploads a budget which is has a duplicate project
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "My Reports"
        And I click "Budget"
        And I wait until I see "Budget Justification" up to "5000"
        And I switch to iframe "budget"
        And I attach the file "NI.User1_duplicate.xls" to "budget"
        And I press "Upload"
        And I switch to iframe "budget"
        Then I should see "has already been used in another column"
        
    Scenario: NI Uploads a budget with a project that they are not on
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "My Reports"
        And I click "Budget"
        And I wait until I see "Budget Justification" up to "5000"
        And I switch to iframe "budget"
        And I attach the file "NI.User1_notonproject.xls" to "budget"
        And I press "Upload"
        And I switch to iframe "budget"
        Then I should see "You are not a member of 'Phase2Project3'"
        
    Scenario: NI Uploads an empty budget
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "My Reports"
        And I click "Budget"
        And I wait until I see "Budget Justification" up to "5000"
        And I switch to iframe "budget"
        And I attach the file "NI.User1_empty.xls" to "budget"
        And I press "Upload"
        And I switch to iframe "budget"
        Then I should see "There is something wrong with the structure of your budget"
