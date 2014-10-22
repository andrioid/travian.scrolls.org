/* scrollsMap - a simple 2D game map in HTML5 canvas
 * - Copyright: Andri Oskarsson (andri80@gmail.com)
 * - All rights reserved
**/

Object.size = function(obj) {
    var size = 0, key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) size++;
    }
    return size;
};

(function($){
  $.fn.scrollsMap = function(server,x,y,z) {
	var map = this;
	var towns = Array();
	map.tz = 2; // base tyle size (do not change this)
	map.state = 0; // 0=none,1=tiles loaded,2=data loaded,3=rendered
	map.defaults = {}
	map.defaults.server = 's3.travian.dk';
	map.defaults.zoomlevel = 30;
	map.settings = {};
	map.settings.dataTiles = 80; // How many tiles to store in data set (must match with server!)
	map.settings.dataServer = 'http://travian.scrolls.org/maps/data'; // no leading slashes
	map.settings.zoomMultiplier = 1.5;
	map.settings.tiles = {};
	map.settings.tiles.fort = '/maps/images/fort.png';
	map.settings.tiles.village = '/maps/images/village.png';
	map.settings.tiles.city = '/maps/images/city.png';
	map.tiles = {};
	map.dataset = {};
	if (z) { var zoomlevel=map.settings.zoomlevel=z; } else { var zoomlevel = map.settings.zoomlevel = map.defaults.zoomlevel; }
	if (x) { var startx=x; } else { var startx = 0; }
	if (y) { var starty=y; } else { var starty = 0; }
	if (server) { map.settings.server = server; } else { map.settings.server = map.defaults.server; }


	var mcx = 0; // mouse current position
	var mcy = 0; // mouse current position

	// Methods
	map.createCanvas = function() {
		map.html('<canvas></canvas>');
		map.append('<div id="controls"><div id="buttons"><a id="zoomin" href="#">Zoom In</a> <a id="zoomout" href="#">Zoom Out</a><a id="moveup" href="#">Up</a> <a id="movedown" href="#">Down</a> <a id="moveleft" href="#">Left</a> <a id="moveright" href="#">Right</a></div></div>');
		map.append('<div id="goto"><label>x</label> <input type=text id="inp_x"> <label>y</label> <input type=text id="inp_y"> <input id="submit" type="submit" value="Go"></div>');

		map.append('<div id="status"></div>');
		map.append('<div id="hoverbox"></div>');
		map.append('<div id="infobox"></div>');
		map.append('<div id="statusbox"><span class="text"></span><span class="icon"></span></div>');
		map.append('<div id="mask"></div>');
		map.canvas = $('canvas', map);
		if (map.canvas[0]) {
			if (typeof(G_vmlCanvasManager) != 'undefined') { G_vmlCanvasManager.initElement(map.canvas[0]); }
//			if (typoeof(G_vmlCanvasManager) != 'undefined') { // ie IE
//				G_vmlCanvasManager.initElement(map.canvas[0]);
//			}
			map.ctx = map.canvas[0].getContext('2d');
		} else {
			map.state = -1; map.trigger("mapclick");
			//alert("Your browser does not support HTML5 Canvas.");
		}
	}

	map.updateCanvas = function() {
		map.canvas.attr('width', map.x);
		map.canvas.attr('height', map.y);
		map.canvas.css('width', map.x);
		map.canvas.css('height', map.y);
		map.state = 3; map.trigger("maptick");
	}
	/* This is a function that loads the map tiles and calls render() when ready */
	/* - it is only used at startup */
	map.loadTiles = function() {
		var ready = Object.size(map.settings.tiles);
		var count = 0;
		$.each(map.settings.tiles, function(i, item) {
			map.tiles[i] = new Image();
			map.tiles[i].src = item;
			map.tiles[i].onload = function() {
				count++;
				if (count == ready) { map.state = 1; map.trigger("maptick"); }
			}
		});
	}

	map.loadData = function() {
		var requests =  {};
		var request = '';
		var done = 0;
		var counter = 0;
		var index;
		// Go through it, craft queries (ignore the stuff we already have)
		for (var i=map.range.dsx;i<=map.range.dex;i++) { // for each x coordinate
			for (var j=map.range.dsy;j<=map.range.dey;j++) {
				index = i+','+j;
				if (map.dataset[index]) {
					//console.log('Ignoring tileset ('+index+')');
					// Exists in cache, ignore
				} else {
					counter++;
					requests[index] = map.settings.dataServer+'/'+map.settings.server+'/'+i+'_'+j+'.json';
				}
			}
		}
		//console.log(requests);
		// Initiate rendering if we have all the tiles
		if (counter == 0) { map.state = 4; map.trigger("maptick"); }
		
		// Request the rest and handle it
		$.each(requests, function(i, req) {
			$.get(req, function(data){
				$.each(data, function(i, item) {
					map.addTown(data[i]); // note that map.towns is not populated until after the post is finished (so not at runtime)
				});
				done++;
				map.dataset[i] = 1; // Mark that we have the dataset
				//console.log("Save map data for "+i);
				if (done == counter) {
					map.state = 4; map.trigger("maptick");
				}
			}, 'json');
		});

		
		//console.log("loadData called, state:"+map.state);
/*		
		$.post("config.php", { sx: map.range.sx, ex: map.range.ex, sy: map.range.sy, ey: map.range.ey },
	  		function(data){
				$.each(data, function(i, item) {
					//console.log(data[i].x+','+data[i].y);
					map.addTown(data[i]); // note that map.towns is not populated until after the post is finished (so not at runtime)
					//map.renderTown(data[i].x, data[i].y);
				});
				map.state = 4; map.trigger("maptick");
	   	}, 'json');
*/	
	}

	map.render = function() {
		map.ctx.clearRect(0,0, map.x, map.y); // Clear the area
		map.renderGrid(); // Render grid
		if (map.range.ex - map.range.sx <= 0) { // Range is less than 0?!
			map.state = 0;
			map.trigger("maptick");
			return;
		}

		$('#status', map).html(map.settings.server+" | Zoom: "+Math.round(zoomlevel));
		$('#inp_x', map).attr('value', startx);
		$('#inp_y', map).attr('value', starty);
		if (map.towns) {
			$.each(map.towns, function(i, item) {
				if (map.towns[i]) {
					$.each(item, function(j, twn) {
						if (map.towns[i][j] && twn.x > map.range.sx && twn.x < map.range.ex && twn.y > map.range.sy && twn.y < map.range.ey) {
							map.renderTown2(twn);
						} else {

						}
					});
				}
			});
		}
		map.state = 5; map.trigger("maptick");
	};

	map.addTown = function(d) {
		if (!d) { console.log('d is not defined, wtf'); }
		var tmpx = Number(d.x);
		var tmpy = Number(d.y);
		if (!map.towns) { map.towns = {}; }
		if (!map.towns[tmpx]) { map.towns[tmpx] = {}; }
		map.towns[tmpx][tmpy] = d;
	}


	map.inScopePoint = function(x, y) {
		if (x < map.range.sx && x > map.range.ex) { 
			return 0; 
		}
		if (y < map.range.sy && y > map.range.ey) {
			return 0;
		}
		return 1;
	}

	map.point2row = function (points) { // Convert relative points to points (rows and columns)
		//var px = (startx-points[0]+map.cx);
		var px = Number(map.cx+points[0]-startx);
		var py = Number(starty+map.cy-points[1]);
		return([px, py]); // works 04.10.2010 17:40
	}

	map.row2point = function (rows) { // Convert points to relative points
		// startx-rows[0]+map.cx
		var px = rows[0]+(startx-map.cx);
		var py = starty-rows[1]+map.cy;
		return ([px, py]);
	}

	map.pixel2row = function (pixels) { // Pixel to rows
		var x1 = Math.floor((pixels[0]-map.xmargin)/map.tilesize); // column 
		var y1 = Math.floor((pixels[1]-map.ymargin)/map.tilesize); // row
		return([x1, y1]);
	}

	map.point2pixel = function (point) {
		var rows = map.point2row(point);
		if (rows[0] < 0 || rows[1] < 0) { 
			//console.log("negative rows on:"+point[0]+','+point[1]+' as: '+rows[0]+','+rows[1]); 
		}
		var ppx = Math.round((rows[0]*map.tilesize)+map.xmargin);
		var ppy = Math.round((rows[1]*map.tilesize)+map.ymargin);
		return([ppx, ppy]);		
	}

	map.pixel2point = function (pixels) {
		return map.row2point(map.pixel2row(pixels));
	}

	map.renderGrid = function() {
		map.ctx.globalAlpha = 0.025;
		for (var j=map.ymargin;j<=map.y-map.ymargin;j=j+map.tilesize) {
			map.ctx.strokeStyle = "rgb(0,0,0)";
			map.ctx.beginPath();
			map.ctx.moveTo(map.xmargin, j);
			map.ctx.lineTo(map.x-map.xmargin, j);
			map.ctx.closePath();
			map.ctx.stroke();
		}
		for (var i=map.xmargin; i<=map.x-map.xmargin;i=i+map.tilesize) {
			map.ctx.strokeStyle = "rgb(0,0,0)";
			map.ctx.beginPath();
			map.ctx.moveTo(i, map.ymargin);
			map.ctx.lineTo(i, map.y-map.ymargin);
			map.ctx.closePath();
			map.ctx.stroke();				
		}
		map.ctx.globalAlpha = 1;
		map.ctx.strokeStyle = "#000000";
		var pixel = map.point2pixel([0,0]);
		map.ctx.strokeRect(pixel[0], pixel[1], map.tilesize, map.tilesize);
	}

	map.renderTown = function(x,y) {
		x = Number(x); y = Number(y);
		if (x<map.range.sx || x>map.range.ex || y<map.range.sy || y>map.range.ey) { console.log("broke rendertown"); return; }
		var pixel = map.point2pixel([x,y]);
		//console.log('Point: '+x+','+y+' Pixels:'+pixel[0]+','+pixel[1]);
		if (pixel == null) { console.log(x+','+y); }
		var px = pixel[0];
		var py = pixel[1];

		if (map.tilesize >= 32) {
			/* Large mode */
		   map.ctx.fillStyle = "rgb(20,0,0)";
			map.ctx.font = 8+"px Helvetica";
		 	map.ctx.fillText (x+","+y, px+(map.tilesize/20), py+map.tilesize-(map.tilesize/20));
			var scalex = Math.round((map.tilesize/map.tiles.village.width)*map.tiles.village.width)-15;
			var scaley = Math.round((map.tilesize/map.tiles.village.height)*map.tiles.village.height)-15;
			map.ctx.drawImage(map.tiles.village, px+7.5, py+7.5, scalex, scaley);
		}	else if (map.tilesize < 5) {
			/* Compact mode */
			map.ctx.fillStyle = "rgb(240,34,19)";
			map.ctx.fillRect(px, py, map.tilesize, map.tilesize);
		} else {
			// Inner land
			map.ctx.strokeStyle = "rgb(139,105,20)";
			map.ctx.fillStyle = "rgb(173,216,230)";
			map.ctx.beginPath();
			map.ctx.arc(px+(map.tilesize/2), py+(map.tilesize/2), map.tilesize/2.5, 0, Math.PI*2,true)
			map.ctx.stroke();
			map.ctx.fill();
			map.ctx.closePath();
			map.ctx.beginPath();
			// Outer wall
			map.ctx.strokeStyle = "rgb(0,0,0)";
			map.ctx.fillStyle = "rgb(240,34,19)";
			map.ctx.arc(px+(map.tilesize/2), py+(map.tilesize/2), map.tilesize/3.5, 100, Math.PI*2,true)
			map.ctx.stroke();
			map.ctx.fill();
			map.ctx.closePath();
		}

		//map.ctx.fillText (map.x+","+map.y, px+10, py+(map.tilesize/2)+10);
		return true;
		//map.ctx.strokeRect(this.newx+1, this.newy+1, map.tilesize-1, map.tilesize-1);
		//alert(this.point[0]);
	}

	map.renderTown2 = function(d) {
			if (d.x < map.range.sx || d.x > map.range.ex || d.y < map.range.sy || d.y > map.range.ey) { return; }
			var pixel = map.point2pixel([Number(d.x), Number(d.y)]);
			var px = pixel[0];
			var py = pixel[1];
			//var flagcolor = '#FF00F5';
			var flagcolor = colorToString(randomColor(d.aid));
			if (map.tilesize > 25) {
				if (d.population > 500) {
					var scalex = Math.round((map.tilesize/map.tiles.city.width)*map.tiles.city.width)-15;
					var scaley = Math.round((map.tilesize/map.tiles.city.height)*map.tiles.city.height)-15;
					map.ctx.drawImage(map.tiles.city, px+7.5, py+4, scalex, scaley);
				} else if (d.population > 250) {
					var scalex = Math.round((map.tilesize/map.tiles.village.width)*map.tiles.village.width)-15;
					var scaley = Math.round((map.tilesize/map.tiles.village.height)*map.tiles.village.height)-15;
					map.ctx.drawImage(map.tiles.fort, px+7.5, py+4, scalex, scaley);
				} else {
					var scalex = Math.round((map.tilesize/map.tiles.village.width)*map.tiles.village.width)-15;
					var scaley = Math.round((map.tilesize/map.tiles.village.height)*map.tiles.village.height)-15;
					map.ctx.drawImage(map.tiles.village, px+7.5, py+4, scalex, scaley);
				}
				map.ctx.fillStyle = "rgb(20,0,0)";
				map.ctx.strokeStyle = "#FFFFFF";
				map.ctx.font = 6.5+"pt sans-serif";
				//map.ctx.strokeText (d.x+","+d.y, px+(map.tilesize/20), py+map.tilesize-(map.tilesize/20));
			 	//map.ctx.fillText (d.x+","+d.y, px+(map.tilesize/20), py+map.tilesize-(map.tilesize/20));
				map.ctx.fillText (d.player, px+(map.tilesize/20), py+map.tilesize-(map.tilesize/20));

				if (d.alliance) {
					// Flag
					map.ctx.fillStyle = flagcolor;
					map.ctx.strokeStyle = "#000000";
					map.ctx.beginPath();
					map.ctx.moveTo(px+map.tilesize-(map.tilesize/3),py+map.tilesize-(map.tilesize/10));
					map.ctx.lineTo(px+map.tilesize-(map.tilesize/3),py+(map.tilesize/2));
					map.ctx.lineTo(px+map.tilesize-(map.tilesize/10),py+map.tilesize-(map.tilesize/2));
					map.ctx.lineTo(px+map.tilesize-(map.tilesize/10),py+map.tilesize-(map.tilesize/3.5));
					map.ctx.lineTo(px+map.tilesize-(map.tilesize/3),py+map.tilesize-(map.tilesize/3.5));
					map.ctx.stroke();
					map.ctx.fill();
					var lingrad = map.ctx.createLinearGradient(px+map.tilesize-(map.tilesize/3),
						py+map.tilesize-(map.tilesize/2), 
						px+map.tilesize-(map.tilesize/10), 
						py+map.tilesize-(map.tilesize/2));
					lingrad.addColorStop(0, '#FFFFFF');
					lingrad.addColorStop(0.5, '#000000');
					lingrad.addColorStop(1, '#FFFFFF');
					map.ctx.fillStyle  = lingrad;
					map.ctx.globalAlpha = 0.25;
					map.ctx.fill();
					map.ctx.globalAlpha = 1;
					map.ctx.closePath();
				}			
			} else {
				map.ctx.fillStyle = flagcolor;
				map.ctx.beginPath();
				map.ctx.arc(px+(map.tilesize/2), py+(map.tilesize/2), map.tilesize/2, 0, Math.PI*2,true)
				map.ctx.stroke();
				map.ctx.fill();
				map.ctx.closePath();
			}


	}

	map.updateVariables = function() {
		map.tilesize = map.tz*zoomlevel;
		map.x = this.width(); // map total width (pixels)
		map.y = this.height(); // map total height (pixels)
		map.xtiles = Math.floor(map.x/map.tilesize); // number of tiles x
		map.ytiles = Math.floor(map.y/map.tilesize); // number of tiles y
		map.cx = Math.floor(map.xtiles/2); // center tile x
		map.cy = Math.floor(map.ytiles/2); // center tile y
		map.xmargin = (map.x-(map.xtiles*map.tilesize))/2;
		map.ymargin = (map.y-(map.ytiles*map.tilesize))/2;
		map.range = {
			sx: startx-(map.xtiles/2),
			ex: startx+(map.xtiles/2),
			sy: starty-(map.ytiles/2),
			ey: starty+(map.ytiles/2)
		}
		map.range.dsx = Math.floor(map.range.sx/map.settings.dataTiles);
		map.range.dex = Math.floor(map.range.ex/map.settings.dataTiles);
		map.range.dsy = Math.floor(map.range.sy/map.settings.dataTiles);
		map.range.dey = Math.floor(map.range.ey/map.settings.dataTiles);
		map.state = 2; map.trigger("maptick");
	}

	map.zoomIn = function() {
		zoomlevel = zoomlevel*map.settings.zoomMultiplier;
		map.state = 1; map.trigger("maptick");
	}

	map.zoomOut = function() {
		zoomlevel = zoomlevel/map.settings.zoomMultiplier;
		if (zoomlevel<1) {
			zoomlevel = 1;
			map.state = 1; map.trigger("maptick");
			return false;
		}
		map.state = 1; map.trigger("maptick");
	}

	map.moveUp = function() {
		starty=starty+Math.floor(map.ytiles/4);
		map.state = 1; map.trigger("maptick");
	}

	map.moveDown = function() {
		starty=starty-Math.floor(map.ytiles/4);
		map.state = 1; map.trigger("maptick");
	}

	map.moveRight = function() {
		startx=startx+Math.floor(map.xtiles/4);
		map.state = 1; map.trigger("maptick");
	}

	map.moveLeft = function() {
		startx=startx-Math.floor(map.xtiles/4);
		map.state = 1; map.trigger("maptick");
	}

	map.pixelToPoint = function(x,y) {
		x=x-map.xmargin;
		y=y-map.ymargin;
		x = Math.min(x, map.xtiles*map.tilesize);
		y = Math.min(y, map.ytiles*map.tilesize);
		var x1 = Math.floor(x/map.tilesize); // column 
		var y1 = Math.floor(y/map.tilesize); // row
		var x2 = startx+x1-map.cx;
		var y2 = starty-y1+map.cy;
		return([x2, y2]);
	}

	map.pointToPixel = function(x, y) { // broken
		var px = Number(x)+Number(startx)-Number(0);
		//return;
		var py = starty+map.cy-y;
		//alert(py);
		/* relative points */
		var ppx = ((px*map.tilesize)+map.xmargin);
		var ppy = ((py*map.tilesize)+map.ymargin);
		if (ppx < 0 || ppy < 0) { 
			console.log('Points: '+px+','+py+' Pixels:'+ppx+','+ppy);
			return null; 
		}
		//px = px*map.tilesize;
		//py = py*map.tilesize;
		//var px = (startx+map.cx+x)*map.tilesize+map.xmargin;
		//var py = (starty+map.cy+y)*map.tilesize+map.ymargin;
		//alert(['Test', x, y, px, py]);
		return([ppx, ppy]);
	}

	map.debugPoint = function (point) {
		var pixel = map.point2pixel(point);
	   map.ctx.fillStyle = "rgb(0,0,0)";
		map.ctx.font = "10px Helvetica";
		var ptr = map.pixel2row([pixel[0], pixel[1]]);
	 	map.ctx.fillText ("row:"+ptr[0]+","+ptr[1], pixel[0], pixel[1]-20);
	 	map.ctx.fillText ("pixels:"+pixel[0]+","+pixel[1], pixel[0], pixel[1]-10);

		var ptp = map.row2point([ptr[0],ptr[1]]);
	 	map.ctx.fillText ("points:"+ptp[0]+","+ptp[1], pixel[0], pixel[1]);
		map.ctx.fillText( "spoints:"+point[0]+","+point[1], pixel[0], pixel[1]+10);
	}
	/* A function that is run every time we change the map in any way (does not include resize or creation of elements) */

	// Variables

	//map.update();
	//map.tilesize = 10*zoomlevel;

	// Constructor

	map.createCanvas();
	map.statusbox = $('#statusbox', map);
	map.statusbox.text = function(t, icon) {
		this.css('top', map.height()/2-this.height()/2);
		this.css('left', map.width()/2-this.width()/2);
		$('.text', this).html(t);
		if (!icon) { $('.icon', this).addClass('wait'); } else { $('.icon', this).addClass('error'); }
	}
	
	/* Binds */
	// Map tick
	map.bind('maptick', function (e) {
		var state = 'state:'+map.state;
		if (map.state < 5) { map.statusbox.fadeIn(100); } else { map.statusbox.fadeOut(50); }
		//console.log(state);
		switch (map.state) {
			case -1:
				map.statusbox.text('<p>Your browser does not support HTML5 Canvas.</p> <p>Please consider using Google Chrome or Firefox.</p>', 'error');
			break;
			case 0:
				map.statusbox.text('Preloading map graphics...');
				map.loadTiles();
			break;
			case 1:
				map.updateVariables();
			break
			case 2:
				map.updateCanvas();
			break;
			case 3:
				map.statusbox.text('Loading remote map data...');
				map.loadData();
			break;
			case 4:
				map.statusbox.text('Rendering...');
				map.render();
			break;
			case 5:
				// done
			break;
			default:
				alert("Unsupported map state");
			break;
		}
	});

	// Zoom in / Zoom out (wheel)
	map.canvas.mousewheel(function(event, delta, deltax, deltay){
		if (delta < 0) map.zoomOut();
		else map.zoomIn();
	});
/*
	// Mouse clicking
	map.canvas.mousedown(function(event){
		mcx = event.pageX-map.canvas[0].offsetLeft;
		mcy = event.pageY-map.canvas[0].offsetTop;

		map.canvas.bind('mouseup', function(event) {
			var diffx = mcx-(event.pageX-map.canvas[0].offsetLeft);
			var diffy = mcy-(event.pageY-map.canvas[0].offsetTop);
			//this.diffy = Math.round(this.mcy-event.clientY);


			if (diffx && diffy && (Math.abs(diffy) > 10 || Math.abs(diffx) > 10)) {
				startx = Math.round(startx+(diffx/5)); // reduce sensitivity
				starty = Math.round(starty+(diffy/5)); // reduce sensitivity
				map.state = 1; map.trigger("maptick");
			} else {
				// Treat this as a normal click
				//var ptp = map.pixelToPoint(mcx,mcy);
				//var ptp = map.pixel2point([mcx, mcy]);
				//map.infobox(ptp[0], ptp[1]);
				//startx = ptp[0]; starty = ptp[1];
				//map.render();
			}
			map.canvas.unbind('mouseup');
		});
	});
*/
	map.canvas.dblclick(function(e) {
		var mcx = e.pageX-map.canvas[0].offsetLeft;
		var mcy = e.pageY-map.canvas[0].offsetTop;
		var point = map.pixel2point([mcx, mcy]);
		startx = point[0];
		starty = point[1];
		zoomlevel = map.defaults.zoomlevel;
		map.state = 1; map.trigger("maptick");
	});

	map.infobox = function(x, y) {
		var winw = map.width();
		var winh = map.height();
/*
			$('#mask', map).css({'width': dw, 'height': dh});
			$('#mask', map).fadeIn(1000);
			$('#mask', map).fadeTo("slow", 0.8);
*/
		var infobox = $('#infobox', map);
			infobox.html('('+x+','+y+')<br>');
		if (map.towns[x] && map.towns[x][y]) {
			infobox.append('<label>Player</label>:'+map.towns[x][y].player+'<br>');
			infobox.append('<label>Alliance</label>:'+map.towns[x][y].alliance+'<br>');
			infobox.append('<label>Population</label>:'+map.towns[x][y].population+'<br>');
		}
		infobox.append('<label>Tile Range</label>: ('+map.range.dsx+','+map.range.dsy+') - ('+map.range.dex+','+map.range.dey+')<br>');
		infobox.append('tilesize:'+map.tilesize+'<br>');
		infobox.css('top', map.height()/2-infobox.height()-map.tilesize);
		infobox.css('left', map.width()/2-infobox.width()/2);
		infobox.css('position', 'absolute');
		infobox.fadeIn(100);
	}
	map.canvas.mousemove(function(e) {
		mcx = e.pageX-map.canvas[0].offsetLeft;
		mcy = e.pageY-map.canvas[0].offsetTop;
		var ptp = map.pixel2point([mcx, mcy]);
		var px = ptp[0];
		var py = ptp[1];
		var hover = $('#hoverbox', map);
		if (map.towns && map.towns[px] && map.towns[px][py] && map.state == 5) {
			var town = map.towns[px][py];
			hover.show();
			map.canvas.css('cursor', 'hand');
			hover.css('left', mcx-hover.width()/2);
			hover.css('top', mcy+20);
			hover.css('position', 'absolute');
			if (town.alliance) {
				hover.html('<span id="player">'+town.player+'['+town.alliance+']</span><br>');
			} else {
				hover.html('<span id="player">'+town.player+'</span><br>');
			}
			hover.append('<span id="village">'+town.village+' ('+town.population+')</span><br>');
			hover.append('<span id="xy">('+town.x+','+town.y+')</span><br>');
			//hover.append('<span id="population">'+town.population+'</span>');
		} else {
			map.canvas.css('cursor', 'auto');
			hover.hide();
		}


	});
	map.bind('resize', function() {
		map.state = 1; map.trigger("maptick");
	});

	$('a#zoomin', map).click(function(e){ e.preventDefault(); map.zoomIn(); });
	$('a#zoomout', map).click(function(e){ e.preventDefault(); map.zoomOut(); });
	$('a#movedown', map).click(function(e) { e.preventDefault(); map.moveDown(); });
	$('a#moveup', map).click(function(e) { e.preventDefault(); map.moveUp(); });
	$('a#moveleft', map).click(function(e) { e.preventDefault(); map.moveLeft(); });
	$('a#moveright', map).click(function(e) { e.preventDefault(); map.moveRight(); });
	$('#submit', map).click(function(e) { 
		e.preventDefault(); 
		startx=Number($('#inp_x').attr('value')); 
		starty=Number($('#inp_y').attr('value'));
		zoomlevel=map.settings.zoomlevel;
		map.state = 1; map.trigger("maptick");
	});


	/* Render the map */
	map.trigger("maptick");
	//map.render();
  };
})(jQuery);
