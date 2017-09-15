.. role:: latex(raw)
   :format: latex

Installation
============

Die Extension kann im Plugin-Manager entweder über den direkten Download aus dem *Community Store* oder über den Datei-Upload *Plugin Hochladen* der gepackten Extension installiert werden.

Alternativ ist auch die Installation über die Kommandozeile möglich.

Shopware Community Store
------------------------

.. image:: ./_static/installation-community_store.png

Zu erst müssen Sie sich im Backend Ihrer Shopware 5 Installation anmelden, der Link dafür ist normalerweise:

.. only:: latex

    :latex:`{{\color{easyorange} \texttt{http(s)://IHRE-SHOPWARE-URL.de/backend}}`

.. only:: html

    ``http(s)://IHRE-SHOPWARE-URL.de/backend``

Öffnen Sie im Backend nun den Plugin-Manager. Klicken Sie dazu entweder die drei Tasten `STRG + ALT + P` gleichzeitig 
oder öffnen Sie den Manager mit der Maus über folgende Menü-Punkte: 

.. only:: latex

    :latex:`{{\color{easyorange} \texttt{Einstellungen -> Plugin-Manager}}`

.. only:: html

    ``Einstellungen -> Plugin-Manager``

Geben Sie in der Suche-Zeile oben links (gelber Kasten (1)) **easyCredit** ein:

Wie im Bild zu sehen sollten Sie nun die Extension **ratenkauf by easyCredit** angezeigt werden. Vergewissern Sie sich das neben dem **vom:** Feld (roter Kasten (2)) auch **ratenkauf by easyCredit** als Herrausgeber angegeben wird.

Klicken Sie nun den Button mit der Aufschrift **Installieren**. Die Extension sollte nun automatisch heruntergeladen und installiert werden.

Fahren Sie anschließend mit der Konfiguration fort.

Datei-Upload '*Plugin Hochladen*'
---------------------------------

.. image:: ./_static/installation-file_upload.png

Zu erst müssen Sie sich im Backend Ihrer Shopware 5 Installation anmelden, der Link dafür:

.. only:: latex

    :latex:`{{\color{easyorange} \texttt{http(s)://IHRE-SHOPWARE-URL.de/backend}}`

.. only:: html

    ``http(s)://IHRE-SHOPWARE-URL.de/backend``

Öffnen Sie im Backend nun den Plugin-Manager. Klicken Sie dazu entweder die drei Tasten `STRG + ALT + P` gleichzeitig oder öffnen Sie den Manager mit der Maus über folgende Menü-Punkte: 

.. only:: latex

    :latex:`{{\color{easyorange} \texttt{Einstellungen -> Plugin-Manager}}`

.. only:: html

    ``Einstellungen -> Plugin-Manager``

Wählen Sie nun im Plugin-Manager Fenster den Menu-Punkt 

.. only:: latex

    :latex:`{{\color{easyorange} \texttt{Verwaltung -> Installiert}}`

.. only:: html

    ``Verwaltung -> Installiert``

(gelber Kasten (1)) aus und klicken Sie dort den Button **Plugin hochladen** (roter Kasten (2)).

Wählen Sie nun den lokalen Pfad aus wo sich das Zip-Archive der Shopware 5 Extension befindet und klicken Sie anschließend auf *Plugin hochladen*.

Beispiel: 

.. only:: latex

    :latex:`{{\color{easyorange} \texttt{C:\textbackslash{}easycredit-shopware-1.3.0.zip}}`

.. only:: html

    ``C:\easycredit-shopware-1.3.0.zip``


Fahren Sie anschließend mit der Konfiguration fort.

Kommandozeile
-------------

Entpacken Sie das Zip-Archive 

.. code-block:: console

    cp easycredit-shopware-1.3.0.zip /SHOPWARE_BASIS_VERZEICHNIS/engine/Shopware/Plugins/
    cd /SHOPWARE_BASIS_VERZEICHNIS/engine/Shopware/Plugins/
    unzip easycredit-shopware-1.3.0.zip
    rm easycredit-shopware-1.3.0.zip


Anschließend überprüfen Sie ob das folgende Verzeichnis existiert:

.. only:: latex

    :latex:`{{\color{easyorange} \texttt{/SHOPWARE\_BASIS\_VERZEICHNIS/engine/Shopware/Plugins/Netzkollektiv/EasyCredit}}`

.. only:: html

    ``engine/Shopware/Plugins/Netzkollektiv/EasyCredit``



Führen Sie nun folgende Befehle aus:

.. code-block:: console

    /SHOPWARE_BASIS_VERZEICHNIS/bin/console sw:plugin:refresh
    /SHOPWARE_BASIS_VERZEICHNIS/bin/console sw:plugin:install NetzkollektivEasyCredit
    /SHOPWARE_BASIS_VERZEICHNIS/bin/console sw:plugin:activate NetzkollektivEasyCredit


Fahren Sie anschließend mit der Konfiguration fort.