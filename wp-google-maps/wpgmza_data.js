(function(){}());// This file is used for localization and will be deprecated in the future

function __wpgmzaMapEngineLoadedCallback(){
	/* Google calls this when the Maps API has finished loading, which may be
	 * after the page itself (for example when a consent plugin releases the API
	 * script on consent). Kick an immediate init pass so maps start without
	 * waiting for the 2 second retry. Guarded so it is a harmless no-op if the
	 * core is not ready yet, in which case the retry loop covers it.
	 *
	 * "Ready" means the core's own start-up has run (it creates WPGMZA.restAPI
	 * just before its first initMaps call). WPGMZA.initMaps exists slightly
	 * earlier than that, and a map built in that gap has no REST API to fetch
	 * its markers with, so it stays empty. If the core isn't ready yet, its
	 * start-up builds the maps itself moments later, with Google already loaded.
	 */
	if(window.WPGMZA && typeof WPGMZA.initMaps === "function" && WPGMZA.restAPI){
		/* initMaps schedules its own 2 second retry, so cancel the pending one
		 * rather than starting a second retry loop alongside it */
		if(WPGMZA.Map && WPGMZA.Map.nextInitTimeoutID){
			clearTimeout(WPGMZA.Map.nextInitTimeoutID);
		}
		WPGMZA.initMaps();
	}
}