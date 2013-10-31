function Selection ( parent, area, callback )
{
    'use strict';
    
    this.parent = parent;
    this.area = area;
    this.callback = callback;
    
    this.rx = $('<div />')
        .addClass('ruler-x')
        .width($(window).width())
        .height(1);
    this.rxInfo = $('<div />')
        .addClass('ruler-info');
        
    this.ry = $('<div />')
        .addClass('ruler-y')
        .width(1)
        .height($(window).height());
    this.ryInfo = $('<div />')
        .addClass('ruler-info');
        
    this.ele = $('<div />')
        .addClass('ruler-area')
        .hide();
    
    if(this.parent.css('position') === 'static') {
        this.parent.css('position', 'relative');
    }
        
    this.overlay = $('<div />')
        .addClass('ruler-overlay')
        .css({
            position: 'absolute',
            top: (this.area.offset().top - this.parent.offset().top) + 'px',
            left: (this.area.offset().left - this.parent.offset().left) + 'px',
            width: this.area.width() + 'px',
            height: this.area.height() + 'px',
            overflow: 'hidden',
            cursor: 'crosshair'
        });
    
    this.parent
        .append(this.overlay);
    
    this.overlay
        .append(this.rx)
        .append(this.ry)
        .append(this.ryInfo)
        .append(this.rxInfo)
        .append(this.ele)
    
    this.bindEvents();
}

Selection.prototype.bindEvents = function ()
{
    'use strict';
    
    var self = this,
		isUsed = true,
        px, py, rulerVisible = false;

    this.overlay.unbind('mousedown');
    this.overlay.unbind('mousemove');
    this.overlay.unbind('mouseup');
    
    function bindMouseDown (e)
    {
        e.preventDefault();

    	isUsed = false;

        self.overlay.bind('mouseup', bindMouseUp);
        
        px = e.clientX - (self.overlay.offset().left);
        py = e.clientY - (self.overlay.offset().top);
        
        self.ele.show()
            .css({
                left: px + 'px',
                top: py + 'px',
                width: '0px',
                height: '0px'
            });
        
        rulerVisible = true;
        
        return false;
    }
    
    function bindMouseUp (e)
    {
        e.preventDefault();
        self.overlay.unbind('mouseup', bindMouseUp);

    	isUsed = true;
        
        self.complete();
        
        return false;
    }
    
    this.overlay.bind('mousemove', function (e)
    {
        e.preventDefault();
        
	        px = e.clientX - (self.overlay.offset().left);
	        py = e.clientY - (self.overlay.offset().top);
	        
	        //console.log(px)
	        
        self.ryInfo
            .css({
                left: (px - 30) + 'px',
                top: (py + 15) + 'px'
            })
            .text(py + 'px');
            
        self.rxInfo
            .css({
                left: (px + 15) + 'px',
                top: (py - 15) + 'px'
            })
            .text(px + 'px');
        
        self.rx.css({
            top: py + 'px',
            left: $('#iframe-wrapper').scrollLeft() + 'px'
        });
        
        self.ry.css({
            top: $('#iframe-wrapper').scrollTop() + 'px',
            left: px + 'px'
        });

        if(!isUsed) {
	        if(rulerVisible) {
	            self.ele.css({
	                width: (px - parseInt(self.ele.css('left').replace(/px/g, ''), 10)) + 'px',
	                height: (py - parseInt(self.ele.css('top').replace(/px/g, ''), 10)) + 'px'
	            });
	        }
        }
        
        return false;
    });
    
	self.overlay.bind('mousedown', bindMouseDown);
	self.overlay.bind('mouseup', bindMouseUp);
};

Selection.prototype.complete = function ()
{
    'use strict';
    
    /*    
    this.rx.remove();
    this.ry.remove();
    this.ryInfo.remove();
    this.rxInfo.remove();
    */
    
    if(this.ele.width() < 10 || this.ele.height() < 10) {
        this.destroy();
    }
    
    this.ele
        .addClass('complete')
        .attr('data-width', this.ele.width())
        .attr('data-height', this.ele.height())
        .attr('data-top', parseInt(this.ele.css('top').replace(/px/g, ''), 10))
        .attr('data-left', parseInt(this.ele.css('left').replace(/px/g, ''), 10));
        
    if(typeof this.callback !== 'undefined') {
        this.callback({
            width: this.ele.width(),
            height: this.ele.height(),
            y: parseInt(this.ele.css('top').replace(/px/g, ''), 10),
            x: parseInt(this.ele.css('left').replace(/px/g, ''), 10)
        });
    }
};

Selection.prototype.destroy = function ()
{
    'use strict';
    
    this.overlay.unbind('mousedown');
    this.overlay.unbind('mousemove');
    this.overlay.unbind('mouseup');
    this.ele.remove();
};