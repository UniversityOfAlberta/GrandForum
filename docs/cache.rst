.. index:: single: Cache

Cache
=====

This extension is used to add easy access to PHP's APC cache.  APC will store values in memory as key => value pairs.  APC automatically serializes any value so this doesn't need to be done before hand.

The functions to use the cache are as follows:

.. code-block:: php

    Cache::store($key, $data, $time=3600);
    
    Cache::fetch($key);
    
    Cache::delete($key, $prefix=false);
    
    Cache::exists($key);
