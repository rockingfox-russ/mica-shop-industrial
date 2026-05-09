/**
 * assets/js/stock-modal.js
 * In-store stock checker modal — single product pages only.
 */

( function () {
  'use strict';

/* ═══════════════════════════════════════════
     STOCK MODAL
  ═══════════════════════════════════════════ */
  const overlay    = document.getElementById( 'stock-modal-overlay' );
  const loading    = document.getElementById( 'stock-modal-loading' );
  const results    = document.getElementById( 'stock-modal-results' );
  const storeList  = document.getElementById( 'stock-store-list' );
  const errorWrap  = document.getElementById( 'stock-modal-error' );
  const errorMsg   = document.getElementById( 'stock-error-msg' );
  const closeBtn   = document.getElementById( 'stock-modal-close' );
  const checkBtn   = document.getElementById( 'btn-check-stock' );

  if ( ! overlay ) return; // Not on a PDP

  /* ── Open / close ── */
  function openModal() {
    overlay.classList.add( 'open' );
    overlay.removeAttribute( 'aria-hidden' );
    document.body.style.overflow = 'hidden';
    closeBtn?.focus();
  }

  function closeModal() {
    overlay.classList.remove( 'open' );
    overlay.setAttribute( 'aria-hidden', 'true' );
    document.body.style.overflow = '';
  }

  closeBtn?.addEventListener( 'click', closeModal );
  overlay.addEventListener( 'click', e => {
    if ( e.target === overlay ) closeModal();
  } );
  document.addEventListener( 'keydown', e => {
    if ( e.key === 'Escape' && overlay.classList.contains( 'open' ) ) closeModal();
  } );

  /* ── Show states ── */
  function showLoading() {
    loading   && ( loading.style.display   = '' );
    results   && ( results.style.display   = 'none' );
    errorWrap && ( errorWrap.style.display = 'none' );
  }

  function showResults( stores, userPos ) {
    loading   && ( loading.style.display   = 'none' );
    errorWrap && ( errorWrap.style.display = 'none' );
    results   && ( results.style.display   = '' );

    if ( ! storeList ) return;

    storeList.innerHTML = renderStoreList( stores, userPos );
  }

  function showError( message ) {
    loading   && ( loading.style.display   = 'none' );
    results   && ( results.style.display   = 'none' );
    if ( errorWrap ) errorWrap.style.display = '';
    if ( errorMsg  ) errorMsg.textContent = message;
  }

  /* ── HTML escape ── */
  function esc( str ) {
    return String( str )
      .replace( /&/g, '&amp;' )
      .replace( /</g, '&lt;' )
      .replace( />/g, '&gt;' )
      .replace( /"/g, '&quot;' );
  }

  /* ── Haversine distance (km) ── */
  function haversine( lat1, lng1, lat2, lng2 ) {
    const R  = 6371;
    const dL = ( lat2 - lat1 ) * Math.PI / 180;
    const dN = ( lng2 - lng1 ) * Math.PI / 180;
    const a  = Math.sin( dL / 2 ) ** 2
             + Math.cos( lat1 * Math.PI / 180 ) * Math.cos( lat2 * Math.PI / 180 )
             * Math.sin( dN / 2 ) ** 2;
    return R * 2 * Math.atan2( Math.sqrt( a ), Math.sqrt( 1 - a ) );
  }

  /* ── Request browser geolocation (5s timeout, resolves null on failure) ── */
  function getUserLocation() {
    return new Promise( resolve => {
      if ( ! navigator.geolocation ) { resolve( null ); return; }
      const timer = setTimeout( () => resolve( null ), 5000 );
      navigator.geolocation.getCurrentPosition(
        pos  => { clearTimeout( timer ); resolve( { lat: pos.coords.latitude, lng: pos.coords.longitude } ); },
        ()   => { clearTimeout( timer ); resolve( null ); },
        { timeout: 5000, maximumAge: 300000 }
      );
    } );
  }

  /* ── Build store row HTML ── */
  function storeRowHTML( store, badge ) {
    const levelClass  = 'dot-'    + store.level;
    const statusClass = 'status-' + store.level;
    const dist        = store._dist != null ? `<span class="stock-store-dist">${ store._dist.toFixed( 1 ) } km away</span>` : '';
    const badgeHTML   = badge ? `<span class="stock-nearest-badge">${ esc( badge ) }</span>` : '';

    return `
      <div class="stock-store-row${ badge ? ' is-nearest' : '' }">
        <span class="stock-store-dot ${ levelClass }"></span>
        <div class="stock-store-info">
          <div class="stock-store-name">${ esc( store.store_name ) }${ badgeHTML }</div>
          <div class="stock-store-city">${ esc( store.city ) }${ store.hours ? ' · ' + esc( store.hours ) : '' }${ dist ? ' - ' + dist : '' }</div>
        </div>
        <div class="stock-store-status ${ statusClass }">
          ${ esc( store.label ) }
        </div>
      </div>`;
  }

  /* ── Render full store list, nearest-3 pinned to top when location known ── */
  function renderStoreList( stores, userPos ) {
    if ( ! userPos ) {
      return stores.map( s => storeRowHTML( s, null ) ).join( '' );
    }

    // Attach distance to each store that has coords
    const withDist = stores.map( s => {
      const d = ( s.lat && s.lng )
        ? haversine( userPos.lat, userPos.lng, s.lat, s.lng )
        : Infinity;
      return { ...s, _dist: d === Infinity ? null : d };
    } );

    // Split: stores with known coords vs without
    const located   = withDist.filter( s => s._dist != null ).sort( ( a, b ) => a._dist - b._dist );
    const unlocated = withDist.filter( s => s._dist == null );

    const nearest  = located.slice( 0, 3 );
    const rest     = [ ...located.slice( 3 ), ...unlocated ].sort( ( a, b ) => b.qty - a.qty );

    let html = '';

    if ( nearest.length ) {
      html += `<div class="stock-section-label">Nearest to you</div>`;
      html += nearest.map( ( s, i ) => storeRowHTML( s, i === 0 ? 'Closest' : null ) ).join( '' );
    }

    if ( rest.length ) {
      html += `<div class="stock-section-label stock-section-label--rest">All stores</div>`;
      html += rest.map( s => storeRowHTML( s, null ) ).join( '' );
    }

    return html;
  }

  /* ── Fetch stock + geolocation in parallel ── */
  function fetchStock( productId, sku, nonce ) {
    openModal();
    showLoading();

    const formData = new FormData();
    formData.append( 'action',     'mica_check_store_stock' );
    formData.append( 'product_id', productId );
    formData.append( 'sku',        sku );
    formData.append( 'nonce',      nonce );

    const stockPromise = fetch( micaData.ajaxUrl, {
      method: 'POST',
      body: formData,
      credentials: 'same-origin',
    } ).then( r => r.json() );

    Promise.all( [ stockPromise, getUserLocation() ] )
      .then( ( [ data, userPos ] ) => {
        if ( data.success && data.data.stores ) {
          showResults( data.data.stores, userPos );
        } else {
          showError( data.data?.message || 'Could not load stock data. Please try again.' );
        }
      } )
      .catch( () => {
        showError( 'Network error — please check your connection and try again.' );
      } );
  }

  /* ── Bind check stock button ── */
  function bindCheckBtn( btn ) {
    if ( ! btn || btn.dataset.stockBound ) return;
    btn.dataset.stockBound = '1';

    btn.addEventListener( 'click', () => {
      const productId = document.getElementById( 'mica-product-id' )?.value
                     || btn.dataset.productId || '';
      const sku       = document.getElementById( 'mica-product-sku' )?.value
                     || btn.dataset.sku || '';
      const nonce     = btn.dataset.nonce || micaData.nonce;

      if ( ! sku ) {
        openModal();
        showError( 'No SKU found for this product.' );
        return;
      }

      fetchStock( productId, sku, nonce );
    } );
  }

  bindCheckBtn( checkBtn );

  // Re-bind after Ajax filter reloads the grid (cards with quick-view stock buttons)
  document.addEventListener( 'mica:grid-updated', () => {
    initPaintSwatches();
    document.querySelectorAll( '[data-action="check-stock"]' ).forEach( bindCheckBtn );
  } );

  /* ── Init paint swatches on load ── */
  if ( document.readyState === 'loading' ) {
    document.addEventListener( 'DOMContentLoaded', initPaintSwatches );
  } else {
    initPaintSwatches();
  }

} )();
