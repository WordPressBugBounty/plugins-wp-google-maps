<?php

namespace WPGMZA;

if(!defined('ABSPATH'))
	return;

/**
 * This module loads the Google Maps API unconditionally (as opposed to the GoogleMapsAPILoader)
 * @ deprecated This functionality will be merged into one class with GoogleMapsAPILoader
 * @see GoogleMapsAPILoader
 */
class GoogleMapsLoader
{
	private static $googleAPILoadCalled = false;
	const TEMPORARY_API_KEY	= "QUl6YVN5RG9fZkc3RFhCT1Z2ZGhsckxhLVBIUkV1RkRwVGtsV2hZ";

	/* The last Maps JavaScript API version that draws KML and GeoRSS layers (see getGoogleMapsAPIParams) */
	const LAST_VERSION_WITH_KML = "3.65";
	
	/**
	 * This will be handled by the Factory class
	 * @deprecated
	 */
	public static function _createInstance()
	{
		return new GoogleMapsLoader();
	}
	
	/**
	 * This will be handled by the Factory class
	 * @deprecated
	 */
	public static function createInstance()
	{
		return static::_createInstance();
	}
	
	/**
	 * Gets the parameters to be sent to the Google Maps API load call
	 * @return array An array of key value parameters to be passed to the load URL
	 */
	protected function getGoogleMapsAPIParams()
	{
		global $wpgmza;
		
		// Locale
		$locale = (!empty($wpgmza->settings->locale_override) && $wpgmza->settings->locale_override !== 'site-default') ? $wpgmza->settings->locale_override : get_locale();
		$suffix = '.com';
		$region = false;

		$localeOverride = false;
		switch($locale){
			case 'he_IL':
				// Hebrew correction
				$locale = 'iw';
				break;
			case 'zh_CN':
				// Chinese integration
				$region = 'CN';
				break;
			case 'zh_TW':
				// Traditional Chinese
				$region = 'TW';
				$localeOverride = 'zh-TW';
				break;
		}
		
		
		$locale = substr($locale, 0, 2);
		
		// Default params for google maps
		$params = array(
			'v' 		=> 'quarterly',
			'language'	=> !empty($localeOverride) ? $localeOverride : $locale,
			'suffix'	=> $suffix
		);

		if(!empty($region)){
			/* Google now requires that we load region over the .com suffix, but with a region query */
			$params['region'] = $region;
		}
		
		// Libraries
		$libraries = array('geometry', 'places', 'visualization', 'marker');
		
		// if($wpgmza->getCurrentPage() == Plugin::PAGE_MAP_EDIT){
		// 	$libraries[] = 'drawing';
		// }
		
		$params['libraries'] = implode(',', $libraries);
		
		// API Version
		/*if(!empty(Plugin::$settings->api_version))
		{
			// Force 3.28 if the user has a setting below this
			if(version_compare(Plugin::$settings->api_version, '3.29', '<'))
			{
				$params['v'] = '3.29';
				
				// Force greedy gesture behaviour (the default before 3.27) if the user had this set
				if(version_compare(Plugin::$settings->api_version, '3.27', '<'))
					Plugin::$settings->force_greedy_gestures = true;
			}
			else
				$params['v'] = Plugin::$settings->api_version;
		}
		
		*/
		
		// API Key
		//if(!empty($wpgmza->settings->google_maps_api_key))
			//$params['key'] = $wpgmza->settings->google_maps_api_key;
		
		//if($wpgmza->getCurrentPage() == 'map-edit')
			//$params['libraries'] = 'drawing';
		
		$key = get_option('wpgmza_google_maps_api_key');
		if(!empty($key))
			$params['key'] = $key;
		else if(is_admin())
			$params['key'] = base64_decode(GoogleMapsLoader::TEMPORARY_API_KEY);

		// Callback, required as of 2023
		$params['callback'] = "__wpgmzaMapEngineLoadedCallback";

		if(!empty($wpgmza->settings->enable_google_api_async_param)){
			$params['loading'] = 'async';
		}

		/* Developer Hook (Filter) - Modify Googl Maps API params (URL) */
		$params = apply_filters( 'wpgmza_google_maps_api_params', $params );

		return $params;
	}

	/**
	 * Keeps sites that show KML or GeoRSS files on the last Maps JavaScript API version that draws them.
	 *
	 * Google removed KML and GeoRSS layers in version 3.66 (the "quarterly" channel reached it in November 2026), so a
	 * map with a KML file loaded but no longer showed the file. Version 3.65 still draws them, and Google keeps it
	 * available until May 2027. Sites with a KML or GeoRSS file on any map get 3.65; every other site stays on
	 * "quarterly". When Google retires 3.65 it serves the oldest version it still supports instead, so this needs no
	 * end date, but KML then needs another renderer.
	 *
	 * Hooked on wpgmza_google_maps_api_params (see Plugin), which both Google loaders pass their parameters through,
	 * at an early priority so a site's own filter can still choose a version.
	 * @param array $params
	 * @return array
	 */
	public static function keepKmlVersion($params)
	{
		if(is_array($params) && GoogleMapsLoader::siteHasKmlLayers())
			$params['v'] = GoogleMapsLoader::LAST_VERSION_WITH_KML;

		return $params;
	}

	/**
	 * Whether any map on this site has a KML or GeoRSS file set (the map setting "kml", a comma separated list)
	 * @return bool
	 */
	public static function siteHasKmlLayers()
	{
		global $wpdb, $WPGMZA_TABLE_NAME_MAPS;

		if(empty($WPGMZA_TABLE_NAME_MAPS))
			return false;

		/* active = 0 is a map that has not been trashed */
		$count = $wpdb->get_var("SELECT COUNT(id) FROM $WPGMZA_TABLE_NAME_MAPS WHERE active = 0 AND kml IS NOT NULL AND TRIM(kml) <> ''");

		return !empty($count);
	}

	/**
	 * This function loads the Google API if it hasn't been called already
	 * @return void
	 */
	public function loadGoogleMaps($forceLoad = false)
	{
		global $wpgmza;
		
		if(GoogleMapsLoader::$googleAPILoadCalled && empty($forceLoad))
			return;
		
		$apiLoader = new GoogleMapsAPILoader();
		if(!$apiLoader->isIncludeAllowed())
			return;
		
		$params = $this->getGoogleMapsAPIParams();
		
		$suffix = $params['suffix'];
		unset($params['suffix']);
		
		$url = '//maps.google' . $suffix . '/maps/api/js?' . http_build_query($params);

		$scriptArgs = apply_filters('wpgmza-get-scripts-arguments', array());
		
		wp_enqueue_script('wpgmza_api_call', $url, false, false, $scriptArgs);
		
		GoogleMapsLoader::$googleAPILoadCalled = true;
		if(!empty($wpgmza->settings) && !empty($wpgmza->settings->wpgmza_prevent_other_plugins_and_theme_loading_api)){
			add_filter('script_loader_tag', array($this, 'preventOtherGoogleMapsTag'), 9999999, 3);
		}
	}
	
	/**
	 * Gets the HTML for the settings panel for this module, which appears in the general settings tab.
	 * @return string The HTML string for the settings panel
	 */
	public function preventOtherGoogleMapsTag($tag, $handle, $src)
	{
		if(preg_match('/maps\.google/i', $src))
		{
			if($handle != 'wpgmza_api_call') {
				return '';
			}
			
			if(!preg_match('/\?.+$/', $src))
				return str_replace($src, $src . '?' . http_build_query($this->getGoogleMapsAPIParams()), $tag);
		}

		return $tag;
	}
	
}
