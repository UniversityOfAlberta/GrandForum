.. index:: single: Report

Report
======

The Report is a very important feature of the Forum.  NCEs require periodic reviews of projects in order to evaluate their progress (both for NCE reporting purposes, and internal reviewing).  Reports can also be used for other purposes, like applications and surveys.  Reporting elements are stored in Blobs in the database.  Each blob is indexed by ``year``, ``user_id``, ``proj_id``, ``rp_type``, ``rp_section``, ``rp_item``, ``rp_subitem``.

UML Diagram
-----------

.. image:: _images/report/ReportUML.png

This diagram is a little out of date, but most of what is shown still exists.

XML Structure
-------------

Reports are defined in xml files.  The structure is fairly simple, however the number of possible combination of elements and their attributes can make it quite complex.  Here is a super simple example of what the structure typically looks like:

.. code-block:: xml

    <?xml version="1.0" encoding="UTF-8" ?>
    <Report name="" reportType="" pdfType="" pdfFiles="" ajax="true">
        <Permissions>
            <Role role="">
                <SectionPermission id="" permissions="" />
                <SectionPermission id="" permissions="" />
                <SectionPermission id="" permissions="" />
            </Role>
        </Permissions>
        <ReportSection id="" tooltip="" name="" title="" blobSection="" type="">
            <Instructions>
                <![CDATA[
                    
                ]]> 
            </Instructions>
            <ReportItemSet id="" type="">
                <ReportItem id="" type="" blobType="" blobItem="" blobSubItem="">
                    <![CDATA[
                        {$item}
                    ]]> 
                </ReportItem>
            </ReportItemSet>
        <ReportSection>
    </Report>

The following are explanations on what each element does.  The list is not exhaustive.

**<Report>**
    This is the root element of the report.  It contains information about the report type (for the blob indices), what the PDF XML file is, and the name of the report.

    **<Permissions>**
        Defines who should have access to the report.
        
        **<Role>**
            A role which is allowed access.  Use the ``role`` attribute to define the role.  ``start`` and ``end`` are optional attributes which can be specified to define a date range for the role
            
            **<SectionPermission>**
                A permission for a specific section of the report.  The ``id`` attribute references the id of the ReportSection, and the ``permissions`` attribute says which permissions the section has for that role (can be either 'r', 'w', 'rw' or '-')
            
        **<Project>**
            A project which the report is able to be associated with.
        
    **<ReportSection>**
        A section of a report.  The type of Report Section can be specified with ``type``.  The ``blobSection`` is used to specify where the blobs in the report section will be stored.
        
        **<Instructions>**
            Any instructions to appear on the side instructions panel will co in the CDATA of this element.  If the instructions element is excluded, then no side panel will show up.
            
        **<ReportItemSet>**
            Any time where a section of the report needs to be repeated multiple times or be wrapped by some other element, then the ReportItemSet should be used.  ``type`` will specify the type of ReportItemSet to use.  ReportItemSets can be nested.
            
        **<ReportItem>**
            A widget, field or just text to actually be rendered in a report should use a ReportItem.  ReportItems can go either as a child of the ReportSection, or a child of a ReportItemSet.  There needs to be an ``id`` attribute set otherwise saving of data will not work properly.  Also the ``blobType``, ``blobItem`` and optionally ``blobSubItem`` should be set to specify where the data for that element will be stored.  Each ReportItem will have a set of additional attributes that can be used.  In the CDATA of this element, the appearance of the ReportItem should be written using html.  The ``{$item}`` is a special token used to place the actual widget in the html.

Variables & Functions
---------------------

The Report supports simple variable and functions replaces.  Variables will look like ``{$user_name}`` and will be replaced with context relevant text.  The ReportItemCallback.php file contains a list of all of the variables.

Function calls can also be made to make the reports more dynamic.  A function call will look like ``{concat(STR1,STR2)}`` and will be replaced by the return value of the function call.  Functions behave similarily to a functional language like scheme.  So a nested function call might look like:

