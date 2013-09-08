var AreaPicker = {
    exdm: null,
    step: 0,
    selected: null,
    result: {
        area: {}
    },
    
    init: function ()
    {
        'use strict';
        
        AreaPicker.bindEvents();
    },
    
    bindEvents: function ()
    {
        'use strict';
        
        $('#next-step').bind('click', function (e)
        {
            e.preventDefault();
            
            var curUrl = $('#iframe-url').val().replace(/http:\/\//, '');
            if(curUrl.substring(curUrl.length - 1, curUrl.length) == '/') {
            	curUrl = curUrl.substring(0, curUrl.length - 1);
            }
            AreaPicker.result.url = '/' + curUrl.replace(/\//g, '\\/') + '/';
            
            switch(AreaPicker.step) {
                case 0 :
                    AreaPicker.nextStep();
                    parent.document.getElementById("url").value = document.getElementById("iframe-xdm").value;
                    parent.document.getElementById("domain").value = document.getElementById("iframe-url").value;
                    $('#popin-wrapper').hide();
                    var local = {
                            // fallback swf
                            swf: '../easyxdm/easyxdm.swf',
                            //remote file
                            remote: $('#iframe-xdm').val(),
                            //
                            container: document.getElementById("iframe-wrapper"),
                            width: '100%',
                            //container
                            onReady: AreaPicker.easyXDMReady
                    },
                    remote = {
                            // existing remote service that can be called
                            remote: {
                                embed: {},
                                bindEvents: {},
                                getUrl: {},
                                addStyle: {}
                            },
                            // local service that can be called
                            local: {
                                events: AreaPicker.getEvents
                            }
                        };
                    
                    AreaPicker.exdm = new easyXDM.Rpc(local, remote);
                    break;
                case 1 :
                    if(AreaPicker.selected !== null) {
                        AreaPicker.nextStep();
                        AreaPicker.showElement();
                    }
                    break;
                case 2 :
                    AreaPicker.nextStep();
                    AreaPicker.showResult();
                    break;
                default :
                    break;
            }
            
            return false;
        });
        
        $('#prev-step').bind('click', function (e)
        {
            e.preventDefault();
            
            switch(AreaPicker.step) {
	            case 2 :	            	
	                AreaPicker.prevStep();
                    $('#popin-wrapper').hide();
                    var local = {
                            // fallback swf
                            swf: '../easyxdm/easyxdm.swf',
                            //remote file
                            remote: $('#iframe-xdm').val(),
                            //
                            container: document.getElementById("iframe-wrapper"),
                            width: '100%',
                            //container
                            onReady: AreaPicker.easyXDMReady
                    },
                    remote = {
                            // existing remote service that can be called
                            remote: {
                                embed: {},
                                bindEvents: {},
                                getUrl: {},
                                addStyle: {}
                            },
                            // local service that can be called
                            local: {
                                events: AreaPicker.getEvents
                            }
                        };
                    
                    AreaPicker.exdm = new easyXDM.Rpc(local, remote);
                    break;
                case 3 :
                    AreaPicker.prevStep();
                    $('#tip1').hide();
                    $('#tip3').hide();
                    $('#tip2').hide();
                    
                    $('iframe', '#iframe-wrapper').show();
                    $('#popin-wrapper').hide();
                    break;
                default :
                    break;
            }
            
            return false;
        });
    },
    
    showResult: function ()
    {
        parent.document.getElementById("area").value = JSON.stringify(AreaPicker.result);
        
        AreaPicker.exdm.getUrl(
            {},
            function (rpcdata)
            {
                var resultHtml = JSON.stringify(AreaPicker.result);
                resultHtml = resultHtml.replace(/,"/g, ',<br />"');
                resultHtml = resultHtml.replace(/}/g, '<br />}');
                resultHtml = resultHtml.replace(/{/g, '<br />{<br />');

                AreaPicker.result.url = rpcdata;
                
            	$('#prev-step').hide();
            	$('#next-step').hide();
                $('#tip1').hide();
                $('#tip2').hide();
                $('#tip3').show();
                
                //$('#tip3').html(resultHtml);
                $('#tip3').html('<h1>Thanks</h1>');
            	if(typeof AreaConfig.urlToSendResult === "string") {
            		AreaPicker.sendResult();
            	}
            }
        );
    },
    
    ajaxRequest: function ()
    {
        'use strict';
        
		var activexmodes=["Msxml2.XMLHTTP", "Microsoft.XMLHTTP"] //activeX versions to check for in IE
		
		if (window.ActiveXObject){ //Test for support for ActiveXObject in IE first (as XMLHttpRequest in IE7 is broken)
			for (var i=0; i<activexmodes.length; i++) {
				try {
					return new ActiveXObject(activexmodes[i])
				} catch (e) {
					//suppress error
				}
			}
		}else if (window.XMLHttpRequest) { // if Mozilla, Safari etc
			return new XMLHttpRequest()
		}else {
			return false
		}
	},
	
	sendResult: function ()
	{
        'use strict';
        
		var mypostrequest = new AreaPicker.ajaxRequest();
		
		mypostrequest.onreadystatechange = function ()
		{
			if (mypostrequest.readyState === 4) {
				if (mypostrequest.status === 200 || window.location.href.indexOf("http") === -1) {
					document.getElementById("result").innerHTML = mypostrequest.responseText;
				}else {
					console.log("An error has occured making the request");
				}
			}
		}
		mypostrequest.open("POST", AreaConfig.urlToSendResult, true);
		mypostrequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		mypostrequest.send(JSON.stringify(AreaPicker.result));
	},
    
    showElement: function ()
    {
        'use strict';
        
        var newEle = '';
        
        $('#tip1').hide();
        $('#tip2').show();
        $('#tip3').hide();

        $('.result-content').html('');
        
        $('iframe', '#iframe-wrapper').hide();
        $('#popin-wrapper').show();
        
        switch(AreaPicker.selected.type) {
            case 'IMG':
                newEle = '<img src="' + AreaPicker.selected.content.src
                    + '" width="' + AreaPicker.selected.content.width
                        + '" height="' + AreaPicker.selected.content.height + '" />';
                
                $('.dyna-content', '#tip2').html(newEle);
                AreaPicker.bindImgSelect();
                break;
            default:
                newEle = '<' + AreaPicker.selected.type.toLowerCase() + '>';
                newEle += AreaPicker.selected.content.src;
                newEle += '</' + AreaPicker.selected.type.toLowerCase() + '>';
                
                $('.dyna-content', '#tip2').html(newEle);
                AreaPicker.bindTextSelect();
                break;
        }
    },
    
    bindImgSelect: function ()
    {
        'use strict';
        
        AreaPicker.result.area.text = null;
        
        new Selection(
            $('.dyna-content', '#tip2'),
            $('.dyna-content img', '#tip2'),
            function (area)
            {
                var img = $('<img />'),
                    wrapper = $('<div />');
                    
                img.attr('src', $('.dyna-content img', '#tip2').attr('src'));
                
                img.css({
                        position: 'absolute',
                        top: '-' + area.y + 'px',
                        left: '-' + area.x + 'px'
                    });
                    
                wrapper.css({
                        position: 'relative',
                        width: area.width,
                        height: area.height,
                        overflow: 'hidden'
                    })
                    .addClass('centered')
                    .append(img);

                AreaPicker.result.area.width = area.width;
                AreaPicker.result.area.height = area.height;
                AreaPicker.result.area.x = area.x;
                AreaPicker.result.area.y = area.y;
                $('.result-content', '#tip2').html(wrapper);
            }
        );
    },
    
    bindTextSelect: function ()
    {
        'use strict';

        AreaPicker.result.area.x = null;
        AreaPicker.result.area.y = null;
        AreaPicker.result.area.width = null;
        AreaPicker.result.area.height = null;
        
        $('.dyna-content', '#tip2').bind('mouseup', function (e)
        {
            $('.result-content', '#tip2').html(window.getSelection().toString());
            AreaPicker.result.area.text = window.getSelection().toString();
        });
    },
    
    addStyle: function ()
    {
        'use strict';
        
        AreaPicker.exdm.addStyle(
            {
                css: '.playground-selected { border: 1px solid red; }'
                        + '* { cursor: crosshair !important; }'
            },
            function (rpcdata)
            {
                
            }
        );
        
        AreaPicker.exdm.bindEvents(
            {
                "events": 
                {
                    document: {
                        'evt': 'click',
                        'callback': 'clicked'
                    }
                }
            },
            
            function (rpcdata)
            {
            }
        );
    },
    
    easyXDMReady: function (e)
    {
        'use strict';

        $('iframe', '#iframe-wrapper')
            .height('100%')
            .width('100%')
            .css({
                padding: '0px',
                margin: '0px'
            });
        
        AreaPicker.exdm.embed(
            {
                "url": $('#iframe-url').val()
            },
            function (rpcdata)
            {
                AreaPicker.addStyle();
            }
        );
    },
    
    nextStep: function ()
    {
        'use strict';
        
        AreaPicker.step++;
    },
    
    prevStep: function ()
    {
        'use strict';
        
        AreaPicker.step = ((AreaPicker.step-1) > 0) ? (AreaPicker.step-1) : AreaPicker.step;
    },
    
    getEvents: function (e)
    {
        'use strict';
        
        AreaPicker.selected = JSON.parse(e);
        AreaPicker.result.area.y = AreaPicker.selected.clientY;
        AreaPicker.result.area.x = AreaPicker.selected.clientX;
        AreaPicker.result.area.xpath = AreaPicker.selected.xpath;
        return;
    }
};

$(document).ready(AreaPicker.init);