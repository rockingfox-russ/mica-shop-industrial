/**
 * assets/js/filters.js — Shop filter engine
 *
 * Brands  → product tags  (filter_tag[])
 * AJAX    → no page reload for price/availability/brand/attribute changes
 * Category changes → full page navigate (clean URLs, SEO)
 * Back/forward → page reload (URL holds full state)
 */

( function () {
  'use strict';

  const container      = document.getElementById( 'products-container' );
  if ( ! container ) return; // not a shop page

  const resultCount    = document.getElementById( 'result-count' );
  const paginationWrap = document.getElementById( 'pagination-wrap' );
  const clearAllBtn    = document.getElementById( 'filter-clear-all' );
  const sortSelect     = document.getElementById( 'shop-sort' );

  const scopeCategoryId = window.micaFilterState?.scopeCategoryId ?? 0;
  let   loading         = false;
  let   debounce;

  // ── Collect all checked filter values from DOM ───────────────────────────

  function collectFilters() {
    const f = {
      min_price:        document.getElementById( 'filter-min-price' )?.value.trim() || '',
      max_price:        document.getElementById( 'filter-max-price' )?.value.trim() || '',
      orderby:          sortSelect?.value || 'menu_order',
      on_sale:          document.getElementById( 'filter-on-sale' )?.checked        || false,
      in_stock:         document.getElementById( 'filter-in-stock' )?.checked       || false,
      out_of_stock:     document.getElementById( 'filter-out-of-stock' )?.checked   || false,
      tags:             [],
      attributes:       {},
      local_attributes: {},
    };

    document.querySelectorAll( 'input[name="filter_tag[]"]:checked' ).forEach( cb => {
      f.tags.push( cb.value );
    } );

    document.querySelectorAll( 'input[name^="filter_attr"]:checked' ).forEach( cb => {
      const t = cb.dataset.taxonomy;
      if ( ! f.attributes[ t ] ) f.attributes[ t ] = [];
      f.attributes[ t ].push( cb.value );
    } );

    document.querySelectorAll( 'input[name^="filter_local_attr"]:checked' ).forEach( cb => {
      const t = cb.dataset.taxonomy;
      if ( ! f.local_attributes[ t ] ) f.local_attributes[ t ] = [];
      f.local_attributes[ t ].push( cb.value );
    } );

    return f;
  }

  function hasActiveFilters( f ) {
    return (
      f.min_price !== '' || f.max_price !== '' ||
      f.on_sale || f.in_stock || f.out_of_stock ||
      f.tags.length > 0 ||
      Object.values( f.attributes ).some( v => v.length > 0 ) ||
      Object.values( f.local_attributes ).some( v => v.length > 0 )
    );
  }

  // ── Active filter chips ──────────────────────────────────────────────────

  function renderActiveTags( f ) {
    const bar = document.getElementById( 'active-filters-bar' );
    if ( ! bar ) return;

    const chips = [];

    if ( f.min_price !== '' || f.max_price !== '' ) {
      chips.push( { key: 'price', label: 'R' + ( f.min_price || '0' ) + ' – R' + ( f.max_price || '∞' ) } );
    }
    if ( f.on_sale )      chips.push( { key: 'on_sale',       label: 'On Promotion' } );
    if ( f.in_stock )     chips.push( { key: 'in_stock',      label: 'In Stock' } );
    if ( f.out_of_stock ) chips.push( { key: 'out_of_stock',  label: 'Out of Stock' } );

    f.tags.forEach( v => {
      const label = document.querySelector( `input[name="filter_tag[]"][value="${ CSS.escape( v ) }"]` )
        ?.closest( 'label' )?.querySelector( '.filter-check-name' )?.textContent.trim() || v;
      chips.push( { key: 'tag_' + v, label } );
    } );

    Object.entries( f.attributes ).forEach( ( [ t, vals ] ) => {
      vals.forEach( v => chips.push( { key: 'attr_' + t + '_' + v, label: v } ) );
    } );

    Object.entries( f.local_attributes ).forEach( ( [ t, vals ] ) => {
      vals.forEach( v => chips.push( { key: 'local_' + t + '_' + v, label: v } ) );
    } );

    bar.innerHTML = chips.map( c =>
      `<span class="active-filter-tag" data-filter-key="${ c.key }">
        ${ escHtml( c.label ) }
        <button type="button" aria-label="Remove filter">&times;</button>
      </span>`
    ).join( '' );

    bar.querySelectorAll( 'button' ).forEach( btn => {
      btn.addEventListener( 'click', () =>
        removeFilter( btn.closest( '.active-filter-tag' ).dataset.filterKey )
      );
    } );

    if ( clearAllBtn ) clearAllBtn.style.display = chips.length ? '' : 'none';
  }

  function escHtml( str ) {
    return String( str ).replace( /&/g, '&amp;' ).replace( /</g, '&lt;' ).replace( />/g, '&gt;' );
  }

  // ── Remove / clear filters ───────────────────────────────────────────────

  function removeFilter( key ) {
    if ( key === 'price' ) {
      document.getElementById( 'filter-min-price' ).value = '';
      document.getElementById( 'filter-max-price' ).value = '';
      syncSliderFromInputs();
    } else if ( key === 'on_sale' ) {
      document.getElementById( 'filter-on-sale' ).checked = false;
    } else if ( key === 'in_stock' ) {
      document.getElementById( 'filter-in-stock' ).checked = false;
    } else if ( key === 'out_of_stock' ) {
      document.getElementById( 'filter-out-of-stock' ).checked = false;
    } else if ( key.startsWith( 'tag_' ) ) {
      const slug = key.slice( 4 );
      document.querySelectorAll( `input[name="filter_tag[]"][value="${ CSS.escape( slug ) }"]` )
        .forEach( cb => cb.checked = false );
    } else if ( key.startsWith( 'attr_' ) ) {
      document.querySelectorAll( 'input[name^="filter_attr"]:checked' ).forEach( cb => {
        if ( 'attr_' + cb.dataset.taxonomy + '_' + cb.value === key ) cb.checked = false;
      } );
    } else if ( key.startsWith( 'local_' ) ) {
      document.querySelectorAll( 'input[name^="filter_local_attr"]:checked' ).forEach( cb => {
        if ( 'local_' + cb.dataset.taxonomy + '_' + cb.value === key ) cb.checked = false;
      } );
    }
    runFilters();
  }

  function clearAllFilters() {
    document.getElementById( 'filter-min-price' ) && ( document.getElementById( 'filter-min-price' ).value = '' );
    document.getElementById( 'filter-max-price' ) && ( document.getElementById( 'filter-max-price' ).value = '' );
    [ 'filter-on-sale', 'filter-in-stock', 'filter-out-of-stock' ].forEach( id => {
      const el = document.getElementById( id );
      if ( el ) el.checked = false;
    } );
    document.querySelectorAll( 'input[name="filter_tag[]"], input[name^="filter_attr"], input[name^="filter_local_attr"]' )
      .forEach( cb => cb.checked = false );
    if ( sortSelect ) sortSelect.value = 'menu_order';
    syncSliderFromInputs();
    runFilters();
  }

  // ── Loading state ────────────────────────────────────────────────────────

  function setLoading( on ) {
    loading = on;
    const grid = document.getElementById( 'products-grid' );
    if ( on && ! grid ) {
      container.innerHTML =
        '<div class="products-grid">' +
        Array.from( { length: 8 } ).map( () =>
          '<div class="skeleton" style="aspect-ratio:1;border-radius:12px"></div>'
        ).join( '' ) +
        '</div>';
    } else if ( grid ) {
      grid.style.opacity = on ? '0.4' : '1';
    }
  }

  // ── AJAX filter run ──────────────────────────────────────────────────────

  function runFilters( page = 1 ) {
    if ( loading ) return;

    const f = collectFilters();
    renderActiveTags( f );

    const body = new FormData();
    body.append( 'action',      'mica_filter_products' );
    body.append( 'nonce',       micaData.nonce );
    body.append( 'category_id', scopeCategoryId );
    body.append( 'orderby',     f.orderby );
    body.append( 'paged',       page );

    if ( f.min_price !== '' ) body.append( 'min_price', f.min_price );
    if ( f.max_price !== '' ) body.append( 'max_price', f.max_price );
    if ( f.on_sale )          body.append( 'on_sale',       1 );
    if ( f.in_stock )         body.append( 'in_stock',      1 );
    if ( f.out_of_stock )     body.append( 'out_of_stock',  1 );

    f.tags.forEach( v => body.append( 'tags[]', v ) );

    Object.entries( f.attributes ).forEach( ( [ t, vals ] ) => {
      vals.forEach( v => body.append( `attributes[${ t }][]`, v ) );
    } );
    Object.entries( f.local_attributes ).forEach( ( [ t, vals ] ) => {
      vals.forEach( v => body.append( `local_attributes[${ t }][]`, v ) );
    } );

    setLoading( true );

    fetch( micaData.ajaxUrl, { method: 'POST', body, credentials: 'same-origin' } )
      .then( r => r.json() )
      .then( data => {
        if ( ! data.success ) throw new Error( 'Filter failed' );

        container.innerHTML = data.data.html;

        // paginationWrap was inside container and is now detached — re-find or create it
        let pw = document.getElementById( 'pagination-wrap' );
        if ( ! pw ) {
          pw = document.createElement( 'div' );
          pw.className = 'pagination-wrap';
          pw.id        = 'pagination-wrap';
          container.appendChild( pw );
        }
        pw.innerHTML = data.data.pagination || '';
        bindPagination();

        if ( resultCount ) {
          const n = data.data.found;
          resultCount.innerHTML = `<strong>${ n }</strong> product${ n !== 1 ? 's' : '' }`;
        }

        updateURL( f, page );
        container.scrollIntoView( { behavior: 'smooth', block: 'start' } );
        setLoading( false );
        bindAddToCart();
      } )
      .catch( err => { console.error( '[Filters]', err ); setLoading( false ); } );
  }

  // ── URL management ───────────────────────────────────────────────────────

  function updateURL( f, page ) {
    const url    = new URL( window.location.href );
    const params = url.searchParams;

    // Wipe all managed params
    [ 'min_price', 'max_price', 'orderby', 'on_sale', 'in_stock', 'out_of_stock', 'paged' ]
      .forEach( k => params.delete( k ) );
    [ ...params.keys() ].filter( k =>
      k.startsWith( 'filter_tag' ) ||
      k.startsWith( 'filter_attr' ) ||
      k.startsWith( 'filter_local_attr' )
    ).forEach( k => params.delete( k ) );

    if ( f.min_price !== '' )         params.set( 'min_price',    f.min_price );
    if ( f.max_price !== '' )         params.set( 'max_price',    f.max_price );
    if ( f.orderby !== 'menu_order' ) params.set( 'orderby',      f.orderby );
    if ( f.on_sale )                  params.set( 'on_sale',      1 );
    if ( f.in_stock )                 params.set( 'in_stock',     1 );
    if ( f.out_of_stock )             params.set( 'out_of_stock', 1 );
    if ( page > 1 )                   params.set( 'paged',        page );

    f.tags.forEach( v => params.append( 'filter_tag[]', v ) );
    Object.entries( f.attributes ).forEach( ( [ t, vals ] ) => {
      vals.forEach( v => params.append( `filter_attr[${ t }][]`, v ) );
    } );
    Object.entries( f.local_attributes ).forEach( ( [ t, vals ] ) => {
      vals.forEach( v => params.append( `filter_local_attr[${ t }][]`, v ) );
    } );

    window.history.pushState( null, '', url.toString() );
  }

  // ── Price range slider ───────────────────────────────────────────────────

  function initPriceSlider() {
    const wrap     = document.getElementById( 'price-slider-wrap' );
    if ( ! wrap ) return;

    const minSlider = document.getElementById( 'price-slider-min' );
    const maxSlider = document.getElementById( 'price-slider-max' );
    const fill      = document.getElementById( 'price-slider-fill' );
    const minInput  = document.getElementById( 'filter-min-price' );
    const maxInput  = document.getElementById( 'filter-max-price' );

    const absMin = parseFloat( wrap.dataset.min );
    const absMax = parseFloat( wrap.dataset.max );
    const range  = absMax - absMin || 1;

    function pct( v ) { return ( ( v - absMin ) / range ) * 100; }

    function updateFill() {
      const lo = parseFloat( minSlider.value );
      const hi = parseFloat( maxSlider.value );
      fill.style.left  = pct( lo ) + '%';
      fill.style.width = ( pct( hi ) - pct( lo ) ) + '%';
    }

    function sliderChanged() {
      let lo = parseFloat( minSlider.value );
      let hi = parseFloat( maxSlider.value );
      const gap = Math.max( 1, Math.round( range * 0.005 ) );

      if ( lo >= hi ) {
        if ( this === minSlider ) { lo = hi - gap; minSlider.value = lo; }
        else                      { hi = lo + gap; maxSlider.value = hi; }
      }

      if ( minInput ) minInput.value = lo <= absMin ? '' : lo;
      if ( maxInput ) maxInput.value = hi >= absMax ? '' : hi;
      updateFill();
    }

    minSlider.addEventListener( 'input', sliderChanged );
    maxSlider.addEventListener( 'input', sliderChanged );

    if ( minInput ) {
      minInput.addEventListener( 'input', syncSliderFromInputs );
      minInput.addEventListener( 'keydown', e => { if ( e.key === 'Enter' ) runFilters(); } );
    }
    if ( maxInput ) {
      maxInput.addEventListener( 'input', syncSliderFromInputs );
      maxInput.addEventListener( 'keydown', e => { if ( e.key === 'Enter' ) runFilters(); } );
    }

    updateFill(); // draw initial state
  }

  function syncSliderFromInputs() {
    const wrap     = document.getElementById( 'price-slider-wrap' );
    const minSlider = document.getElementById( 'price-slider-min' );
    const maxSlider = document.getElementById( 'price-slider-max' );
    const fill      = document.getElementById( 'price-slider-fill' );
    const minInput  = document.getElementById( 'filter-min-price' );
    const maxInput  = document.getElementById( 'filter-max-price' );
    if ( ! wrap || ! minSlider || ! maxSlider ) return;

    const absMin = parseFloat( wrap.dataset.min );
    const absMax = parseFloat( wrap.dataset.max );
    const range  = absMax - absMin || 1;

    let lo = minInput?.value !== '' ? parseFloat( minInput.value ) : absMin;
    let hi = maxInput?.value !== '' ? parseFloat( maxInput.value ) : absMax;
    lo = Math.max( absMin, Math.min( lo, absMax ) );
    hi = Math.max( absMin, Math.min( hi, absMax ) );
    if ( lo > hi ) [ lo, hi ] = [ hi, lo ];

    minSlider.value = lo;
    maxSlider.value = hi;
    if ( fill ) {
      fill.style.left  = ( ( lo - absMin ) / range * 100 ) + '%';
      fill.style.width = ( ( hi - lo )     / range * 100 ) + '%';
    }
  }

  // ── Show more / less ─────────────────────────────────────────────────────

  function bindShowMore() {
    document.querySelectorAll( '.filter-show-more' ).forEach( btn => {
      const list      = btn.closest( '.filter-checkbox-list, .filter-section-body' );
      const allLabels = list ? [ ...list.querySelectorAll( '.filter-check-label' ) ] : [];
      const overflow  = allLabels.filter( ( _, i ) => i >= 5 );

      btn.addEventListener( 'click', () => {
        const expanded = btn.dataset.expanded === '1';
        if ( expanded ) {
          overflow.forEach( l => { if ( ! l.querySelector( 'input' ).checked ) l.classList.add( 'filter-item-hidden' ); } );
          btn.dataset.expanded = '0';
          btn.textContent = btn.dataset.showText;
        } else {
          overflow.forEach( l => l.classList.remove( 'filter-item-hidden' ) );
          btn.dataset.expanded = '1';
          btn.textContent = btn.dataset.hideText;
        }
      } );
    } );
  }

  // ── Search within filter sections ────────────────────────────────────────

  function bindFilterSearch() {
    document.querySelectorAll( '.filter-search-input' ).forEach( input => {
      input.addEventListener( 'input', () => {
        const q    = input.value.toLowerCase().trim();
        const list = input.nextElementSibling; // .filter-checkbox-list
        if ( ! list ) return;
        list.querySelectorAll( '.filter-check-label' ).forEach( label => {
          const name = label.querySelector( '.filter-check-name' )?.textContent.toLowerCase() || '';
          label.style.display = ( ! q || name.includes( q ) ) ? '' : 'none';
        } );
      } );
    } );
  }

  // ── Category subcat dropdowns ────────────────────────────────────────────

  function bindCategoryToggles() {
    document.querySelectorAll( '.filter-cat-toggle' ).forEach( btn => {
      btn.addEventListener( 'click', () => {
        const expanded = btn.getAttribute( 'aria-expanded' ) === 'true';
        btn.setAttribute( 'aria-expanded', String( ! expanded ) );
        const sublist = btn.closest( '.filter-cat-item' )?.querySelector( '.filter-subcat-list' );
        if ( sublist ) {
          if ( expanded ) sublist.setAttribute( 'hidden', '' );
          else            sublist.removeAttribute( 'hidden' );
        }
      } );
    } );
  }

  // ── Section collapse/expand ──────────────────────────────────────────────

  function bindSectionToggles() {
    document.querySelectorAll( '.filter-section-toggle' ).forEach( btn => {
      btn.addEventListener( 'click', () => {
        const body     = btn.nextElementSibling;
        const expanded = btn.getAttribute( 'aria-expanded' ) === 'true';
        btn.setAttribute( 'aria-expanded', String( ! expanded ) );
        btn.classList.toggle( 'collapsed', expanded );
        if ( body ) {
          body.removeAttribute( 'hidden' );
          body.classList.toggle( 'hidden', expanded );
        }
      } );
    } );
  }

  // ── Pagination ───────────────────────────────────────────────────────────

  function bindPagination() {
    document.getElementById( 'pagination-wrap' )
      ?.querySelectorAll( 'a.page-numbers' )
      .forEach( a => {
        a.addEventListener( 'click', e => {
          e.preventDefault();
          const p = parseInt( new URL( a.href ).searchParams.get( 'paged' ) || '1', 10 );
          runFilters( p );
        } );
      } );
  }

  // ── Add to Cart ──────────────────────────────────────────────────────────

  function bindAddToCart() {
    document.querySelectorAll( '.btn-add-to-cart:not([data-bound])' ).forEach( btn => {
      btn.dataset.bound = '1';
      btn.addEventListener( 'click', function () {
        const id    = this.dataset.productId;
        const nonce = this.dataset.nonce;
        const self  = this;

        self.disabled  = true;
        self.innerHTML = '<svg class="spin" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Adding…';

        const body = new FormData();
        body.append( 'action',     'woocommerce_ajax_add_to_cart' );
        body.append( 'product_id', id );
        body.append( 'quantity',   1 );
        body.append( 'nonce',      nonce );

        fetch( micaData.ajaxUrl, { method: 'POST', body, credentials: 'same-origin' } )
          .then( r => r.json() )
          .then( data => {
            self.disabled  = false;
            self.classList.add( 'added' );
            self.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> Added';
            if ( data.fragments ) {
              Object.entries( data.fragments ).forEach( ( [ sel, html ] ) => {
                const el = document.querySelector( sel );
                if ( el ) el.outerHTML = html;
              } );
            }
            setTimeout( () => {
              self.classList.remove( 'added' );
              self.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg> <span>Add</span>';
            }, 2000 );
          } )
          .catch( () => { self.disabled = false; self.innerHTML = 'Error — retry'; } );
      } );
    } );
  }

  // ── Mobile drawer ────────────────────────────────────────────────────────

  function bindDrawer() {
    const btn     = document.getElementById( 'filter-mobile-btn' );
    const overlay = document.getElementById( 'filter-drawer-overlay' );
    const drawer  = document.getElementById( 'filter-drawer' );
    const close   = document.getElementById( 'filter-drawer-close' );
    const sidebar = document.getElementById( 'filter-sidebar' );

    // Remember original DOM position so we can restore it on close
    const sidebarParent  = sidebar?.parentNode;
    const sidebarNext    = sidebar?.nextSibling;

    const open = () => {
      if ( sidebar ) drawer.appendChild( sidebar );
      drawer?.classList.add( 'open' );
      overlay?.classList.add( 'open' );
      btn?.setAttribute( 'aria-expanded', 'true' );
      document.body.style.overflow = 'hidden';
    };
    const shut = () => {
      if ( sidebar && sidebarParent ) sidebarParent.insertBefore( sidebar, sidebarNext );
      drawer?.classList.remove( 'open' );
      overlay?.classList.remove( 'open' );
      btn?.setAttribute( 'aria-expanded', 'false' );
      document.body.style.overflow = '';
    };

    btn?.addEventListener( 'click', open );
    close?.addEventListener( 'click', shut );
    overlay?.addEventListener( 'click', shut );
    document.addEventListener( 'keydown', e => { if ( e.key === 'Escape' ) shut(); } );
  }

  // ── View toggle ──────────────────────────────────────────────────────────

  function bindViewToggle() {
    document.getElementById( 'view-grid' )?.addEventListener( 'click', () => {
      document.getElementById( 'products-grid' )?.classList.replace( 'products-list', 'products-grid'  );
      document.getElementById( 'view-grid' )?.classList.add( 'active' );
      document.getElementById( 'view-list' )?.classList.remove( 'active' );
    } );
    document.getElementById( 'view-list' )?.addEventListener( 'click', () => {
      document.getElementById( 'products-grid' )?.classList.replace( 'products-grid', 'products-list' );
      document.getElementById( 'view-list' )?.classList.add( 'active' );
      document.getElementById( 'view-grid' )?.classList.remove( 'active' );
    } );
  }

  // ── Init ─────────────────────────────────────────────────────────────────

  function init() {
    bindSectionToggles();
    bindCategoryToggles();
    bindShowMore();
    bindFilterSearch();
    bindPagination();
    bindAddToCart();
    bindDrawer();
    bindViewToggle();
    initPriceSlider();

    // Price apply
    document.getElementById( 'btn-apply-price' )?.addEventListener( 'click', () => runFilters() );

    // Sort
    sortSelect?.addEventListener( 'change', () => runFilters() );

    // Clear all
    clearAllBtn?.addEventListener( 'click', clearAllFilters );

    // All checkboxes — debounced
    document.querySelectorAll(
      '[data-taxonomy], input[name="filter_tag[]"], #filter-on-sale, #filter-in-stock, #filter-out-of-stock'
    ).forEach( el => {
      if ( el.type === 'checkbox' ) {
        el.addEventListener( 'change', () => {
          clearTimeout( debounce );
          debounce = setTimeout( () => runFilters(), 300 );
        } );
      }
    } );

    // Back / forward → reload (URL holds full state, server renders correctly)
    window.addEventListener( 'popstate', () => window.location.reload() );

    // Render initial chips from server-rendered state
    const initial = window.micaFilterState?.activeFilters;
    if ( initial ) renderActiveTags( initial );
  }

  document.readyState === 'loading'
    ? document.addEventListener( 'DOMContentLoaded', init )
    : init();

} )();
