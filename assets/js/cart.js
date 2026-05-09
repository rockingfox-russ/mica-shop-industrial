/**
 * assets/js/cart.js
 * Mini cart fragment updates + cart page qty stepper.
 */

( function () {
  'use strict';

  /* ── Update cart count badge from WC fragments ── */
  document.body.addEventListener( 'wc_fragments_refreshed', updateCartBadge );
  document.body.addEventListener( 'wc_fragments_loaded',    updateCartBadge );

  function updateCartBadge() {
    const badge = document.getElementById( 'cart-count' );
    if ( ! badge ) return;
    const count = parseInt( badge.textContent.trim(), 10 );
    badge.style.display = count > 0 ? '' : 'none';
  }

  /* ── Cart page: qty change → update cart via WC Ajax ── */
  function initCartQtyUpdate() {
    document.querySelectorAll( '.cart-qty-input' ).forEach( input => {
      if ( input.dataset.bound ) return;
      input.dataset.bound = '1';

      let timer;
      input.addEventListener( 'change', () => {
        clearTimeout( timer );
        timer = setTimeout( () => {
          // WC handles this via the update cart button — just trigger it
          const updateBtn = document.querySelector( '[name="update_cart"]' );
          if ( updateBtn ) {
            updateBtn.removeAttribute( 'disabled' );
            updateBtn.click();
          }
        }, 600 );
      } );
    } );
  }

  /* ── Single product ATC — update go-to-cart link ── */
  const singleAtcBtn = document.getElementById( 'single-atc-btn' );
  if ( singleAtcBtn ) {
    singleAtcBtn.addEventListener( 'click', function () {
      const productId = this.dataset.productId;
      const nonce     = this.dataset.nonce;
      const qtyInput  = document.querySelector( '.qty-input' );
      const qty       = qtyInput ? parseInt( qtyInput.value, 10 ) : 1;
      const self      = this;

      self.disabled = true;
      self.innerHTML = '<svg class="spin" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4"/></svg> Adding…';

      const body = new FormData();
      body.append( 'action',     'woocommerce_ajax_add_to_cart' );
      body.append( 'product_id', productId );
      body.append( 'quantity',   qty );
      body.append( 'nonce',      nonce );

      fetch( micaData.ajaxUrl, { method: 'POST', body, credentials: 'same-origin' } )
        .then( r => r.json() )
        .then( data => {
          self.disabled  = false;
          self.innerHTML = '✓ Added to Cart';
          self.style.background = 'var(--clr-success)';

          // Show go-to-cart link
          const gotoCart = document.getElementById( 'single-goto-cart' );
          if ( gotoCart ) gotoCart.style.display = 'inline-flex';

          // Update fragments
          if ( data.fragments ) {
            Object.entries( data.fragments ).forEach( ( [ sel, html ] ) => {
              const el = document.querySelector( sel );
              if ( el ) el.outerHTML = html;
            } );
          }

          if ( window.micaToast ) {
            micaToast( 'Added to cart!', 'success' );
          }
        } )
        .catch( () => {
          self.disabled  = false;
          self.innerHTML = 'Error — try again';
          self.style.background = 'var(--clr-error)';
        } );
    } );
  }

  if ( document.readyState === 'loading' ) {
    document.addEventListener( 'DOMContentLoaded', initCartQtyUpdate );
  } else {
    initCartQtyUpdate();
  }

} )();
