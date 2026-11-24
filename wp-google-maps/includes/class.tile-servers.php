<?php

namespace WPGMZA;

class TileServers {
	/* The Zero Cost (OpenFreeMap) style saved when the user has not chosen one */
	const ZERO_COST_DEFAULT = 'openfreemap-liberty';

	private static $cached;

	/* Tile server definitions used as a per map override on this page, by slug */
	private static $requestedOverrides = array();
	private static $overridesPrinted = false;

    /**
     * Get the list of available servers
     * 
     * This does not run additional post-processing but does implement some basic caching for recurring calls
     * 
     * Filter available here for extension
     * 
     * @return object
     */
    public static function getList(){
        if(empty(TileServers::$cached)) {
			$str	= file_get_contents( plugin_dir_path(WPGMZA_FILE) . 'js/tileservers.json' );
			$json	= json_decode($str);
			
            foreach($json as $slug => $data){
                $json->{$slug}->slug = $slug;
            }
            
			TileServers::$cached = $json;
		}

        /* Developer Hook (Filter) - Mutate the tile server select options */
        return apply_filters('wpgmza_tile_servers', TileServers::$cached);
    }

    /**
     * Get the list of servers based on a common provider
     * 
     * This is helpful for, as an example, getting all Azure tilesets
     * 
     * @return object
     */
    public static function getByProvider($provider){
        $provider = trim($provider);
        $servers = TileServers::getList();
        $filtered = (object) array();
        foreach($servers as $serverKey => $server){
            if(!empty($server->provider) && $server->provider === $provider){
                $filtered->{$serverKey} = $server;
            }
        }

        return $filtered;
    }

    /**
     * Get a single server definition by a URL
     * 
     * Checks for mismatched server replacements and also runs the the hydration call
     * 
     * @return object
     */
    public static function getByUrl($url){
        $servers = TileServers::getList();
        if(!empty($url)){
            foreach($servers as $server){
                if(!empty($server->url) && $server->url === trim($url)){
                    return TileServers::hydrate($server);
                }

                if(!empty($server->url)&& $server->url === trim(str_replace("{a-c}", "{s}", $url))){
                    return TileServers::hydrate($server);
                }
            }
        }
        return false;
    }

    /**
     * Get a single server definition by a slug
     * 
     * Runs the hydration call. Returns empty if not applicable
     * 
     * @return object
     */
    public static function getBySlug($slug){
        $slug = trim($slug);
        $servers = TileServers::getList();
        if(!empty($servers->{$slug})){
            return TileServers::hydrate($servers->{$slug});
        }
        return false;
    }

    /**
     * Get a server by slug for use as a per map override, and remember it so it is printed with the page
     *
     * @param string $slug
     *
     * @return object|false
     */
    public static function registerOverride($slug){
        $server = TileServers::getBySlug($slug);

        if(!empty($server)){
            TileServers::$requestedOverrides[trim($slug)] = $server;
        }

        return $server;
    }

    /**
     * Print the per map tile server definitions used on this page
     *
     * Security: the map script reads these from here, never from a map element, because an element's attributes can
     * be written by anyone who can save post content. The list is printed whenever the plugin's scripts are on the
     * page, also when it is empty, so the script can tell "no override" from "no list" (an older WP Go Maps)
     *
     * @return void
     */
    public static function printRequestedOverrides(){
        if(TileServers::$overridesPrinted){
            return;
        }

        if(empty(TileServers::$requestedOverrides) && !wp_script_is('wpgmza', 'enqueued') && !wp_script_is('wpgmza', 'done')){
            return;
        }

        TileServers::$overridesPrinted = true;

        $overrides = TileServers::$requestedOverrides;

        if(is_admin()){
            /* Editors (block editor, page builders) add map previews to the page after this list has been printed,
             * so in the admin area every definition is listed. The front end lists only what the page uses */
            foreach(TileServers::getList() as $slug => $server){
                if(empty($overrides[$slug])){
                    $overrides[$slug] = TileServers::hydrate($server);
                }
            }
        }

        wp_print_inline_script_tag('window.WPGMZA_tile_server_overrides = ' . wp_json_encode((object) $overrides, JSON_HEX_TAG | JSON_HEX_AMP) . ';');
    }

