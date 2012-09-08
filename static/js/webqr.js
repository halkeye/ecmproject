// QRCODE reader Copyright 2011 Lazar Laszlo
// http://www.webqr.com

var stream = null;
var gCtx = null;
var gCanvas = null;
var imageData = null;
var disabled = 0;
var spinner;

 
// shim layer with setTimeout fallback
window.requestAnimFrame = (function(){
      return  window.requestAnimationFrame       || 
              window.webkitRequestAnimationFrame || 
              window.mozRequestAnimationFrame    || 
              window.oRequestAnimationFrame      || 
              window.msRequestAnimationFrame     || 
              function( callback ){
                window.setTimeout(callback, 1000 / 60);
              };
})();
 

function hasGetUserMedia() {
  // Note: Opera is unprefixed.
  return !!(navigator.getUserMedia || navigator.webkitGetUserMedia ||
            navigator.mozGetUserMedia || navigator.msGetUserMedia);
}

function initCanvas(ww,hh)
{
    gCanvas = document.getElementById("qr-canvas");
    var w = ww;
    var h = hh;
    gCanvas.style.width = w + "px";
    gCanvas.style.height = h + "px";
    gCanvas.width = w;
    gCanvas.height = h;
    gCtx = gCanvas.getContext("2d");
    gCtx.clearRect(0, 0, w, h);
    imageData = gCtx.getImageData( 0,0,320,240);
}


function htmlEntities(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function read(a)
{
    console.debug(a);

    var html="<br>";
    if(a.indexOf("http://") === 0 || a.indexOf("https://") === 0)
        html+="<a target='_blank' href='"+a+"'>"+a+"</a><br>";
    html+="<b>"+htmlEntities(a)+"</b><br><br>";
    document.getElementById("result").innerHTML=html;
}

function createObjectURL(obj) {
    if (window.webkitURL) {
        return window.webkitURL.createObjectURL(obj);
    }
    return window.URL.createObjectURL(obj);
}

function finishLookup(data)
{
    var container = jQuery('#kiosk_lookup');
    container.hide();

    if (data.error) {
        if (data.not_found)
        {
            alert("Unable to find given registration");
            return;
        }
        alert("Unknown error occurred: " + JSON.stringify(data));
        return;
    }
    jQuery('#lookup_name').text([data.gname, data.sname].join(' '));
    jQuery('#lookup_reg_id').text(data.pretty_reg_id);
    jQuery('#lookup_pass').text(data.pass);
    jQuery('#lookup_convention').text(data.convention);
    var pickupObj = jQuery('#lookup_pickup_status').text(data.pickup_status ? "Yes" : "No");
    if (data.pickup_status) {
        pickupObj.addClass('alreadyPickedUp');
    } else {
        pickupObj.removeClass('alreadyPickedUp');
    }
    var statusObj = jQuery('#lookup_status').text(data.status_name);
    if (data.status != 99) {
        statusObj.addClass('notPaid');
    } else {
        statusObj.removeClass('notPaid');
    }

    if (data.status == 99) 
    {
        // enable print button
        // print button also marks as picked up
    }
    else
    {
        // disable print button
    }

    container.show();
}

function lookupReg(reg_id) 
{
    jQuery.ajax({
        beforeSend: function() { 
            spinner.show();
            jQuery('#kiosk_lookup table').hide();
            disabled = 1; 
        },
        complete: function() {
            spinner.hide();
            jQuery('#kiosk_lookup table').show();
            disabled = 0; 
        },
        data: { regId: reg_id },
        url: lookupURL,
        success: function(data, textStatus, jqXHR) {
            finishLookup(data);
        }
    });
}

jQuery(document).ready(function() {
    var lookupLength = "ECM-01-0001".length;
    jQuery('#kiosk_lookup table').hide();
    var lookupText = jQuery('#kiosk_lookup_text').change(function() {
        var that = jQuery(this);
        if (that.val().length >= lookupLength) {
            lookupReg(that.val());
        }
    });
    lookupText.closest('form').submit(function(e) { 
        e.preventDefault();
        lookupReg(lookupText.val());
        return false;
    });
    spinner = jQuery('#kiosk_lookup').spinner({
        img: spinnerImage,
        height: 48,
        width: 48
    }).data('spinner');
    spinner.hide();
    if (hasGetUserMedia()) {
        initCanvas(800,600);
        qrcode.callback=read;

        var webcamError = function(e) {
            alert('Webcam error!', e);
        };

        if (navigator.getUserMedia) {
            navigator.getUserMedia({audio: true, video: true}, function(stream) {
                v = document.querySelector('video');
                v.src = stream;
            }, webcamError);
        } else if (navigator.webkitGetUserMedia) {
            navigator.webkitGetUserMedia({audio:true, video:true}, function(stream) {
                v = document.querySelector('video');
                v.src = window.webkitURL.createObjectURL(stream);
            }, webcamError);
        } else {
            //video.src = 'somevideo.webm'; // fallback.
        }
    }
});


(function qrcodeFrameCheck(){
    requestAnimFrame(qrcodeFrameCheck);
    if (stream && gCtx && !disabled) {
        gCtx.drawImage(v,0,0, v.width, v.height);
        try{
            qrcode.decode();
        }
        catch(e){       
            //console.log(e);
            /* Check again */
        }
    }
})();
