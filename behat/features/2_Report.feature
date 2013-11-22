Feature: Reporting

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
        
    Scenario: PNI attempts to view an HQP report
        Given I am logged in as "PNI.User1" using password "PNI.Pass1"
        When I go to "index.php/Special:Report?report=HQPReport"
        Then I should see "Permission error"
        
    Scenario: PNI (who is not a PL) attempts to view a PL report
        Given I am logged in as "PNI.User1" using password "PNI.Pass1"
        When I go to "index.php/Special:Report?report=ProjectFinalReport&project=Phase1Project1"
        Then I should see "Permission error"
    
    Scenario: PNI attempts to view their NI report
        Given I am logged in as "PNI.User1" using password "PNI.Pass1"
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
        
    Scenario: COPL attempts to view their COPL report
        Given I am logged in as "COPL.User1" using password "COPL.Pass1"
        When I go to "index.php/Special:Report?report=ProjectReport&project=Phase2Project1"
        Then I should not see "Permission error"

    Scenario: HQP edits and saves their report
        Given I am logged in as "HQP.User1" using password "HQP.Pass1"
        When I follow "My Reports"
        And I click by css "#HQPReport"
        And I wait until I see "Excellence of the Research Program: How my research contributes to the Network" up to "5000"
        And I click "Excellence of the Research Program: How my research contributes to the Network"
        And I fill in "HQPReport_Ia_head_person14_project0_milestone0_OR_person14_project0_milestone0_ProjectLimit_Ia_person14_project0_milestone0_projects_person14_project192_milestone0_Ia" with "lorem ipsum"
        And I press "Save"
        And I click "HQP Dashboard"
        And I wait until I see "Projects:" up to "5000"
        And I click by css "#HQPReport"
        And I wait until I see "Excellence of the Research Program: How my research contributes to the Network" up to "5000"
        And I click "Excellence of the Research Program: How my research contributes to the Network"
        Then I should see "lorem ipsum"

    Scenario: CNI Uploads a budget within limits (with BigBet Project)
        Given I am logged in as "CNI.User1" using password "CNI.Pass1"
        When I follow "My Reports"
        And I click "Budget Request"
        And I wait until I see "Budget Justification" up to "5000"
        And I switch to iframe "budget"
        And I attach the file "CNI.User1_valid.xls" to "budget"
        And I press "Upload"
        And I switch to iframe "budget"
        Then I should see "$85000"
        And I should not see "is greater than the maximum"
        
    Scenario: CNI Uploads a budget which is over the limits
        Given I am logged in as "CNI.User1" using password "CNI.Pass1"
        When I follow "My Reports"
        And I click "Budget Request"
        And I wait until I see "Budget Justification" up to "5000"
        And I switch to iframe "budget"
        And I attach the file "CNI.User1_invalid.xls" to "budget"
        And I press "Upload"
        And I switch to iframe "budget"
        Then I should see "$36000"
        And I should see "is greater than the maximum $35000"
        
    Scenario: CNI who is also a COPL Uploads a budget which is within limits (with BigBet Project)
        Given I am logged in as "CNICOPL.User1" using password "CNICOPL.Pass1"
        When I follow "My Reports"
        And I click "Budget Request"
        And I wait until I see "Budget Justification" up to "5000"
        And I switch to iframe "budget"
        And I attach the file "CNICOPL.User1_valid.xls" to "budget"
        And I press "Upload"
        And I switch to iframe "budget"
        Then I should see "$95000"
        And I should not see "is greater than the maximum"
        
    Scenario: CNI who is also a COPL Uploads a budget which is over the limits
        Given I am logged in as "CNICOPL.User1" using password "CNICOPL.Pass1"
        When I follow "My Reports"
        And I click "Budget Request"
        And I wait until I see "Budget Justification" up to "5000"
        And I switch to iframe "budget"
        And I attach the file "CNICOPL.User1_invalid.xls" to "budget"
        And I press "Upload"
        And I switch to iframe "budget"
        Then I should see "$46000"
        And I should see "is greater than the maximum $45000"
        
    Scenario: PNI Uploads a budget within limits (with BigBet Project)
        Given I am logged in as "PNI.User1" using password "PNI.Pass1"
        When I follow "My Reports"
        And I click "Budget Request"
        And I wait until I see "Budget Justification" up to "5000"
        And I switch to iframe "budget"
        And I attach the file "PNI.User1_valid.xls" to "budget"
        And I press "Upload"
        And I switch to iframe "budget"
        Then I should see "$115000"
        And I should not see "is greater than the maximum"
        
    Scenario: PNI Uploads a budget which is over the limits
        Given I am logged in as "PNI.User1" using password "PNI.Pass1"
        When I follow "My Reports"
        And I click "Budget Request"
        And I wait until I see "Budget Justification" up to "5000"
        And I switch to iframe "budget"
        And I attach the file "PNI.User1_invalid.xls" to "budget"
        And I press "Upload"
        And I switch to iframe "budget"
        Then I should see "$66000"
        And I should see "is greater than the maximum $65000"
        
    Scenario: PNI Uploads a budget which is over the limits
        Given I am logged in as "PNI.User1" using password "PNI.Pass1"
        When I follow "My Reports"
        And I click "Budget Request"
        And I wait until I see "Budget Justification" up to "5000"
        And I switch to iframe "budget"
        And I attach the file "PNI.User1_duplicate.xls" to "budget"
        And I press "Upload"
        And I switch to iframe "budget"
        Then I should see "has already been used in another column"
