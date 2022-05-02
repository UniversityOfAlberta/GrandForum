Feature: Products
    In order to manage products to the forum
    As a User
    I need to be able to view/add/edit/delete products

    Scenario: Adding a new Publication
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "Manage Products"
        And I press "Add Product"
        And I fill in "title" with "New Publication"
        And I fill in "description" with "This is a description"
        And I select "Publication" from "category"
        And I select "Proceedings Paper" from "type"
        And I click by css "#projects_Phase2Project1"
        And I press "Save Product"
        And I wait until I see "The Product has been saved sucessfully" up to "1000"
        Then I should see "The Product has been saved sucessfully"
        
    Scenario: Viewing list of Publications
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I go to "index.php/Special:Products#/Publication"
        And I wait until I see "Search:" up to "1000"
        Then I should see "New Publication"
        
    Scenario: Viewing list of Publications as guest
        Given I am on "index.php"
        And I go to "index.php/Special:Products#/Publication"
        And I wait until I see "Search:" up to "1000"
        Then I should see "No data available in table"
        
    Scenario: Viewing Publication as guest (should get error)
        Given I am on "index.php/Special:Products#/Publication/3"
        Then I should see "This Product does not exist"
        
    Scenario: Changing the permissions of a product
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I go to "index.php/Special:Products#/Publication"
        And I wait until I see "Search:" up to "1000"
        And I follow "New Publication"
        And I press "Edit Publication"
        And I wait until I see "Visibility" up to "1000"
        And I select "Public" from "access"
        And I press "Save Publication"
        Then I should see "New Publication"
        
    Scenario: Viewing list of Publications as guest
        Given I am on "index.php"
        And I go to "index.php/Special:Products#/Publication"
        And I wait until I see "Search:" up to "1000"
        Then I should see "New Publication"
        
    Scenario: Viewing Publication as guest (should get error)
        Given I am on "index.php/Special:Products#/Publication/3"
        Then I should see "New Publication"
    
    Scenario: Editing a Publication
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I go to "index.php/Special:Products#/Publication"
        And I wait until I see "Search:" up to "1000"
        And I follow "New Publication"
        And I press "Edit Publication"
        And I wait until I see "Authors" up to "1000"
        And I select "NI User2" from "rightauthors"
        And I press "<<"
        And fill in "description" with "This is an edited description"
        And I press "Save Publication"
        And I wait until I see "Edit Publication" up to "1000"
        Then I should see "This is an edited description"
        Then I should see "NI User2"
        
    Scenario: Deleting a Publication
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I go to "index.php/Special:Products#/Publication"
        And I wait until I see "Search:" up to "1000"
        And I follow "New Publication"
        And I accept confirmation dialogs
        And I press "Delete Publication"
        Then I should see "The Publication New Publication was deleted sucessfully"
        And I reload the page
        And I wait until I see "This publication has been deleted, and will not show up anywhere else on the forum" up to "1000"
        Then I should see "This publication has been deleted, and will not show up anywhere else on the forum"
        When I go to "index.php/Special:Products#/Publication"
        And I wait until I see "Search:" up to "1000"
        Then I should not see "New Publication"
        
    Scenario: Adding a new Publication with Tags
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "Manage Products"
        And I wait until I see "Add Product" up to "1000"
        And I press "Add Product"
        And I fill in "title" with "Publication with Tags"
        And I select "NI User2" from "rightauthors"
        And I press "<<"
        And I fill in TagIt "tags" with "Hello World"
        And I select "Publication" from "category"
        And I select "Proceedings Paper" from "type"
        And I press "Save Product"
        And I wait until I see "The Product has been saved sucessfully" up to "1000"
        Then I should see "The Product has been saved sucessfully"
        
    Scenario: Uploading a valid BibTeX
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "Manage Products"
        And I wait until I see "Import BibTeX" up to "1000"
        And I press "Import BibTeX"
        And I fill in "bibtex" with:
        """
        @inproceedings{Xing:2005:UAO:1101908.1101919,
         author = {Xing, Zhenchang and Stroulia, Eleni and User1, NI},
         title = {UMLDiff: An Algorithm for Object-oriented Design Differencing},
         booktitle = {Proceedings of the 20th IEEE/ACM International Conference on Automated Software Engineering},
         series = {ASE '05},
         year = {2005},
         isbn = {1-58113-993-4},
         location = {Long Beach, CA, USA},
         pages = {54--65},
         numpages = {12},
         url = {http://doi.acm.org/10.1145/1101908.1101919},
         doi = {10.1145/1101908.1101919},
         acmid = {1101919},
         publisher = {ACM},
         address = {New York, NY, USA},
         keywords = {design differencing, design mentoring, design understanding, structural evolution},
        }
        """
        And I click "Import"
        And I wait until I see "1 products were created/update" up to "1000"
        Then I should see "1 products were created/updated"
        
    Scenario: Uploading a duplicate BibTeX (with overwrite not checked)
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "Manage Products"
        And I wait until I see "Import BibTeX" up to "1000"
        And I press "Import BibTeX"
        And I fill in "bibtex" with:
        """
        @inproceedings{Xing:2005:UAO:1101908.1101919,
         author = {Xing, Zhenchang and Stroulia, Eleni and User1, NI},
         title = {UMLDiff: An Algorithm for Object-oriented Design Differencing},
         booktitle = {Proceedings of the 20th IEEE/ACM International Conference on Automated Software Engineering},
         abstract = {Hello World},
         series = {ASE '05},
         year = {2005},
         isbn = {1-58113-993-4},
         location = {Long Beach, CA, USA},
         pages = {54--65},
         numpages = {12},
         url = {http://doi.acm.org/10.1145/1101908.1101919},
         doi = {10.1145/1101908.1101919},
         acmid = {1101919},
         publisher = {ACM},
         address = {New York, NY, USA},
         keywords = {design differencing, design mentoring, design understanding, structural evolution},
        }
        """
        And I click "Import"
        And I wait until I see "1 products were ignored" up to "1000"
        Then I should see "1 products were ignored (probably duplicates)"
        
    Scenario: Uploading a duplicate BibTeX (with overwrite checked)
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "Manage Products"
        And I wait until I see "Import BibTeX" up to "1000"
        And I press "Import BibTeX"
        And I fill in "bibtex" with:
        """
        @inproceedings{Xing:2005:UAO:1101908.1101919,
         author = {Xing, Zhenchang and Stroulia, Eleni and User1, NI},
         title = {UMLDiff: An Algorithm for Object-oriented Design Differencing},
         booktitle = {Proceedings of the 20th IEEE/ACM International Conference on Automated Software Engineering},
         abstract = {Hello World},
         series = {ASE '05},
         year = {2005},
         isbn = {1-58113-993-4},
         location = {Long Beach, CA, USA},
         pages = {54--65},
         numpages = {12},
         url = {http://doi.acm.org/10.1145/1101908.1101919},
         doi = {10.1145/1101908.1101919},
         acmid = {1101919},
         publisher = {ACM},
         address = {New York, NY, USA},
         keywords = {design differencing, design mentoring, design understanding, structural evolution},
        }
        """
        And I check "bibtex_overwrite"
        And I click "Import"
        And I wait until I see "1 products were created/updated" up to "1000"
        Then I should see "1 products were created/updated"
        When I fill in "Search:" with "UMLDiff"
        When I click by css ".edit-icon"
        And I wait until I see "Hello World" up to "1000"
        Then I should see "Hello World"
        
    Scenario: Uploading an invalid BibTeX
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "Manage Products"
        And I wait until I see "Import BibTeX" up to "1000"
        And I press "Import BibTeX"
        And I fill in "bibtex" with:
        """
        @inproceedings{
         booktitle = {Proceedings of the 20th IEEE/ACM International Conference on Automated Software Engineering},
         series = {ASE '05},
         year = {2005},
         isbn = {1-58113-993-4},
         location = {Long Beach, CA, USA},
         pages = {54--65},
         numpages = {12},
         url = {http://doi.acm.org/10.1145/1101908.1101919},
         doi = {10.1145/1101908.1101919},
         acmid = {1101919},
         publisher = {ACM},
         address = {New York, NY, USA},
         keywords = {design differencing, design mentoring, design understanding, structural evolution},
        }
        """
        And I click "Import"
        And I wait until I see "A publication was missing a title" up to "1000"
        Then I should see "A publication was missing a title"
        
    Scenario: Uploading a BibTeX with capital letters 
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "Manage Products"
        And I wait until I see "Import BibTeX" up to "1000"
        And I press "Import BibTeX"
        And I fill in "bibtex" with:
        """
        @INPROCEEDINGS{Xing:2006:UAO:1101908.1101919,
         AUTHOR = {Xing, Zhenchang and Stroulia, Eleni and User1, NI},
         TITLE = {This has capital letters but should still work},
         BOOKTITLE = {Proceedings of the 20th IEEE/ACM International Conference on Automated Software Engineering},
         SERIES = {ASE '05},
         YEAR = {2006},
         ISBN = {1-58113-993-4},
         LOCATION = {Long Beach, CA, USA},
         PAGES = {54--65},
         NUMPAGES = {12},
         URL = {http://doi.acm.org/10.1145/1101908.1101919},
         DOI = {10.1145/1101908.1101919},
         ACMID = {1101919},
         PUBLISHER = {ACM},
         ADDRESS = {New York, NY, USA},
         KEYWORDS = {design differencing, design mentoring, design understanding, structural evolution},
        }
        """
        And I click "Import"
        And I wait until I see "1 products were created/updated" up to "1000"
        Then I should see "1 products were created/updated"
        
    Scenario: Uploading an empty BibTeX
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "Manage Products"
        And I wait until I see "Import BibTeX" up to "1000"
        And I press "Import BibTeX"
        And I fill in "bibtex" with ""
        And I click "Import"
        And I wait until I see "No BibTeX references were found" up to "1000"
        Then I should see "No BibTeX references were found"
        
    Scenario: Adding a new Publication (testing events leak bug)
        Given I am logged in as "NI.User1" using password "NI.Pass1"
        When I follow "Manage Products"
        And I wait until I see "Add Product" up to "1000"
        And I press "Add Product"
        And I fill in "title" with "Test1"
        And I select "NI User1" from "rightauthors"
        And I press "<<"
        And I select "Publication" from "category"
        And I select "Proceedings Paper" from "type"
        And I press "Save Product"
        And I wait until I see "The Product has been saved sucessfully" up to "1000"
        Then I should see "Test1"
        And I press "Add Product"
        And I fill in "title" with "Test2"
        And I select "NI User1" from "rightauthors"
        And I press "<<"
        And I select "Publication" from "category"
        And I select "Proceedings Paper" from "type"
        And I press "Save Product"
        And I wait until I see "The Product has been saved sucessfully" up to "1000"
        Then I should see "Test1"
        And I should see "Test2"
