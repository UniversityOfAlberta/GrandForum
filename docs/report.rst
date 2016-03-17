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

.. code:: xml

    <?xml version="1.0" encoding="UTF-8" ?>
    <Report name="" reportType="" pdfType="" pdfFiles="" ajax="true">
        <Permissions>
            <Role role="">
                <SectionPermission id="" permissions="" />
                <SectionPermission id="" permissions="" />
                <SectionPermission id="" permissions="" />
            </Role>
        </Permissions>
        <ReportSection id="" tooltip="" name="" blobSection="" type="">
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
        asdf
            

