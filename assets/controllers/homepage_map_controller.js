import { Controller } from "@hotwired/stimulus";
import * as L from 'leaflet';
import {MarkerClusterGroup} from 'leaflet.markercluster';
import 'leaflet.markercluster/dist/MarkerCluster.Default.css';

export default class extends Controller
{
    _cluster = null;
    _map = null;

    connect() {
        this._cluster = new MarkerClusterGroup();

        this.element.addEventListener('ux:map:connect', this._onConnect.bind(this));
        this.element.addEventListener('ux:map:marker:before-create', this._onMarkerBeforeCreate.bind(this));
        this.element.addEventListener('ux:map:marker:after-create', this._onMarkerAfterCreate.bind(this));
    }

    disconnect() {
        this.element.removeEventListener('ux:map:connect', this._onConnect.bind(this));
        this.element.removeEventListener('ux:map:marker:before-create', this._onMarkerBeforeCreate.bind(this));
        this.element.removeEventListener('ux:map:marker:after-create', this._onMarkerAfterCreate.bind(this));
    }

    _onConnect(event) {
        const { L, map } = event.detail;

        this._map = map;
        this._map.eachLayer(layer => {
            if (layer.dragging) {
                this._map.removeLayer(layer);
            }
        });

        this._map.addLayer(this._cluster);
    }

    _onMarkerBeforeCreate(event) {
        const { definition, L } = event.detail;

        definition.rawOptions = {
            icon: L.icon({
                iconUrl: definition.extra.icon,
                iconSize: [32, 32],
                iconColor: 'blue'
            }),
        };
    }

    _onMarkerAfterCreate(event) {
        const { marker, L } = event.detail;

        this._cluster.addLayer(marker);

    }
}
