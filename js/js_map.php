<?php
	require_once("../config/config.php");
?>

//Request na server
var firstRun = true;
var xmultiplicator;
var zmultiplicator;
var oldTop = {};
var oldLeft = {};
var oldRotation = {};
var animatePlayerCSteamID = "";
var animateTop = "";
var animateLeft = "";
var animateRotation = "";
var animateOldRotation = "";
var skipAnimate = false;
var admin = 0;
var resize = false;
var naturalHeight;


$(function() {

	$(document).keydown(function(event) {
		if (event.ctrlKey==true && (event.which == "61" || event.which == "107" || event.which == "173" || event.which == "109"  || event.which == "187"  || event.which == "189"  ) ) {
			event.preventDefault();
		}
	});

	$(window).bind("mousewheel DOMMouseScroll", function (event) {
		if (event.ctrlKey == true) {
			event.preventDefault();
		}
	});

	var ms_ie = false;
	var ua = window.navigator.userAgent;
	var old_ie = ua.indexOf("MSIE ");
	var new_ie = ua.indexOf("Trident/");
	var edge = ua.indexOf("Edge/");

	if ((old_ie > -1) || (new_ie > -1) || (edge > -1)) {
		ms_ie = true;
	}

	if ( ms_ie ) {
		document.body.innerHTML = document.body.innerHTML + '<div style="position: fixed; background: #8B0000; width: 600px; top: 10px; left: 50%; margin-left: -320px; font-size: 30px; padding-left: 20px; padding-right: 20px; color: red; border: 2px solid black; z-index: 99999999999; border-radius: 20px; text-align: center">You are using browser, that is not supported!</div>';
	}

	sendRequest();
	$("#mainImage").load(function() {if(firstRun==true){init();firstRun=false;sendRequest();};});
	$(window).resize(function(){resize = true; init()});
	var interval = setInterval(sendRequest, <?php echo $syncinterval; ?>);
});
function init() {
	if (document.body.offsetHeight > document.body.offsetWidth) {
		//Pozice obrázku
		document.getElementById("mainImage").style.height = "";
		document.getElementById("mainImage").style.width = "100%";
		document.getElementById("mainImage").style.position = "absolute";
		document.getElementById("mainImage").style.top = "50%";
		marginTop = Number(document.getElementById("mainImage").offsetHeight)/2*-1;
		document.getElementById("mainImage").style.marginTop = marginTop + "px";
		document.getElementById("positionFrame").style.top = "50%";
		document.getElementById("positionFrame").style.marginTop = marginTop + "px";
	} else {
		document.getElementById("mainImage").style.position = "static";
		document.getElementById("mainImage").style.margin = "0 auto";
		document.getElementById("mainImage").style.height = "100%";
		document.getElementById("mainImage").style.width = "";
		document.getElementById("mainImage").style.top = "";
		document.getElementById("mainImage").style.marginTop = "";
		document.getElementById("positionFrame").style.top = "";
		document.getElementById("positionFrame").style.marginTop = ""; 
	}

	$("*").css("visibility", "visible");
	$("#loading").css("visibility", "hidden");
	document.getElementById("positionFrame").style.height = document.getElementById("mainImage").offsetHeight + "px";
	document.getElementById("positionFrame").style.width = document.getElementById("mainImage").offsetWidth + "px";
	var marginLeft = Number(document.getElementById("mainImage").offsetWidth) / 2 * -1;
	document.getElementById("positionFrame").style.marginLeft = marginLeft + "px";


	//Vypočítání poměru velikosti obrázku a skutečného rozlišení obrázku
	var naturalHeight = document.getElementById("mainImage").naturalHeight;
	var naturalWidth = document.getElementById("mainImage").naturalWidth;
	var offsetHeight = document.getElementById("mainImage").offsetHeight;
	var offsetWidth = document.getElementById("mainImage").offsetWidth;


	xmultiplicator =  Number(offsetWidth) / Number(naturalWidth);
	zmultiplicator = Number(offsetHeight) / Number(naturalHeight);


	sendRequest();
}

