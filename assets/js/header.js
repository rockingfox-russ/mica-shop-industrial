/**
 * header.js — Multi-level department sidebar
 *
 * The full category tree is inlined in the page as JSON inside
 * #mica-dept-tree — no AJAX, no loading states, always instant.
 */

( function () {
    'use strict';

    // ── Bootstrap tree from inline JSON ─────────────────────────────────────

    let fullCategoryTree = [];
    let categoryMap      = {};

    const treeEl = document.getElementById( 'mica-dept-tree' );
    if ( treeEl ) {
        try {
            fullCategoryTree = JSON.parse( treeEl.textContent );
            buildCategoryMap( fullCategoryTree );
        } catch ( e ) {
            console.warn( '[Header] Could not parse dept tree', e );
        }
    }

    function buildCategoryMap( tree ) {
        function traverse( cats ) {
            cats.forEach( cat => {
                categoryMap[ cat.id ] = cat;
                if ( cat.children && cat.children.length ) traverse( cat.children );
            } );
        }
        traverse( tree );
    }

    function getChildren( catId ) {
        return categoryMap[ catId ] ? ( categoryMap[ catId ].children || [] ) : [];
    }

    // ── Sidebar elements ─────────────────────────────────────────────────────

    const deptBtn      = document.getElementById( 'shop-dept-btn' );
    const deptSidebar  = document.getElementById( 'dept-sidebar' );
    const deptOverlay  = document.getElementById( 'dept-overlay' );
    const closeDeptBtn = document.getElementById( 'close-dept' );
    const level1       = document.getElementById( 'level-1' );
    const dynamicLevels = document.getElementById( 'dynamic-levels' );
    let   levelHistory  = [];

    // ── Panel creation ───────────────────────────────────────────────────────

    function createPanel( level, catId, catName, catSlug ) {
        const panel    = document.createElement( 'div' );
        panel.className = `sidebar-level level-${ level }`;
        panel.id        = `level-${ level }-${ catId }`;

        const children = getChildren( catId );
        const catObj   = categoryMap[ catId ];
        const catUrl   = catObj ? catObj.url : `/product-category/${ catSlug }/`;

        panel.innerHTML = `
            <div class="sidebar-header">
                <button class="sidebar-back" data-level="${ level - 1 }">
                    <span class="back-icon">‹</span> Back
                </button>
                <h3>${ esc( catName ) }</h3>
                <button class="close-dept">✕</button>
            </div>
            <div class="sidebar-content">
                <div class="sidebar-item view-all">
                    <a href="${ catUrl }" class="sidebar-link view-all-link">
                        🛍️ Shop All ${ esc( catName ) }
                    </a>
                </div>
                ${ children.length > 0 ? children.map( sub => `
                    <div class="sidebar-item"
                         data-cat-id="${ sub.id }"
                         data-cat-name="${ esc( sub.name ) }"
                         data-cat-slug="${ sub.slug || '' }">
                        <a href="${ sub.url }" class="sidebar-link">
                            ${ esc( sub.name ) }
                            <span class="item-count">${ sub.count > 0 ? '(' + sub.count + ')' : '' }</span>
                        </a>
                        ${ sub.children && sub.children.length > 0 ? `
                            <button class="sidebar-next"
                                    data-level="${ level + 1 }"
                                    data-cat="${ sub.id }"
                                    data-cat-name="${ esc( sub.name ) }"
                                    data-cat-slug="${ sub.slug || '' }">
                                <span class="next-icon">›</span>
                            </button>
                        ` : '' }
                    </div>
                ` ).join( '' ) : `
                    <div class="sidebar-item">
                        <a href="${ catUrl }" class="sidebar-link">
                            View all products in ${ esc( catName ) }
                        </a>
                    </div>
                ` }
            </div>`;

        attachPanelEvents( panel, level );
        return panel;
    }

    function attachPanelEvents( panel, level ) {
        panel.querySelector( '.sidebar-back' )?.addEventListener( 'click', e => {
            e.preventDefault();
            goBack( level );
        } );

        panel.querySelector( '.close-dept' )?.addEventListener( 'click', closeSidebar );

        panel.querySelectorAll( '.sidebar-next' ).forEach( btn => {
            btn.addEventListener( 'click', e => {
                e.preventDefault();
                e.stopPropagation();
                navigateTo(
                    parseInt( btn.dataset.level ),
                    parseInt( btn.dataset.cat ),
                    btn.dataset.catName,
                    btn.dataset.catSlug
                );
            } );
        } );
    }

    // ── Navigation ───────────────────────────────────────────────────────────

    function navigateTo( level, catId, catName, catSlug ) {
        const current = document.querySelector( '.sidebar-level.active' );
        if ( current ) current.classList.remove( 'active' );

        let panel = document.getElementById( `level-${ level }-${ catId }` );
        if ( ! panel ) {
            panel = createPanel( level, catId, catName, catSlug );
            dynamicLevels.appendChild( panel );
        }

        levelHistory.push( { level: level - 1, panelId: current?.id } );

        requestAnimationFrame( () => panel.classList.add( 'active' ) );
    }

    function goBack( currentLevel ) {
        const current = document.querySelector( '.sidebar-level.active' );
        if ( current ) current.classList.remove( 'active' );

        const prev = levelHistory.pop();

        if ( prev?.panelId ) {
            document.getElementById( prev.panelId )?.classList.add( 'active' );
        } else {
            level1?.classList.add( 'active' );
        }

        setTimeout( () => {
            if ( current && ! current.classList.contains( 'active' ) ) current.remove();
        }, 300 );
    }

    // ── Open / close ─────────────────────────────────────────────────────────

    function openSidebar() {
        deptSidebar?.classList.add( 'open' );
        deptOverlay?.classList.add( 'active' );
        document.body.style.overflow = 'hidden';
        resetToLevel1();
    }

    function closeSidebar() {
        deptSidebar?.classList.remove( 'open' );
        deptOverlay?.classList.remove( 'active' );
        document.body.style.overflow = '';
        resetToLevel1();
    }

    function resetToLevel1() {
        levelHistory = [];
        if ( dynamicLevels ) dynamicLevels.innerHTML = '';
        level1?.classList.add( 'active' );
    }

    // ── Wire up Level 1 "next" buttons (server-rendered) ────────────────────

    function bindLevel1Buttons() {
        document.querySelectorAll( '#level-1 .sidebar-next' ).forEach( btn => {
            const fresh = btn.cloneNode( true ); // remove any previous listeners
            btn.replaceWith( fresh );
            fresh.addEventListener( 'click', e => {
                e.preventDefault();
                e.stopPropagation();
                level1?.classList.remove( 'active' );
                navigateTo(
                    parseInt( fresh.dataset.level ),
                    parseInt( fresh.dataset.cat ),
                    fresh.dataset.catName,
                    fresh.dataset.catSlug
                );
            } );
        } );
    }

    // ── Utility ──────────────────────────────────────────────────────────────

    function esc( str ) {
        if ( ! str ) return '';
        return String( str ).replace( /[&<>"']/g, m =>
            ( { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' } )[ m ]
        );
    }

    // ── Event listeners ──────────────────────────────────────────────────────

    deptBtn?.addEventListener( 'click', openSidebar );
    closeDeptBtn?.addEventListener( 'click', closeSidebar );
    deptOverlay?.addEventListener( 'click', closeSidebar );
    document.addEventListener( 'keydown', e => {
        if ( e.key === 'Escape' && deptSidebar?.classList.contains( 'open' ) ) closeSidebar();
    } );

    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', bindLevel1Buttons );
    } else {
        bindLevel1Buttons();
    }

    // ── Mobile drawer ────────────────────────────────────────────────────────

    const mobileToggle  = document.getElementById( 'mobile-menu-toggle' );
    const mobileDrawer  = document.getElementById( 'mobile-drawer' );
    const mobileOverlay = document.getElementById( 'mobile-overlay' );
    const mobileClose   = document.getElementById( 'mobile-close' );

    mobileToggle?.addEventListener(  'click', () => { mobileDrawer?.classList.add( 'open' );    mobileOverlay?.classList.add( 'active' );    document.body.style.overflow = 'hidden'; } );
    mobileClose?.addEventListener(   'click', () => { mobileDrawer?.classList.remove( 'open' ); mobileOverlay?.classList.remove( 'active' ); document.body.style.overflow = ''; } );
    mobileOverlay?.addEventListener( 'click', () => { mobileDrawer?.classList.remove( 'open' ); mobileOverlay?.classList.remove( 'active' ); document.body.style.overflow = ''; } );

} )();
