FabricHelper = function(canvasId, backgrounds, image, position) {

	this.backgrounds = backgrounds;
	this.image = image;
	this.canvasId = canvasId;
	this.bgIndex = 0;
	this.position = position;
	this.activeObject;

	this.canvas = new fabric.Canvas(this.canvasId, {	
	  selection: false
	});

	this.canvas.setBackgroundImage(this.backgrounds[this.bgIndex], this.canvas.renderAll.bind(this.canvas));

	fabric.Image.fromURL(this.image, function(img) {
	  img.scale(1).set({
	  	originX: 'left',
	  	originY: 'top',
	    left: 150,
	    top: 150,
	    angle: 0
	  });
	  this.canvas.add(img).setActiveObject(img);
	  this.activeObject = img;
	  this.setPosition();
	}.bind(this));

	this.listenFabric('object:rotating');
	this.listenFabric('object:scaling');
	this.listenFabric('object:moving');
	this.listenKeyboard();
	this.listenDblClick();

}

FabricHelper.prototype.listenFabric = function(e) {
	this.canvas.on(e, function(event) {
	  if (event.target) {
	  	this.activeObject = event.target;
	    //this.position[this.bgIndex] = "(" + event.target.scaleX + "," + event.target.scaleY + "," + event.target.left + "," + event.target.top + "," + event.target.angle + ")";
	    this.position[this.bgIndex] = this.recordPosition();
	  }
	}.bind(this));
}

FabricHelper.prototype.listenDblClick = function(e) {
	document.addEventListener("dblclick", function(e) {
		var result = "array(\n";
		this.position.forEach(function(a){
			result += "array(" + a[0] + ', ' + a[1] + ', ' + a[2] + ', ' + a[3] + ', ' + a[4] + "),\n";
		});
		result += ")";
		console.log(result);
	}.bind(this));
}

FabricHelper.prototype.listenKeyboard = function() {
	document.addEventListener('keydown', function(e) {
		this.activeObject = this.canvas.getActiveObject();
		if (!this.activeObject) {
			this.activeObject = this.canvas.item(0);
			this.canvas.setActiveObject(this.canvas.item(0));
		}
    	//left
	    if(e.keyCode == 37) {
	    	e.preventDefault();
	    	if (this.activeObject) {
	            this.activeObject.left -= 1;
        	}
        	this.position[this.bgIndex] = this.recordPosition();
	    }
	    //top
	    else if(e.keyCode == 38) {
	    	e.preventDefault();
	        if (this.activeObject) {
	            this.activeObject.top -= 1;
        	}
        	this.position[this.bgIndex] = this.recordPosition();
	    }
	    //right
	    else if(e.keyCode == 39) {
	    	e.preventDefault();
	        if (this.activeObject) {
	            this.activeObject.left += 1;
        	}
        	this.position[this.bgIndex] = this.recordPosition();
	    }
	    //bottom
	    else if(e.keyCode == 40) {
	    	e.preventDefault();
	        if (this.activeObject) {
	            this.activeObject.top += 1;
        	}
        	this.position[this.bgIndex] = this.recordPosition();
	    }

	    if(e.keyCode === 68) {
			e.preventDefault();
			//si mon tableau a l'index en cours est vide, je mets les coo
			if(typeof this.position[this.bgIndex] == 'undefined'){
				//this.position[this.bgIndex] = "(" + this.activeObject.scaleX + "," + this.activeObject.scaleY + "," + this.activeObject.left + "," + this.activeObject.top + "," + this.activeObject.angle + ")";
				this.position[this.bgIndex] = this.recordPosition();
			}
			if(this.bgIndex + 1 < this.backgrounds.length) {
				this.bgIndex += 1;
			} else {
				this.bgIndex = 0;
			}
			this.canvas.setBackgroundImage(this.backgrounds[this.bgIndex], this.canvas.renderAll.bind(this.canvas));
			this.setPosition();

		} else if(e.keyCode === 81) {
			e.preventDefault();
			if(this.bgIndex - 1 >= 0) {
				this.bgIndex -= 1;
			} else {
				this.bgIndex = this.backgrounds.length - 1;
			}
			this.canvas.setBackgroundImage(this.backgrounds[this.bgIndex], this.canvas.renderAll.bind(this.canvas));
			this.setPosition();
		}

	    this.activeObject.setCoords();
	    this.canvas.renderAll();
	}.bind(this));

}

FabricHelper.prototype.recordPosition = function() {
	return [this.activeObject.scaleX, this.activeObject.scaleY, this.activeObject.left, this.activeObject.top, this.activeObject.angle, this.activeObject.oCoords.tl.x, this.activeObject.oCoords.tl.y];
}

FabricHelper.prototype.setPosition = function() {

	if (typeof this.position[this.bgIndex] !== 'undefined' && this.position[this.bgIndex] != '') {
		this.activeObject.angle = this.position[this.bgIndex][4];
		this.activeObject.left = this.position[this.bgIndex][2];
		this.activeObject.top = this.position[this.bgIndex][3];
		this.activeObject.scaleX = this.position[this.bgIndex][0];
		this.activeObject.scaleY = this.position[this.bgIndex][1];
		this.activeObject.oCoords.tl.x = this.position[this.bgIndex][5];
		this.activeObject.oCoords.tl.y = this.position[this.bgIndex][6];

		this.activeObject.setCoords();
	    this.canvas.renderAll();
	}
}
