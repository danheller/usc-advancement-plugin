/*!
 * Signature Pad v4.1.7 | https://github.com/szimek/signature_pad
 * (c) 2023 Szymon Nowak | Released under the MIT license
 */
!function(t,e){"object"==typeof exports&&"undefined"!=typeof module?module.exports=e():"function"==typeof define&&define.amd?define(e):(t="undefined"!=typeof globalThis?globalThis:t||self).SignaturePad=e()}(this,(function(){"use strict";class t{constructor(t,e,i,s){if(isNaN(t)||isNaN(e))throw new Error(`Point is invalid: (${t}, ${e})`);this.x=+t,this.y=+e,this.pressure=i||0,this.time=s||Date.now()}distanceTo(t){return Math.sqrt(Math.pow(this.x-t.x,2)+Math.pow(this.y-t.y,2))}equals(t){return this.x===t.x&&this.y===t.y&&this.pressure===t.pressure&&this.time===t.time}velocityFrom(t){return this.time!==t.time?this.distanceTo(t)/(this.time-t.time):0}}class e{static fromPoints(t,i){const s=this.calculateControlPoints(t[0],t[1],t[2]).c2,n=this.calculateControlPoints(t[1],t[2],t[3]).c1;return new e(t[1],s,n,t[2],i.start,i.end)}static calculateControlPoints(e,i,s){const n=e.x-i.x,o=e.y-i.y,h=i.x-s.x,r=i.y-s.y,a=(e.x+i.x)/2,c=(e.y+i.y)/2,d=(i.x+s.x)/2,l=(i.y+s.y)/2,u=Math.sqrt(n*n+o*o),v=Math.sqrt(h*h+r*r),_=v/(u+v),p=d+(a-d)*_,m=l+(c-l)*_,g=i.x-p,w=i.y-m;return{c1:new t(a+g,c+w),c2:new t(d+g,l+w)}}constructor(t,e,i,s,n,o){this.startPoint=t,this.control2=e,this.control1=i,this.endPoint=s,this.startWidth=n,this.endWidth=o}length(){let t,e,i=0;for(let s=0;s<=10;s+=1){const n=s/10,o=this.point(n,this.startPoint.x,this.control1.x,this.control2.x,this.endPoint.x),h=this.point(n,this.startPoint.y,this.control1.y,this.control2.y,this.endPoint.y);if(s>0){const s=o-t,n=h-e;i+=Math.sqrt(s*s+n*n)}t=o,e=h}return i}point(t,e,i,s,n){return e*(1-t)*(1-t)*(1-t)+3*i*(1-t)*(1-t)*t+3*s*(1-t)*t*t+n*t*t*t}}class i{constructor(){try{this._et=new EventTarget}catch(t){this._et=document}}addEventListener(t,e,i){this._et.addEventListener(t,e,i)}dispatchEvent(t){return this._et.dispatchEvent(t)}removeEventListener(t,e,i){this._et.removeEventListener(t,e,i)}}class s extends i{constructor(t,e={}){super(),this.canvas=t,this._drawingStroke=!1,this._isEmpty=!0,this._lastPoints=[],this._data=[],this._lastVelocity=0,this._lastWidth=0,this._handleMouseDown=t=>{1===t.buttons&&this._strokeBegin(t)},this._handleMouseMove=t=>{this._strokeMoveUpdate(t)},this._handleMouseUp=t=>{1===t.buttons&&this._strokeEnd(t)},this._handleTouchStart=t=>{if(t.cancelable&&t.preventDefault(),1===t.targetTouches.length){const e=t.changedTouches[0];this._strokeBegin(e)}},this._handleTouchMove=t=>{t.cancelable&&t.preventDefault();const e=t.targetTouches[0];this._strokeMoveUpdate(e)},this._handleTouchEnd=t=>{if(t.target===this.canvas){t.cancelable&&t.preventDefault();const e=t.changedTouches[0];this._strokeEnd(e)}},this._handlePointerStart=t=>{t.preventDefault(),this._strokeBegin(t)},this._handlePointerMove=t=>{this._strokeMoveUpdate(t)},this._handlePointerEnd=t=>{this._drawingStroke&&(t.preventDefault(),this._strokeEnd(t))},this.velocityFilterWeight=e.velocityFilterWeight||.7,this.minWidth=e.minWidth||.5,this.maxWidth=e.maxWidth||2.5,this.throttle="throttle"in e?e.throttle:16,this.minDistance="minDistance"in e?e.minDistance:5,this.dotSize=e.dotSize||0,this.penColor=e.penColor||"black",this.backgroundColor=e.backgroundColor||"rgba(0,0,0,0)",this.compositeOperation=e.compositeOperation||"source-over",this._strokeMoveUpdate=this.throttle?function(t,e=250){let i,s,n,o=0,h=null;const r=()=>{o=Date.now(),h=null,i=t.apply(s,n),h||(s=null,n=[])};return function(...a){const c=Date.now(),d=e-(c-o);return s=this,n=a,d<=0||d>e?(h&&(clearTimeout(h),h=null),o=c,i=t.apply(s,n),h||(s=null,n=[])):h||(h=window.setTimeout(r,d)),i}}(s.prototype._strokeUpdate,this.throttle):s.prototype._strokeUpdate,this._ctx=t.getContext("2d"),this.clear(),this.on()}clear(){const{_ctx:t,canvas:e}=this;t.fillStyle=this.backgroundColor,t.clearRect(0,0,e.width,e.height),t.fillRect(0,0,e.width,e.height),this._data=[],this._reset(this._getPointGroupOptions()),this._isEmpty=!0}fromDataURL(t,e={}){return new Promise(((i,s)=>{const n=new Image,o=e.ratio||window.devicePixelRatio||1,h=e.width||this.canvas.width/o,r=e.height||this.canvas.height/o,a=e.xOffset||0,c=e.yOffset||0;this._reset(this._getPointGroupOptions()),n.onload=()=>{this._ctx.drawImage(n,a,c,h,r),i()},n.onerror=t=>{s(t)},n.crossOrigin="anonymous",n.src=t,this._isEmpty=!1}))}toDataURL(t="image/png",e){return"image/svg+xml"===t?("object"!=typeof e&&(e=void 0),`data:image/svg+xml;base64,${btoa(this.toSVG(e))}`):("number"!=typeof e&&(e=void 0),this.canvas.toDataURL(t,e))}on(){this.canvas.style.touchAction="none",this.canvas.style.msTouchAction="none",this.canvas.style.userSelect="none";const t=/Macintosh/.test(navigator.userAgent)&&"ontouchstart"in document;window.PointerEvent&&!t?this._handlePointerEvents():(this._handleMouseEvents(),"ontouchstart"in window&&this._handleTouchEvents())}off(){this.canvas.style.touchAction="auto",this.canvas.style.msTouchAction="auto",this.canvas.style.userSelect="auto",this.canvas.removeEventListener("pointerdown",this._handlePointerStart),this.canvas.removeEventListener("pointermove",this._handlePointerMove),this.canvas.ownerDocument.removeEventListener("pointerup",this._handlePointerEnd),this.canvas.removeEventListener("mousedown",this._handleMouseDown),this.canvas.removeEventListener("mousemove",this._handleMouseMove),this.canvas.ownerDocument.removeEventListener("mouseup",this._handleMouseUp),this.canvas.removeEventListener("touchstart",this._handleTouchStart),this.canvas.removeEventListener("touchmove",this._handleTouchMove),this.canvas.removeEventListener("touchend",this._handleTouchEnd)}isEmpty(){return this._isEmpty}fromData(t,{clear:e=!0}={}){e&&this.clear(),this._fromData(t,this._drawCurve.bind(this),this._drawDot.bind(this)),this._data=this._data.concat(t)}toData(){return this._data}_getPointGroupOptions(t){return{penColor:t&&"penColor"in t?t.penColor:this.penColor,dotSize:t&&"dotSize"in t?t.dotSize:this.dotSize,minWidth:t&&"minWidth"in t?t.minWidth:this.minWidth,maxWidth:t&&"maxWidth"in t?t.maxWidth:this.maxWidth,velocityFilterWeight:t&&"velocityFilterWeight"in t?t.velocityFilterWeight:this.velocityFilterWeight,compositeOperation:t&&"compositeOperation"in t?t.compositeOperation:this.compositeOperation}}_strokeBegin(t){if(!this.dispatchEvent(new CustomEvent("beginStroke",{detail:t,cancelable:!0})))return;this._drawingStroke=!0;const e=this._getPointGroupOptions(),i=Object.assign(Object.assign({},e),{points:[]});this._data.push(i),this._reset(e),this._strokeUpdate(t)}_strokeUpdate(t){if(!this._drawingStroke)return;if(0===this._data.length)return void this._strokeBegin(t);this.dispatchEvent(new CustomEvent("beforeUpdateStroke",{detail:t}));const e=t.clientX,i=t.clientY,s=void 0!==t.pressure?t.pressure:void 0!==t.force?t.force:0,n=this._createPoint(e,i,s),o=this._data[this._data.length-1],h=o.points,r=h.length>0&&h[h.length-1],a=!!r&&n.distanceTo(r)<=this.minDistance,c=this._getPointGroupOptions(o);if(!r||!r||!a){const t=this._addPoint(n,c);r?t&&this._drawCurve(t,c):this._drawDot(n,c),h.push({time:n.time,x:n.x,y:n.y,pressure:n.pressure})}this.dispatchEvent(new CustomEvent("afterUpdateStroke",{detail:t}))}_strokeEnd(t){this._drawingStroke&&(this._strokeUpdate(t),this._drawingStroke=!1,this.dispatchEvent(new CustomEvent("endStroke",{detail:t})))}_handlePointerEvents(){this._drawingStroke=!1,this.canvas.addEventListener("pointerdown",this._handlePointerStart),this.canvas.addEventListener("pointermove",this._handlePointerMove),this.canvas.ownerDocument.addEventListener("pointerup",this._handlePointerEnd)}_handleMouseEvents(){this._drawingStroke=!1,this.canvas.addEventListener("mousedown",this._handleMouseDown),this.canvas.addEventListener("mousemove",this._handleMouseMove),this.canvas.ownerDocument.addEventListener("mouseup",this._handleMouseUp)}_handleTouchEvents(){this.canvas.addEventListener("touchstart",this._handleTouchStart),this.canvas.addEventListener("touchmove",this._handleTouchMove),this.canvas.addEventListener("touchend",this._handleTouchEnd)}_reset(t){this._lastPoints=[],this._lastVelocity=0,this._lastWidth=(t.minWidth+t.maxWidth)/2,this._ctx.fillStyle=t.penColor,this._ctx.globalCompositeOperation=t.compositeOperation}_createPoint(e,i,s){const n=this.canvas.getBoundingClientRect();return new t(e-n.left,i-n.top,s,(new Date).getTime())}_addPoint(t,i){const{_lastPoints:s}=this;if(s.push(t),s.length>2){3===s.length&&s.unshift(s[0]);const t=this._calculateCurveWidths(s[1],s[2],i),n=e.fromPoints(s,t);return s.shift(),n}return null}_calculateCurveWidths(t,e,i){const s=i.velocityFilterWeight*e.velocityFrom(t)+(1-i.velocityFilterWeight)*this._lastVelocity,n=this._strokeWidth(s,i),o={end:n,start:this._lastWidth};return this._lastVelocity=s,this._lastWidth=n,o}_strokeWidth(t,e){return Math.max(e.maxWidth/(t+1),e.minWidth)}_drawCurveSegment(t,e,i){const s=this._ctx;s.moveTo(t,e),s.arc(t,e,i,0,2*Math.PI,!1),this._isEmpty=!1}_drawCurve(t,e){const i=this._ctx,s=t.endWidth-t.startWidth,n=2*Math.ceil(t.length());i.beginPath(),i.fillStyle=e.penColor;for(let i=0;i<n;i+=1){const o=i/n,h=o*o,r=h*o,a=1-o,c=a*a,d=c*a;let l=d*t.startPoint.x;l+=3*c*o*t.control1.x,l+=3*a*h*t.control2.x,l+=r*t.endPoint.x;let u=d*t.startPoint.y;u+=3*c*o*t.control1.y,u+=3*a*h*t.control2.y,u+=r*t.endPoint.y;const v=Math.min(t.startWidth+r*s,e.maxWidth);this._drawCurveSegment(l,u,v)}i.closePath(),i.fill()}_drawDot(t,e){const i=this._ctx,s=e.dotSize>0?e.dotSize:(e.minWidth+e.maxWidth)/2;i.beginPath(),this._drawCurveSegment(t.x,t.y,s),i.closePath(),i.fillStyle=e.penColor,i.fill()}_fromData(e,i,s){for(const n of e){const{points:e}=n,o=this._getPointGroupOptions(n);if(e.length>1)for(let s=0;s<e.length;s+=1){const n=e[s],h=new t(n.x,n.y,n.pressure,n.time);0===s&&this._reset(o);const r=this._addPoint(h,o);r&&i(r,o)}else this._reset(o),s(e[0],o)}}toSVG({includeBackgroundColor:t=!1}={}){const e=this._data,i=Math.max(window.devicePixelRatio||1,1),s=this.canvas.width/i,n=this.canvas.height/i,o=document.createElementNS("http://www.w3.org/2000/svg","svg");if(o.setAttribute("xmlns","http://www.w3.org/2000/svg"),o.setAttribute("xmlns:xlink","http://www.w3.org/1999/xlink"),o.setAttribute("viewBox",`0 0 ${s} ${n}`),o.setAttribute("width",s.toString()),o.setAttribute("height",n.toString()),t&&this.backgroundColor){const t=document.createElement("rect");t.setAttribute("width","100%"),t.setAttribute("height","100%"),t.setAttribute("fill",this.backgroundColor),o.appendChild(t)}return this._fromData(e,((t,{penColor:e})=>{const i=document.createElement("path");if(!(isNaN(t.control1.x)||isNaN(t.control1.y)||isNaN(t.control2.x)||isNaN(t.control2.y))){const s=`M ${t.startPoint.x.toFixed(3)},${t.startPoint.y.toFixed(3)} C ${t.control1.x.toFixed(3)},${t.control1.y.toFixed(3)} ${t.control2.x.toFixed(3)},${t.control2.y.toFixed(3)} ${t.endPoint.x.toFixed(3)},${t.endPoint.y.toFixed(3)}`;i.setAttribute("d",s),i.setAttribute("stroke-width",(2.25*t.endWidth).toFixed(3)),i.setAttribute("stroke",e),i.setAttribute("fill","none"),i.setAttribute("stroke-linecap","round"),o.appendChild(i)}}),((t,{penColor:e,dotSize:i,minWidth:s,maxWidth:n})=>{const h=document.createElement("circle"),r=i>0?i:(s+n)/2;h.setAttribute("r",r.toString()),h.setAttribute("cx",t.x.toString()),h.setAttribute("cy",t.y.toString()),h.setAttribute("fill",e),o.appendChild(h)})),o.outerHTML}}return s}));

