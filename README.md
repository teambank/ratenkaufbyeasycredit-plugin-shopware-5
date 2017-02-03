# easyCredit Shopware Extension

Das easyCredit Zahlungsmodul für Shopware 4./5. ermöglicht es Ihnen durch einfache Installation Ratenkauf by easyCredit in Ihrem Shopware-Store anbieten zu können.
Weitere Informationen zu easyCredit finden Sie unter  [Ratenkauf by easyCredit](https://www.easycredit.de/Ratenkauf.htm)

## Installation

## Konfiguration

### Zahlarten-Einstellung

Die Zahlungsarten-Konfiguration befindet sich in unter *System -> Konfiguration -> Zahlungsarten -> easyCredit Ratenzahlung*

![Zahlarten-Konfiguration](./screenshots/payment_method_settings.png "Zahlarten-Konfiguration")

| Option                                        | Erklärung                                                                                                                                                                                                                                                                       |
|-----------------------------------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| Title                                         | Unter dem Titel wird die Zahlungsmethode im Checkout angezeigt.                                                                                                                                                                                                                 |
| Status neuer Bestellungen                     | Ermöglicht es Ihnen den Status festzulegen den Bestellungen die mit Ratenkauf by easyCredit bezahlt wurden, nach dem Eingang im System aufweisen.                                                                                                                               |
| Zeige Modellrechner-Widget neben Produktpreis | Aktivieren Sie diese Option wenn Sie auf Produkt-Detail-Seiten ein monatliches Raten Angebot anzeigen möchten. Bitte beachten Sie das ein monaterlicher Ratenpreis nur angezeigt wird wenn der Preis des Produkts sich in der festgelegten Preisspanne für Ratenkäufe befindet. |
| Zahlung aus zutreffenden Ländern              | Stellen Sie diese Option bitte auf Bestimmte Länder (Specific Countries)                                                                                                                                                                                                        |
| Zahlung aus bestimmmten Ländern               | Wählen Sie hier als einziges Land Deutschland aus.                                                                                                                                                                                                                              |
| Debug Logging                                 | Erlaubt Ihnen festzulegen ob der Inhalt aller easyCredit API-Zugriffe in var/log/debug.log gespeichert werden soll. Fehlermitteillungen werden immer gespeichert.                                                                                                               |
| API Key                                       | Der API-Key wird Ihnen von der Teambank AG zur Verfügung gestellt.                                                                                                                                                                                                              |
| API Token                                     | Der nicht öffentliche API Token wird Ihnen von der Teambank AG zur Verfügung gestellt und sollte nicht mit Dritten geteilt werden.                                                                                                                                              |
| easyCredit Zugangsdaten überprüfen            | Ein Klick auf diesen Button überprüft die Kombination von API-Key und -Token auf Gültigkeit. Bitte vergessen Sie nicht nach einem erfolgreichen Test noch auf Speichern zu klicken.                                                                                             |

## Kompatibilität

Die Extension wurde mit folgenden Shopware Versionen und PHP5 getestet:
* 4.3.0
* 4.3.6
* 5.1.3
* 5.1.5
* 5.2.x (auch PHP7)