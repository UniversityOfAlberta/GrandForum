.. index:: single: Running Tests

Running Tests
=============

The Forum uses `behat`_ and `mink`_ as a testing framework.

Installation
------------

To get things started, you should first run ``install.sh`` to install
any required packages.

You will also most likely want to add
``alias behat='bin/behat --ansi --no-paths'`` to your .bashrc file to
make running behat simpler.

Running Behat
-------------

Assuming you have added the alias to your .bashrc you can run the test
suite by simply running ``behat`` inside of the symfony/ directory. If you
would like to only run a specific feature, you can instead run
``behat features/<feature_name>.feature``

Results
-------

The results will show up both in your terminal, as well as in
symfony/output/output.html which can be viewed in the web browser.
Clicking on one of the steps will bring up a screenshot of the browser
view of that step.

Configuration
-------------

The configuration for behat goes in behat.yml and should look something
like this:

::

    default:
        filters:
            tags: ~@grand
            tags: ~@glyconet
            tags: ~@agewell
        formatter:
            name: pretty,Behat\Behat\Formatter\MyHtmlFormatter
            parameters:
                output_path: null,output/output.html
        extensions:
            Behat\MinkExtension\Extension:
                base_url: http://grand.cs.ualberta.ca/~dwt/grand_forum_staging/
                files_path: "/opt/uploads"
                default_session: selenium2
                selenium2:
                    wd_host: 'http://129.128.184.79:8643/wd/hub'

All of the settings should be left alone, although the following may
need to be changed:

``base_url`` should be replaced with the url of the instance that the
Forum is located. ``files_path`` can also be changed to a different
location if you are running everything on your local computer, but if
you are using the Selenium Grid node server (ssrg5) then you should
leave it as is (more about this later) ``wd_host`` again if you are
running locally, you can get rid of this, but if not then leave this
here

PhantomJS Setup
---------------
`PhantomJS`_ is a headless browser using webkit as a renderer.  By using webdriver
behat can interact with this instead of a real browser, which will speed up the tests.

You will need to install PhantomJS by running:

.. code-block:: bash

    $ sudo npm -g install phantomjs

Selenium Grid Setup
-------------------

If for whatever reason the phantomjs setup is not working,
The selenium can also be used. You can use `Selenium Grid`_ to allow there
to be a single hub, and any number of nodes executing the tests.

Hub
~~~

Wherever you run behat, the hub will automatically get started at that
location and will point to wherever wd\_host is set. The hub will be
running on 129.128.184.79:4444 (grand.cs.ualberta.ca:4444). 

Node
~~~~
 
The node will be always running on 129.128.184.85:5555
(ssrg5.cs.ualberta.ca:5555), with a Xvfb frame buffer running on
display=:10. Selenium and Xvfb are configured to be started as init.d
daemons, so if anything need to be changed with respect to either of
them, their init.d scripts are located /etc/init.d/selenium and
/etc/init.d/xvfb on ssrg5.  
NOTE: This is currently disabled, they will need to be re-enabled for this to work

File Uploads
------------

File uploads were tough to get working, but it seems as long as both the
Hub and Node have the files at the same absolute paths, then the uploads
will work fine. The files are in /opt/uploads/ on both grand and ssrg5.

.. _behat: http://behat.org/
.. _mink: http://mink.behat.org/
.. _PhantomJS: http://phantomjs.org/
.. _Selenium Grid: https://code.google.com/p/selenium/wiki/Grid2
