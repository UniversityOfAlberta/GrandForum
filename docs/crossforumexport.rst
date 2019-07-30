.. index:: single: Cross Forum Export

Cross Forum Export
==================

This extension allows for importing publications between Forum instances, even accross different branches.  As long as the instance has the extension 'CrossForumExport' and it is enabled then it will be able to utilize the feature.  There should also be a toBibTeX() function inside of Paper.php  The list of available Forum instances can be configured using the 'crossForumUrls' config variable.  It should be an associative array where the index is the name of the instance, and the value is the url to the CrossForumExport special page url.
