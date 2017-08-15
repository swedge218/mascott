/*
jQWidgets v4.5.4 (2017-June)
Copyright (c) 2011-2017 jQWidgets.
License: http://jqwidgets.com/license/
*/
!function(){window.addResizeHandler=function(a,b){var c=document.createElement("div");c.className="jqx-resize-trigger-container",c.innerHTML='<div class="jqx-resize-trigger-container"><div class="jqx-resize-trigger"></div></div><div class="jqx-resize-trigger-container"><div class="jqx-resize-trigger-shrink"></div></div>';var d=a.widget.data().jqxWidget;(d.autoheight||null===d.height||"auto"===d.height)&&(c.style.height="0.1px",c.style.top="-1px"),a.appendChild(c),a.resizeTrigger=c;var e,f,g,h,i=c.childNodes[0],j=i.childNodes[0],k=c.childNodes[1],l=function(){j.style.width="100000px",j.style.height="100000px",i.scrollLeft=1e5,i.scrollTop=1e5,k.scrollLeft=1e5,k.scrollTop=1e5},m=a.offsetWidth,n=a.offsetHeight;l(),a.resizeHandler=function(){g=a.offsetWidth,h=a.offsetHeight,e=g!==m||h!==n,e&&!f&&(f=requestAnimationFrame(function(){f=0,e&&(m=g,n=h,b())})),l()},i.addEventListener("scroll",a.resizeHandler),k.addEventListener("scroll",a.resizeHandler)},window.removeResizeHandler=function(a){var b=a.resizeTrigger,c=b.childNodes[0],d=b.childNodes[1];c.removeEventListener("scroll",a.resizeHandler),d.removeEventListener("scroll",a.resizeHandler),b.parentNode.removeChild(b),a.resizeHandler=null,a.resizeTrigger=null}}(),function(a){"use strict";a.jqx.elements||(a.jqx.elements=new Array),window.JQXElements={settings:{}},a.jqx.elements.push({name:"jqxCalendar",template:"<div></div>",attributeSync:!0,properties:{disabled:{attributeSync:!1},width:{type:"length"},height:{type:"length"},min:{type:"date"},max:{type:"date"},value:{type:"date"}}}),a.jqx.elements.push({name:"jqxButton",template:"<div></div>"}),a.jqx.elements.push({name:"jqxBulletChart",template:"<div></div>"}),a.jqx.elements.push({name:"jqxRadioButton",template:"<div></div>"}),a.jqx.elements.push({name:"jqxCheckBox",template:"<div></div>"}),a.jqx.elements.push({name:"jqxRepeatButton",template:"<button></button>"}),a.jqx.elements.push({name:"jqxSwitchButton",template:"<div></div>"}),a.jqx.elements.push({name:"jqxLinkButton",template:"<a></a>"}),a.jqx.elements.push({name:"jqxToggleButton",template:"<button></button>"}),a.jqx.elements.push({name:"jqxBarGauge",template:"<div></div>"}),a.jqx.elements.push({name:"jqxChart",template:"<div></div>",properties:{source:{attributeSync:!1}}}),a.jqx.elements.push({name:"jqxColorPicker",template:"<div></div>"}),a.jqx.elements.push({name:"jqxComboBox",template:"<div></div>",properties:{source:{attributeSync:!1}}}),a.jqx.elements.push({name:"jqxComplexInput",template:"<div><input/><div></div></div>"}),a.jqx.elements.push({name:"jqxDataTable",template:"<div></div>",properties:{source:{attributeSync:!1}}}),a.jqx.elements.push({name:"jqxDateTimeInput",template:"<div></div>"}),a.jqx.elements.push({name:"jqxDocking",template:"<div></div>"}),a.jqx.elements.push({name:"jqxDockPanel",template:"<div></div>"}),a.jqx.elements.push({name:"jqxDragDrop",template:"<div></div>"}),a.jqx.elements.push({name:"jqxDropDownList",template:"<div></div>",properties:{source:{attributeSync:!1}}}),a.jqx.elements.push({name:"jqxEditor",template:"<div></div>"}),a.jqx.elements.push({name:"jqxExpander",template:"<div></div>"}),a.jqx.elements.push({name:"jqxFileUpload",template:"<div></div>"}),a.jqx.elements.push({name:"jqxFormattedInput",template:"<div><input/><div></div></div>"}),a.jqx.elements.push({name:"jqxGauge",template:"<div></div>",propertyMap:{style:"backgroundStyle"}}),a.jqx.elements.push({name:"jqxLinearGauge",template:"<div></div>",propertyMap:{style:"backgroundStyle"}}),a.jqx.elements.push({name:"jqxGrid",template:"<div></div>",properties:{source:{attributeSync:!1}}}),a.jqx.elements.push({name:"jqxInput",template:"<input/>",properties:{source:{attributeSync:!1}}}),a.jqx.elements.push({name:"jqxKanban",template:"<div></div>",properties:{source:{attributeSync:!1}}}),a.jqx.elements.push({name:"jqxKnob",template:"<div></div>"}),a.jqx.elements.push({name:"jqxLayout",template:"<div></div>"}),a.jqx.elements.push({name:"jqxDockingLayout",template:"<div></div>"}),a.jqx.elements.push({name:"jqxListBox",template:"<div></div>",properties:{source:{attributeSync:!1}}}),a.jqx.elements.push({name:"jqxListMenu",template:"<div></div>",properties:{source:{attributeSync:!1}}}),a.jqx.elements.push({name:"jqxLoader",template:"<div></div>"}),a.jqx.elements.push({name:"jqxMaskedInput",template:"<input/>"}),a.jqx.elements.push({name:"jqxMenu",template:"<div></div>",properties:{source:{attributeSync:!1}}}),a.jqx.elements.push({name:"jqxNavBar",template:"<div></div>"}),a.jqx.elements.push({name:"jqxNavigationBar",template:"<div></div>"}),a.jqx.elements.push({name:"jqxNotification",template:"<div></div>",properties:{appendContainer:{type:"string"}}}),a.jqx.elements.push({name:"jqxNumberInput",template:"<div></div>"}),a.jqx.elements.push({name:"jqxPanel",template:"<div></div>"}),a.jqx.elements.push({name:"jqxPasswordInput",template:"<input type='password'/>"}),a.jqx.elements.push({name:"jqxPopover",template:"<div></div>",properties:{title:{type:"string"},arrowOffsetValue:{type:"number"},offset:{type:"json"},selector:{type:"string"},initContent:{type:"object"}}}),a.jqx.elements.push({name:"jqxProgressBar",template:"<div></div>"}),a.jqx.elements.push({name:"jqxRangeSelector",template:"<div></div>"}),a.jqx.elements.push({name:"jqxRating",tagName:"jqx-rating",template:"<div></div>"}),a.jqx.elements.push({name:"jqxResponsivePanel",template:"<div></div>"}),a.jqx.elements.push({name:"jqxRibbon",template:"<div></div>"}),a.jqx.elements.push({name:"jqxScheduler",template:"<div></div>",properties:{source:{attributeSync:!1}}}),a.jqx.elements.push({name:"jqxScrollBar",template:"<div></div>"}),a.jqx.elements.push({name:"jqxScrollView",template:"<div></div>"}),a.jqx.elements.push({name:"jqxSortable",template:"<div></div>",propertyMap:{appendTo:"addTo"}}),a.jqx.elements.push({name:"jqxSplitter",template:"<div></div>",properties:{panels:{type:"array"}}}),a.jqx.elements.push({name:"jqxTabs",template:"<div></div>"}),a.jqx.elements.push({name:"jqxTagCloud",template:"<div></div>"}),a.jqx.elements.push({name:"jqxTextArea",template:"<div></div>"}),a.jqx.elements.push({name:"jqxToolBar",template:"<div></div>"}),a.jqx.elements.push({name:"jqxTooltip",tagName:"jqx-tool-tip",template:"<div></div>"}),a.jqx.elements.push({name:"jqxTree",template:"<div></div>",properties:{source:{attributeSync:!1}}}),a.jqx.elements.push({name:"jqxTreeGrid",template:"<div></div>",properties:{source:{attributeSync:!1}}}),a.jqx.elements.push({name:"jqxTreeMap",template:"<div></div>",properties:{source:{attributeSync:!1}}}),a.jqx.elements.push({name:"jqxValidator",template:"<div></div>"}),a.jqx.elements.push({name:"jqxWindow",template:"<div></div>"}),document.registerElement&&(Object.is||(Object.is=function(a,b){return a===b?0!==a||1/a==1/b:a!==a&&b!==b}),a(document).ready(function(){a.each(a.jqx.elements,function(){var b=this.name,c=this;c.tagName||(c.tagName=c.name.split(/(?=[A-Z])/).join("-").toLowerCase());var d=Object.create(HTMLElement.prototype);d.name=b,d.instances=new Array;var e={},f=function(){var a={};return{addAttributeConfig:function(b,c,d){void 0===a[b]&&(a[b]={}),a[b][c]=d},getAttributeConfig:function(b,c){return void 0===a[b]||void 0===a[b][c]?void 0:a[b][c]},getAttributeList:function(b){return a[b]}}}();if(!a.jqx["_"+b])return!0;var g=a.jqx["_"+b].prototype.defineInstance();return"jqxDockingLayout"==b&&(g=a.extend(g,a.jqx._jqxLayout.prototype.defineInstance())),"jqxToggleButton"!=b&&"jqxRepeatButton"!=b&&"jqxLinkButton"!=b||(g=a.extend(g,a.jqx._jqxButton.prototype.defineInstance())),"jqxTreeGrid"==b&&(g=a.extend(g,a.jqx._jqxDataTable.prototype.defineInstance())),d.initElement=function(){var c=this;if(!g)return void console.log(b+" is undefined");a.each(g,function(a,b){c["_"+a]=b})},g?(a.each(g,function(a,g){if(c.properties||(c.properties=[]),a.indexOf("_")>=0)return!0;var h=c.properties[a],i=a.split(/(?=[A-Z])/).join("-").toLowerCase(),j=typeof g,k=h&&h.attributeSync||c.attributeSync||!0,l="_"+a;"width"!==a&&"height"!==a||(j="length"),h&&h.type&&(j=h.type);var m={defaultValue:g,type:j,propertyName:a,attributeSync:k};f.addAttributeConfig(c.tagName,i,Object.freeze(m)),e[a]=i;var n=function(d){var g=this;if(this[l]=d,this.widget){c.propertyMap&&c.propertyMap[a]&&(a=c.propertyMap[a]);var h={};h[a]=d,this.widget[b](h);var i=e[a],j=f.getAttributeConfig(c.tagName,i);j.attributeSync&&(g.isUpdatingAttribute=!0,g.setAttributeTyped(i,j,d),g.isUpdatingAttribute=!1),g.propertyUpdated(a,d)}else this.initialSettings[a]=d};c.propertyMap&&c.propertyMap[a]&&(a=c.propertyMap[a]),Object.defineProperty(d,a,{configurable:!1,enumerable:!0,get:function(){return this[l]},set:function(a){n.call(this,a)}})}),d.getAttributeTyped=function(a,b){return this.attributeStringToTypedValue(a,b,this.getAttribute(a))},d.setAttributeTyped=function(a,b,c){var d;this.getAttributeTyped(a,b),d=this.typedValueToAttributeString(c),void 0===d?this.removeAttribute(a):this.setAttribute(a,d)},d.typedValueToAttributeString=function(a){var b=typeof a;return"boolean"===b?a?"":void 0:"number"===b?Object.is(a,-0)?"-0":a.toString():"string"===b||"length"===b?a:"object"===b?JSON.stringify(a,function(a,b){if("number"==typeof b){if(!1===isFinite(b))return b.toString();if(Object.is(b,-0))return"-0"}return b}):void 0},d.attributeStringToTypedValue=function(a,b,c){return"boolean"===b.type?""===c||c===a||"true"===c:"number"===b.type?null===c||void 0===c?void 0:parseFloat(c):"string"===b.type?null===c||void 0===c?void 0:c:"length"===b.type?null===c?null:null!==c&&c.indexOf("px")>=0?parseFloat(c):null!==c&&c.indexOf("%")>=0?c:isNaN(parseFloat(c))?c:parseFloat(c):"json"===b.type||"array"===b.type?JSON.parse(c.replace(/'/g,'"')):"object"===b.type?window.JQXElements.settings[c]||window[c]:void 0},d.createdCallback=function(){var a=this;a.isReady=!1,a.initialSettings={},a.initElement()},d.setup=function(){var d=this;if(!d.isReady){d.isReady=!0;var e,g,h,d=this,i=null,j=null,k=[],l=!0,m=f.getAttributeList(c.tagName),n=d.settings||{},o=d.initialSettings,p=c.template;for(var q in m)if(m.hasOwnProperty(q)&&d.hasAttribute(q)){var r,s=m[q],t=d.getAttributeTyped(q,s);r=void 0===t?s.defaultValue:t,n[s.propertyName]=r}h=d.attributes;for(var q in h){var u=h[q];if(u&&u.name&&u.name.indexOf("on-")>=0){var v=u.value,w="";v.indexOf("(")>=0&&(w=v.substring(0,v.indexOf("("))),k.push({name:u.name.substring(3),handler:w})}}var x=function(a){var b=document.createDocumentFragment(),c=document.createElement("div");b.appendChild(c);var d=/<(?!area|br|col|embed|hr|img|input|link|meta|param)(([\w:]+)[^>]*)\/>/gi,e=/<([\w:]+)/;a=a.replace(d,"<$1></$2>");var f=((e.exec(a)||["",""])[1].toLowerCase(),[0,"",""]),g=f[0];for(c.innerHTML=f[1]+a+f[2];g--;)c=c.lastChild;return a=c.childNodes,c.parentNode.removeChild(c),x=a[0]}(p);e=x;if(d.hasAttribute("settings")){var y=d.getAttribute("settings");o=window.JQXElements.settings[y]||window[y],a.each(o,function(a,b){d["_"+a]=b})}!function(c){var f=!1;if("jqxDragDrop"!==b&&"jqxPopover"!==b&&"jqxWindow"!==b&&"jqxSortable"!==b&&"jqxDraw"!==b&&"jqxValidator"!==b||(f=!0,d.style.overflow="visible"),f)e=d;else{for(;d.childNodes.length;)e.appendChild(d.firstChild);d.appendChild(e)}"jqxScrollBar"!==b&&"jqxNotification"!==b||(d.style.overflow="visible",d.style.borderLeftWidth="0px",d.style.borderRightWidth="0px",d.style.borderTopWidth="0px",d.style.borderBottomWidth="0px"),a.extend(n,c);var h=b.toLowerCase();p.indexOf("button")>=0||1==p.indexOf("input")||p.indexOf("textarea")>=0||h.indexOf("button")>=0||h.indexOf("checkbox")>=0||h.indexOf("radio")>=0?d.style.display="inline-block":d.style.display="block";var m=function(a,b){l&&!f&&("string"==typeof b&&b.indexOf("%")>=0?d.style[a]=b:"string"==typeof b&&b.indexOf("px")>=0?d.style[a]=b:"auto"===b?d.style[a]=b:b?d.style[a]=b+"px":d.style[a]&&(d.style[a]=null))};n.width&&m("width",n.width),n.height&&m("height",n.height),g=new jqxBaseFramework(d),g.data(d,"jqxWidget",{element:d}),i=g.width(),j=g.height(),"jqxChart"===b||"jqxMenu"===b||"jqxToolBar"===b?e.style.width=e.style.height="100%":f||(i&&!n.width&&"auto"!==d.style.width&&(n.width=i-2),j&&!n.height&&"auto"!==d.style.height&&j!==d.firstChild.offsetHeight&&(n.height=j-2));var o=a.jqx["_"+b].prototype,q=Object.getOwnPropertyNames(o);for(var r in q){var s=q[r];if(!(s.indexOf("_")>=0)&&("base"!==s&&"baseType"!==s&&"resize"!==s&&"scrollWidth"!==s&&"scrollHeight"!==s&&"constructor"!==s&&"createInstance"!==s&&"defineInstance"!==s&&"function"==typeof o[s])){d[s]=function(a,b){var c=Array.prototype.slice.call(arguments,2),e=d;return function(){if(!e._isUpdating){if(e._isUpdating=!0,!e.widget){var b=arguments;return void(e.timer=setInterval(function(){e.widget&&(clearInterval(e.timer),a.apply(e.widget.data().jqxWidget,c.concat(Array.prototype.slice.call(b))),e._isUpdating=!1)},50))}var d=a.apply(e.widget.data().jqxWidget,c.concat(Array.prototype.slice.call(arguments)));return e._isUpdating=!1,d}}}(o[s],s)}}var t=d.widget=a(e)[b](n);if(d.propertyUpdated=function(a,b){"width"!==a&&"height"!==a||m(a,b)},!f){var u=t.data().jqxWidget;u.base?u.base.host.addClass("jqx-element-container"):u.host.addClass("jqx-element-container"),g.addClass("jqx-widget jqx-element"),"jqxCheckBox"!==b&&"jqxBulletChart"!==b&&"jqxRangeSelector"!==b&&"jqxPopover"!=b&&"jqxRadioButton"!==b&&"jqxChart"!==b&&"jqxTooltip"!==b&&"jqxGauge"!==b&&"jqxLinearGauge"!=b&&"jqxExpander"!=b&&"jqxNavigationBar"!=b||g.addClass("jqx-element-no-border"),"jqxRangeSelector"===b&&g.css("overflow","visible")}for(var v=0;v<k.length;v++){var w=k[v];t.on(w.name,function(a){window.JQXElements.settings[w.handler]&&a.args?window.JQXElements.settings[w.handler].apply(d,[a]):window[w.handler]&&a.args&&window[w.handler].apply(d,[a])})}var x=function(){f||(l=!1,i=g.width(),j=g.height(),"jqxChart"===b||"jqxDraw"===b?(t.element.style.width="100%",t.element.style.height="100%"):(t[b]({width:i}),t[b]({height:j})),l=!0)};f||addResizeHandler(d,function(){x()})}(o)}},d.attachedCallback=function(){this.setup()},d.attributeChangedCallback=function(a,b,d){var e=this,g=f.getAttributeConfig(c.tagName,a);if(!e.isUpdatingAttribute&&g){var h,i=e.getAttributeTyped(a,g);h=void 0===i?currAttrConfig.defaultValue:i,e[currAttrConfig.propertyName]=h}},document.registerElement(c.tagName,{prototype:d})):void console.log(b+" is undefined")})}))}(jqxBaseFramework);

