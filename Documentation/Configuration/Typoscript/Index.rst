

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


TypoScript
^^^^^^^^^^

The integration of aloha requires a custom stdWrap option. It behaves
similar to the property editIcons which is used to render the old
frontend editing icons. Looking at running code is sometimes the
fastest way to explain something, therefor you should take a look at
the file  *Configuration/TypoScript/Modification/setup.txt* .

All you need to get aloha running for the content element “Text” are
those lines:

::

   01 tt_content.text.20.editIcons.beforeLastTag = 0
   02 tt_content.text.20.alohaProcess = 1
   03 tt_content.text.20.alohaProcess {
   04      field = bodytext
   05      allow = all
   06 }

**For explanation** :

Line 01: removes the icons of the old frontend editing.

Line 02: enables Aloha

Line 04: adds the field which holds the value. This field is used to
save the changes.

Line 05: sets the actions which are allowed.

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Description
         Description:


.. container:: table-row

   Property
         field
   
   Description
         Field of the table where the content comes from and where it should be
         saved.


.. container:: table-row

   Property
         allow
   
   Description
         Comma separated list of allowed actions. Possible values are: move,
         edit, link, hide, unhide, newContentElementBelow, delete. You can also
         set “all” to allow all.


.. container:: table-row

   Property
         nostyles
   
   Description
         Set it to 1 to allow no formatting styles of the content. This is e.g.
         useful for headers which must not be styled with bold, italic or
         whatever.


.. container:: table-row

   Property
         class
   
   Description
         Add an additional class which can be used in the frontend for an
         additional styling


.. container:: table-row

   Property
         style
   
   Description
         Similar to the property “class” but for setting an inline CSS style.


.. container:: table-row

   Property
         tag
   
   Description
         Define which HTML element is used for adding the aloha editor. Default
         is div but you can set e.g. a span


.. ###### END~OF~TABLE ######