    /**
     * Get the list, based on the settings object
     * 
     * More specifically, we check the engine and then determine the active setting option for
     * the current configuration
     * 
     * We then fetch the tile server URL by this setting
     * 
     * @param object $settings
     * 
     * @return object
     */
    public static function getBySettings($settings){
        $tileServer = false;
        if(!empty($settings->engine) && $settings->engine !== 'google-maps'){
			switch($settings->engine){
				case 'open-layers':
				case 'open-layers-latest':
					$tileServer = TileServers::getByUrl(!empty($settings->tile_server_url) ? $settings->tile_server_url : false);
					break;
				case 'leaflet':
					/* When nothing is saved yet the Leaflet map draws OpenStreetMap's standard tiles (see LeafletMap), so
					 * use that definition: without one the map showed no attribution for those tiles */
					$tileServer = !empty($settings->tile_server_url) ? TileServers::getByUrl($settings->tile_server_url) : TileServers::getBySlug('openstreetmap');
					break;
				case 'leaflet-azure':
					$tileServer = TileServers::getByUrl(!empty($settings->tile_server_url_leaflet_azure) ? $settings->tile_server_url_leaflet_azure : false);
					break;
				case 'leaflet-stadia':
					$tileServer = TileServers::getByUrl(!empty($settings->tile_server_url_leaflet_stadia) ? $settings->tile_server_url_leaflet_stadia : false);
					break;
				case 'leaflet-maptiler':
					$tileServer = TileServers::getByUrl(!empty($settings->tile_server_url_leaflet_maptiler) ? $settings->tile_server_url_leaflet_maptiler : false);
					break;
                case 'leaflet-locationiq':
					$tileServer = TileServers::getByUrl(!empty($settings->tile_server_url_leaflet_locationiq) ? $settings->tile_server_url_leaflet_locationiq : false);
					break;
                case 'leaflet-zerocost':
					/* As for Leaflet above: with no style saved the map draws OpenStreetMap's standard tiles. (That state
					 * should no longer occur: the setup wizard saves a style from 10.2.05, and the upgrade to 10.2.05
					 * saves the default style on sites it had left without one, see Upgrader) */
					$tileServer = !empty($settings->tile_server_url_leaflet_zerocost) ? TileServers::getByUrl($settings->tile_server_url_leaflet_zerocost) : TileServers::getBySlug('openstreetmap');
					break;
                case 'leaflet-gotiles':
					/* Go Tiles needs no setup, so fall back to the light style (as the map JS does) when nothing is saved yet */
					$tileServer = !empty($settings->tile_server_url_leaflet_gotiles) ? TileServers::getByUrl($settings->tile_server_url_leaflet_gotiles) : TileServers::getBySlug('gotiles_light');
					break;
                case 'leaflet-mapiron':
					$tileServer = TileServers::getByUrl(!empty($settings->tile_server_url_leaflet_mapiron) ? $settings->tile_server_url_leaflet_mapiron : false);
					break;
			}
		}
        return $tileServer;
    }

    /**
     * Hydrates a server definition in two steps
     * 
     * The first is to load dependency layers, and the second is to load multi-layers for switching
     * 
     * @param object $server
     * 
     * @return object
     */
    public static function hydrate($server){
        return TileServers::multilayer(TileServers::dependencies($server));
    }

    /**
     * Get any dependency layers
     * 
     * This is specifically for when a map layer consists of multiple additional layers, for example
     * if we need satellite imagery and we also need road layer on top, we would flag it as a dependency
     * 
     * The respective engine can then preload the dependency layers so that they can be stacked
     * 
     * @param object $server
     * 
     * @return object
     */
    public static function dependencies($server){
        if(!empty($server->dependencies)){
            /* This server requires some additional layers */
            $dependencies = array();
            foreach($server->dependencies as $slug){
                if(is_string($slug)){
                    $layer = TileServers::getBySlug($slug);
                    if(!empty($layer)){
                        if($layer->provider === $server->provider){
                            if($layer->type === $server->type){
                                /* Layer is valid, matched provider and type */
                                $dependencies[] = $layer;
                            }
                        }
                    }
                }
            }

            if(!empty($dependencies)){
                $server->dependencies = $dependencies;
            }
        }
        return $server;
    }

    /**
     * Prepare a multi-layer option
     * 
     * This is when a map supports layer switching, meaning it allows users or admins to move from one layer to another in realtime
     * 
     * This hydrates the layers into groups
     * 
     * @param object $server 
     * 
     * @return object
     */
    public static function multilayer($server){
        if(!empty($server) && !empty($server->multi)){
            /* This is a multi-layer combination - Let's hydrate */
            if(!empty($server->layers)){
                $typeLock = false;
                foreach($server->layers as $layerIndex => $layer){
                    if(!empty($layer->slug)){
                        /* We have a slug */
                        $layerConfig = TileServers::getBySlug($layer->slug);
                        if(!empty($layerConfig)){
                            if($layerConfig->provider === $server->provider){
                                /* Providers must match for key sharing */
                                if(empty($typeLock)){
                                    $typeLock = $layerConfig->type;
                                }

                                if($typeLock === $layerConfig->type){
                                    $layer->config = $layerConfig;
                                } else {
                                    /* Ingore - Cannot mix vector/xyz */
                                    unset($server->layers[$layerIndex]);
                                }
                            } else {
                                /* Ignore - Providers must match */
                                unset($server->layers[$layerIndex]);
                            }
                        } else {
                            /* Ignore - Missing configuration */
                            unset($server->layers[$layerIndex]);
                        }
                    } else {
                        /* Ignore - Missing slug */
                        unset($server->layers[$layerIndex]);
                    }
                }
            }
        }
        return $server;
    }
}