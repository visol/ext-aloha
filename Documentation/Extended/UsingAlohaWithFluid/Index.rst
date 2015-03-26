

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


Using Aloha with Fluid
^^^^^^^^^^^^^^^^^^^^^^

There is a custom ViewHelper if you want to make your fluid based
extension editable through aloha.

Using it is very simple:

::

   <aloha:editable table="tx_news_domain_model_news" field="teaser" uid="{newsItem.uid}" configuration="{nostyles: 1}">
           {newsItem.teaser}
   </aloha:editable>

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Argument
         Argument:
   
   Description
         Description:


.. container:: table-row

   Argument
         table
   
   Description
         Name of the database table


.. container:: table-row

   Argument
         field
   
   Description
         Name of the field inside the table


.. container:: table-row

   Argument
         uid
   
   Description
         UID of the record


.. container:: table-row

   Argument
         configuration
   
   Description
         Optional configuration, see chapter “Configuration” for an
         explanation.


.. ###### END~OF~TABLE ######

