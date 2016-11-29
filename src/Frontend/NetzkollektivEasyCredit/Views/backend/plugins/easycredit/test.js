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
            title = '<span style=\"font-weight: bold;\">Ratenkauf by easyCredit</span>',
            text;

        if (!data.status) {
            text = 'Bitte geben Sie den API-Key und den API-Token ein!'
        } else {
            if (data.valid) {
                text = 'Zugangsdaten korrekt. Plugin ist einsatzbereit!';
            } else {
                text = 'Die Zugangsdaten sind inkorrekt. Bitte überprüfen Sie Ihre Eingaben.';
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