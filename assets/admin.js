/* Carosello Articoli — schede del pannello impostazioni (ricorda l'ultima scheda aperta). */
( function () {
	'use strict';
	const tabs = Array.prototype.slice.call( document.querySelectorAll( '[data-cda-tab]' ) );
	const panels = Array.prototype.slice.call( document.querySelectorAll( '[data-cda-panel]' ) );
	if ( ! tabs.length ) {
		return;
	}
	const KEY = 'cda-admin-tab';

	function show( id, focus ) {
		if ( ! tabs.some( ( t ) => t.dataset.cdaTab === id ) ) {
			id = tabs[ 0 ].dataset.cdaTab;
		}
		tabs.forEach( ( t ) => {
			const on = t.dataset.cdaTab === id;
			t.classList.toggle( 'nav-tab-active', on );
			t.setAttribute( 'aria-selected', on ? 'true' : 'false' );
		} );
		panels.forEach( ( p ) => { p.hidden = p.dataset.cdaPanel !== id; } );
		try { window.localStorage.setItem( KEY, id ); } catch ( e ) { /* storage not available */ }
		if ( focus ) {
			history.replaceState( null, '', '#' + id );
		}
	}

	tabs.forEach( ( t ) => {
		t.addEventListener( 'click', ( e ) => {
			e.preventDefault();
			show( t.dataset.cdaTab, true );
		} );
	} );

	// A field with an error (e.g. required) in a hidden tab: open that tab.
	document.addEventListener( 'invalid', ( e ) => {
		const panel = e.target.closest && e.target.closest( '[data-cda-panel]' );
		if ( panel && panel.hidden ) {
			show( panel.dataset.cdaPanel, true );
		}
	}, true );

	let start = window.location.hash.replace( '#', '' );
	if ( ! start ) {
		try { start = window.localStorage.getItem( KEY ) || ''; } catch ( e ) { start = ''; }
	}
	show( start, false );
}() );
