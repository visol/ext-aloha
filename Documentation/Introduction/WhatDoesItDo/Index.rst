

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


What does it do?
^^^^^^^^^^^^^^^^

This extension implements a powerful frontend editing by integrating
the awesome `Aloha <http://www.aloha-editor.org/>`_ . For the first
time,  **true** inline editing is possible within TYPO3 v4.


Features / What is possible
"""""""""""""""""""""""""""

- Inline editing of text of the content elements “Text”, “Text with
  Images”, “Table”, “List”

- Inline editing of all headers of content elements.

- Inline editing wherever it is enabled by using
  
  - TypoScript for pibased based extensions & fields.
  
  - An integrated ViewHelper for fluid based extensions

- Different possibilities for saving changes:
  
  - **Directly:** Every change is directly saved in the database
  
  - **Intermediate:** Changes are saved only if a Save-Button is pressed.

