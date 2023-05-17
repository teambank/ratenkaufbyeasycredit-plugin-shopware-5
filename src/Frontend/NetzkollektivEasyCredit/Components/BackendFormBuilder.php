<?php
class EasyCredit_BackendFormBuilder
{
    public function __construct() {

    }

    public function build ($form) {
        $position = 10;
        // Frontend settings

        $form->setElement(
            'button',//'easycreditIntro',
            'easyCreditIntro',
            array(
                'label' => 'Intro',
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'helpText' => file_get_contents(dirname(__FILE__).'/../Views/backend/easycredit_config/intro.html'),
                'handler' => "function(btn) { Ext.Msg.alert('Intro', btn.helpText); }",
                'position' => $position++
            )
        );

        $form->setElement(
            'button',//'easycreditIntro',
            'easycreditCredentials',
            array(
                'label' => 'API-Zugangsdaten',
                'value' => true,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'helpText' => '<h2>API-Zugangsdaten</h2>',
                'position' => $position++
            )
        );

        $form->setElement(
            'text',
            'easycreditApiKey',
            array(
                'label' => 'Webshop-ID',
                'required' => true,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'stripCharsRe' => ' ',
                'description' => 'Ihre Webshop-ID finden Sie nach erfolgreicher Anmeldung in der Shopadministration (z.B. 1.de.xxxx.1) innerhalb des Partnerportals.',
                'position' => $position++
            )
        );

        $form->setElement(
            'text',
            'easycreditApiPassword',
            array(
                'label' => 'API-Kennwort',
                'required' => true,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'stripCharsRe' => ' ',
                'description' => 'Ihr API-Kennwort legen Sie in der Shopadministration innerhalbs des Partnerportals selbst fest.',
                'position' => $position++
            )
        );

        $form->setElement(
            'text',
            'easycreditApiSignature',
            array(
                'label' => 'API-Signatur (optional)',
                'required' => false,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'stripCharsRe' => ' ',
                'description' => 'Die API Signatur sichert die Datenübertragung gegen Datenmanipulation von Dritten ab. Sie können die API-Signatur im easyCredit-Ratenkauf Partnerportal aktivieren.',
                'position' => $position++
            )
        );

        if (is_file(__DIR__ . '/../Views/backend/plugins/easycredit/test.js')) {
            $form->setElement(
                'button',
                'easycreditButtonClientTest',
                array(
                    'label' => '<strong>Jetzt API-Zugangsdaten testen & Kennung synchronisieren<strong>',
                    'handler' => "function(btn) {"
                        . file_get_contents(__DIR__ . '/../Views/backend/plugins/easycredit/test.js') . "}",
                    'position' => $position++
                )
            );
        }

        $form->setElement(
            'button',//'easycreditIntro',
            'easycreditBehavior',
            array(
                'label' => 'Verhalten',
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'helpText' => '<h2>Verhalten</h2>',
                'position' => $position++
            )
        );

        $form->setElement(
            'boolean',
            'easycreditDebugLogging',
            array(
                'label' => 'API Debug Logging',
                'value' => false,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'position' => $position++
            )
        );

        $form->setElement(
            'select',
            'easycreditOrderStatus',
            array(
                'label' => 'Bestellungsstatus',
                'value' => 0,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'store' => 'base.OrderStatus',
                'displayField' => 'description',
                'valueField' => 'id',
                'position' => $position++
            )
        );

        $form->setElement(
            'select',
            'easycreditOrderErrorStatus',
            array(
                'label' => 'Bestellungsstatus bei Fehlern',
                'value' => 4,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'store' => 'base.OrderStatus',
                'displayField' => 'description',
                'valueField' => 'id',
                'position' => $position++
            )
        );

        $form->setElement(
            'select',
            'easycreditPaymentStatus',
            array(
                'label' => 'Zahlungsstatus',
                'value' => 12,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'store' => 'base.PaymentStatus',
                'displayField' => 'description',
                'valueField' => 'id',
                'position' => $position++
            )
        );

        $form->setElement(
            'boolean',
            'easycreditRemoveInterestFromOrder',
            array(
                'label' => 'Zinsen nach Bestellabschluss aus Bestellung entfernen',
                'value' => true,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'description' => 'Die Ausweisung der beim Ratenkauf anfallenden Zinsen ggü. dem Kunden ist rechtlich erforderlich. Für die Klärung, wie Sie die Zinsen mit in Ihre Buchhaltung übernehmen, empfehlen wir Ihnen sich mit Ihrem Steuerberater abzustimmen.',
                'position' => $position++
            )
        );

        $form->setElement(
            'boolean',
            'easycreditMarkShipped',
            array(
                'label' => '„Lieferung melden“ automatisch durchführen?',
                'value' => false,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'description' => 'Bei Aktivierung dieser Option wird die Lieferung bei dem in der folgenden Option eingestellten Bestellstatus automatisch an easyCredit-Ratenkauf übermittelt.',
                'position' => $position++
            )
        );

        $form->setElement(
            'select',
            'easycreditMarkShippedStatus',
            array(
                'label' => 'Lieferung bei folgendem Bestellstatus melden',
                'value' => 0,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'store' => 'base.OrderStatus',
                'displayField' => 'description',
                'valueField' => 'id',
                'position' => $position++
            )
        );

        $form->setElement(
            'boolean',
            'easycreditMarkRefunded',
            array(
                'label' => 'Rückabwicklung automatisch durchführen?',
                'value' => false,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'description' => 'Bei Aktivierung dieser Option wird die Rückabwicklung bei dem in der folgenden Option eingestellten Bestellstatus automatisch an easyCredit-Ratenkauf übermittelt.',
                'position' => $position++
            )
        );

        $form->setElement(
            'select',
            'easycreditMarkRefundedStatus',
            array(
                'label' => 'Rückabwicklung bei folgendem Bestellstatus durchführen',
                'value' => 0,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'store' => 'base.OrderStatus',
                'displayField' => 'description',
                'valueField' => 'id',
                'position' => $position++
            )
        );

        $form->setElement(
            'button',//'easycreditIntro',
            'easycreditMarketing',
            array(
                'label' => 'Marketing',
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'helpText' => file_get_contents(dirname(__FILE__).'/../Views/backend/easycredit_config/marketing-intro.html'),
                'handler' => "function(btn) { Ext.Msg.alert('Marketing', btn.helpText); }",
                'position' => $position++
            )
        );

        $form->setElement(
            'button',//'easycreditIntro',
            'easyCreditMarketingExpressHeading',
            array(
                'label' => 'Marketing - Express Button',
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'helpText' => '<h2 id="marketing-express">Marketing - Express Button</h2>',
                'position' => $position++
            )
        );

        $form->setElement(
            'boolean',
            'easyCreditExpressProduct',
            array(
                'label' => 'Zeige Express-Button auf Detailseite',
                'value' => true,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'description' => 'Steigern Sie ihre Conversion, indem Sie Kunden ermöglichen mit dem easyCredit-Ratenkauf direkt von der Produktseite aus zu bezahlen.',
                'position' => $position++
            )
        );

        $form->setElement(
            'boolean',
            'easyCreditExpressCart',
            array(
                'label' => 'Zeige Express-Button im Warenkorb',
                'value' => true,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'description' => 'Steigern Sie ihre Conversion, indem Sie Kunden ermöglichen mit dem easyCredit-Ratenkauf direkt aus dem Warenkorb zu bezahlen.',
                'position' => $position++
            )
        );

        $form->setElement(
            'boolean',
            'easyCreditExpressOffCanvas',
            array(
                'label' => 'Zeige Express-Button im Off-Canvas Warenkorb',
                'value' => true,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'description' => 'Steigern Sie ihre Conversion, indem Sie Kunden ermöglichen mit dem easyCredit-Ratenkauf direkt aus dem Off-Canvas Warenkorb zu bezahlen.',
                'position' => $position++
            )
        );

        $form->setElement(
            'button',//'easycreditIntro',
            'easyCreditMarketingWidgetHeading',
            array(
                'label' => 'Marketing - Widget',
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'helpText' => '<h2 id="marketing-widget">Marketing - Widget</h2>',
                'position' => $position++
            )
        );

        $form->setElement(
            'boolean',
            'easycreditModelWidget',
            array(
                'label' => 'Zeige Ratenrechner-Widget neben Produktpreis',
                'value' => true,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'description' => 'Für den größten Erfolg mit dem easyCredit-Ratenkauf empfehlen wir, das Widget zu aktivieren.',
                'position' => $position++
            )
        );

        $form->setElement(
            'boolean',
            'easycreditModelWidgetCart',
            array(
                'label' => 'Zeige Ratenrechner-Widget im Warenkorb',
                'value' => true,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'description' => 'Für den größten Erfolg mit dem easyCredit-Ratenkauf empfehlen wir, das Widget zu aktivieren.',
                'position' => $position++
            )
        );

        $form->setElement(
            'boolean',
            'easycreditModelWidgetOffCanvas',
            array(
                'label' => 'Zeige Ratenrechner-Widget im Off-Canvas Warenkorb',
                'value' => true,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'description' => 'Für den größten Erfolg mit dem easyCredit-Ratenkauf empfehlen wir, das Widget zu aktivieren.',
                'position' => $position++
            )
        );

        $form->setElement(
            'button',//'easycreditIntro',
            'easyCreditMarketingModalHeading',
            array(
                'label' => 'Marketing - Modal',
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'helpText' => '<h2 id="marketing-modal">Marketing - Modal</h2>',
                'position' => $position++
            )
        );

        $form->setElement(
            'boolean',
            'easyCreditMarketingModal',
            array(
                'label' => 'Modal automatisch einblenden',
                'value' => false,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'description' => 'Das Modal wird beim ersten Besuch des Onlineshops automatisch eingeblendet.',
                'position' => $position++
            )
        );

        $form->setElement(
            'number',
            'easyCreditMarketingModalSettingsDelay',
            array(
                'label' => 'Verzögerung (in Sekunden)',
                'value' => 5,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'description' => 'Hier können Sie in Sekunden die Verzögerung angeben, nach welcher dem Kunden beim Laden der Seite das Modal angezeigt wird (beispielsweise "10" für 10 Sekunden Verzögerung).',
                'position' => $position++
            )
        );

        $form->setElement(
            'number',
            'easyCreditMarketingModalSettingsSnoozeFor',
            array(
                'label' => 'Reaktivieren nach (in Sekunden)',
                'value' => 86400,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'description' => 'Hier können Sie in Sekunden angeben, nach welcher Zeit dem Kunden das Modal wieder angezeigt wird (beim Laden der Seite), nachdem er das Modal aktiv geschlossen hat (beispielsweise "3600" für 1 Stunde).',
                'position' => $position++
            )
        );

        $form->setElement(
            'mediaselection',
            'easyCreditMarketingModalSettingsMedia',
            array(
                'label' => 'Eigenes Bild verwenden',
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'position' => $position++,
                'readOnly' => false
            )
        );

        $form->setElement(
            'button',//'easycreditIntro',
            'easyCreditMarketingCardHeading',
            array(
                'label' => 'Marketing - Card',
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'helpText' => '<h2 id="marketing-card">Marketing - Card</h2>',
                'position' => $position++
            )
        );

        $form->setElement(
            'boolean',
            'easyCreditMarketingCard',
            array(
                'label' => 'Card innerhalb Produktliste (Kategorie) anzeigen',
                'value' => false,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'position' => $position++
            )
        );

        $form->setElement(
            'boolean',
            'easyCreditMarketingCardSearch',
            array(
                'label' => 'Card in Suchergebnissen anzeigen',
                'value' => false,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'position' => $position++
            )
        );

        $form->setElement(
            'number',
            'easyCreditMarketingCardSettingsPosition',
            array(
                'label' => 'Position in Produktliste',
                'value' => 0,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'position' => $position++
            )
        );

        $form->setElement(
            'mediaselection',
            'easyCreditMarketingCardSettingsMedia',
            array(
                'label' => 'Eigenes Bild verwenden',
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'position' => $position++,
                'readOnly' => false
            )
        );

        $form->setElement(
            'button',//'easycreditIntro',
            'easyCreditMarketingFlashboxHeading',
            array(
                'label' => 'Marketing - Flashbox',
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'helpText' => '<h2 id="marketing-flashbox">Marketing - Flashbox</h2>',
                'position' => $position++
            )
        );

        $form->setElement(
            'boolean',
            'easyCreditMarketingFlashbox',
            array(
                'label' => 'Flashbox am unteren Rand des Bildschirms anzeigen',
                'value' => false,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'position' => $position++
            )
        );

        $form->setElement(
            'mediaselection',
            'easyCreditMarketingFlashboxSettingsMedia',
            array(
                'label' => 'Eigenes Bild verwenden',
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'position' => $position++,
                'readOnly' => false
            )
        );

        $form->setElement(
            'button',//'easycreditIntro',
            'easyCreditMarketingBarHeading',
            array(
                'label' => 'Marketing - Bar',
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'helpText' => '<h2 id="marketing-bar">Marketing - Bar</h2>',
                'position' => $position++
            )
        );

        $form->setElement(
            'boolean',
            'easyCreditMarketingBar',
            array(
                'label' => 'Leiste am oberen Rand des Bildschirms anzeigen',
                'value' => false,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'position' => $position++
            )
        );

        $form->setElement(
            'button',//'easycreditIntro',
            'easycreditClickAndCollectIntro',
            array(
                'label' => 'Click & Collect',
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'helpText' => file_get_contents(dirname(__FILE__).'/../Views/backend/easycredit_config/clickandcollect.html'),
                'handler' => "function(btn) { Ext.Msg.alert('Click & Collect', btn.helpText); }",
                'position' => $position++
            )
        );

        $form->setElement(
            'button',//'easycreditIntro',
            'easyCreditClickAndCollectHeading',
            array(
                'label' => 'Click & Collect - Versandart',
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'helpText' => '<h2>Click & Collect - Versandart</h2>',
                'position' => $position++
            )
        );

        $form->setElement(
            'select',
            'easycreditClickAndCollectShippingMethod',
            array(
                'label' => 'Versandart',
                'value' => '',
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'store' => 'base.Dispatch',
                'displayField' => 'name',
                'valueField' => 'id',
                'position' => $position++
            )
        );
    }
}