function sendRequest() {
	admin = 0;
	var conn;
	if(window.XMLHttpRequest) {
		conn = new XMLHttpRequest();
	} else {
		conn = new ActiveXObject("Microsoft.XMLHTTP");
	}
	conn.open("GET", "core.php?user=client", false);
	conn.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	conn.send();

	var data = conn.responseText;
	var data = data.split("]");

	var players = Number(data.length) - 2;
	if (players != 1) {s = "s"} else {s = ""};

	//Zjisštění aktuální mapy
	var map = data[0].substring(1);
	var map = map.split("=");
	var map = map[1];
	document.getElementById("info").innerHTML = "Current map: " + map + "<br>Currently " + players + " player" + s + " online";
	document.getElementById("mainImage").src = ".maps/" + map + ".png";
	
	//Vypsání hráčů
	if (firstRun == false) {
		document.getElementById("positionFrameWrap").innerHTML = "";
		for (var i = 1; i < data.length; i++) {
			if (data[i] != "") {
				player = data[i].substring(1);

				//Jméno hráče
				var playerName = player.split(";");
				var playerName = playerName[0];
				var playerName = playerName.split("=");
				var playerName = playerName[1];

				//CSteamID hráče
				var playerCSteamID = player.split(";");
				var playerCSteamID = playerCSteamID[1];
				var playerCSteamID = playerCSteamID.split("=");
				var playerCSteamID = playerCSteamID[1];

				//Pozice hráče
				var playerPosition = player.split(";");
				var playerPosition = playerPosition[2];
				var playerPosition = playerPosition.split("=");
				var playerPosition = playerPosition[1];
				var playerPosition = playerPosition.substring(1);
				var playerPosition = playerPosition.substring(0, playerPosition.length -1);

				//Pozice x
				var x = playerPosition.split(",");
				var x = x[0];

				//Pozice z
				var z = playerPosition.split(",");
				var z = z[2];

				//Rotace
				var rotation = player.split(";");
				var rotation = rotation[3];
				var rotation = rotation.split("=");
				var rotation = rotation[1];

				//PlayerStatus
				var playerStatus = player.split(";");
				var playerStatus = playerStatus[4];
				var playerStatus = playerStatus.split("=");
				var playerStatus = playerStatus[1];
				if (playerStatus == "player") {
					playerStatus = "playerColor";
				};
				if (playerStatus == "admin") {
					admin++;
				};

				//Přepočítání pozice x na hodnotu left
				if (document.getElementById("mainImage").naturalHeight == "1024") {					
					if (Number(x) < 0) {
						var left = Number(x)*-1;
						var left = Number(left)/1.93630573;
						var left = 512 - Number(left);
						var left = Number(left) * Number(xmultiplicator);
					} else {
						var left = Number(x);
						var left = Number(left)/1.93630573;
						var left = Number(left) + 512;
						var left = Number(left) * Number(xmultiplicator);
					}

					//Přepočítání hodnoty x na hodnotu top
					if (Number(z) < 0) {
						var top = Number(z);
						var top = Number(top)/1.93630573;
						var top = 512 - Number(top);
						var top = Number(top) * Number(zmultiplicator);
					} else {
						var top = Number(z)*-1;
						var top = Number(top)/1.93630573;
						var top = Number(top) + 512;
						var top = Number(top) * Number(zmultiplicator);
					}
				} else if(document.getElementById("mainImage").naturalHeight == "2048") {
					if (Number(x) < 0) {
						var left = Number(x)*-1;
						var left = Number(left)/1.93630573;
						var left = 1024 - Number(left);
						var left = Number(left) * Number(xmultiplicator);
					} else {
						var left = Number(x);
						var left = Number(left)/1.93630573;
						var left = Number(left) + 1024;
						var left = Number(left) * Number(xmultiplicator);
					}

					//Přepočítání hodnoty x na hodnotu top
					if (Number(z) < 0) {
						var top = Number(z);
						var top = Number(top)/1.93630573;
						var top = 1024 - Number(top);
						var top = Number(top) * Number(zmultiplicator);
					} else {
						var top = Number(z)*-1;
						var top = Number(top)/1.93630573;
						var top = Number(top) + 1024;
						var top = Number(top) * Number(zmultiplicator);
					}
				} else if(document.getElementById("mainImage").naturalHeight == "512") {
					if (Number(x) < 0) {
						var left = Number(x)*-1;
						var left = Number(left)/1.93630573;
						var left = 256 - Number(left);
						var left = Number(left) * Number(xmultiplicator);
					} else {
						var left = Number(x);
						var left = Number(left)/1.93630573;
						var left = Number(left) + 256;
						var left = Number(left) * Number(xmultiplicator);
					}

					//Přepočítání hodnoty x na hodnotu top
					if (Number(z) < 0) {
						var top = Number(z);
						var top = Number(top)/1.93630573;
						var top = 256 - Number(top);
						var top = Number(top) * Number(zmultiplicator);
					} else {
						var top = Number(z)*-1;
						var top = Number(top)/1.93630573;
						var top = Number(top) + 256;
						var top = Number(top) * Number(zmultiplicator);
					}
				}
				document.getElementById("positionFrameWrap").innerHTML = document.getElementById("positionFrameWrap").innerHTML + '<div style="visibility: visible" class="player" id="\' + playerCSteamID + \'"><img style="visibility: visible" class="playerImage" id="\' + playerCSteamID + \'cursor"src="cursor.png"><div style="visibility: visible" class="playerInfo \' + playerStatus + \'">\' + playerName + \'</div></div>';
				if (oldTop[playerCSteamID] != undefined && resize != true) {	
					var topDiference = oldTop[playerCSteamID] - top;
					var leftDiference = oldLeft[playerCSteamID] - left;
					if (topDiference > 100 || topDiference < -100 || leftDiference > 100 || leftDiference < -100) {skipAnimate = true};
					document.getElementById(playerCSteamID).style.top = oldTop[playerCSteamID] + "px";
					document.getElementById(playerCSteamID).style.left = oldLeft[playerCSteamID] + "px";
					document.getElementById(playerCSteamID + "cursor").style.transform = "rotate(" + oldRotation[playerCSteamID] + "deg)";
					document.getElementById(playerCSteamID + "cursor").style.msTransform = "rotate(" + oldRotation[playerCSteamID] + "deg)";
					document.getElementById(playerCSteamID + "cursor").style.webkitTransform = "rotate(" + oldRotation[playerCSteamID] + "deg)";
				} else {
					document.getElementById(playerCSteamID).style.top = top + "px";
					document.getElementById(playerCSteamID).style.left = left + "px";
					document.getElementById(playerCSteamID + "cursor").style.transform = "rotate(" + rotation + "deg)";
					document.getElementById(playerCSteamID + "cursor").style.msTransform = "rotate(" + rotation + "deg)";
					document.getElementById(playerCSteamID + "cursor").style.webkitTransform = "rotate(" + rotation + "deg)";
				}
				if (skipAnimate == false || resize == true) {
					animatePlayerCSteamID = animatePlayerCSteamID + ";" + playerCSteamID;
					animateTop = animateTop + ";" + top;
					animateLeft = animateLeft + ";" + left;
					if (oldRotation[playerCSteamID] != undefined) {
						cache = Number(rotation) - Number(oldRotation[playerCSteamID]);
						animateRotation = animateRotation + ";" + cache;
					} else {
						animateRotation = animateRotation + ";" + rotation;
					}
					animateOldRotation = animateOldRotation + ";" + oldRotation[playerCSteamID];
				} else {
					skipAnimate = false;
					document.getElementById(playerCSteamID).style.top = top + "px";
					document.getElementById(playerCSteamID).style.left = left + "px";
					document.getElementById(playerCSteamID + "cursor").style.transform = "rotate(" + rotation + "deg)";
					document.getElementById(playerCSteamID + "cursor").style.msTransform = "rotate(" + rotation + "deg)";
					document.getElementById(playerCSteamID + "cursor").style.webkitTransform = "rotate(" + rotation + "deg)";
				}
					
				oldTop[playerCSteamID] = top;
				oldLeft[playerCSteamID] = left;
				oldRotation[playerCSteamID] = rotation;
			}
		}

		if(resize != true) {animate()};
		if (admin == 1) {
			admins = "";
		} else {
			admins = "s";
		}
		document.getElementById("info").innerHTML = document.getElementById("info").innerHTML + "<br>Currently " + admin + " admin" + admins + " online";
		resize = false;
	};
}

