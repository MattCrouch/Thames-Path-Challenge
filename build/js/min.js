$(document).ready(function(){$("#map").length>0&&map()});var map=function(){function n(){google.maps.event.addDomListener(window,"load",function(){y=new google.maps.Map($("#map")[0],{center:S.start,zoom:14}),t(),e(),o(),s(),m(),p(),d()})}function e(){S={start:{name:"Putney Bridge",lat:51.46678,lng:-.213112,info:"START - Putney Bridge",icon:b.flag},"half way":{name:"Hurst Park",lat:51.392211,lng:-.344472,info:"25km - Half Way<br/>Hurst Park",icon:b.pointsOfInterest},finish:{name:"Runnymede Pleasure Ground",lat:51.442137,lng:-.55082,info:"FINISH - Runnymede",icon:b.flag}}}function t(){b.pointsOfInterest={anchor:new google.maps.Point(15,15),url:k+"live/icons/poi.svg",scaledSize:new google.maps.Size(30,30)},b.flag={anchor:new google.maps.Point(15,15),url:k+"live/icons/poi.svg",scaledSize:new google.maps.Size(30,30)},b.twitter={anchor:new google.maps.Point(15,15),url:k+"live/icons/twitter.svg",scaledSize:new google.maps.Size(30,30)},b.instagram={anchor:new google.maps.Point(15,15),url:k+"live/icons/instagram.svg",scaledSize:new google.maps.Size(30,30)}}function o(){i(T.pointsOfInterest),$.each(S,function(n,e){var t=c(e.lat,e.lng,e.name,e.icon);T.pointsOfInterest.push(t),h(T.pointsOfInterest);var o=new google.maps.InfoWindow({content:e.info});google.maps.event.addListener(t,"click",function(){a(),o.open(y,t),I.push(o)})})}function i(n){$.each(n,function(n,e){e.setMap(null)}),n=[]}function a(){$.each(I,function(n,e){e.close()})}function s(){P=new google.maps.Polyline({clickable:!1,strokeColor:"#0F3670",strokeOpacity:1,strokeWeight:5}),P.setMap(y),$.each(z,function(n,e){f(e)})}function l(n){var e={lat:n.lat,lng:n.lng,timestamp:n.timestamp};z.push(e),f(e)}function c(n,e,t,o){"undefined"==typeof o&&(o=null);var i=new google.maps.Marker({position:new google.maps.LatLng(n,e),title:t,icon:o});return i.setMap(y),i}function r(n){var e=c(n.lat,n.lng,"this is a post from "+n.source,b[n.source]),t=new google.maps.InfoWindow({content:v(n)});google.maps.event.addListener(e,"click",function(){a(),t.open(y,e),I.push(t)})}function u(n){L.length>=O&&L.splice(L.length-1,1),L.unshift(n),console.log(L[0].title),g(n)}function g(n){w(n);$(".overlay .lastfm").html(w(n))}function f(n){var e=P.getPath(),t=new google.maps.LatLng(n.lat,n.lng);e.push(t)}function m(){$.ajax({url:"fetchcoords.php",data:{since:x.waypoints},type:"GET",success:function(n){x.waypoints=n.sinceTimestamp,$.each(n.coordinates,function(n,e){l(e)}),M&&setTimeout(function(){m()},6e4)},error:function(){alert("Can't get the location at the moment :(")}})}function p(){$.ajax({url:"fetchsocial.php",data:{since:x.social},type:"GET",success:function(n){x.social=n.sinceTimestamp,$.each(n.posts,function(n,e){r(e)}),M&&setTimeout(function(){p()},3e5)},error:function(){alert("Can't get social feeds :(")}})}function d(){$.ajax({url:"fetchlastfm.php",data:{since:x.lastfm},type:"GET",success:function(n){x.lastfm=n.sinceTimestamp,$.each(n.tracks,function(n,e){u(e)}),M&&setTimeout(function(){d()},3e5)},error:function(){alert("Can't get scrobbles :(")}})}function h(n){for(var e=new google.maps.LatLngBounds,t=0;t<n.length;t++)e.extend(n[t].getPosition());y.fitBounds(e)}function v(n){return html="<div class='social "+n.source+"'><a href='"+n.url+"' target='_blank'><img src='"+n.image+"'/></a><p class='caption'>"+n.text+"</p></div>",html}function w(n){return html="<div class='nowPlaying'><a href='"+n.url+"'><img src='"+(""!==n.image_url_large?n.image_url_large:"build/images/live/icons/music-no-circle.svg")+"' alt='Now Playing' class='albumArt'/></a><div class='detail'><span>Now Playing</span><ul><li class='title'>"+n.title+"</li><li class='artist'>"+n.artist+"</li><li class='album'>"+n.album+"</li></ul></div></div>",html}var y,P,k="/build/images/",T={pointsOfInterest:[],social:[]},b={},I=[],S={},z=[],L=[],O=5,x={waypoints:null,social:null},M=!1;n()};