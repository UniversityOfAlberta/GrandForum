.. index:: single: Report

Report
======

These diagrams were made in Summer/Fall 2012 so are somewhat out of date, but the main classes are still there.

UML Diagram
-----------

.. image:: _images/report/ReportUML.png

UI Mockup
---------

.. image:: _images/report/ReportMockup.png

XML Structure
-------------

Reports are defined in xml files.  The structure is fairly simple, however the number of possible combination of elements and their attributes can make it quite complex.  Here is a super simple example of what the structure looks like:

.. code:: xml

    <?xml version="1.0" encoding="UTF-8" ?>
    <Report name="" reportType="" pdfType="" pdfFiles="" ajax="true">
        <Permissions>
            <Role role="">
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