function animate() {
	animatePlayerCSteamID = animatePlayerCSteamID.split(";");
	animateTop = animateTop.split(";");
	animateLeft = animateLeft.split(";");
	animateRotation = animateRotation.split(";");
	animateOldRotation = animateOldRotation.split(";");
	for (var i = 0; i < animatePlayerCSteamID.length; i++) {
		$("#" + animatePlayerCSteamID[i]).animate({top: animateTop[i], left: animateLeft[i]}, {duration: ' . $syncinterval . ', easing:"linear", queue: false});
		AnimateRotate(animateRotation[i], animateOldRotation[i], animatePlayerCSteamID[i]);
	};
	animatePlayerCSteamID = "";
	animateTop = "";
	animateLeft = "";
	animateRotation = "";
	animateOldRotation = "";

	checkArray();
}

function AnimateRotate(d, old, id){
	$({deg: 0}).animate({deg: d}, {
		duration: ' . $syncinterval . ',
		step: function(now, fx){
			rotationLevel = Number(now) + Number(old);
			$("#" + id + "cursor").css({
				transform: "rotate(" + rotationLevel + "deg)"
			});
		}
	});
}

function checkArray() {
	var inputs = document.getElementsByClassName("player");
	var shownPlayers = []
	var array = Object.keys(oldTop);
	for (var i = 0; i < inputs.length; i++) {
		shownPlayers.push(inputs[i].id);
	}

	for (var i = 0; i < array.length; i++) {
		if(shownPlayers.indexOf(array[i]) == -1) {
			delete oldLeft[array[i]];
			delete oldTop[array[i]];
		}
	};
}