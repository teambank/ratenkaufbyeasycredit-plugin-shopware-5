.. role:: latex(raw)
   :format: latex

Häufige Fragen
============================

Nach Auswahl der Zahlungsart im Frontend, wird die Zustimmungserklärung nicht angezeigt
----------------------------------------------------------------------------------------

Wird die Zahlungsart in der Zahlartenauswahl durch Klick auf den Radio-Button ausgewählt, erscheint bei korrekter Funktionalität eine Zustimmungserklärung, in der der Kunde seine Zustimmung zur Übermittlung seiner p
ersönlichen Daten an die Server der Teambank AG zustimmt. Diese Übermittlung ist notwendig, um den Kunden an das Zahlungsterminal weiterzuleiten, um ihm seinen Ratenwunsch zu berechnen.

Wird diese Zustimmungserklärung nicht angezeigt, ist der Fehler meist im verwendeten Template zu finden. Im Template ``themes/Frontend/MeinTheme/frontend/checkout/shipping_payment.tpl`` muss an erster Stelle stehen:
 ``{extends file="parent:frontend/checkout/shipping_payment.tpl"}``. Nur so erbt das Template vom für die confirm-Seite vorgesehenen Template.

.. note:: Beginnt das Template mit ``{extends file="frontend/index/index.tpl"}`` so erbt das Template von allgemeinen Standardtemplate. In diesem Fall wird die Zustimmungserklärung nicht angezeigt.

Nach Aktivieren der Debug Logging Option in den Plugin-Einstellungen, wird kein Debug Output geloggt
-----------------------------------------------------------------------------------------------------------------

Das Log Level im Production Modus von Shopware ist standardmäßig so eingestellt, dass nur kritische Fehler in den Logfiles erscheinen.
Durch folgende Anpassung des Log Levels in der `config.php` werden auch weniger kritische Nachricht geloggt:

.. code-block:: php 

    'logger' => [
       'level' => 100
    ]

Nach Auswahl der Zahlungsart im Frontend, wird der Tilgungsplan auf der Bestellbestätigungsseite nicht angezeigt
-----------------------------------------------------------------------------------------------------------------

.. note:: Dieser Hinweis gilt nur für ältere Versionen des Plugins (<= v1.5.x)

Wurde die Zahlungsart vom Kunden augewählt und der Ratenwunsch über das Ratenkaufterminal bestätigt, wird dem Kunden der Tilgungsplan auf der Bestätigungsseite angezeigt.

Wird der Tilgungsplan nicht angezeigt, ist der Fehler meist im verwendeten Template zu finden. Im Template `themes/Frontend/MeinTheme/frontend/checkout/confirm.tpl` muss an erster Stelle stehen: ``{extends file="par
ent:frontend/checkout/confirm.tpl"}``. Nur so erbt das Template vom für die confirm-Seite vorgesehenen Template.

.. note:: Beginnt das Template mit ``{extends file="frontend/index/index.tpl"}`` so erbt das Template von allgemeinen Standardtemplate. In diesem Fall wird der Tilgungsplan nicht angezeigt.
