/**
 * CP3.2 — Tách dải thumbnail (ol.flex-control-thumbs) của gallery WooCommerce
 * ra ô riêng dưới ảnh chính, thêm 2 nút mũi tên trái/phải để cuộn.
 * Vanilla, chỉ chạy ở trang chi tiết sản phẩm (enqueue có guard is_product()).
 */
( function () {
	'use strict';

	var ARROW_L = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>';
	var ARROW_R = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>';

	function wrap( ol ) {
		if ( ! ol || ol.closest( '.cp-thumbs' ) ) {
			return;
		}

		var box = document.createElement( 'div' );
		box.className = 'cp-thumbs';

		var prev = document.createElement( 'button' );
		prev.type = 'button';
		prev.className = 'cp-thumbs-nav cp-thumbs-prev';
		prev.setAttribute( 'aria-label', 'Ảnh trước' );
		prev.innerHTML = ARROW_L;

		var next = document.createElement( 'button' );
		next.type = 'button';
		next.className = 'cp-thumbs-nav cp-thumbs-next';
		next.setAttribute( 'aria-label', 'Ảnh sau' );
		next.innerHTML = ARROW_R;

		ol.parentNode.insertBefore( box, ol );
		box.appendChild( prev );
		box.appendChild( ol );
		box.appendChild( next );

		function step() {
			var li = ol.querySelector( 'li' );
			var w = li ? li.getBoundingClientRect().width : 80;
			return ( w + 10 ) * 3;
		}

		function sync() {
			var max = ol.scrollWidth - ol.clientWidth - 1;
			box.classList.toggle( 'is-static', max <= 0 );
			prev.disabled = ol.scrollLeft <= 0;
			next.disabled = ol.scrollLeft >= max;
		}

		prev.addEventListener( 'click', function () {
			ol.scrollBy( { left: -step(), behavior: 'smooth' } );
		} );
		next.addEventListener( 'click', function () {
			ol.scrollBy( { left: step(), behavior: 'smooth' } );
		} );
		ol.addEventListener( 'scroll', sync, { passive: true } );
		window.addEventListener( 'resize', sync );
		sync();

		// flexslider có thể đã đo width theo container rộng hơn cột thật (ảnh tràn phải).
		// Ép nó tính lại kích thước theo cột hiện tại.
		relayoutFlexslider();
	}

	function relayoutFlexslider() {
		[ 0, 60, 200 ].forEach( function ( d ) {
			setTimeout( function () {
				window.dispatchEvent( new Event( 'resize' ) );
			}, d );
		} );
	}

	function init() {
		var gallery = document.querySelector( '.woocommerce-product-gallery' );
		if ( ! gallery ) {
			return;
		}

		var ol = gallery.querySelector( 'ol.flex-control-thumbs' );
		if ( ol ) {
			wrap( ol );
			return;
		}

		// flexslider dựng control nav sau khi init — theo dõi tới khi có.
		var obs = new MutationObserver( function () {
			var found = gallery.querySelector( 'ol.flex-control-thumbs' );
			if ( found ) {
				obs.disconnect();
				wrap( found );
			}
		} );
		obs.observe( gallery, { childList: true, subtree: true } );
		setTimeout( function () {
			obs.disconnect();
		}, 5000 );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
}() );
