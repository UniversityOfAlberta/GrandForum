.. index:: single: Project Evolution

Project Evolution
=============

The purpose for Project Evolution is to track the evolution of a
project. There are 3 types of projects, and 3 types of status.

**Types**

-  Research
-  Administrative
-  Strategic

**Status**

-  Proposed
-  Active
-  Ended

A project is not only defined by it’s name, but rather the progression
of it’s names. For example, a project could start off by being called
PROJECT and later become PROJECT\_B, and both are technically the same
project despite have different entries in the database. This makes it
easier to allow things like Products and project membership to still
reference old versions of projects, and still display with the most
recent version of the project.

There are 4 types of actions which can occur during a project’s
lifetime:

-  CREATE
    -  Initializes the project with its first revision. last\_id, and
       project\_id are set to -1. new\_id is set to the id of the project.
-  EVOLVE
    -  Changes the project from one to another (ie. PROJECTA -> PROJECTB).
       The project’s name can remain the same(ie. PROJECTA -> PROJECTA). The
       status and type can change. last\_id is set to the previous
       evolution\_id. project\_id is set to the previous id of the project.
       new\_id is set to the new id of the project.
-  MERGE
    -  This is a special case of EVOLVE, except that there are multiple
       parents.
-  DELETE
    -  Sets a project as deleted (or ‘ended’ as it would appear on the
       forum). This does not mean that the project no longer exists, just
       that it is no longer active (so a soft delete). project\_id and
       new\_id are set to the id of the project. last\_id is set to the last
       evolution\_id.
       For each of these actions, there are at least one entry in
       grand\_project\_status and grand\_project\_descriptions, related by
       by evolution\_id and project\_id.

User Interface
--------------

The UI for making these changes is found at Special:ProjectEvolution,
and in general the UI should always be used for making these changes. If
the changes are done manually in the database, then it could go into an
unstable state. If for example, a project is created with an entry in
grand\_project and grand\_project\_evolution, but not it
grand\_project\_status or grand\_project\_description, then it will be
as if the project does not exist, or will exist in some partial state.
There are some checks when instantiating projects for these states,
however it may still be possible that project instantiation will fail if
there is something missing in the DB. Therefore it is best to always use
the UI for these changes.

Database ER Diagram
-------------------

.. image:: ../../images/evolution_er.png
