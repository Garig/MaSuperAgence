import L from 'leaflet'
import marker from 'leaflet/dist/images/marker-icon.png'
import 'leaflet/dist/leaflet.css'

export default class Map {

  static init () {
    let map = document.querySelector('#map')
    if (map === null) {
      return
    }
    let icon = L.icon({
      iconUrl: marker,
    })
    let center = [map.dataset.lat, map.dataset.lng]
    map = L.map('map').setView(center, 13)
    let token = 'pk.eyJ1IjoiZ2FyaWciLCJhIjoiY2p0cmxib3V3MG94YTRkazB0dHlpNW42ZiJ9.mOTsC7twKvbH7_i0M6C3fg'
    L.tileLayer(`https://api.mapbox.com/v4/mapbox.streets/{z}/{x}/{y}.png?access_token=${token}`, {
      maxZoom: 18,
      minZoom: 9,
      attribution: '© <a href="https://www.mapbox.com/feedback/">Mapbox</a> © <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map)
    L.marker(center, {icon: icon}).addTo(map)
  }
}