'use strict';

import {Controller} from '@hotwired/stimulus';
import L from 'leaflet';
// @ts-expect-error
import MarkerIcon from '../images/marker-icon.png';
// @ts-expect-error
import MarkerShadow from '../images/marker-shadow.png';

export default class extends Controller {
  static values = {
    latitude: {type: Number, default: 0},
    longitude: {type: Number, default: 0},
  };
  declare readonly latitudeValue: number;
  declare readonly longitudeValue: number;

  connect() {
    L.Marker.prototype.options.icon = L.icon({
      iconUrl: MarkerIcon,
      shadowUrl: MarkerShadow,
      iconSize: [24, 36],
      iconAnchor: [12, 36]
    });
    // @ts-expect-error
    const map = L.map(this.element, { zoomControl: false }).setView([this.latitudeValue, this.longitudeValue], 13);
    L.marker([this.latitudeValue, this.longitudeValue]).addTo(map);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);
  }
}
