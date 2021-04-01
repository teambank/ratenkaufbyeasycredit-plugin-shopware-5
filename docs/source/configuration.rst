.. role:: latex(raw)
   :format: latex

.. _configuration:

Konfiguration
=============

Nachdem Sie die Installation erfolgreich abgeschlossen haben, konfigurieren Sie das Plugin. Damit das Plugin als Zahlungsmethode angezeigt wird aktivieren Sie ratenkauf by easyCredit als Zahlungsmethode für den deutschen Store.

Konfigurations Menü öffnen
--------------------------

Zur Konfiguration öffnen Sie im Backend erneut den Plugin-Manager. In der Liste der installierten Plugins sollte nun **ratenkauf by easyCredit** enthalten sein.
In dieser Zeile klicken Sie das Stifte Icon, um die Plugin Konfiguration zu öffnen.

.. image:: ./_static/config-open.png
           :scale: 50%

Plugin konfigurieren
--------------------

Die Konfigurationsmöglichkeiten sind im Folgenden gezeigt und in der Tabelle im einzelnen beschrieben. Als Mindestkonfiguration geben Sie hier Ihre Webshop-Id und Ihr API-Passwort an.
Sollten Sie Einstellungen vorgenommen habem, so speichern Sie die Einstellungen mit einem Klick auf **Speichern**.

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
| Webshop-Id                                    | Der API-Key wird Ihnen von der Teambank AG zur Verfügung gestellt.                                                                                                                                                                                                              |
+-----------------------------------------------+---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| API-Passwort                                  | Der nicht öffentliche API Token wird Ihnen von der Teambank AG zur Verfügung gestellt und sollte nicht mit Dritten geteilt werden.                                                                                                                                              |
+-----------------------------------------------+---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| easyCredit Zugangsdaten überprüfen            | Ein Klick auf diesen Button überprüft die Kombination von API-Key und -Token auf Gültigkeit. Bitte vergessen Sie nicht nach einem erfolgreichen Test noch auf Speichern zu klicken.                                                                                             |
+-----------------------------------------------+---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+

.. note:: Nach einem erfgolreichen Test der API-Zugangsdaten, vergessen Sie bitte nicht auf **Speichern** zu klicken.

Zahlungsart Einstellungen
-------------------------

Um die Zahlungsart **ratenkauf by easyCredit** im Frontend anzuzeigen, muss die Zahlungsart aktiviert sein, und dem Land *Deutschland* zugewiesen werden. Navigieren Sie hierzu zu den Zahlungsart Einstellungen: :menuselection:`System -> Konfiguration -> Zahlungsarten -> ratenkauf by easyCredit`
Im ersten Reiter **Generell** stellen Sie sicher, dass **ratenkauf by easyCredit** aktiviert ist.

.. image:: ./_static/config-payment-active.png

.. raw:: latex

    \clearpage

Aktivieren Sie als letzten Schritt nun im Reiter **Länder-Auswahl** das Land **Deutschland**.

.. image:: ./_static/config-payment-country.png