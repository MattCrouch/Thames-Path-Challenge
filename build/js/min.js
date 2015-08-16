function fetchDonations(t){var n=$(".donate .current-amount .current"),e=$(".donate .current-amount .total");$.ajax({url:"fetchdonations.php",type:"GET",success:function(t){function o(){var t=parseFloat(n.data("amount")+u);t>n.data("total")&&(t=parseFloat(n.data("total"))),n.data("amount",t),n.text(t.toFixed(2)),t<n.data("total")&&setTimeout(function(){o()},r)}n.removeClass("loading"),e.removeClass("loading");var a=t.totalRaised.replace(/\,/g,""),i=t.target.replace(/\,/g,""),s=0;n.data("amount")&&(s=n.data("amount")),n.text(parseFloat(s).toFixed(2)).data("amount",parseFloat(s).toFixed(2)).data("total",parseFloat(a).toFixed(2)),e.text(i);var l=1e3,c=25,r=l/c,u=a/c;o()},error:function(){n.text("-"),e.text("-")}}),t&&setTimeout(function(){fetchDonations()},18e5)}function checkLive(t){$.ajax({url:"checklive.php",type:"GET",success:function(t){if(t.live){console.log("LIVE!");var n="<div class='live-banner'>Follow my progress right now! <a href='live' class='button'>Watch Live</a></div>";$("body").prepend(n)}else console.log("NOT LIVE :(")},error:function(){}}),t&&setTimeout(function(){fetchDonations()},18e5)}$(document).ready(function(){$("#map").length>0?map():(fetchDonations(),checkLive())});var map=function(){function t(){google.maps.event.addDomListener(window,"load",function(){T=new google.maps.Map($("#map")[0],{center:F.start,zoom:14}),e(),n(),o(),s(),p(),d(),h(),fetchDonations(O)})}function n(){F={start:{name:"Putney Bridge",lat:51.46678,lng:-.213112,info:"START - Putney Bridge",icon:L.flag},"half way":{name:"Hurst Park",lat:51.392211,lng:-.344472,info:"25km - Half Way<br/>Hurst Park",icon:L.pointsOfInterest},finish:{name:"Runnymede Pleasure Ground",lat:51.442137,lng:-.55082,info:"FINISH - Runnymede",icon:L.flag}}}function e(){L.pointsOfInterest={anchor:new google.maps.Point(15,15),url:P+"live/icons/poi.svg",scaledSize:new google.maps.Size(30,30)},L.flag={anchor:new google.maps.Point(15,15),url:P+"live/icons/poi.svg",scaledSize:new google.maps.Size(30,30)},L.twitter={anchor:new google.maps.Point(15,15),url:P+"live/icons/twitter.svg",scaledSize:new google.maps.Size(30,30)},L.instagram={anchor:new google.maps.Point(15,15),url:P+"live/icons/instagram.svg",scaledSize:new google.maps.Size(30,30)}}function o(){a(x.pointsOfInterest),$.each(F,function(t,n){var e=c(n.lat,n.lng,n.name,n.icon);x.pointsOfInterest.push(e),v(x.pointsOfInterest);var o=new google.maps.InfoWindow({content:n.info});google.maps.event.addListener(e,"click",function(){i(),o.open(T,e),b.push(o)})})}function a(t){$.each(t,function(t,n){n.setMap(null)}),t=[]}function i(){$.each(b,function(t,n){n.close()})}function s(){k=new google.maps.Polyline({clickable:!1,strokeColor:"#0F3670",strokeOpacity:1,strokeWeight:5}),k.setMap(T),$.each(I,function(t,n){m(n)})}function l(t){var n={lat:t.lat,lng:t.lng,timestamp:t.timestamp};I.push(n),m(n)}function c(t,n,e,o){"undefined"==typeof o&&(o=null);var a=new google.maps.Marker({position:new google.maps.LatLng(t,n),title:e,icon:o});return a.setMap(T),a}function r(t){var n=c(t.lat,t.lng,"this is a post from "+t.source,L[t.source]),e=new google.maps.InfoWindow({content:w(t)});google.maps.event.addListener(n,"click",function(){i(),e.open(T,n),b.push(e)})}function u(t){S.length>=z&&S.splice(S.length-1,1),S.unshift(t)}function f(t){var n=$(".overlay .lastfm .nowPlaying");n.addClass("out"),setTimeout(function(){n.remove()},1e3),g(t)}function g(t){var n=$(".overlay .lastfm"),e=y(t);n.append(e),setTimeout(function(){$(".overlay .lastfm .nowPlaying").removeClass("in")},1e3)}function m(t){var n=k.getPath(),e=new google.maps.LatLng(t.lat,t.lng);n.push(e)}function p(){$.ajax({url:"fetchcoords.php",data:{since:C.waypoints},type:"GET",success:function(t){C.waypoints=t.sinceTimestamp,$.each(t.coordinates,function(t,n){l(n)}),O&&setTimeout(function(){p()},6e4)},error:function(){alert("Can't get the location at the moment :(")}})}function d(){$.ajax({url:"fetchsocial.php",data:{since:C.social},type:"GET",success:function(t){C.social=t.sinceTimestamp,$.each(t.posts,function(t,n){r(n)}),O&&setTimeout(function(){d()},3e5)},error:function(){alert("Can't get social feeds :(")}})}function h(){$.ajax({url:"fetchlastfm.php",data:{since:C.lastfm},type:"GET",success:function(t){C.lastfm=t.sinceTimestamp,$.each(t.tracks,function(t,n){u(n)}),t.tracks.length>0&&f(t.tracks[0]),O&&setTimeout(function(){h()},3e5)},error:function(){alert("Can't get scrobbles :(")}})}function v(t){for(var n=new google.maps.LatLngBounds,e=0;e<t.length;e++)n.extend(t[e].getPosition());T.fitBounds(n)}function w(t){return html="<div class='social "+t.source+"'><a href='"+t.url+"' target='_blank'><img src='"+t.image+"'/></a><p class='caption'>"+t.text+"</p></div>",html}function y(t){return html="<div class='nowPlaying in'><a href='"+t.url+"'><img src='"+(""!==t.image_url_large?t.image_url_large:"build/images/live/icons/music-no-circle.svg")+"' alt='Now Playing' class='albumArt'/></a><div class='detail'><h3>Now Playing</h3><ul><li class='title'>"+t.title+"</li><li class='artist'>"+t.artist+"</li><li class='album'>"+t.album+"</li></ul></div></div>",html}var T,k,P="/build/images/",x={pointsOfInterest:[],social:[]},L={},b=[],F={},I=[],S=[],z=5,C={waypoints:null,social:null},O=!1;t()};