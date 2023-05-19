document.addEventListener("DOMContentLoaded", function() {
    
    var map = L.map('mapid').setView([coordinates[0]['latitude'], coordinates[0]['longitude']], 14);

    L.tileLayer('https://maps.geoapify.com/v1/tile/positron/{z}/{x}/{y}.png?apiKey=51f9cf2740d44cb0ab35415e9099d3ce', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    L.marker([coordinates[0]['latitude'], coordinates[0]['longitude']]).addTo(map);
});