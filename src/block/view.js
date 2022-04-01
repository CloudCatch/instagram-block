import { tns } from 'tiny-slider';

const ccInstagram = ccInstagram || {};

ccInstagram.carousel = {
	init: function() {
		const carousels = document.querySelectorAll( '.wp-block-cloudcatch-instagram.is-carousel' );

		if ( carousels.length ) {
			carousels.forEach( container => {
				const carousel = container.querySelector( '.wp-block-cloudcatch-instagram__content:not(.tns-slider)' );

				if ( carousel && carousel.children.length ) {
					const centerMode = container.getAttribute( 'data-center-mode' );
					const slidesViewport = container.getAttribute( 'data-slides-viewport' ) || 1;
					const infinite = container.getAttribute( 'data-infinite' );
					const autoWidth = container.getAttribute( 'data-auto-width' );
					const autoHeight = container.getAttribute( 'data-auto-height' );
					const options = this.tryParseJSON( container.getAttribute( 'data-options' ) );

					tns( {
						container: carousel,
						center: !! centerMode,
						autoWidth: !! autoWidth,
						autoHeight: !! autoHeight,
						items: 1,
						loop: !! infinite,
						nav: false,
						controlsContainer: '.wp-block-cloudcatch-instagram__nav',
						responsive: {
							480: {
								items: centerMode ? 2 : 1,
							},
							600: {
								items: Math.round( slidesViewport / 2 ),
							},
							782: {
								items: slidesViewport,
							},
						},
						...options,
					} );
				}
			} );
		}
	},

	tryParseJSON: function( json ) {
		try {
			const o = JSON.parse( json );

			if ( o && typeof o === 'object' ) {
				return o;
			}
		} catch ( e ) { }

		return {};
	},
};

ccInstagram.videoPlayer = {
	parents: null,

	videos: null,

	init: function() {
		this.parents = document.querySelectorAll( '.wp-block-cloudcatch-instagram__content-video' );
		this.videos = document.querySelectorAll( '.wp-block-cloudcatch-instagram__content-video video' );

		this.videos.forEach( video => {
			this.render( video );
		} );

		this.onClick();
		this.onMouseMove();
		this.onPlay();
		this.onPause();
	},

	render: function( video ) {
		const parent = video.parentElement;

		parent.classList.add( 'paused' );
		video.removeAttribute( 'controls' );

		if ( !! video.getAttribute( 'autoplay' ) ) {
			parent.classList.remove( 'paused' );
			parent.classList.add( 'playing' );
		}

		if ( video.getAttribute( 'muted' ) === 'true' || video.volume === 0 ) {
			video.muted = true;
			parent.classList.add( 'muted' );
		}
	},

	playPause: function( video ) {
		if ( video.paused ) {
			video.play();
		} else {
			video.pause();
		}
	},

	muteUnmute: function( video ) {
		if ( video.muted ) {
			video.muted = false;
		} else {
			video.muted = true;
		}
	},

	onClick: function() {
		this.parents.forEach( parent => {
			const mediaControls = parent.querySelector( '[data-media]' );
			const video = parent.querySelector( 'video' );

			mediaControls.addEventListener( 'click', ( e ) => {
				const data = e.target.getAttribute( 'data-media' );

				if ( data === 'play-pause' ) {
					this.playPause( video );
				}

				if ( data === 'muted' ) {
					this.muteUnmute( video );
				}
			} );

			video.addEventListener( 'click', () => {
				this.playPause( video );
			} );
		} );
	},

	onMouseMove: function() {
		this.parents.forEach( parent => {
			let _t;

			parent.addEventListener( 'mousemove', () => {
				const controls = parent.querySelector( '.wp-block-cloudcatch-instagram__content-video-controls' );

				controls.classList.add( 'show' );

				clearTimeout( _t );

				_t = setTimeout( function() {
					controls.classList.remove( 'show' );
				}, 2250 );
			} );

			parent.addEventListener( 'mouseleave', () => {
				const controls = parent.querySelector( '.wp-block-cloudcatch-instagram__content-video-controls' );

				controls.classList.remove( 'show' );
			} );
		} );
	},

	onPlay: function() {
		this.videos.forEach( video => {
			video.addEventListener( 'play', () => {
				video.parentElement.classList.remove( 'paused' );
				video.parentElement.classList.add( 'playing' );
			} );
		} );
	},

	onPause: function() {
		this.videos.forEach( video => {
			video.addEventListener( 'pause', () => {
				video.parentElement.classList.add( 'paused' );
				video.parentElement.classList.remove( 'playing' );
			} );
		} );
	},
};

ccInstagram.carousel.init();
ccInstagram.videoPlayer.init();

window.ccInstagram = ccInstagram;
