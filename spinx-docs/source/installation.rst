.. role:: latex(raw)
   :format: latex

Installation
============

Das Plugin kann im Plugin-Manager entweder über den direkten Download aus dem **Community Store** oder über den **Datei-Upload "Plugin Hochladen"** der gepackten Extension installiert werden.
Alternativ ist auch die Installation über die Kommandozeile oder FTP möglich.

Zur Installation melden Sie sich im Backend Ihrer Shopware Installation an. Sie finden das Backend unter:

    ``http(s)://mein-shop.de/backend``

Öffnen Sie im Backend nun den Plugin-Manager. Klicken Sie dazu auf den folgenden Menüpunkt:

    :menuselection:`Einstellungen --> Plugin-Manager`

Installation über Shopware Community Store
------------------------------------------

.. image:: ./_static/installation-community_store.png

* Zur Installation über den Shopware Community Store suchen Sie das Plugin im Community Store.
* Klicken Sie nach Auswahl des Plugins den Button **Installieren**. 
* Die Extension wird nun heruntergeladen und installiert.

* Fahren Sie anschließend mit der *Konfiguration* fort.

Installation über Datei-Upload
---------------------------------

.. image:: ./_static/installation-file_upload.png

* Zur Installation über den Plugin-Upload wählen Sie im Plugin-Manager den Menu-Punkt :menuselection:`Verwaltung --> Installiert`
* Klicken Sie dort den Button **Plugin hochladen**.
* Wählen Sie nun das Zip-Archiv, dass Sie von unserer Website heruntergeladen haben aus
* und klicken Sie auf *Plugin hochladen*.

Beispiel: 

    ``C:\easycredit-shopware-1.3.0.zip``

* Fahren Sie anschließend mit der *Konfiguration* fort.

Installation über Kommandozeile
-------------------------------

Entpacken Sie das Zip-Archive 

.. code-block:: bash

    $ cp easycredit-shopware-x.x.zip /shopware/engine/Shopware/Plugins/
    $ cd /shopware/engine/Shopware/Plugins/
    $ unzip easycredit-shopware-x.x.zip
    $ rm easycredit-shopware-x.x.zip


Anschließend überprüfen Sie ob das folgende Verzeichnis existiert:

    ``engine/Shopware/Plugins/Netzkollektiv/EasyCredit``

Führen Sie nun folgende Befehle aus:

.. code-block:: bash

    $ /shopware/bin/console sw:plugin:refresh
    $ /shopware/bin/console sw:plugin:install NetzkollektivEasyCredit
    $ /shopware/bin/console sw:plugin:activate NetzkollektivEasyCredit

Fahren Sie anschließend mit der Konfiguration fort.
