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

.. code-block:: yaml

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
                base_url: https://grand.cs.ualberta.ca/~dwt/behat_test/
                files_path: "/opt/uploads"
                default_session: selenium2
                selenium2:
                    wd_host: 'http://129.128.243.133:8643/wd/hub'

All of the settings should be left alone, although the following may
need to be changed:

``base_url`` should be replaced with the url of the instance that the
Forum is located. ``files_path`` can also be changed to a different
location if you are running everything on your local computer.

PhantomJS Setup
---------------
`PhantomJS`_ is a headless browser using webkit as a renderer.  By using webdriver
behat can interact with this instead of a real browser, which will speed up the tests.

You will need to install PhantomJS by running:

.. code-block:: bash

    $ sudo npm -g install phantomjs

.. _behat: http://behat.org/
.. _mink: http://mink.behat.org/
.. _PhantomJS: http://phantomjs.org/
