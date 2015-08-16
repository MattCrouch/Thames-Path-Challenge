function fetchDonations(t){var n=$(".donate .current-amount .current"),e=$(".donate .current-amount .total");$.ajax({url:"fetchdonations.php",type:"GET",success:function(t){function a(){var t=parseFloat(n.data("amount")+l);t>n.data("total")&&(t=parseFloat(n.data("total"))),n.data("amount",t),n.text(t.toFixed(2)),t<n.data("total")&&setTimeout(function(){a()},s)}n.removeClass("loading"),e.removeClass("loading"),n.text("0.00").data("amount","0.00").data("total",parseFloat(t.totalRaised).toFixed(2)),e.text(t.target);var o=1e3,i=25,s=o/i,l=t.totalRaised/i;a()},error:function(){n.text("-"),e.text("-")}}),t&&setTimeout(function(){fetchDonations()},18e5)}$(document).ready(function(){$("#map").length>0?map():fetchDonations()});var map=function(){function t(){google.maps.event.addDomListener(window,"load",function(){P=new google.maps.Map($("#map")[0],{center:S.start,zoom:14}),e(),n(),a(),s(),p(),d(),h(),fetchDonations(O)})}function n(){S={start:{name:"Putney Bridge",lat:51.46678,lng:-.213112,info:"START - Putney Bridge",icon:b.flag},"half way":{name:"Hurst Park",lat:51.392211,lng:-.344472,info:"25km - Half Way<br/>Hurst Park",icon:b.pointsOfInterest},finish:{name:"Runnymede Pleasure Ground",lat:51.442137,lng:-.55082,info:"FINISH - Runnymede",icon:b.flag}}}function e(){b.pointsOfInterest={anchor:new google.maps.Point(15,15),url:k+"live/icons/poi.svg",scaledSize:new google.maps.Size(30,30)},b.flag={anchor:new google.maps.Point(15,15),url:k+"live/icons/poi.svg",scaledSize:new google.maps.Size(30,30)},b.twitter={anchor:new google.maps.Point(15,15),url:k+"live/icons/twitter.svg",scaledSize:new google.maps.Size(30,30)},b.instagram={anchor:new google.maps.Point(15,15),url:k+"live/icons/instagram.svg",scaledSize:new google.maps.Size(30,30)}}function a(){o(x.pointsOfInterest),$.each(S,function(t,n){var e=c(n.lat,n.lng,n.name,n.icon);x.pointsOfInterest.push(e),v(x.pointsOfInterest);var a=new google.maps.InfoWindow({content:n.info});google.maps.event.addListener(e,"click",function(){i(),a.open(P,e),I.push(a)})})}function o(t){$.each(t,function(t,n){n.setMap(null)}),t=[]}function i(){$.each(I,function(t,n){n.close()})}function s(){T=new google.maps.Polyline({clickable:!1,strokeColor:"#0F3670",strokeOpacity:1,strokeWeight:5}),T.setMap(P),$.each(z,function(t,n){m(n)})}function l(t){var n={lat:t.lat,lng:t.lng,timestamp:t.timestamp};z.push(n),m(n)}function c(t,n,e,a){"undefined"==typeof a&&(a=null);var o=new google.maps.Marker({position:new google.maps.LatLng(t,n),title:e,icon:a});return o.setMap(P),o}function r(t){var n=c(t.lat,t.lng,"this is a post from "+t.source,b[t.source]),e=new google.maps.InfoWindow({content:w(t)});google.maps.event.addListener(n,"click",function(){i(),e.open(P,n),I.push(e)})}function u(t){L.length>=C&&L.splice(L.length-1,1),L.unshift(t)}function f(t){var n=$(".overlay .lastfm .nowPlaying");n.addClass("out"),setTimeout(function(){n.remove()},1e3),g(t)}function g(t){var n=$(".overlay .lastfm"),e=y(t);n.append(e),setTimeout(function(){$(".overlay .lastfm .nowPlaying").removeClass("in")},1e3)}function m(t){var n=T.getPath(),e=new google.maps.LatLng(t.lat,t.lng);n.push(e)}function p(){$.ajax({url:"fetchcoords.php",data:{since:F.waypoints},type:"GET",success:function(t){F.waypoints=t.sinceTimestamp,$.each(t.coordinates,function(t,n){l(n)}),O&&setTimeout(function(){p()},6e4)},error:function(){alert("Can't get the location at the moment :(")}})}function d(){$.ajax({url:"fetchsocial.php",data:{since:F.social},type:"GET",success:function(t){F.social=t.sinceTimestamp,$.each(t.posts,function(t,n){r(n)}),O&&setTimeout(function(){d()},3e5)},error:function(){alert("Can't get social feeds :(")}})}function h(){$.ajax({url:"fetchlastfm.php",data:{since:F.lastfm},type:"GET",success:function(t){F.lastfm=t.sinceTimestamp,$.each(t.tracks,function(t,n){u(n)}),t.tracks.length>0&&f(t.tracks[0]),O&&setTimeout(function(){h()},3e5)},error:function(){alert("Can't get scrobbles :(")}})}function v(t){for(var n=new google.maps.LatLngBounds,e=0;e<t.length;e++)n.extend(t[e].getPosition());P.fitBounds(n)}function w(t){return html="<div class='social "+t.source+"'><a href='"+t.url+"' target='_blank'><img src='"+t.image+"'/></a><p class='caption'>"+t.text+"</p></div>",html}function y(t){return html="<div class='nowPlaying in'><a href='"+t.url+"'><img src='"+(""!==t.image_url_large?t.image_url_large:"build/images/live/icons/music-no-circle.svg")+"' alt='Now Playing' class='albumArt'/></a><div class='detail'><h3>Now Playing</h3><ul><li class='title'>"+t.title+"</li><li class='artist'>"+t.artist+"</li><li class='album'>"+t.album+"</li></ul></div></div>",html}var P,T,k="/build/images/",x={pointsOfInterest:[],social:[]},b={},I=[],S={},z=[],L=[],C=5,F={waypoints:null,social:null},O=!1;t()};