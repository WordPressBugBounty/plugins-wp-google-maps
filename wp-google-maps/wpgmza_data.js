(function(){}());// This file is used for localization and will be deprecated in the future

function __wpgmzaMapEngineLoadedCallback(){
	/* Google calls this when the Maps API has finished loading, which may be
	 * after the page itself (for example when a consent plugin releases the API
	 * script on consent). Kick an immediate init pass so maps start without
	 * waiting for the 2 second retry. Guarded so it is a harmless no-op if the
	 * core is not ready yet, in which case the retry loop covers it.
	 */
	if(window.WPGMZA && typeof WPGMZA.initMaps === "function"){
		WPGMZA.initMaps();
	}
}