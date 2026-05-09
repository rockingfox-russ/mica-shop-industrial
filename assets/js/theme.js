/**
 * assets/js/theme.js
 * General theme JS: header scroll, mobile nav, tooltips, misc.
 */

( function () {
  'use strict';

  /* ── Header scroll shadow ── */
  const header = document.getElementById( 'masthead' );
  if ( header ) {
    let lastScroll = 0;
    window.addEventListener( 'scroll', () => {
      const y = window.scrollY;
      header.style.boxShadow = y > 10
        ? '0 2px 12px rgba(0,0,0,.12)'
        : '0 1px 3px rgba(0,0,0,.08)';
      lastScroll = y;
    }, { passive: true } );
  }

  /* ── Checkout: show/hide click & collect store picker ── */
  function initDeliveryToggle() {
    const collectWrap = document.getElementById( 'mica-click-collect-wrap' );
    if ( ! collectWrap ) return;

    const shippingMethods = document.querySelectorAll( 'input[name="shipping_method[0]"]' );
    if ( ! shippingMethods.length ) return;

    function toggleCollectField() {
      const selected = document.querySelector( 'input[name="shipping_method[0]"]:checked' );
      const isCollect = selected && (
        selected.value.includes( 'local_pickup' ) ||
        selected.value.includes( 'pickup' ) ||
        selected.value.includes( 'collect' )
      );
      collectWrap.style.display = isCollect ? '' : 'none';

      // Toggle required
      const select = document.getElementById( 'mica_collection_store' );
      if ( select ) {
        select.required = isCollect;
      }
    }

    shippingMethods.forEach( r => r.addEventListener( 'change', toggleCollectField ) );
    toggleCollectField();

    // WC updates checkout via ajax — re-bind on update
    document.body.addEventListener( 'updated_checkout', () => {
      const newMethods = document.querySelectorAll( 'input[name="shipping_method[0]"]' );
      newMethods.forEach( r => r.addEventListener( 'change', toggleCollectField ) );
      toggleCollectField();
    } );
  }

  /* ── Single product: gallery thumbnails ── */
  function initProductGallery() {
    const mainImg = document.querySelector( '.product-gallery-main img' );
    const thumbs  = document.querySelectorAll( '.gallery-thumb' );
    if ( ! mainImg || ! thumbs.length ) return;

    thumbs.forEach( thumb => {
      thumb.addEventListener( 'click', () => {
        const src    = thumb.dataset.full || thumb.querySelector( 'img' )?.src;
        const srcset = thumb.dataset.srcset || '';
        if ( src ) {
          mainImg.src    = src;
          mainImg.srcset = srcset;
          thumbs.forEach( t => t.classList.remove( 'active' ) );
          thumb.classList.add( 'active' );
        }
      } );
    } );
  }

  /* ── Single product: quantity stepper ── */
  function initQuantityStepper() {
    document.querySelectorAll( '.qty-control' ).forEach( control => {
      const input = control.querySelector( '.qty-input' );
      const minus = control.querySelector( '.qty-btn[data-action="minus"]' );
      const plus  = control.querySelector( '.qty-btn[data-action="plus"]' );
      if ( ! input ) return;

      const min = parseInt( input.min || '1', 10 );
      let   max = parseInt( input.max || '9999', 10 );

      // WC's hidden qty input inside form.cart
      const wcQty = () => document.querySelector( 'form.cart .wc-qty-hidden input[name="quantity"]' );
      const sync  = () => { const wq = wcQty(); if ( wq ) wq.value = input.value; };

      const clamp = () => {
        let v = parseInt( input.value, 10 );
        if ( isNaN( v ) || v < min ) v = min;
        if ( v > max ) v = max;
        input.value = v;
        sync();
      };

      minus?.addEventListener( 'click', () => {
        const v = parseInt( input.value, 10 );
        if ( v > min ) { input.value = v - 1; sync(); }
      } );
      plus?.addEventListener( 'click', () => {
        const v = parseInt( input.value, 10 );
        if ( v < max ) { input.value = v + 1; sync(); }
      } );

      input.addEventListener( 'change', clamp );

      // Variable products: update max when a variation is selected or cleared
      const form = control.closest( 'form.cart' ) || document.querySelector( 'form.cart' );
      if ( form ) {
        form.addEventListener( 'found_variation', ( e ) => {
          const variation = e.detail || ( e.originalEvent && e.originalEvent.detail );
          const inStock   = variation ? variation.is_in_stock : true;
          const qty       = variation ? variation.max_qty : null;
          max = ( qty === '' || qty === null || qty === undefined ) ? 9999 : parseInt( qty, 10 );
          input.max = max;
          control.classList.toggle( 'is-disabled', ! inStock );
          clamp();
        } );
        form.addEventListener( 'reset_data', () => {
          // Variation deselected — restore original product max
          max = parseInt( input.dataset.originalMax || '9999', 10 );
          input.max = max;
          input.value = min;
          control.classList.remove( 'is-disabled' );
          sync();
        } );
      }
    } );

    // WC fires jQuery custom events; bridge them to native CustomEvents so the listeners above work
    if ( window.jQuery ) {
      jQuery( document ).on( 'found_variation', ( e, variation ) => {
        document.querySelectorAll( 'form.cart' ).forEach( form => {
          form.dispatchEvent( new CustomEvent( 'found_variation', { detail: variation, bubbles: false } ) );
        } );
      } );
      jQuery( document ).on( 'reset_data', () => {
        document.querySelectorAll( 'form.cart' ).forEach( form => {
          form.dispatchEvent( new CustomEvent( 'reset_data', { bubbles: false } ) );
        } );
      } );

      // Sync variation barcode/SKU to the hidden inputs used by the stock checker
      initVariationStockSync();
    }
  }

  function initVariationStockSync() {
    const skuInput      = document.getElementById( 'mica-product-sku' );
    const barcodeInput  = document.getElementById( 'mica-product-barcode' );
    const checkBtn      = document.getElementById( 'btn-check-stock' );

    // Display elements in the product-info header
    const barcodeWrap   = document.getElementById( 'pdp-barcode-wrap' );
    const barcodeVal    = document.getElementById( 'pdp-barcode-val' );
    const skuWrap       = document.getElementById( 'pdp-sku-wrap' );
    const skuVal        = document.getElementById( 'pdp-sku-val' );

    if ( ! skuInput ) return;

    // Store originals so reset_data can restore them
    skuInput.dataset.original    = skuInput.value;
    if ( barcodeInput ) barcodeInput.dataset.original = barcodeInput.value;
    if ( barcodeVal  ) barcodeVal.dataset.original    = barcodeVal.textContent;
    if ( skuVal      ) skuVal.dataset.original        = skuVal.textContent;

    function setDisplay( barcode, sku ) {
      if ( barcodeWrap && barcodeVal ) {
        barcodeWrap.style.display = barcode ? '' : 'none';
        barcodeVal.textContent    = barcode;
      }
      if ( skuWrap && skuVal ) {
        skuWrap.style.display = sku ? '' : 'none';
        skuVal.textContent    = sku;
      }
    }

    jQuery( document ).on( 'found_variation', ( e, variation ) => {
      // Barcode: read from the variation form's variable_sku1 input (custom field)
      const barcodeField = document.querySelector( 'input[name="variable_sku1"]' )
                        || document.getElementById( 'variable_sku1' );
      const barcode = barcodeField ? barcodeField.value.trim() : '';

      // SKU: WC passes it directly in the variation data; fall back to description text
      let sku = ( variation && variation.sku ) ? variation.sku.trim() : '';
      if ( ! sku && variation && variation.variation_description ) {
        const match = variation.variation_description.replace( /<[^>]+>/g, '' ).trim().match( /\S+/ );
        if ( match ) sku = match[0];
      }

      // Update hidden inputs used by the stock checker
      if ( barcode ) {
        skuInput.value = barcode;
        if ( barcodeInput ) barcodeInput.value = barcode;
      } else if ( sku ) {
        skuInput.value = sku;
      }

      if ( checkBtn && skuInput.value ) checkBtn.dataset.sku = skuInput.value;

      // Update visible display
      setDisplay( barcode, sku );
    } );

    jQuery( document ).on( 'reset_data', () => {
      skuInput.value = skuInput.dataset.original;
      if ( barcodeInput ) barcodeInput.value = barcodeInput.dataset.original;
      if ( checkBtn ) checkBtn.dataset.sku = skuInput.dataset.original;

      // Restore original displayed values
      setDisplay(
        barcodeVal ? barcodeVal.dataset.original : '',
        skuVal     ? skuVal.dataset.original     : ''
      );
    } );
  }

  /* ── Product tabs ── */
  function initProductTabs() {
    const tabBtns   = document.querySelectorAll( '.tab-btn' );
    const tabPanels = document.querySelectorAll( '.tab-panel' );
    if ( ! tabBtns.length ) return;

    tabBtns.forEach( btn => {
      btn.addEventListener( 'click', () => {
        const target = btn.dataset.tab;
        tabBtns.forEach(   b => b.classList.remove( 'active' ) );
        tabPanels.forEach( p => p.classList.remove( 'active' ) );
        btn.classList.add( 'active' );
        document.getElementById( 'tab-' + target )?.classList.add( 'active' );
      } );
    } );
  }

  /* ── Click & Collect: store stock check on PDP ── */
  function initStoreStockCheck() {
    const storeSelect  = document.getElementById( 'mica-store-picker' );
    const stockResult  = document.getElementById( 'mica-store-stock-result' );
    const productIdEl  = document.getElementById( 'mica-product-id' );

    if ( ! storeSelect || ! stockResult || ! productIdEl ) return;

    const productId = productIdEl.value;

    storeSelect.addEventListener( 'change', () => {
      const storeId = storeSelect.value;
      if ( ! storeId ) {
        stockResult.innerHTML = '';
        return;
      }

      stockResult.innerHTML = '<span style="color:var(--clr-text-muted)">Checking stock…</span>';

      const body = new FormData();
      body.append( 'action',     'mica_store_stock' );
      body.append( 'product_id', productId );
      body.append( 'store_id',   storeId );

      fetch( micaData.ajaxUrl, { method: 'POST', body, credentials: 'same-origin' } )
        .then( r => r.json() )
        .then( data => {
          if ( data.success ) {
            const d = data.data;
            const cls  = d.in_stock ? 'in-stock' : 'no-stock';
            const icon = d.in_stock ? '✓' : '✗';
            stockResult.innerHTML = `<span class="store-stock-result ${ cls }">${ icon } ${ d.label }</span>`;
          }
        } )
        .catch( () => {
          stockResult.innerHTML = '<span style="color:var(--clr-error)">Could not check stock</span>';
        } );
    } );
  }

  /* ── Toast notification ── */
  window.micaToast = function ( message, type = 'success', duration = 3000 ) {
    let toast = document.getElementById( 'mica-toast' );
    if ( ! toast ) {
      toast = document.createElement( 'div' );
      toast.id = 'mica-toast';
      toast.style.cssText = [
        'position:fixed',
        'bottom:24px',
        'right:24px',
        'background:var(--clr-text)',
        'color:#fff',
        'padding:.75rem 1.25rem',
        'border-radius:8px',
        'font-size:.875rem',
        'font-weight:500',
        'z-index:9999',
        'transform:translateY(60px)',
        'opacity:0',
        'transition:all .3s ease',
        'pointer-events:none',
        'max-width:320px',
        'box-shadow:0 4px 16px rgba(0,0,0,.25)',
      ].join( ';' );
      document.body.appendChild( toast );
    }

    if ( type === 'error'   ) toast.style.background = 'var(--clr-error)';
    if ( type === 'success' ) toast.style.background = 'var(--clr-success)';
    if ( type === 'default' ) toast.style.background = 'var(--clr-text)';

    toast.textContent = message;
    requestAnimationFrame( () => {
      toast.style.transform = 'translateY(0)';
      toast.style.opacity   = '1';
    } );

    clearTimeout( toast._timer );
    toast._timer = setTimeout( () => {
      toast.style.transform = 'translateY(60px)';
      toast.style.opacity   = '0';
    }, duration );
  };

  /* ── CSS spinner keyframes (injected once) ── */
  if ( ! document.getElementById( 'mica-spin-style' ) ) {
    const style = document.createElement( 'style' );
    style.id = 'mica-spin-style';
    style.textContent = '@keyframes spin{to{transform:rotate(360deg)}} .spin{animation:spin .7s linear infinite}';
    document.head.appendChild( style );
  }

  /* ── Init on DOM ready ── */
  function init() {
    initDeliveryToggle();
    initProductGallery();
    initQuantityStepper();
    initProductTabs();
    initStoreStockCheck();
  }

  if ( document.readyState === 'loading' ) {
    document.addEventListener( 'DOMContentLoaded', init );
  } else {
    init();
  }

} )();
