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

  // Initialize page navigation
  initPageNav();
}

function initPageNav() {
  var totalPages = $('.flipbook').turn('pages');
  var navContainer = $('#page-nav');

  // Create page buttons
  for (var i = 1; i <= totalPages; i++) {
    var btn = $('<button class="page-nav-btn" data-page="' + i + '">' + i + '</button>');
    navContainer.append(btn);
  }

  // Set initial active state
  updatePageNav(1);

  // Handle page button clicks
  navContainer.on('click', '.page-nav-btn', function(e) {
    e.preventDefault();
    var page = $(this).data('page');
    $('.flipbook').turn('page', page);
  });

  // Update active button when page changes
  $('.flipbook').bind('turned', function(event, page) {
    updatePageNav(page);
  });
}

function updatePageNav(currentPage) {
  $('.page-nav-btn').removeClass('active');
  $('.page-nav-btn[data-page="' + currentPage + '"]').addClass('active');
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