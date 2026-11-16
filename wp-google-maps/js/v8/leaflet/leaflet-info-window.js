/**
 * @namespace WPGMZA
 * @module LeafletInfoWindow
 * @requires WPGMZA.InfoWindow
 * @pro-requires WPGMZA.ProInfoWindow
 */
jQuery(function($) {
	
	var Parent;
	
	WPGMZA.LeafletInfoWindow = function(feature) {
		var self = this;

		Parent.call(this, feature);

		/* Content was added after the popup opened (Pro galleries, Gold ratings), so re-measure, reposition
		 * and pan the popup. Hand Leaflet the existing content element rather than calling update(), which
		 * would re-render the original HTML and drop whatever was added */
		this.on("infowindowcontentshift", function(event) {
			if(!self.leafletPopup || !self.leafletPopup.isOpen()){
				return;
			}

			var content = $(self.leafletPopup.getElement()).find('.leaflet-popup-content > .wpgmza-infowindow')[0];
			if(content){
				self.leafletPopup.setContent(content);
			}
		});
	}
	
	if(WPGMZA.isProVersion())
		Parent = WPGMZA.ProInfoWindow;
	else
		Parent = WPGMZA.InfoWindow;
	
	WPGMZA.LeafletInfoWindow.prototype = Object.create(Parent.prototype);
	WPGMZA.LeafletInfoWindow.prototype.constructor = WPGMZA.LeafletInfoWindow;
	
	WPGMZA.LeafletInfoWindow.prototype.open = function(map, feature) {
		var latLng = feature.getPosition();

		if(!latLng){
			return false;
		}
		
		if(!Parent.prototype.open.call(this, map, feature)){
			return false;
		}
		
		// Set parent for events to bubble up
		this.parent = map;
		
        if(!this.leafletPopup){
            this.leafletPopup = L.popup({
				maxWidth: this.maxWidth ? parseInt(this.maxWidth) : 320,
				minWidth: this.maxWidth && parseInt(this.maxWidth) < 320 ? parseInt(this.maxWidth) : 320,
				maxHeight: 280
			});

			map.leafletMap.on('popupclose', (event) => {
				if(event.popup === this.leafletPopup){
					this.state = WPGMZA.InfoWindow.STATE_CLOSED;
					this.trigger("infowindowclose");
				}
			});
        }
		
		this.leafletPopup.setLatLng({lat : latLng.lat, lng : latLng.lng});
        
		const dataProps = this.compileWrapperAttributes();
		const styleTypeClass = this.getStyleTypeClass();
		this.setContent(`<div class='wpgmza-infowindow leaflet-info-window-container ${styleTypeClass}' ${dataProps}>${this.content}</div>`);

        this.leafletPopup.openOn(map.leafletMap);

		if(this.feature && this.feature instanceof WPGMZA.Marker){
			const nativeElement = this.feature.getNativeElement();
			const markerHeight = $(nativeElement).css('--wpgmza-leaflet-icon-height');
			if(markerHeight && this.leafletPopup){
				$(this.leafletPopup.getElement()).css('--wpgmza-leaflet-icon-height', markerHeight);
			}
		} else {
			/* It is linked to another type of feature - Probably a shape*/
			$(this.leafletPopup.getElement()).addClass('no-offsets');
		}

		this.element = this.leafletPopup.getElement();

		this.trigger("infowindowopen");
		this.trigger("domready");
	}
	
	WPGMZA.LeafletInfoWindow.prototype.close = function(event) {
		if(this.leafletPopup && this.leafletPopup.isOpen()){
			this.leafletPopup.close();
		}

		WPGMZA.InfoWindow.prototype.close.call(this);
		this.trigger("infowindowclose");
	}
	
	WPGMZA.LeafletInfoWindow.prototype.setContent = function(html) {
		Parent.prototype.setContent.call(this, html);
		this.content = html;

		let editButton = !WPGMZA.isProVersion() ? this.addEditButton() : '';
		if(editButton){
			html += editButton;
		}

		if(this.leafletPopup){
        	this.leafletPopup.setContent(html);
		}
	}
	
	WPGMZA.LeafletInfoWindow.prototype.setOptions = function(options) {
		if(options.maxWidth){
			$(this.element).css({"max-width": options.maxWidth + "px"});
		}
	}
	
	WPGMZA.LeafletInfoWindow.prototype.onOpen = function() {
		WPGMZA.InfoWindow.prototype.onOpen.apply(this, arguments);
	}
});