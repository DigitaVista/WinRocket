/* WinRocket front-end: announcement bar, hero slider, tabs. */
( function () {
	'use strict';

	// Announcement bar: close and remember for this browser session.
	var bar = document.querySelector( '[data-wr-announcement]' );
	if ( bar ) {
		var hidden = false;
		try {
			hidden = window.sessionStorage.getItem( 'wr-announcement' ) === '1';
		} catch ( e ) {}
		if ( hidden ) {
			bar.remove();
		} else {
			var close = bar.querySelector( '.wr-announcement__close' );
			if ( close ) {
				close.addEventListener( 'click', function () {
					bar.remove();
					try {
						window.sessionStorage.setItem( 'wr-announcement', '1' );
					} catch ( e ) {}
				} );
			}
		}
	}

	// Hero slider.
	document.querySelectorAll( '[data-wr-hero]' ).forEach( function ( hero ) {
		var slides = hero.querySelectorAll( '.wr-hero__slide' );
		var dots = hero.querySelectorAll( '.wr-hero__dot' );
		if ( slides.length < 2 ) {
			return;
		}
		var current = 0;
		var timer = null;
		var reduce = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

		function go( i ) {
			slides[ current ].classList.remove( 'is-active' );
			slides[ current ].setAttribute( 'aria-hidden', 'true' );
			dots[ current ] && dots[ current ].classList.remove( 'is-active' );
			current = ( i + slides.length ) % slides.length;
			slides[ current ].classList.add( 'is-active' );
			slides[ current ].removeAttribute( 'aria-hidden' );
			dots[ current ] && dots[ current ].classList.add( 'is-active' );
		}
		function start() {
			if ( ! reduce ) {
				timer = window.setInterval( function () {
					go( current + 1 );
				}, 6000 );
			}
		}
		function stop() {
			window.clearInterval( timer );
		}
		dots.forEach( function ( dot ) {
			dot.addEventListener( 'click', function () {
				stop();
				go( parseInt( dot.getAttribute( 'data-go' ), 10 ) );
				start();
			} );
		} );
		hero.addEventListener( 'mouseenter', stop );
		hero.addEventListener( 'mouseleave', start );
		hero.addEventListener( 'focusin', stop );
		start();
	} );

	// Single competition tabs.
	document.querySelectorAll( '[data-wr-tabs]' ).forEach( function ( wrap ) {
		var tabs = wrap.querySelectorAll( '.wr-tabs__tab' );
		tabs.forEach( function ( tab, index ) {
			tab.addEventListener( 'click', function () {
				tabs.forEach( function ( t ) {
					var panel = document.getElementById( t.getAttribute( 'aria-controls' ) );
					t.classList.remove( 'is-active' );
					t.setAttribute( 'aria-selected', 'false' );
					if ( panel ) {
						panel.hidden = true;
					}
				} );
				tab.classList.add( 'is-active' );
				tab.setAttribute( 'aria-selected', 'true' );
				var panel = document.getElementById( tab.getAttribute( 'aria-controls' ) );
				if ( panel ) {
					panel.hidden = false;
				}
			} );
			tab.addEventListener( 'keydown', function ( e ) {
				var next = null;
				if ( 'ArrowRight' === e.key ) {
					next = tabs[ ( index + 1 ) % tabs.length ];
				} else if ( 'ArrowLeft' === e.key ) {
					next = tabs[ ( index - 1 + tabs.length ) % tabs.length ];
				}
				if ( next ) {
					e.preventDefault();
					next.focus();
					next.click();
				}
			} );
		} );
	} );
} )();
