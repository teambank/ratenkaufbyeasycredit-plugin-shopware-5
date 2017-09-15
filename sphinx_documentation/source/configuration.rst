.. role:: latex(raw)
   :format: latex

============= 
Konfiguration 
=============

Zu erst muss das Plugin konfiguriert werden und 
danach noch als Zahlungsmethode für den deutschen Shop aktiviert werden.

Konfigurations Menü öffnen
--------------------------

.. image:: ./_static/config-open.png  
           :scale: 50%

Zu erst müssen Sie sich im Backend Ihrer Shopware 5 Installation anmelden, 
der Link dafür ist:

.. only:: latex

    :latex:`{{\color{easyorange} \texttt{http(s)://IHRE-SHOPWARE-URL.de/backend}}`

.. only:: html

    ``http(s)://IHRE-SHOPWARE-URL.de/backend``


Öffnen Sie im Backend nun den Plugin-Manager. 
Klicken Sie dazu entweder die drei Tasten `STRG + ALT + P` gleichzeitig 
oder öffnen Sie den Manager mit der Maus über folgende Menü-Punkte: 

.. only:: latex

    :latex:`{{\color{easyorange} \texttt{Einstellungen -> Plugin-Manager}}`

.. only:: html

    ``Einstellungen -> Plugin-Manager``


In der Liste der installierten Plugins sollte nun **ratenkauf by easyCredit** 
mit aufgelistet sein. In dieser Zeile klicken Sie das Stifte Icon (roter Kasten (1)) 
um die Plugin Konfiguration zu öffnen.

Plugin Konfigurieren
--------------------

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

**Bitte vergessen Sie nicht nach einem erfolgreichen Test noch auf Speichern zu klicken.**

Zahlungsart Einstellungen
-------------------------

Im Shopware 5 Backend öffnen Sie den Zahlungsarten Eintrag für **ratenkauf by easyCredit**

.. only:: latex

    :latex:`{{\color{easyorange} \texttt{System -> Konfiguration -> Zahlungsarten -> ratenkauf by easyCredit}}`

.. only:: html

    ``System -> Konfiguration -> Zahlungsarten -> ratenkauf by easyCredit``


Im ersten Reiter '*Generell*' muss sichergestellt werden das **ratenkauf by easyCredit** aktiviert (gelbe Markierung (1)) ist:

.. image:: ./_static/config-payment-active.png

.. raw:: latex

    \clearpage

Als letzter Schritt muss im Reiter '*Länder-Auswahl*' Deutschland aktiviert (gelber Kasten (1)) werden:

.. image:: ./_static/config-payment-country.png