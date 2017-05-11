.. index:: single: REST API

REST API
========

The Forum features a REST API to fetch and update various objects.  The url format is always prefixed with ``index.php?action=api.XXXXX`` where XXXXX is the name of the api.  whenever there is a :value in the end-point, it means that it will accept a parameter in that position.

Most of the apis have some level of access control, so certain actions will not be allowed depending on the current user's role, or will return a subset of the full result.

person/:id
----------

**Arguments**

    **:id**
        The id of the Person.  Multiple ids can be specified if separated by commas.

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
        The id of the Person.

**Actions**

    **GET**
        Returns an array of Person-Projects
        
    **POST**
        Adds the Person to a Project
        
person/:id/projects/:personProjectId
------------------------------------

**Arguments**

    **:id**
        The id of the Person.
    **:personProjectId**
        The id of the Person-Project relationship

**Actions**

    **GET**
        Returns the specified Person-Project

    **PUT**
        Updates the specified Person-Project

    **DELETE**
        Deletes the specified Person-Project
