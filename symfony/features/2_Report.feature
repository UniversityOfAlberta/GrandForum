Feature: Reporting
    In order to report on the forum
    As a User
    I need to be able to access the relevant reports and edit the fields in the report

    Scenario: Validate Report XML
        Given I validate report xml

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
