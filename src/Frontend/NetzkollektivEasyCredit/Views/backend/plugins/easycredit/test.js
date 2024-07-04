var pnl = btn.up('panel');
var url = document.location.pathname + 'paymentEasycredit/verifyCredentials';
var els = pnl.query('[isFormField]'),
    vls = {};

Ext.Array.each(els, function(el, i) {
    var v = el.getSubmitValue();
    if(v === false) { v = 0; }
    if(v === true) { v = 1; }
    vls[el.elementName] = v;
});


Ext.Ajax.request({
    url: url,
    params: vls,
    jsonData: true,
    success: function (response) {
        var data = Ext.decode(response.responseText),
            title = '<span style=\"font-weight: bold;\">easyCredit</span>',
            text;

        if (!data.status) {
            text = 'Bitte geben Sie die Webshop-ID und das API-Kennwort ein!'
        } else {
            if (data.valid) {
                text = 'Zugangsdaten korrekt. Plugin ist einsatzbereit!';
            } else {
                text = 'Die Zugangsdaten sind inkorrekt oder Ihr Webshop ist noch nicht aktiviert. <br><br>Bitte überprüfen Sie Ihre Eingaben oder wenden Sie sich an Ihren Ansprechpartner von easyCredit.';
            }
        }

        Shopware.Notification.createStickyGrowlMessage({
            title: title,
            text: text,
            width: 440,
            log: false
        });
    }
});
