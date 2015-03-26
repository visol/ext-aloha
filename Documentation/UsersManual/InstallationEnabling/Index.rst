

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


Installation & Enabling
^^^^^^^^^^^^^^^^^^^^^^^

#. The extension needs to be installed as any other extension of TYPO3.
   If you got a T3X file, just upload it in the Extension Manager and
   install the extension.

#. Add the Static TypoScript. There are 2 entries you need to select:
   “Aloha Basic” & “Aloha Modification”. You need both. More about those
   in the chapter “Configuration”,

#. Enable aloha in your  **TypoScript** (Setup) by adding the
   lineconfig.aloha = 1

#. Enable aloha in your  **UserTsConfig** by adding the linealoha = 1

If your editor wants to use aloha, he needs to actiivate it in the
Admin Panel, therefore, the Admin Panel needs to be available too. You
can use this lines in the UserTsConfig:

::

   admPanel {
           enable.edit = 1
   }

