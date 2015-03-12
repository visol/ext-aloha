.. include:: Images.txt

.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. ==================================================
.. DEFINE SOME TEXTROLES
.. --------------------------------------------------
.. role::   underline
.. role::   typoscript(code)
.. role::   ts(typoscript)
   :class:  typoscript
.. role::   php(code)


Adding custom headers
^^^^^^^^^^^^^^^^^^^^^

The default implementation makes it possible to use h1-h6 tags.
Sometimes tags with additional classes are needed to allow more
styling options for an editor.

|img-9|

For adding headers with classes, you need to change the settings
inside the configuration.js.

::

   01 "format": {
   02      'h3' : ['fo', 'bar'],
   03      config : [ 'b', 'i', 'sub', 'h1', 'h3','h3-1', 'h3-0','h2'],
   04      editables : {
   05              // no formatting allowed for title
   06              '.nostyles'     : [ ]
   07      }
   08 },

**Explanation**

Line 02: There are 2 different h3 tags available: One with class “fo”
and another one with “bar”.

Line 03: Those 2 need to be enabled by adding “h3-0” and “h3-1” to the
allowed tags.


Styling
"""""""

CSS is used to change the image in the panel to visualize the
different output. An additional class which is built out of the tag
name and the additional class can be used:

::

   ul.aloha-multisplit button.h3-fo {
           background-image:url(../Images/h3-a.png) !important;
           background-position: center center !important;
   }
   ul.aloha-multisplit button.h3-bar {
           background-image:url(../Images/h3-b.png) !important;
           background-position: center center !important;
   }




