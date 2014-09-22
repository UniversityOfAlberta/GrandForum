.. index:: single: Coding Conventions

Coding Conventions
==================

For forum development, we will be following a slightly modified
mediawiki coding conventions
(http://www.mediawiki.org/wiki/Manual:Coding_conventions). This page
will point out some of the more important conventions, as well as some
of the differences to the mediawiki conventions.

-  Use camelCase for methods

   -  Instead of naming a function, or variable my\_new\_function, it
      should be written as myNewFunction.

-  Indentation should be 4 spaces

   -  This is one which is different to the mediawiki conventions. Having 4
      spaces will ensure that the code will look the same regardless of
      what system or editor is being used.

-  SQL statements should use upper case characters for SQL keywords(ie.
   SELECT, UPDATE, FROM …)
-  There is no restriction on line lengths(this is within reason,
   obviously a line should not be hundreds of characters long)
-  Lines with boolean expressions should be broken up using a new line
   at each logical operator (a good example of this can be seen in the
   line continuation section in the mediawiki conventions)

   -  If you have a group of expressions with parentheses, it would be ok
      to keep them on a single line, or you could further indent those
      parenthesised expressions if they are longer than 2 or 3 expressions)

-  Braces should be placed on the same line as the function, loop,
   conditional etc.

Since these conventions were previously not formulated, there may be
instances of code not following these conventions. If it is something
simple like improper indentation, then just fix it right there. If it is
a more complicated thing like a function not using camel casing, then it
may require some refactoring in many files throughout the code, in which
case testing at least the pages directly affected by the refactor should
be visited and tested to ensure that the functionality still works with
no errors.
