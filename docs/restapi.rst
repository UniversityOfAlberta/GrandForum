.. index:: single: REST API

REST API
========

The Forum features a REST API to fetch and update various objects.  The url format is always prefixed with ``index.php?action=api.XXXXX`` where XXXXX is the name of the api.  whenever there is a :value in the end-point, it means that it will accept a parameter in that position.  The apis were designed to be used with Backbone.js but should be able to be used for other frameworks/applications.

Most of the apis have some level of access control, so certain actions will not be allowed depending on the current user's role, or will return a subset of the full result.

person/:id
----------

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
-------------------

**Arguments**

    **:id**
        The id of the Person

**Actions**

    **GET**
        Returns an array of Person-Projects
    **POST**
        Adds the Person to a Project
        
person/:id/projects/:personProjectId
------------------------------------

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
-----------------------

**Arguments**

    **:id**
        The id of the Person

**Actions**

    **GET**
        Returns all of the universities for the Person
    **POST**
        Adds the Person to a University
        
person/:id/universities/:personUniversityId
-------------------------------------------

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
----------------

**Arguments**

    **:id**
        The id of the Person

**Actions**

    **GET**
        Returns a simplified array of this Person's Roles
        
person/:id/relations
--------------------

**Arguments**

    **:id**
        The id of the Person

**Actions**

    **GET**
        Returns all of the relationships for the Person
    **POST**
        Adds the Person to a Relationship
        
person/:id/relations/:relId
---------------------------

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
-------------------

**Arguments**

    **:id**
        The id of the Person

**Actions**

    **GET**
        Returns a list of non-private Products authored by this Person
        
person/:id/products/private
---------------------------

**Arguments**

    **:id**
        The id of the Person

**Actions**

    **GET**
        Returns a list of private Products authored by this Person
        
person/:id/products/all
-----------------------

**Arguments**

    **:id**
        The id of the Person

**Actions**

    **GET**
        Returns a list of all Products authored by this Person
        
person/:id/products/:productId
------------------------------

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
------------------------

**Arguments**

    **:id**
        The id of the Person

**Actions**

    **GET**
        Returns a list of contributions that involve this Person
        
person/:id/allocations
----------------------

**Arguments**

    **:id**
        The id of the Person

**Actions**

    **GET**
        Returns the amount of allocations per year, per project
        
personRoleString/:id
--------------------

**Arguments**

    **:id**
        The id of the Person

**Actions**

    **GET**
        Returns a simplified string version of this Person's current roles
        
people
------

**Actions**

    **GET**
        Returns a list of all People
        
people/managed
--------------

**Actions**

    **GET**
        Returns a list of all People that the current user manages (either implicitely or explicitely)
        
people/:role
------------

**Arguments**

    **:role**
        The type of Role to filter by.  Multiple Roles can be specified if separated by commas.  Using 'all' for the Role will include all roles.

**Actions**

    **GET**
        Returns a list of all People that belong to the specified Role(s)
        
people/:role/:university
------------------------

**Arguments**

    **:role**
        The type of Role to filter by.  Multiple Roles can be specified if separated by commas.  Using 'all' for the Role will include all roles.
    **:university**
        The name of the University to filter by

**Actions**

    **GET**
        Returns a list of all People that belong to the specified Role(s), and are from the specified University
        
role
----

**Actions**

    **GET**
        Returns a list of roles that can be used by the Forum
    **POST**
        Adds a Person to a role
        
role/:id
--------

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
        

