(function (factory) {
if (typeof define === 'function' && define.amd) {
    // AMD. Register as an anonymous module depending on jQuery.
    define(['jquery'], factory);
} else {
    // No AMD. Register plugin with global jQuery object.
    factory(jQuery);
}
}(function ($) {

    var easycreditModal = {
        template: [
            '<div class="easycredit-modal">',
            '<div class="easycredit-embed-responsive"></div>',
            '</div>'
        ].join('\n'),
        handleModal: function(element, content) {
            var modal = $(this.template)
            modal.find('.easycredit-embed-responsive').append(content).css({
                'height': '900px',
                'max-height':'900px'
            });
            $(element).append(modal);
            modal.easycreditmodal();
        },
        init: function(element, content) {
            this.handleModal(element,content);
        }
    }

    var defaults = {
        hostname: 'https://ratenkauf.easycredit.de',
        endpoint: '/ratenkauf-ws/rest/v1/modellrechnung/guenstigsterRatenplan',
        iframeSrc: '/ratenkauf/content/intern/paymentPageBeispielrechnung.jsf',
        modal: easycreditModal.init.bind(easycreditModal),
        webshopId: null,
        amount: null,
        debug: false,
        currencySymbol: '&euro;', //"\u25B2",
        installmentTemplate: '%amount% %currency_symbol% / Monat',
        widgetTemplate: [
            '<div class="easycredit-widget">',
                '<span class="easycredit-suffix">%suffix% </span>',
                '<span class="easycredit-rate">%installmentTemplate%</span>',
                '<br />',
                '<a class="easycredit-link">%link_text%</a>',
            '</div>'
        ].join("\n"),
        suffix: 'Finanzieren ab',
        linkText: 'mehr Infos zum Ratenkauf'
    }

    var getApiUri = function(opts){
        return [
            opts.hostname+opts.endpoint,
            $.param({
                webshopId: opts.webshopId,
                finanzierungsbetrag: opts.amount
            })
        ].join('?');
    }
    var getIframeUri = function(opts){
        return [
            opts.hostname+opts.iframeSrc,
            $.param({
                'shopKennung': opts.webshopId,
                'bestellwert': opts.amount
            })
        ].join('?');
    }
    var getMinimumInstallment = function(uri,cb) {
        $.ajax({
            type : 'GET',
            url : uri,
            contentType : 'application/json; charset=utf-8',
            dataType : 'jsonp',
            success: cb
        });
    };
    var formatAmount = function( amount ) {
        return Number(Math.round(amount+'e2')+'e-2').toFixed(2).replace('.',',');
    }
    var template = function( template, data ){
        return template
          .replace(
            /%(\w*)%/g,
            function( m, key ){
              return data.hasOwnProperty( key ) ? data[ key ] : "";
            }
          );
    }
    var loadStyles = function(uri) {
        var bs = document.createElement('link');
        bs.rel   = 'stylesheet';
        bs.media ="screen";
        bs.href  = uri;
        document.head.appendChild(bs);
    }
    var getModalContent = function(uri) {
        return '<iframe class="embed-responsive-item" src="' + uri + '"></iframe>';
    }
    var showModal = function(element, opts) {
        var content = getModalContent(
            getIframeUri(opts)
        );
        opts.modal(element, content);
    }

    var rkPaymentPage = function(opts) {
        var opts = $.extend({}, defaults, opts);
        var me = $(this);

        if ($(this).data('easycredit-amount')) {
            opts.amount = $(this).data('easycredit-amount');
        }

        if (isNaN(opts.amount) || opts.amount < 200 || opts.amount > 5000) {
            if (opts.debug) {
                console.log(opts.amount+' is not between 200 and 5000');
            }
            return;
        }

        if (opts.webshopId == null
            || opts.webshopId == ''
        ) {
            throw new Error('webshopId must be set for easycredit widget');
        }

        var uri = getApiUri(opts);
        getMinimumInstallment(uri, function(res){
            if (!res || res.wsMessages.messages.length > 0) {
                return;
            }

            var data = {
                number_of_installments:   res.anzahlRaten,
                amount:                   formatAmount(res.betragRate),
                currency_symbol:          opts.currencySymbol,
                suffix:                   opts.suffix,
                link_text:                opts.linkText
            };
            data.installmentTemplate =    template(opts.installmentTemplate, data);

            var widget = $(template(opts.widgetTemplate,data));
            $(me).append(widget);
            widget.find('a').click(
                showModal.bind(this, me, opts)
            );
        });
    }

    $.fn.rkPaymentPage = function(opts) {
        return this.each(function(index,element){
            rkPaymentPage.apply(element,[opts]);
        });
    };

    window.rkPlugin = {};
    window.rkPlugin.anzeige = function(componentID, options) {
        $('#' + componentID).rkPaymentPage(options);
    };
}));
