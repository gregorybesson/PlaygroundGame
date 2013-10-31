function getPathTo (element)
{
    'use strict';
    
    if (typeof element.id !== 'undefined' && element.id !== '') {
        return 'id("' + element.id + '")';
    }else if (element === document.body)  {
        return element.tagName;
    }

    var ix = 0,
        siblings = element.parentNode.childNodes,
        i, sibling;
    for (i= 0; i < siblings.length; i++) {
        sibling = siblings[i];
        if (sibling === element) {
            return getPathTo(element.parentNode) + ' ' + element.tagName + '[' + (ix + 1) + ']';
        }else if (sibling.nodeType === 1
            && sibling.tagName === element.tagName) {
            ix++;
        }
    }
}

function createXPathFromElement(elm) {
    var allNodes = document.getElementsByTagName('*'); 
    for (segs = []; elm && elm.nodeType == 1; elm = elm.parentNode) 
    {
        if (elm.hasAttribute('id')) { 
                var uniqueIdCount = 0,
                    n; 
                for (n = 0; n < allNodes.length; n++) { 
                    if (allNodes[n].hasAttribute('id') && allNodes[n].id == elm.id) uniqueIdCount++; 
                    if (uniqueIdCount > 1) break; 
                }; 
                if ( uniqueIdCount == 1) { 
                    segs.unshift('id("' + elm.getAttribute('id') + '")'); 
                    return segs.join('/'); 
                } else { 
                    segs.unshift(elm.localName.toLowerCase() + '[@id="' + elm.getAttribute('id') + '"]'); 
                } 
        } else if (elm.hasAttribute('class')) { 
            segs.unshift(elm.localName.toLowerCase() + '[@class="' + elm.getAttribute('class') + '"]'); 
        } else { 
            for (i = 1, sib = elm.previousSibling; sib; sib = sib.previousSibling) { 
                if (sib.localName == elm.localName)  i++; }; 
                segs.unshift(elm.localName.toLowerCase() + '[' + i + ']'); 
        };
    };
    return segs.length ? '/' + segs.join('/') : null; 
}

function getElementTreeXPath (element)
{
    'use strict';
    
    var paths = [],
        sibling, index, tagName, pathIndex;

    // Use nodeName (instead of localName) so namespace prefix is included (if any).
    for (null; element && element.nodeType === 1; element = element.parentNode) {
        index = 0;
        // EXTRA TEST FOR ELEMENT.ID
        if (element && element.id) {
            paths.splice(0, 0, '/*[@id="' + element.id + '"]');
            break;
        }

        for (sibling = element.previousSibling; sibling; sibling = sibling.previousSibling) {
            // Ignore document type declaration.
            if (sibling.nodeType === Node.DOCUMENT_TYPE_NODE){
                continue;
            }

            if (sibling.nodeName === element.nodeName){
                ++index;
            }
        }

        tagName = element.nodeName.toLowerCase();
        pathIndex = "[" + (index + 1) + "]";
        paths.splice(0, 0, tagName + pathIndex);
    }

    return paths.length ? "/" + paths.join("/") : null;
}

function getElementXPath (element)
{
    'use strict';
    
    var r;
    if (element && element.id) {
        r = '//*[@id="' + element.id + '"]';
    }else {
        r = '/' + getElementTreeXPath(element);
    }
    
    return r;
}

function getElementContent (element)
{
    'use strict';
    
    var r = {
        src: (typeof element.target.src !== 'undefined') ? element.target.src : element.target.innerHTML,
        width: element.target.style.width,
        height: element.target.style.height
    };
    
    return r;
}

function bindEvent (ele, evt, callback)
{
    'use strict';
    
    ele.addEventListener(evt, function (e)
    {
        e.preventDefault();
        e.stopPropagation();
        
        var obj = {
                'callback': callback,
                'clientX': e.clientX,
                'clientY': e.clientY,
                'className': 'playground-selected',
                'type': e.target.nodeName,
                'which': e.which,
                'content': '',
                'xpath': getElementXPath('target' in e? e.target : e.srcElement)
            },
            old = window.frames[0].document.getElementsByClassName('playground-selected'),
            i;
            
        obj.content = getElementContent(e);
        
        if(e.target.className.indexOf('playground-selected') === -1) {
            for (i in old) {
                if(typeof old[i] !== 'undefined'
                    && typeof old[i].className !== 'undefined') {
                    old[i].className = old[i].className.replace(/playground-selected/g, '');
                }
            }
            e.target.className = ((e.target.className !== '') ? e.target.className + ' ' : '') + 'playground-selected';
        }
        remote.events(JSON.stringify(obj));
        
        return false;
    }, true);
};

/*
function bindEvent (ele, evt, callback)
{
    'use strict';
    
    ele.addEventListener(evt, function (e)
    {
        e.preventDefault();
        e.stopPropagation();
        
        var obj = {
                'callback': callback,
                'clientX': e.clientX,
                'clientY': e.clientY,
                'className': 'playground-selected',
                'type': e.target.nodeName,
                'which': e.which,
                'content': '',
                'xpath': getElementXPath('target' in e? e.target : e.srcElement)
            },
            old = window.frames[0].document.getElementsByClassName('playground-selected'),
            i;
            
        obj.content = getElementContent(e);
        
        if(e.target.className.indexOf('playground-selected') === -1) {
            for (i in old) {
                if(typeof old[i] !== 'undefined'
                    && typeof old[i].className !== 'undefined') {
                    old[i].className = old[i].className.replace(/playground-selected/g, '');
                }
            }
            e.target.className = ((e.target.className !== '') ? e.target.className + ' ' : '') + 'playground-selected';
        }
        remote.events(JSON.stringify(obj));
        
        return false;
    }, true);
};
*/

// instantiate a new easyXDM object which will handle the request 
var url,
    remote = new easyXDM.Rpc(
{
},
{
    local: {
        // define the exposed method
        embed: function(config, success, error)
        {
            document.body.style.margin = '0px';
            document.body.style.padding = '0px';
            document.body.style.height = '100%';
            document.body.style.overflow = 'hidden';
            
            var i = document.createElement('iframe');
            i.setAttribute('src', config.url);
            i.id = 'iframe-embed';
            i.style.width = '100%';
            i.style.height = '100%';
            i.style.border = '0px';
            i.style.frameborder = '0px';
            
            document.body.appendChild(i);
            
            i.addEventListener('load', function (e)
            {
                success();
            });
        },
        
        bindEvents: function(config, success, error)
        {
            var i, ele;
            for (i in config.events) {
                if(i === 'document') {
                    ele = window.frames[0].document;
                }else if(i === 'window') {
                    ele = window.frames[0].window;
                }else {
                    ele = window.frames[0].document.querySelector(i);
                }
                bindEvent(ele, config.events[i].evt, config.events[i].callback)
            }
        },
        
        getUrl: function(config, success, error)
        {
            success(document.getElementById('iframe-embed').contentWindow.location.href);
        },
        
        addStyle: function(config, success, error)
        {
            var head = window.frames[0].document.getElementsByTagName('head')[0],
                style = window.frames[0].document.createElement('style');
            
            style.type = 'text/css';
            if (style.styleSheet) {
                style.styleSheet.cssText = config.css;
            }else {
                style.appendChild(window.frames[0].document.createTextNode(config.css));
            }
            
            head.appendChild(style);
        }
    },
    remote: {
        events: {}
    }
});