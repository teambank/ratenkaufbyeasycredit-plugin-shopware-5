============= 
Konfiguration 
=============

Die Konfiguration teilt sich auf in die Plugin-Konfiguration und die Zahlarten-Konfiguration. 

Plugin konfigurieren
--------------------------

.. image:: ./_static/config-open.png
           :scale: 50%

* Öffnen Sie im Backend den Plugin-Manager. :menuselection:`Einstellungen -> Plugin-Manager`
* In der Liste der installierten Plugins sollte nun **ratenkauf by easyCredit** erscheinen. 
* Öffnen Sie die Plugin-Konfiguration über das Stifte Icon (ganz rechts).

Für eine Grundkonfiguration tragen Sie zumindest die Zugangsdaten (API Key & API Token) ein.
Testen Sie die Zugangsdaten mit Klick auf **Zugangsdaten testen**.
Nach dem erfolgreichen Test klicken Sie auf **Speichern**

.. image:: ./_static/config-dialog.png

.. tabularcolumns:: |p{85pt}|J|

+-----------------------------------------------+---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| Option                                        | Erklärung                                                                                                                                                                                                                                                                       |
+-----------------------------------------------+---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| Zeige Modellrechner-Widget neben Produktpreis | Aktivieren Sie diese Option wenn Sie auf Produkt-Detail-Seiten ein monatliches Raten Angebot anzeigen möchten. Bitte beachten Sie das ein monatlicher Ratenpreis nur angezeigt wird wenn der Preis des Produkts sich in der festgelegten Preisspanne für Ratenkäufe befindet.   |
+-----------------------------------------------+---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| Bestellungsstatus                             | Ermöglicht es Ihnen den Status festzulegen den Bestellungen die mit **ratenkauf by easyCredit** bezahlt wurden, nach dem Eingang im System aufweisen.                                                                                                                           |
+-----------------------------------------------+---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| Zahlungsstatus                                | Ermöglicht es Ihnen den Zahlungsstatus festzulegen den Bestallungen die mit **ratenkauf by easyCredit** bezahlt wurden, nach dem Eingang im System aufweisen.                                                                                                                   |
+-----------------------------------------------+---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| Debug Logging                                 | Erlaubt Ihnen festzulegen ob der Inhalt aller easyCredit API-Zugriffe werden soll. Fehlermitteillungen werden immer gespeichert.                                                                                                                                                |
+-----------------------------------------------+---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| API Key                                       | Der API-Key wird Ihnen von der Teambank AG zur Verfügung gestellt.                                                                                                                                                                                                              |
+-----------------------------------------------+---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| API Token                                     | Der nicht öffentliche API Token wird Ihnen von der Teambank AG zur Verfügung gestellt und sollte nicht mit Dritten geteilt werden.                                                                                                                                              |
+-----------------------------------------------+---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| easyCredit Zugangsdaten überprüfen            | Ein Klick auf diesen Button überprüft die Kombination von API-Key und -Token auf Gültigkeit. Bitte vergessen Sie nicht nach einem erfolgreichen Test noch auf Speichern zu klicken.                                                                                             |
+-----------------------------------------------+---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+


Zahlungsart Einstellungen
-------------------------

Im Shopware Backend öffnen Sie den Zahlungsarten Eintrag für **ratenkauf by easyCredit**

    :menuselection:`System -> Konfiguration -> Zahlungsarten -> ratenkauf by easyCredit`

Im ersten Reiter '*Generell*' stellen Sie sicher, dass **ratenkauf by easyCredit** aktiviert ist.

.. image:: ./_static/config-payment-active.png

.. raw:: latex

    \clearpage

Aktivieren Sie weiterhin im Reiter *Länder-Auswahl* das Land Deutschland.

.. image:: ./_static/config-payment-country.png
