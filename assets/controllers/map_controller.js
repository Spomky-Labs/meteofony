'use strict';
import {Controller} from '@hotwired/stimulus';
import L from 'leaflet';

export default class extends Controller {
  static values = {
    latitude: {type: Number},
    longitude: {type: Number},
    iconUrl: {type: String},
    shadowUrl: {type: String},
    zoom: {type: Number, default: 13},
  };

  connect() {
    L.Marker.prototype.options.icon = L.icon({
      iconUrl: this.iconUrlValue,
      shadowUrl: this.shadowUrlValue,
      iconSize: [24, 36],
      iconAnchor: [12, 36]
    });
    const map = L.map(this.element, { zoomControl: false }).setView([this.latitudeValue, this.longitudeValue], this.zoomValue);
    L.marker([this.latitudeValue, this.longitudeValue]).addTo(map);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);
  }
}
