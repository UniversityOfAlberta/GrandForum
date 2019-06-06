.. index:: single: Messages

Messages
========

Messages are used to display status messages to the user, usually after a form is submitted.

Message Types
-------------

There are 5 types of messages

- Error
    .. image:: _images/messages/errorMessage.jpeg
- Warning
    .. image:: _images/messages/warningMessage.jpeg
- Success
    .. image:: _images/messages/successMessage.jpeg
- Info
    .. image:: _images/messages/infoMessage.jpeg
- Purple Info
    .. image:: _images/messages/purpleInfoMessage.png
    
Each type is internally stored using an array, which means that each message can contain more than one line.

PHP Usage
---------

Using Messages is very simple, and similar to how the global $wgOut variable is used with mediawiki. Messages uses a global variable $wgMessage, and messages can be added by using the following 5 methods:

.. code-block:: php

    global $wgMessage;

    $wgMessage->addError("This is an error message");
    $wgMessage->addWarning("This is a warning message");
    $wgMessage->addSuccess("This is a success message");
    $wgMessage->addInfo("This is an info message");
    $wgMessage->addPurpleInfo("This is a purple info message");
    
Messages will always be display in the same order, regardless of when each method was called.

Messages can also be cleared by calling the following methods:

.. code-block:: php

    global $wgMessage;

    $wgMessage->clearError();
    $wgMessage->clearWarning();
    $wgMessage->clearSuccess();
    $wgMessage->clearInfo();
    $wgMessage->clearPurpleInfo();
    
Javascript Usage
----------------

Messages can also be added using the javascript interface.

.. code-block:: javascript

    addError("This is an error message");
    addWarning("This is a warning message");
    addSuccess("This is a success message");
    addInfo("This is an info message");
    addPurpleInfo("This is a purple info message");

    clearError();
    clearWarning();
    clearSuccess();
    clearInfo();
    clearPurpleInfo();
    clearAllMessages();
