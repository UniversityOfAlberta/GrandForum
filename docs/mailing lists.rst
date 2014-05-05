.. index:: single: Mailing Lists

Mailing Lists
=============

Installing the necessary software
---------------------------------

-  The software is normally installed as part of Red Hat Enterprise
   Linux (Mail Man mailing list software):
   http://www.gnu.org/software/mailman/index.html
-  If mailman is not installed, sudo yum install mailman should install
   the required package.

Creating the mailing list
-------------------------

#. Create the list at the command line on grand
#. Command: sudo /usr/lib/mailman/bin/newlist

   #. Enter name of list
   #. Enter email of admin (person running list). Probably TA or project
      leader
   #. Enter admin password for this list
   #. You will be presented with a list of aliases that need to be
      created. Copy this into a buffer/temporary file

#. Paste these aliases into /etc/aliases
#. Run: sudo /usr/bin/newaliases
#. You can now admin the list at:
   http://grand.cs.ualberta.ca/mailman/admin/LISTNAME
#. Secure the installation by changing a few settings:
#. On the main page you may want to set to "0" the setting "Maximum
   length in kilobytes (KB) of a message body."
#. On the Privacy options set "What steps are required for
   subscription?" to be "Confirm and Approve"
#. Again, on privacy options set "Advertise this list when people ask
   what lists are on this machine" to be "No"
#. Under "Archiving Options", set "Is archive file source for public or
   private archival?" to "private".

Killing an obsolete mailing list
--------------------------------

You need to run the commands as sudo. Be careful as always.

Remove the list from mailman: Commandline using:

-  sudo /usr/lib/mailman/bin/rmlist -a listname
-  The -a removes the archives on disk
-  You can get params and help on any of the binaries for mailman with
   something like:
-  sudo /usr/lib/mailman/bin/rmlist -h

Remove the aliases (to avoid cruft) in /etc/aliases

-  Open /etc/aliases in your favourite editor (emacs of course!)
-  After removing/adding entries, you need to run the command: sudo
   newaliases
