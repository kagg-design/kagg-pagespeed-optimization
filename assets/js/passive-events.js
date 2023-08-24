( function() {
	const supportsPassive = eventListenerOptionsSupported();

	if ( supportsPassive ) {
		const addEvent = EventTarget.prototype.addEventListener;
		overwriteAddEvent( addEvent );
	}

	function overwriteAddEvent( superMethod ) {
		const defaultOptions = {
			passive: true,
			capture: false
		};

		EventTarget.prototype.addEventListener = function( type, listener, options ) {
			const usesListenerOptions = typeof options === 'object';
			const useCapture = usesListenerOptions ? options.capture : options;

			options = usesListenerOptions ? options : {};

			if ( type === 'touchstart' || type === 'scroll' || type === 'wheel' ) {
				options.passive = options.passive !== undefined ? options.passive : defaultOptions.passive;
			}

			options.capture = useCapture !== undefined ? useCapture : defaultOptions.capture;

			superMethod.call( this, type, listener, options );
		};
	}

	function eventListenerOptionsSupported() {
		let supported = false;

		try {
			const opts = Object.defineProperty( {}, 'passive', {
				get: function() {
					supported = true;
				}
			} );
			window.addEventListener( 'test', null, opts );
		} catch ( e ) {
		}

		return supported;
	}
} )();
