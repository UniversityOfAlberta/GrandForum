.. index:: single: REST API

REST API
========

The Forum features a REST API to fetch and update various objects.  The url format is always prefixed with ``index.php?action=api.XXXXX`` where XXXXX is the name of the api.  whenever there is a :value in the end-point, it means that it will accept a parameter in that position.  The apis were designed to be used with Backbone.js but should be able to be used for other frameworks/applications.

Most of the apis have some level of access control, so certain actions will not be allowed depending on the current user's role, or will return a subset of the full result.

People
------

person/:id
~~~~~~~~~~

    **Arguments**
        **:id**
            The id of the Person.  Multiple ids can be specified if separated by commas
    **Actions**
        **GET**
            Returns a Person, or an array of People if multiple ids are specified.
        **POST**
            Creates a new the Person
        **PUT**
            Updates the Person
        **DELETE**
            Deletes the Person

person/:id/projects
~~~~~~~~~~~~~~~~~~~

    **Arguments**
        **:id**
            The id of the Person
    **Actions**
        **GET**
            Returns an array of Person-Projects
        **POST**
            Adds the Person to a Project
        
person/:id/projects/:personProjectId
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    **Arguments**
        **:id**
            The id of the Person
        **:personProjectId**
            The id of the Person-Project relationship
    **Actions**
        **GET**
            Returns the specified Person-Project
        **PUT**
            Updates the specified Person-Project
        **DELETE**
            Deletes the specified Person-Project
        
person/:id/universities
~~~~~~~~~~~~~~~~~~~~~~~

    **Arguments**
        **:id**
            The id of the Person
    **Actions**
        **GET**
            Returns all of the universities for the Person
        **POST**
            Adds the Person to a University
        
person/:id/universities/:personUniversityId
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    **Arguments**
        **:id**
            The id of the Person
        **:personUniversityId**
            The id of the Person-University relationship
    **Actions**
        **GET**
            Returns the specified Person-University
        **PUT**
            Updates the specified Person-University
        **DELETE**
            Deletes the specified Person-University
        
person/:id/roles
~~~~~~~~~~~~~~~~

    **Arguments**
        **:id**
            The id of the Person
    **Actions**
        **GET**
            Returns a simplified array of this Person's Roles
        
person/:id/relations
~~~~~~~~~~~~~~~~~~~~

    **Arguments**
        **:id**
            The id of the Person
    **Actions**
        **GET**
            Returns all of the relationships for the Person
        **POST**
            Adds the Person to a Relationship
        
person/:id/relations/:relId
~~~~~~~~~~~~~~~~~~~~~~~~~~~

    **Arguments**
        **:id**
            The id of the Person
        **:relId**
            The id of the Relationship
    **Actions**
        **PUT**
            Updates the specified Relationship
        **DELETE**
            Deletes the specified Relationship
        
person/:id/products
~~~~~~~~~~~~~~~~~~~

    **Arguments**
        **:id**
            The id of the Person
    **Actions**
        **GET**
            Returns a list of non-private Products authored by this Person
        
person/:id/products/private
~~~~~~~~~~~~~~~~~~~~~~~~~~~

    **Arguments**
        **:id**
            The id of the Person
    **Actions**
        **GET**
            Returns a list of private Products authored by this Person
        
person/:id/products/all
~~~~~~~~~~~~~~~~~~~~~~~

    **Arguments**
        **:id**
            The id of the Person
    **Actions**
        **GET**
            Returns a list of all Products authored by this Person
        
person/:id/products/:productId
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    **Arguments**
        **:id**
            The id of the Person
        **:productId**
            The id of the Product
    **Actions**
        **GET**
            Returns the Person-Product
        **POST**
            Adds the Person to the given Product
        **DELETE**
            Removes the Person from the given Product
        
person/:id/contributions
~~~~~~~~~~~~~~~~~~~~~~~~

    **Arguments**
        **:id**
            The id of the Person
    **Actions**
        **GET**
            Returns a list of contributions that involve this Person
        
person/:id/allocations
~~~~~~~~~~~~~~~~~~~~~~

    **Arguments**
        **:id**
            The id of the Person
    **Actions**
        **GET**
            Returns the amount of allocations per year, per project
        