!(function () {


	release = document.querySelector("form.release");

	if( release ) {

		release.addEventListener("submit",function(e){
			e.preventDefault();

			const submitter = document.querySelector("form.release button[type=submit]");
			const response = new FormData(release, submitter);

			document.getElementById("signature").value = signaturePad.toDataURL();
			document.getElementById("parent-signature").value = parentSignaturePad.toDataURL();
			
			release.classList.add("loading");
			thisdata = new Object();
			thisdata.action = 'add_release';
			thisdata._ajax_nonce = nonce;
			thisdata.date = document.getElementById("date").value;
			thisdata.signedby = document.getElementById("name").value;
			thisdata.address = document.getElementById("address").value;
			thisdata.city = document.getElementById("city").value;
			thisdata.state = document.getElementById("state").value;
			thisdata.zip = document.getElementById("zip").value;
			thisdata.phone = document.getElementById("phone").value;
			thisdata.email = document.getElementById("email").value;
			thisdata.signature = document.getElementById("signature").value;
			thisdata.parent = document.getElementById("parent").value;
			thisdata.parentsignature = document.getElementById("parent-signature").value;

			console.log( thisdata );

			jQuery.ajax({
				url: ajax,
				type: 'post',
				data: thisdata,
				success: function( msg ) {
					release.classList.remove("loading");

					if( msg ) {
						release.classList.add("submitted");
						release.reset();
					} else {
						alert( 'Something went wrong.' );
					}

				},
			});

		});
	}

	document.getElementById("date").valueAsDate = new Date();


//	var addressinput = document.querySelector(".field.address input");
	var sigcanvas = document.getElementById('signature-canvas');
	var parsigcanvas = document.getElementById('parent-signature-canvas');
	var inputwidth = sigcanvas.offsetWidth;
//	var inputheight = inputwidth * 0.425;

//	sigcanvas.style.width = inputwidth + 'px';
//	sigcanvas.style.height = inputheight + 'px';

//	parsigcanvas.style.width = inputwidth + 'px';
//	parsigcanvas.style.height = inputheight + 'px';

//	var sigwrapper = document.getElementById("signature-pad"),
//		sigcanvas = sigwrapper.querySelector("canvas"),
//		signaturePad;
	
//	var signaturePad = new SignaturePad(sigcanvas);
//	signaturePad.minWidth = 1; //minimale Breite des Stiftes
//	signaturePad.maxWidth = 3; //maximale Breite des Stiftes
//	signaturePad.penColor = "#000000"; //Stiftfarbe
//	signaturePad.backgroundColor = "#FAFAFA"; //Hintergrundfarbe

//	var signature = new PortrayCanvas(sigcanvas, {
//	lineWidth: 2,
//	onLineFinish: function(c){
//	console.log("Lines:");
//	console.log(c.getLines());
//	}
//	});
    
	var sigclear = document.getElementById("signature-clear");
	sigclear.addEventListener("click",function(e) {
		e.preventDefault();
		signaturePad.clear();
	});

//    var parentsig = new PortrayCanvas(parsigcanvas, {
//      lineWidth: 2,
//      onLineFinish: function(c){
//        console.log("Lines:");
//        console.log(c.getLines());
//      }
//    });

	var parsigclear = document.getElementById("parent-signature-clear");
	parsigclear.addEventListener("click",function(e) {
		e.preventDefault();
	  	parentSignaturePad.clear();
	});

//	function resizeCanvas() {
//		var oldContent = signaturePad.toData();
//		var ratio = Math.max(window.devicePixelRatio || 1, 1);
//		sigcanvas.width = sigcanvas.offsetWidth * ratio;
//		sigcanvas.height = sigcanvas.offsetHeight * ratio;
//		sigcanvas.getContext("2d").scale(ratio, ratio);
//		signaturePad.clear();
//		signaturePad.fromData(oldContent);
//	}

//	window.onresize = resizeCanvas;
//	resizeCanvas();

	sigcanvas.addEventListener("touchstart",  function(event) {event.preventDefault()});
	sigcanvas.addEventListener("touchmove",   function(event) {event.preventDefault()});
	sigcanvas.addEventListener("touchend",    function(event) {event.preventDefault()});
	sigcanvas.addEventListener("touchcancel", function(event) {event.preventDefault()});

	parsigcanvas.addEventListener("touchstart",  function(event) {event.preventDefault()});
	parsigcanvas.addEventListener("touchmove",   function(event) {event.preventDefault()});
	parsigcanvas.addEventListener("touchend",    function(event) {event.preventDefault()});
	parsigcanvas.addEventListener("touchcancel", function(event) {event.preventDefault()});


	const wrapper = document.getElementById("signature-pad");
	const canvasWrapper = document.getElementById("canvas-wrapper");
	const savePNGButton = wrapper.querySelector("[data-action=save-png]");
	const saveJPGButton = wrapper.querySelector("[data-action=save-jpg]");
	const saveSVGButton = wrapper.querySelector("[data-action=save-svg]");
	let undoData = [];
	const canvas = wrapper.querySelector("canvas");
	signaturePad = new SignaturePad(canvas, {
	  // It's Necessary to use an opaque color when saving image as JPEG;
	  // this option can be omitted if only saving as PNG or SVG
	  backgroundColor: 'rgb(255, 255, 255)'
	});


	const parentwrapper = document.getElementById("parent-signature-pad");
	const parentcanvasWrapper = document.getElementById("parent-canvas-wrapper");
	let undoParentData = [];
	const parentcanvas = parentwrapper.querySelector("canvas");
	parentSignaturePad = new SignaturePad(parentcanvas, {
	  // It's Necessary to use an opaque color when saving image as JPEG;
	  // this option can be omitted if only saving as PNG or SVG
	  backgroundColor: 'rgb(255, 255, 255)'
	});
	
	// Adjust canvas coordinate space taking into account pixel ratio,
	// to make it look crisp on mobile devices.
	// This also causes canvas to be cleared.
	function resizeCanvas() {
	  // When zoomed out to less than 100%, for some very strange reason,
	  // some browsers report devicePixelRatio as less than 1
	  // and only part of the canvas is cleared then.
	  const ratio = Math.max(window.devicePixelRatio || 1, 1);
	
	  // This part causes the canvas to be cleared
	  canvas.width = canvas.offsetWidth * ratio;
	  canvas.height = canvas.offsetHeight * ratio;
	  canvas.getContext("2d").scale(ratio, ratio);

	  parentcanvas.width = parentcanvas.offsetWidth * ratio;
	  parentcanvas.height = parentcanvas.offsetHeight * ratio;
	  parentcanvas.getContext("2d").scale(ratio, ratio);
	
	  // This library does not listen for canvas changes, so after the canvas is automatically
	  // cleared by the browser, SignaturePad#isEmpty might still return false, even though the
	  // canvas looks empty, because the internal data of this library wasn't cleared. To make sure
	  // that the state of this library is consistent with visual state of the canvas, you
	  // have to clear it manually.
	  //signaturePad.clear();
	
	  // If you want to keep the drawing on resize instead of clearing it you can reset the data.
	  signaturePad.fromData(signaturePad.toData());
	  parentSignaturePad.fromData(parentSignaturePad.toData());
	}
	
	// On mobile devices it might make more sense to listen to orientation change,
	// rather than window resize events.
	window.onresize = resizeCanvas;
	resizeCanvas();
	
	window.addEventListener("keydown", (event) => {
	  switch (true) {
		case event.key === "z" && event.ctrlKey:
		  undoButton.click();
		  break;
		case event.key === "y" && event.ctrlKey:
		  redoButton.click();
		  break;
	  }
	});
	
	function download(dataURL, filename) {
	  const blob = dataURLToBlob(dataURL);
	  const url = window.URL.createObjectURL(blob);
	
	  const a = document.createElement("a");
	  a.style = "display: none";
	  a.href = url;
	  a.download = filename;
	
	  document.body.appendChild(a);
	  a.click();
	
	  window.URL.revokeObjectURL(url);
	}
	
	// One could simply use Canvas#toBlob method instead, but it's just to show
	// that it can be done using result of SignaturePad#toDataURL.
	function dataURLToBlob(dataURL) {
	  // Code taken from https://github.com/ebidel/filer.js
	  const parts = dataURL.split(';base64,');
	  const contentType = parts[0].split(":")[1];
	  const raw = window.atob(parts[1]);
	  const rawLength = raw.length;
	  const uInt8Array = new Uint8Array(rawLength);
	
	  for (let i = 0; i < rawLength; ++i) {
		uInt8Array[i] = raw.charCodeAt(i);
	  }
	
	  return new Blob([uInt8Array], { type: contentType });
	}
	
	signaturePad.addEventListener("endStroke", () => {
	  // clear undoData when new data is added
	  undoData = [];
	});
	
	savePNGButton.addEventListener("click", () => {
	  if (signaturePad.isEmpty()) {
		alert("Please provide a signature first.");
	  } else {
		const dataURL = signaturePad.toDataURL();
		download(dataURL, "signature.png");
	  }
	});
	
	saveJPGButton.addEventListener("click", () => {
	  if (signaturePad.isEmpty()) {
		alert("Please provide a signature first.");
	  } else {
		const dataURL = signaturePad.toDataURL("image/jpeg");
		download(dataURL, "signature.jpg");
	  }
	});
	
	saveSVGButton.addEventListener("click", () => {
	  if (signaturePad.isEmpty()) {
		alert("Please provide a signature first.");
	  } else {
		const dataURL = signaturePad.toDataURL('image/svg+xml');
		download(dataURL, "signature.svg");
	  }
	});
	

})();


