Feature: Reporting
    In order to report on the forum
    As a User
    I need to be able to access the relevant reports and edit the fields in the report

    Scenario: Validate Report XML
        Given I validate report xml

    Scenario: Checking Lazy ReportItemSet
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I go to "index.php/Special:Report?report=Test"
        Then The load time should be no greater than "1000"
        
    Scenario: Testing If/IfElse/Else ReportItemSet
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I go to "index.php/Special:Report?report=Report&section=Section+4"
        Then I should see "A0"
        And I should not see "B0"
        And I should not see "A1"
        And I should see "B1"
        And I should not see "A2"
        And I should see "B2"
        And I should not see "C2"
        And I should not see "A3"
        And I should not see "B3"
        And I should see "C3"
        
    Scenario: Testing ReportItemSets with the same id
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I go to "index.php/Special:Report?report=Report&section=Section+5"
        Then I should see "Hello World"
        And I should not see "World World"

    Scenario: HQP attempts to view an NI-only report
        Given I am logged in as "HQP.User1" using password "HQP.Pass1"
        When I go to "index.php/Special:Report?report=Report"
        Then I should see "Permission error"
        
    Scenario: NI edits a field in the report
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I go to "index.php/Special:Report?report=Report"
        Then I should see "Section 1"
        And I fill in "Section1_text1" with "Filled in Text"
        And I press "Save"
        And I wait "1000"
        And I go to "index.php/Special:Report?report=Report"
        Then I should see "Filled in Text"
        
    Scenario: Word Count with hyphens
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I go to "index.php/Special:Report?report=Report"
        And I fill in TinyMCE "Section1_textarea" with "Hello-World"
        Then I should see "1 words"
        When I fill in TinyMCE "Section1_textarea" with "Hello-World two three four five six seven eight nine ten"
        Then I should see "10 words"
        
    Scenario: Checking for edge case html entities 1
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I go to "index.php/Special:Report?report=Report"
        And I click "Section 2"
        And I wait "1000"
        And I fill in TinyMCE "Section2_textarea" with "Hello &11;"
        And I press "Save"
        And I wait "1000"
        And I go to "index.php/Special:Report?report=Report&section=Section+2"
        Then I should see "Hello &11;"
        When I go to "index.php/Special:Report?report=Report"
        And I click "Section 2"
        And I wait "1000"
        And I fill in TinyMCE "Section2_textarea" with "Hello &11; World"
        And I press "Save"
        And I go to "index.php/Special:Report?report=Report&section=Section+2"
        Then I should see "Hello &11; World"
        
    Scenario: Checking for edge case html entities 2
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I go to "index.php/Special:Report?report=Report"
        And I click "Section 2"
        And I wait "1000"
        And I fill in TinyMCE "Section2_textarea" with "Hello &asdf;"
        And I press "Save"
        And I wait "1000"
        And I go to "index.php/Special:Report?report=Report&section=Section+2"
        Then I should see "Hello &asdf;"
        When I go to "index.php/Special:Report?report=Report"
        And I click "Section 2"
        And I wait "1000"
        And I fill in TinyMCE "Section2_textarea" with "Hello &asdf; World"
        And I press "Save"
        And I go to "index.php/Special:Report?report=Report&section=Section+2"
        Then I should see "Hello &asdf; World"
        
    Scenario: Checking for edge case html entities 3
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I go to "index.php/Special:Report?report=Report"
        And I click "Section 3"
        And I wait "1000"
        And I fill in "Section3_textarea" with "<p>Hello &11;</p>"
        And I press "Save"
        And I wait "1000"
        And I go to "index.php/Special:Report?report=Report&section=Section+3"
        Then I should see "Hello &11;"
        When I go to "index.php/Special:Report?report=Report"
        And I click "Section 3"
        And I wait "1000"
        And I fill in "Section3_textarea" with "<p>Hello &11; <b>World</b></p>"
        And I press "Save"
        And I go to "index.php/Special:Report?report=Report&section=Section+3"
        Then I should see "Hello &11; World"
        
    Scenario: Checking for edge case html entities 4
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I go to "index.php/Special:Report?report=Report"
        And I click "Section 3"
        And I wait "1000"
        And I fill in "Section3_textarea" with "<p>Hello &asdf;</p>"
        And I press "Save"
        And I wait "1000"
        And I go to "index.php/Special:Report?report=Report&section=Section+3"
        Then I should see "Hello &asdf;"
        When I go to "index.php/Special:Report?report=Report"
        And I click "Section 3"
        And I wait "1000"
        And I fill in "Section3_textarea" with "<p>Hello &asdf; <b>World</b></p>"
        And I press "Save"
        And I go to "index.php/Special:Report?report=Report&section=Section+3"
        Then I should see "Hello &asdf; World"
        
    Scenario: NI generates and submits the report
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I go to "index.php/Special:Report?report=Report"
        And I click "Submit"
        And I wait "1000"
        Then I should see "Generate a new Report PDF for submission"
        And I press "Generate Report PDF"
        And I wait "2000"
        Then I should see "PDF Generated Successfully"
        And I click by css "#submitCheck"
        And I press "Submit Report PDF"
        And I wait "1000"
        Then I should see "Generated/Submitted"
