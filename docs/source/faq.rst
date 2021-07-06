.. role:: latex(raw)
   :format: latex

Häufige Fragen
============================

Nach Aktivieren der Debug Logging Option in den Plugin-Einstellungen, wird kein Debug Output geloggt
-----------------------------------------------------------------------------------------------------------------

Das Log Level im Production Modus von Shopware ist standardmäßig so eingestellt, dass nur kritische Fehler in den Logfiles erscheinen.
Durch folgende Anpassung des Log Levels in der `config.php` werden auch weniger kritische Nachricht geloggt:

.. code-block:: php

    'logger' => [
       'level' => 100
    ]

Nach Auswahl der Zahlungsart im Frontend, wird die Zustimmungserklärung nicht angezeigt
----------------------------------------------------------------------------------------

Wird die Zahlungsart in der Zahlartenauswahl durch Klick auf den Radio-Button ausgewählt, erscheint bei korrekter Funktionalität eine Zustimmungserklärung, in der der Kunde seine Zustimmung zur Übermittlung seiner p
ersönlichen Daten an die Server der Teambank AG zustimmt. Diese Übermittlung ist notwendig, um den Kunden an das Zahlungsterminal weiterzuleiten, um ihm seinen Ratenwunsch zu berechnen.

Wird diese Zustimmungserklärung nicht angezeigt, ist der Fehler meist im verwendeten Template zu finden. Im Template ``themes/Frontend/MeinTheme/frontend/checkout/shipping_payment.tpl`` muss an erster Stelle stehen:
 ``{extends file="parent:frontend/checkout/shipping_payment.tpl"}``. Nur so erbt das Template vom für die confirm-Seite vorgesehenen Template.

.. note:: Beginnt das Template mit ``{extends file="frontend/index/index.tpl"}`` so erbt das Template von allgemeinen Standardtemplate. In diesem Fall wird die Zustimmungserklärung nicht angezeigt.

Nach Auswahl der Zahlungsart im Frontend, wird der Tilgungsplan auf der Bestellbestätigungsseite nicht angezeigt
-----------------------------------------------------------------------------------------------------------------

.. note:: Dieser Hinweis gilt nur für ältere Versionen des Plugins (<= v1.5.x)

Wurde die Zahlungsart vom Kunden augewählt und der Ratenwunsch über das Ratenkaufterminal bestätigt, wird dem Kunden der Tilgungsplan auf der Bestätigungsseite angezeigt.

Wird der Tilgungsplan nicht angezeigt, ist der Fehler meist im verwendeten Template zu finden. Im Template `themes/Frontend/MeinTheme/frontend/checkout/confirm.tpl` muss an erster Stelle stehen: ``{extends file="parent:frontend/checkout/confirm.tpl"}``. Nur so erbt das Template vom für die confirm-Seite vorgesehenen Template.

.. note:: Beginnt das Template mit ``{extends file="frontend/index/index.tpl"}`` so erbt das Template von allgemeinen Standardtemplate. In diesem Fall wird der Tilgungsplan nicht angezeigt.

Nach Rückleitung vom Zahlungsterminal werden die Versandkosten doppelt berechnet und das Plugin zeigt "Raten müssen erneut berechnet werden"
---------------------------------------------------------------------------------------------------------------------------------------------

Dieses Verhalten tritt auf, wenn die Versandkosten-Einstellung "Eigene Berechnung" gesetzt ist, aber den Artikeltyp nicht berücksichtigt (`modus`). Die Option befindet sich unter :menuselection:`Einstellungen --> Versandkosten --> Erweiterte Einstellungen --> Eigene Berechnung`. Mit dieser lassen sich flexible Warenkorb-abhängige Versandkosten konfigurieren, indem die Projektion des SQL-Queries beeinflusst wird.

Beispiel für eine "Eigene Berechnung" ohne Berücksichtigung des Artikeltyps
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: mysql

  SUM(IF(b.price<2000,(b.quantity*15.99),0))

In diesem Fall werden für jeden Einzelartikel einer Bestellposition 15,99 EUR als Versandkosten berechnet.

Das `ratenkauf by easyCredit`-Plugin fügt nach erfolgreicher Ratenberechnung zur Transparenz für den Kunden eine Position `Zinsen für Ratenzahlung` in den Warenkorb ein. Im Beispiel werden auf diese Position ebenfalls Versandkosten in Höhe von 15,99 EUR berechnet, die aber vor der Ratenberechnung nicht aufgeschlagen wurden. Dadurch unterscheidet sich der Gesamtbetrag vor und nach der Ratenberechnung und das Plugin erkennt eine Betragsänderung, die wiederum eine erneute Ratenberechnung erforderlich macht.

Beispiel für eine "Eigene Berechnung" mit Berücksichtigung des Artikeltyps
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Für eine korrekte Berechnung der Versandkosten ist es daher notwendig den Artikeltyp (`modus`) zu berücksichtigten:

.. code-block:: mysql

    SUM(IF(b.price<2000,(
      (CASE WHEN modus = '0' THEN b.quantity
      ELSE 0
      END)    
    *15.99),0))

Die CASE-Anweisung bewirkt in diesem Fall, dass die Versandkosten nur für Artikel berechnet werden, die den `modus` = 0 haben. Bei anderen Artikeltypen werden keine Versandkosten aufgeschlagen.

.. warning::
  Diese Anpassung ist nicht nur für ratenkauf by easyCredit wichtig. Ist der Modus in "Eigene Berechnung" nicht berücksichtigt, kann dies auch zu Versandkosten-Aufschlägen bei Gutscheinen oder anderen Zusatzpositionen führen.

.. note::

  Die Versandkosten-Berechnung erfolgt in sAdmin::sGetPremiumDispatches (`engine/Shopware/Core/sAdmin.php:~2719`). Um den SQL-Query zu debuggen empfiehlt sich eine Ausgabe / Logging von `(string) $queryBuilder` auf Höhe des Events `Shopware_Modules_Admin_GetPremiumDispatches_QueryBuilder`

.. note::

  Es existieren die folgenden Artikeltypen: PRODUCT = 0, PREMIUM_PRODUCT = 1, VOUCHER = 2, REBATE = 3, SURCHARGE_DISCOUNT = 4 (Auszug aus `themes/Frontend/Bare/frontend/checkout/cart_item.tpl`)
