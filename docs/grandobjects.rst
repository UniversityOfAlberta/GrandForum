.. index:: single: GrandObjects

GrandObjects
============

Most of the classes used by the forum are contained in this extension.  These classes will fetch data from the database and construct objects.

Classes
-------

.. toctree::
   :maxdepth: 1
   
   activity
   backbonemodel
   contribution
   milestone
   partner
   pdf
   person
   product
   project
   relationship
   role
   theme
   wiki
   
API
---

Contains the REST API files used for Backbone Models.  See `REST API <restapi.html>`_

BackboneModels
--------------

Contains the Javascript files used for Backbone Models.
   
ProductStructures
-----------------

ProductStructures is a directory in the GrandObjects extension which contains XML files for each of the possible network instances.  The file should be named exactly the same as the networkName in the configuration.  The files describe what is the structure of the Products for each respective instance.  A Product Structure contains a top level grouping called 'category' and a second layer grouping called 'type'.  An example Category will look like this:

.. code-block:: xml

    <Publications category="Publication">
        ...
    </Publications>
    
And inside that element will be the types:

.. code-block:: xml

    <Publication type="Journal Paper" ccv_id="9a34d6b273914f18b2273e8de7c48fd6" ccv_name="Journal Articles" status="Submitted|Revision Requested|Accepted|In Press|Published|Rejected">
        <title ccv_id="f3fd4878d47c4e83aef6959620ba4870" ccv_name="Article Title"></title>
        <statuses ccv_id="3b56e4362d6a495aa5d22a1de5914741" ccv_name="Publishing Status">
            <status lov_id="00000000000000000000000100001700" lov_name="Submitted">Submitted</status>
            <status lov_id="00000000000000000000000100001701" lov_name="Revision Requested">Revision Requested</status>
            <status lov_id="00000000000000000000000100001702" lov_name="Accepted">Accepted</status>
            <status lov_id="00000000000000000000000100001703" lov_name="In Press">In Press</status>
            <status lov_id="00000000000000000000000100001704" lov_name="Published">Published</status>
            <status>Rejected</status>
        </statuses>
        <data>
            <field ccv_id="5c04ea4dae464499807d0b40b4cad049" ccv_name="Journal" ccvtk="journal" bibtex="journal" label="Journal" type="String">published_in</field>
            <field ccv_id="5c04ea4dae464499807d0b40b4cad049" ccv_name="Journal" label="Journal" type="String" hidden="true">journal_title</field>
            <field ccv_id="0a826c656ff34e579dfcbfb373771260" ccv_name="Volume" ccvtk="volume" bibtex="volume" label="Volume" type="String">volume</field>
            <field ccv_id="cc1d9e14945b4e8496641dbe22b3448a" ccv_name="Issue" ccvtk="number" bibtex="number" label="Issue" type="String">number</field>
            <field ccv_id="00ba1799ece344dc8d0779a3f05a4df8" ccv_name="Page Range" ccvtk="pages" bibtex="pages" label="Page Range" type="String">pages</field>
            <field ccv_id="4ad593960aba4a21bf154fa8daf37f9f" ccv_name="Publisher" ccvtk="publisher" bibtex="publisher" label="Publisher" type="String">publisher</field>
            <field ccv_id="707a6e0ca58341a5a82fb923b2842530" ccv_name="Editors" ccvtk="editors" label="Editors" type="String">editors</field>
            <field bibtex="isbn" label="ISBN" type="String">isbn</field>
            <field bibtex="issn" label="ISSN" type="String">issn</field>
            <field bibtex="doi" label="DOI" type="DOI">doi</field>
            <field ccv_id="478545acac5340c0a73b7e0d2a4bee06" ccv_name="URL" ccvtk="url" bibtex="url" label="URL" type="URL">url</field>
            <field ccv_id="2089ff1a86844b6c9a10fc63469f9a9d" ccv_name="Refereed?" ccvtk="peer_reviewed" label="Peer Reviewed" type="Radio" options="Yes|No">peer_reviewed</field>
        </data>
        <date ccv_id="6fafe258e19e49a7884428cb49d75424" ccv_name="Date"></date>
        <authors ccv_id="bc3b428d99384b04bb749311bb804e1d" ccv_name="Authors"></authors>
        <description ccv_id="1167905d079c4400ae7a4a76a203a445" ccv_name="Description / Contribution Value"></description>
    </Publication>
    
Not all of the types need to have that many attributes, for example the ccv attributes are only needed for the CCV importing, so any types which don't have a corresponding CCV item, or if you don't need CCV Importing, then you can ignore those attributes.  The categories & types will dictate what fields will show up when editing Products.
