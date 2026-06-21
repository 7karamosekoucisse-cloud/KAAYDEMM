<?php
$routes = $popularRoutes ?? [];
$cities = [
  'Dakar'       => [14.6937, -17.4441],
  'Thies'       => [14.7910, -16.9359],
  'Thiès'       => [14.7910, -16.9359],
  'Saint-Louis' => [16.0179, -16.4896],
  'Ziguinchor'  => [12.5584, -16.2733],
  'Touba'       => [14.8600, -15.8833],
  'Kaolack'     => [14.1490, -16.0726],
  'Mbour'       => [14.3974, -16.9672],
  'Diourbel'    => [14.6558, -16.2341],
  'Tambacounda' => [13.7707, -13.6673],
  'Louga'       => [15.6173, -16.2247],
  'Fatick'      => [14.3390, -16.4110],
  'Kolda'       => [12.8967, -14.9403],
];
?>
<div id="kd-heatmap" style="width:100%;height:400px;border-radius:14px;overflow:hidden;position:relative;z-index:0;background:#1a2035"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var el = document.getElementById('kd-heatmap');
  if (!el || typeof L === 'undefined') return;

  var map = L.map('kd-heatmap', {
    zoomControl: true,
    scrollWheelZoom: false,
    attributionControl: true
  }).setView([14.5, -15.5], 7);

  L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
    attribution: '&copy; OSM &copy; CARTO',
    maxZoom: 14
  }).addTo(map);

  var cities = <?= json_encode($cities, JSON_UNESCAPED_UNICODE) ?>;
  var routes = <?= json_encode($routes, JSON_UNESCAPED_UNICODE) ?>;

  /* Marqueurs villes */
  var cityIcon = L.divIcon({
    html: '<div style="width:11px;height:11px;border-radius:50%;background:#F0A500;border:2px solid #fff;box-shadow:0 0 8px rgba(240,165,0,.7)"></div>',
    iconSize:[11,11], iconAnchor:[5,5], className:''
  });

  Object.entries(cities).forEach(function(e) {
    L.marker(e[1], {icon: cityIcon})
      .addTo(map)
      .bindTooltip('<strong>' + e[0] + '</strong>', {direction:'top', offset:[0,-8]});
  });

  /* Lignes routes populaires */
  var colors = ['#2E7DEB','#F0A500','#0D9F6E','#E8442F','#7C3AED','#EC4899','#F97316','#06B6D4'];
  routes.forEach(function(r, i) {
    var dep = cities[r.departure_city];
    var arr = cities[r.arrival_city];
    if (!dep || !arr) return;
    var w = Math.max(2, Math.min(8, parseInt(r.trip_count) || 1));
    L.polyline([dep, arr], {color: colors[i % colors.length], weight: w, opacity: .85})
      .addTo(map)
      .bindTooltip('<strong>' + r.departure_city + ' → ' + r.arrival_city + '</strong><br>' + (r.trip_count||1) + ' trajet(s)', {sticky:true});
  });

  map.invalidateSize();
});
</script>