personRoleString/:id
~~~~~~~~~~~~~~~~~~~~

    **Arguments**
        **:id**
            The id of the Person
    **Actions**
        **GET**
            Returns a simplified string version of this Person's current roles
        
people
~~~~~~

    **Actions**
        **GET**
            Returns a list of all People
        
people/managed
~~~~~~~~~~~~~~

    **Actions**
        **GET**
            Returns a list of all People that the current user manages (either implicitely or explicitely)
        
people/:role
~~~~~~~~~~~~

    **Arguments**
        **:role**
            The type of Role to filter by.  Multiple Roles can be specified if separated by commas.  Using 'all' for the Role will include all roles.
    **Actions**
        **GET**
            Returns a list of all People that belong to the specified Role(s)
        
people/:role/:university
~~~~~~~~~~~~~~~~~~~~~~~~

    **Arguments**
        **:role**
            The type of Role to filter by.  Multiple Roles can be specified if separated by commas.  Using 'all' for the Role will include all roles.
        **:university**
            The name of the University to filter by
    **Actions**
        **GET**
            Returns a list of all People that belong to the specified Role(s), and are from the specified University
            
people/:role/:university/:department
~~~~~~~~~~~~~~~~~~~~~~~~

    **Arguments**
        **:role**
            The type of Role to filter by.  Multiple Roles can be specified if separated by commas.  Using 'all' for the Role will include all roles.
        **:university**
            The name of the University to filter by
        **:department**
            The name of the department to filter by
    **Actions**
        **GET**
            Returns a list of all People that belong to the specified Role(s), and are from the specified University and Department

-----

Roles
-----
        
role
~~~~

    **Actions**
        **GET**
            Returns a list of roles that can be used by the Forum
        **POST**
            Adds a Person to a role
        
role/:id
~~~~~~~~

    **Arguments**
        **:id**
            The id of the Role
    **Actions**
        **GET**
            Returns the specified Role
        **PUT**
            Updates the specified Role
        **DELETE**
            Deletes the specified Role

-----

Projects
--------
  
project
~~~~~~~

    **Actions**
        **GET**
            Returns a list of all Projects
        
project/:id
~~~~~~~~~~~

    **Arguments**
        **:id**
            The id of the Project
    **Actions**
        **GET**
            Returns the specified Project
        
project/:id/members
~~~~~~~~~~~~~~~~~~~

    **Arguments**
        **:id**
            The id or name of the Project.
    **Actions**
        **GET**
            Returns a list of the People in the specified Project
        
project/:id/members/:role
~~~~~~~~~~~~~~~~~~~~~~~~~

    **Arguments**
        **:id**
            The id or name of the Project
        **:role**
            The type of Role to filter by.  Multiple Roles can be specified if separated by commas.
    **Actions**
        **GET**
            Returns a list of the People in the specified Project
        
project/:id/contributions
~~~~~~~~~~~~~~~~~~~~~~~~~

    **Arguments**
        **:id**
            The id or name of the Project
    **Actions**
        **GET**
            Returns a list Contributions associated with the specified Project
        
project/:id/allocations
~~~~~~~~~~~~~~~~~~~~~~~

    **Arguments**
        **:id**
            The id or name of the Project
    **Actions**
        **GET**
            Returns the amount of allocations per year
        
project/:id/products
~~~~~~~~~~~~~~~~~~~~

    **Arguments**
        **:id**
            The id of the Project
    **Actions**
        **GET**
            Returns a simplified list of Products associated with the specified Project
        **POST**
            Associates a product with the specified Project
        
project/:id/products/:productId
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    **Arguments**
        **:id**
            The id of the Project
        **::productId**
            The id of the Product
    **Actions**
        **GET**
            Returns a simplified Product specified by the productId
        **DELETE**
            Removes the Project from the specified Product

-----

Freeze
------
  
freeze
~~~~~~

    **Actions**
        **GET**
            Returns a list of Frozen features
        **POST**
            Adds a new Frozen Project/Feature pair
        
freeze/:id
~~~~~~~~~~

    **Arguments**
        **:id**
            The id of the Freeze feature
    **Actions**
        **GET**
            Returns the specified Frozen feature
        **DELETE**
            Removes the specified Frozen Project/Feature pair

