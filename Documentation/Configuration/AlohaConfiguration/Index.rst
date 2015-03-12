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


Aloha configuration
^^^^^^^^^^^^^^^^^^^

As aloha is done in JavaScript, the whole configuration is taking
place in JavaScript too.

If you want to change the configuration, you need to change the
template inside the Extension Manager's settings. The following
screenshot shows the essential part.

Change the path of the template to one of your files.

|img-8|

The header templates holds the following files:

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   File
         File:
   
   Description
         Description:


.. container:: table-row

   File
         AlohaIntegration.css
   
   Description
         Styling of the integration, especially the top bar


.. container:: table-row

   File
         Aloha.css
   
   Description
         Styling of aloha itself


.. container:: table-row

   File
         Configuration.js
   
   Description
         Configuration of integration of Aloha


.. container:: table-row

   File
         Loading.js
   
   Description
         General JS stuff needed for aloha to work


.. ###### END~OF~TABLE ######

