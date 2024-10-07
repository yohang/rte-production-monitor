import { Controller } from "@hotwired/stimulus";

export default class extends Controller
{
    connect() {
        this.element.addEventListener('ux:map:marker:before-create', this._onMarkerBeforeCreate);
    }

    disconnect() {
        this.element.removeEventListener('ux:map:marker:before-create', this._onMarkerBeforeCreate);
    }

    _onMarkerBeforeCreate(event) {
        const { definition, L } = event.detail;

        const redIcon = L.icon({
            // Note: instead of using an hardcoded URL, you can use the `extra` parameter from `new Marker()` (PHP) and access it here with `definition.extra`.
            iconUrl: definition.extra.icon,
            iconSize: [32, 32], // size of the icon
            //shadowSize: [50, 64], // size of the shadow
            //iconAnchor: [22, 94], // point of the icon which will correspond to marker's location
            //shadowAnchor: [4, 62],  // the same for the shadow
            //popupAnchor: [-3, -76] // point from which the popup should open relative to the iconAnchor
        })

        definition.rawOptions = {
            icon: redIcon,
        }
    }
}