-----

Products
--------
     
product
~~~~~~~

    **Actions**
        **GET**
            Returns a list of all Products in the Forum.

            Be aware that this request might fail if there are a large number of Products in the Forum.  Look to use one of the more restrictive API
        **POST**
            Creates a new Product
        
product/:projectId/:category/:grand
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    **Arguments**
        **:projectId**
            The id of a Project to filter by.  Multiple ids can be specified if separated by a comma.
        **:category**
            The category of the Product to filter by
        **:grand**
            Can be 'grand', 'nonGrand', or 'both'.  'grand'.
            
            **grand**
                Include Products which are associated with at least 1 Project
            **nonGrand**
                Include Products which are not associated with any Projects
            **both**
                Include Products regardless of whether they are associated with any Projects

    **Actions**
        **GET**
            Returns a list of filtered Products in the Forum.
        
product/:projectId/:category/:grand/:start/:count
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    **Arguments**
        **:projectId**
            The id of a Project to filter by.  Multiple ids can be specified if separated by a comma.
        **:category**
            The category of the Product to filter by
        **:grand**
            Can be 'grand', 'nonGrand', or 'both'.  'grand'.
            
            **grand**
                Include Products which are associated with at least 1 Project
            **nonGrand**
                Include Products which are not associated with any Projects
            **both**
                Include Products regardless of whether they are associated with any Projects
        **:start**
            The result index to start with (useful for pagination/getting the result over multile requests)
        **:count**
            The number of results to include in the result
    **Actions**
        **GET**
            Returns a list of filtered Products in the Forum.
        
product/:id
~~~~~~~~~~~

    **Arguments**
        **:id**
            The id of the Product.  Multiple ids can be specified if separated by a comma.
    **Actions**
        **GET**
            Returns the specified Product(s)
        **PUT**
            Updates the specified Product
        **DELETE**
            Deletes the specified Product.  If the Product was 'private' the deletion will be permanent.
        
product/:id/citation
~~~~~~~~~~~~~~~~~~~~

    **Arguments**
        **:id**
            The id of the Product
    **Actions**
        **GET**
            Returns the citation of the Product
        
product/:id/authors
~~~~~~~~~~~~~~~~~~~

    **Arguments**
        **:id**
            The id of the Product
    **Actions**
        **GET**
            Returns a simplified list of People who authored this Product
        **POST**
            Adds a Person as an author to the specified Product

product/:id/authors/:personId
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    **Arguments**
        **:id**
            The id of the Product
        **:personId**
            The id of the author
    **Actions**
        **GET**
            Returns a simplified Person-Product
        **DELETE**
            Removes the Person from the author list of this Product
        
product/:id/projects
~~~~~~~~~~~~~~~~~~~~

    **Arguments**
        **:id**
            The id of the Product
    **Actions**
        **GET**
            Returns a list of Projects associated with this Product
        **POST**
            Associates a Project with this Product
        
product/:id/projects/:projectId
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    **Arguments**
        **:id**
            The id of the Product
        **:projectId**
            The id of the Project
    **Actions**
        **GET**
            Returns the specified Project-Product associated with this Product
        **DELETE**
            Remove the Project from this Product
        
product/tags
~~~~~~~~~~~~

    **Actions**
        **GET**
            Returns a list of all Product tags
        
productDuplicates/:category/:title/:id
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    **Arguments**
        **:category**
            The category of the Product
        **:title**
            The Title of the Product to check against
        **:id**
            The id of the Product to check duplicates against
    **Actions**
        **GET**
            Returns a list of Products that might be duplicates of the specified Product

-----

Bibliographies
--------------

bibliography
~~~~~~~~~~~~

    **Actions**
        **GET**
            Returns a list of all Bibliographies
        **POST**
            Creates a new Bibliography
        
bibliography/:id
~~~~~~~~~~~~~~~~

    **Arguments**
        **:id**
            The id of the Bibliography
    **Actions**
        **GET**
            Returns the specified Bibliography
        **PUT**
            Updates the specified Bibliography
        **DELETE**
            Deletes the specified Bibliography
        
