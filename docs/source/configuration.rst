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

Die Konfigurationsmöglichkeiten sind im Folgenden gezeigt. Als Mindestkonfiguration geben Sie hier Ihre Webshop-Id und Ihr API-Passwort an.

.. image:: ./_static/config-dialog.png

.. note:: Nach einem erfolgreichen Test der API-Zugangsdaten, vergessen Sie bitte nicht auf **Speichern** zu klicken.

Zahlungsart Einstellungen
-------------------------

Um die Zahlungsart **ratenkauf by easyCredit** im Frontend anzuzeigen, muss die Zahlungsart aktiviert sein, und dem Land *Deutschland* zugewiesen werden. Navigieren Sie hierzu zu den Zahlungsart Einstellungen: :menuselection:`System -> Konfiguration -> Zahlungsarten -> ratenkauf by easyCredit`
Im ersten Reiter **Generell** stellen Sie sicher, dass **ratenkauf by easyCredit** aktiviert ist.

.. image:: ./_static/config-payment-active.png

.. raw:: latex

    \clearpage

Aktivieren Sie als letzten Schritt nun im Reiter **Länder-Auswahl** das Land **Deutschland**.

.. image:: ./_static/config-payment-country.png

Click & Collect konfigurieren
------------------------------

Um *Click & Collect* für eine Versandart zu aktivieren, kann diese als *Click & Collect*-Versandart ausgewählt werden. Wählt der Kunde diese Versandart im Bezahlvorgang aus, wird dies bei der Finanzierungsanfrage entsprechend übertragen. Weitere Informationen finden Sie unter `Click & Collect <https://www.easycredit-ratenkauf.de/click-und-collect/>`_

.. image:: ./_static/config-clickandcollect.png
           :scale: 50%
