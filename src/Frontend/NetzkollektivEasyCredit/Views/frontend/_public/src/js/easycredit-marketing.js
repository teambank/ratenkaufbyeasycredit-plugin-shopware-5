(function () {
    var onHydrated = function (selector, cb) {
        if (!document.querySelector(selector)) {
            return
        }

        window.setTimeout(function() {
            if (!document.querySelector(selector).classList.contains('hydrated')) {
                return onHydrated(selector, cb);
            }
            cb();
        }, 50)
    }

    var styleCardListing = function () {
        var card = document.querySelector('easycredit-box-listing');

        if ( card ) {
            var siblings = n => [...n.parentElement.children].filter(c=>c!=n);
            var siblingsCard = siblings(card);

            var cardHeight = siblingsCard[0].clientHeight;
            card.style.height = cardHeight + 'px';
            card.style.visibility = 'hidden';
        }
    }

    var styleCardListingHydrated = function () {
        var card = document.querySelector('easycredit-box-listing');

        if ( card ) {
            card.shadowRoot.querySelector('.ec-box-listing').style.maxWidth = '100%';
            card.shadowRoot.querySelector('.ec-box-listing').style.height = '100%';
            card.shadowRoot.querySelector('.ec-box-listing__image').style.minHeight = '100%';
            card.style.visibility = '';
        }
    }

    var positionCardInListing = function () {
        var card = document.querySelector('easycredit-box-listing');

        if ( card ) {
            var siblings = n => [...n.parentElement.children].filter(c=>c!=n);
            var siblingsCard = siblings(card);
    
            var position = card.getAttribute('position');
            var previousPosition = ( typeof position === undefined ) ? null : Number( position - 1 );
            var appendAfterPosition = ( typeof position === undefined ) ? null : Number( position - 2 );
    
            if ( !position || previousPosition <= 0 ) {
                // do nothing
            } else if ( appendAfterPosition in siblingsCard ) {
                siblingsCard[appendAfterPosition].after(card);
            } else {
                card.parentElement.append(card);
            }
        }
    }

    styleCardListing();
    onHydrated('easycredit-box-listing', styleCardListingHydrated);
    positionCardInListing();
}());