bibliography/person/:person_id
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    **Arguments**
        **:person_id**
            The id of the Person that the Bibliographies belong to
    **Actions**
        **GET**
            Returns a list of Bibliographies that belong to the specified Person

-----

Universities
------------

university
~~~~~~~~~~

    **Actions**
        **GET**
            Returns a list of all Universities
            
university/:id
~~~~~~~~~~~~~~

    **Arguments**
        **:id**
            The id of the University
    **Actions**
        **GET**
            Returns the specified University
            
departments
~~~~~~~~~~~

    **Actions**
        **GET**
            Returns a list of departments (Strings)
          
-----
            
Wiki Pages
----------

wikipage/:id
~~~~~~~~~~~~

    **Arguments**
        **:id**
            The id of the WikiPage
    **Actions**
        **GET**
            Returns the specified WikiPage
            
wikipage/:namespace/:title
~~~~~~~~~~~~~~~~~~~~~~~~~~

    **Arguments**
        **:namespace**
            The namespace of the WikiPage
        **:id**
            The title of the WikiPage
    **Actions**
        **GET**
            Returns the specified WikiPage

-----

Message Boards
--------------

board/:id
~~~~~~~~~

    **Arguments**
        **:id**
            The id of the Board
    **Actions**
        **GET**
            Returns the specified Board
            
boards
~~~~~~

    **Actions**
        **GET**
            Returns a list of all Boards
            
thread
~~~~~~

    **Actions**
        **POST**
            Creates a new Thread
            
thread/:id
~~~~~~~~~~

    **Arguments**
        **:id**
            The id of the Thread
    **Actions**
        **GET**
            Returns the specified Thread
        **PUT**
            Updates the specified Thread
        **DELETE**
            Deletes the specified Thread
            
threads/:board
~~~~~~~~~~~~~~

    **Arguments**
        **:board**
            The id of the Board
    **Actions**
        **GET**
            Returns a list of all Threads which belong to the specified Board
            
threads/:board/:search
~~~~~~~~~~~~~~~~~~~~~~

    **Arguments**
        **:board**
            The id of the Board
        **:search**
            A search string 
    **Actions**
        **GET**
            Returns a list of all Threads which belong to the specified Board and match the specified search string based on a full text search
            
post
~~~~

    **Actions**
        **POST**
            Creates a new Post
            
post/:id
~~~~~~~~

    **Arguments**
        **:id**
            The id of the Post
    **Actions**
        **GET**
            Returns the specified Post
        **PUT**
            Updates the specified Post
        **DELETE**
            Deletes the specified Post
   
-----
    
PDFs
----

pdf/:id
~~~~~~~

    **Arguments**
        **:id**
            The id (token) of the PDF
    **Actions**
        **GET**
            Returns the specified PDF
            
-----

Mailing Lists
-------------

mailingList
~~~~~~~~~~~

    **Actions**
        **GET**
            Returns a list of all MailingLists
            
mailingList/:listId
~~~~~~~~~~~~~~~~~~~

    **Arguments**
        **:listId**
            The id of the MailingList
    **Actions**
        **GET**
            Returns the specified MailingList
            
mailingList/:listId/rules
~~~~~~~~~~~~~~~~~~~~~~~~~

    **Arguments**
        **:listId**
            The id of the MailingList
    **Actions**
        **GET**
            Returns a list of the MailingListRules for the specified MailingList
        **POST**
            Creates a new MailingListRule for the specified MailingList
            
mailingList/:listId/rules/:ruleId
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    **Arguments**
        **:listId**
            The id of the MailingList
        **:ruleId**
            The id of the MailingListRule
    **Actions**
        **GET**
            Returns the specified MailingListRule
        **PUT**
            Updates the specified MailingListRule
        **DELETE**
            Deletes the specified MailingListRule

-----

Search
------

globalSearch/:group/:search
~~~~~~~~~~~~~~~~~~~~~~~~~~~

    **Arguments**
        **:group**
            The type of search to do.  Can be 'people', 'experts', 'projects', 'products', or 'wikipage'
        **:search**
            The search string
    **Actions**
        **GET**
            Returns a list of search results from the given search string
