window.addEventListener('resize', resize);

document.body.addEventListener('touchmove', function(e) {
  e.preventDefault();
});

function loadApp() {
  console.log('Load App');

  var size = getSize();

  // Create the flipbook
  $('.flipbook').turn({
      // Width
     width: size.width,

      // Height
     height: size.height,

      // Elevation
      elevation: 50,

      // Enable gradients
      gradients: true,

      // Auto center this flipbook
      autoCenter: true,
  });
 $('.flipbook').turn('display', 'single');
}

function getSize() {
  console.log('get size');
  var width;
  var height;
  if($(window).width() > 980){
    height = ($('.wrapper').height()*0.9);
    width = height*0.77;
  }
  else{
    width = (document.body.clientWidth)*0.8;
    height = width*1.3;
  }
  return {
    width: width,
    height: height
  }
}

function resize() {
  console.log('resize event triggered');

  var size = getSize();
  console.log(size);

  if (size.width > size.height) { // landscape
    $('.flipbook').turn('display', 'double');
  }
  else {
    $('.flipbook').turn('display', 'single');
  }

  $('.flipbook').turn('size', size.width, size.height);
}

var oTurn = $(".flipbook");
$("#prev").click(function(e){
  e.preventDefault();
  oTurn.turn("previous");
});

$("#next").click(function(e){
  e.preventDefault();
  oTurn.turn("next");
});

$('.wrapper').css({'height':$(window).height()})

// Load App
loadApp();