.. code-block:: xml

    {concat(STR1,
        {concat(STR2,
            {concat(STR3,STR4)}
        )}
    )}
    
State can also be stored by calling the ``{set(var,val)}`` function.  The state is stored in the parent ReportItemSet or ReportSection depending on which one is closest to the ReportItem.  The values can be retrieved using the ``{get(var)}`` function.  For example:

.. code-block:: xml

    {set(var1, Hello World)}
    
    {get(var1)}
    
It should be noted that strings are not wrapped in quotes or anything like that, and are instead interpreted literally.  Arguments for functions are separated by a comma.

Conditionals
------------

A special type of ReportItem & ReportItemSet are conditionals.  If, ElseIf, Else and For are supported to create more dynamic reporting structures.  Here are some examples:

.. code-block:: xml

    <If if="{condition}">
        ...
    </If>
    <ElseIf if="{condition}">
        ...
    </ElseIf>
    <Else>
        ...
    </Else>
    
.. code-block:: xml

    <For from="0" to="100">
        ...
    </For>
    
If, ElseIf and Else can be either a ReportItem or a ReportItemSet.  For is only a ReportItemSet.

Encryption
----------

ReportItems aswell as entire Reports can be encrypted by adding the encrypt="true" to the element.  If this is done to a PDF XML, it will encrypt the pdf aswell.

Some setup needs to be done though.  You will need to run the keygen.php script from maintenance and save to a file:

.. code-block:: sh

    php keygen.php > encrypt.key
    
Then you should move it to a more secure location, like /etc/pki/tls/private/ and also make sure the file permissions are set to be root only

.. code-block:: sh

    sudo mv encrypt.key /etc/pki/tls/private/encrypt.key
    sudo chown root:root /etc/pki/tls/private/encrypt.key
    sudo chmod 600 /etc/pki/tls/private/encrypt.key

Then make sure Apache reads this into an envionment variable at startup.  This could be at a couple different locations, like /etc/apache2/envvars or /etc/sysconfig/httpd.  Once you found the file add this to the file:

.. code-block:: sh

    export ENC_KEY=$(cat /etc/pki/tls/private/encrypt.key)
    
And finally add the following to the Apache config

.. code-block:: sh

    PassEnv ENC_KEY
    
Now restart Apache and encryption should work.

PDF Generation
--------------

PDFs are generated using the HTML -> PDF library `DomPDF`_.  The Reports will use an alternate version of the XML used for formatting the PDF.  Typically what can be done is after the final structure of the report if finalized, the Report XML can be copied for a PDF XML and have some minor modifications done to it.

There are some limitations with DomPDF which can sometimes cause problems.  Sometimes large tables will cause the generation to crash or timeout, so it is best to avoid large tables or at least make the font size in the tables small so that it takes up less space.  Also sometimes certain characters will not render correctly because of font or encoding settings.  The function ``replaceSpecial()`` in PDFGenerator.php largely helps with this issue, however it does not cover all possible characters and will probably need to be edited as other ones show up.  Pagination is also somewhat difficult to fully control.  Css attributes like ``page-break-after:always;`` or ``page-break-before:always;`` can be used to force pagebreaks, but these options are limited.

.. _DomPDF: https://github.com/dompdf/dompdf

Special Pages
-------------

There is a directory 'SpecialPages' in the Reporting extension which contains a folder for each of the possible network instances.  The folder should be named exactly the same as the networkName in the configuration.  Within theses directories will be a set of SpecialPages which will construct tabs for each of the reports.  At minimum there should be a Report.php and a DummyReport.php SpecialPage.  The DummyReport isn't a real SpecialPage, rather it will be used to handle a virtual Report, like for getting pdf files.  In the Report.php file, the tabs will be added using the TopLevelTabs and SubLevelTabs hooks.  Additional SpecialPages can be used as well to further organize certain types of Reports, or it could be used to show Review Tables etc.
