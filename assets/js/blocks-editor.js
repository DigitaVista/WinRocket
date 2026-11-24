/* WinRocket theme blocks: editor UI (server-rendered previews). */
( function ( wp, data ) {
	if ( ! wp || ! data ) {
		return;
	}
	var el = wp.element.createElement;
	var __ = wp.i18n.__;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var C = wp.components;
	var SSR = wp.serverSideRender;

	var icons = {
		announcement: 'megaphone',
		'header-button': 'button',
		hero: 'cover-image',
		stats: 'chart-bar',
		'section-nav': 'menu',
		'competition-grid': 'grid-view',
		'results-cta': 'awards',
		'draw-results': 'list-view'
	};

	var sources = [
		{ label: __( 'Featured', 'winrocket' ), value: 'featured' },
		{ label: __( 'Ending soon', 'winrocket' ), value: 'ending' },
		{ label: __( 'Instant wins', 'winrocket' ), value: 'instant' },
		{ label: __( 'Newest', 'winrocket' ), value: 'latest' },
		{ label: __( 'Category', 'winrocket' ), value: 'category' }
	];

	var labels = {
		eyebrow: __( 'Small label above the title', 'winrocket' ),
		title: __( 'Title', 'winrocket' ),
		subtitle: __( 'Subtitle', 'winrocket' ),
		text: __( 'Text', 'winrocket' ),
		button: __( 'Button text', 'winrocket' ),
		button2: __( 'Second button text', 'winrocket' ),
		productIds: __( 'Product IDs (comma separated, empty = featured)', 'winrocket' ),
		slides: __( 'Slides', 'winrocket' ),
		links: __( 'Links: one per line, "Label|#anchor-or-url"', 'winrocket' ),
		anchorId: __( 'Anchor ID (for section links)', 'winrocket' ),
		source: __( 'Show', 'winrocket' ),
		category: __( 'Category', 'winrocket' ),
		count: __( 'Number of competitions', 'winrocket' ),
		columns: __( 'Columns', 'winrocket' ),
		showLink: __( 'Show "view all" link', 'winrocket' ),
		linkText: __( 'Link text', 'winrocket' ),
		perPage: __( 'Results per page', 'winrocket' )
	};

	function field( name, def, props ) {
		var value = props.attributes[ name ];
		var set = function ( v ) {
			var o = {};
			o[ name ] = v;
			props.setAttributes( o );
		};
		if ( 'source' === name ) {
			return el( C.SelectControl, { key: name, label: labels[ name ], value: value, options: sources, onChange: set } );
		}
		if ( 'category' === name ) {
			if ( 'category' !== props.attributes.source ) {
				return null;
			}
			var opts = [ { label: __( '— Select —', 'winrocket' ), value: '' } ].concat( data.categories || [] );
			return el( C.SelectControl, { key: name, label: labels[ name ], value: value, options: opts, onChange: set } );
		}
		if ( 'boolean' === def.type ) {
			return el( C.ToggleControl, { key: name, label: labels[ name ] || name, checked: !! value, onChange: set } );
		}
		if ( 'number' === def.type ) {
			var max = 'columns' === name ? 6 : ( 'slides' === name ? 5 : ( 'perPage' === name ? 50 : 24 ) );
			return el( C.RangeControl, { key: name, label: labels[ name ] || name, value: value, min: 1, max: max, onChange: set } );
		}
		if ( 'links' === name || 'text' === name ) {
			return el( C.TextareaControl, { key: name, label: labels[ name ] || name, value: value, onChange: set } );
		}
		return el( C.TextControl, { key: name, label: labels[ name ] || name, value: value, onChange: set } );
	}

	Object.keys( data.blocks ).forEach( function ( slug ) {
		var def = data.blocks[ slug ];
		var name = 'winrocket/' + slug;

		wp.blocks.registerBlockType( name, {
			apiVersion: 3,
			title: def.title,
			category: 'theme',
			icon: icons[ slug ] || 'star-filled',
			attributes: def.attributes,
			supports: { html: false, align: [ 'wide', 'full' ] },
			edit: function ( props ) {
				var blockProps = useBlockProps( { className: 'wr-editor-block' } );
				var keys = Object.keys( def.attributes );
				var controls = keys.length
					? el( InspectorControls, null, el( C.PanelBody, { title: def.title, initialOpen: true }, keys.map( function ( k ) {
						return field( k, def.attributes[ k ], props );
					} ) ) )
					: null;
				var note = ( props.isSelected && ( 'announcement' === slug || 'header-button' === slug ) )
					? el( C.Notice, { status: 'info', isDismissible: false }, __( 'Edit this in Appearance > WinRocket settings.', 'winrocket' ) )
					: null;
				return el( 'div', blockProps, controls, note, el( SSR, {
					block: name,
					attributes: props.attributes,
					EmptyResponsePlaceholder: function () {
						return el( C.Placeholder, { label: def.title, instructions: __( 'Nothing to show yet. Add competitions or check the settings.', 'winrocket' ) } );
					}
				} ) );
			},
			save: function () {
				return null;
			}
		} );
	} );
} )( window.wp, window.winrocketBlocks );
