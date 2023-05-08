$ = jQuery;
$(document).ready( function() {
  console.log('Hello LQU');
  $(document).scroll();

  if ( $('section.intro-video').length > 0) { lqu.header(); }
  if ( $('section.commitment').length > 0) { lqu.commitment(); }
  if ( $('section.category-pathing').length > 0) { lqu.categoryPathing(); }
  if ( $('section.footer').length > 0) { lqu.footer(); }
  if ( $('div.lqu-limited-list').length > 0) { lqu.limitedList(); }
  if ( $('div.lqu-clamp').length > 0) { lqu.clamper(); }
  if ( $('nav.main-mobile-nav').length > 0) { lqu.mobileNav(); }
  if ( $('section.faq').length > 0) { lqu.faq(); }
  if ( $('div.lqu-contact-form form').length > 0) { lqu.contactForm(); }
  if ( $('div.inquire-overlay').length > 0) { lqu.inquireOverlay(); }
  if ( $('div.similar-grid.limited').length > 0) { lqu.limitSimilarGrid(); }

});


lqu = new Object();



/***************
Inquire Overlay Toggle
****************/
lqu.limitSimilarGrid = function() {
  var $grid = $('div.similar-grid.limited')
  var items = $grid.children('a');
  var limited_number_of_items = $('div.similar-grid.limited').attr('data-similar-limit');
  var total_number_of_items = console.log(items.length);
  //shuffle and delete
  while (items.length) {
    $grid.append(items.splice(Math.floor(Math.random() * items.length), 1)[0]);
  }
  $("div.similar-grid.limited a:gt("+(limited_number_of_items-1)+")").remove();
}
lqu.inquireOverlay = function() {
  var $overlay = $('body>div.inquire-overlay');
  var $links = $('*.show-inquire-link');
  var $blank_area_to_close_the_overlay = $('div.blank-area-to-close-the-overlay');
  console.log($links);
  $links.each( function(i,l) {
    $(l).click( function(event) {
      event.preventDefault();
      $overlay.addClass('shown');
    });
  });
  $overlay.find('span.close-inquire-button').click( function() {
    $overlay.removeClass('shown');
  });
  $blank_area_to_close_the_overlay.click( function() {
    $overlay.removeClass('shown');
  });
}

/***************
Contact
****************/
//Disable Submit Button until Required Fields Filled
lqu.contactForm = function() {
  $form = $('div.lqu-contact-form form');
  var $required_fields = $form.find(':required');
  var $submit_button = $form.find('[name=submit]');
  var update = function() {
    //console.log($required_fields);
    var enableSubmitButton = true;
    $required_fields.each( function(i,f) {
      if ( f.checkValidity() == false ) {
        enableSubmitButton = false;
        return false;
      }
    });
    if (enableSubmitButton) {
      //$submit_button.prop('disabled', false);
      $submit_button.removeClass('disabled');
    } else {
      //$submit_button.prop('disabled', true);
      $submit_button.addClass('disabled');
    }
  }
  $required_fields.change( function() { update(); });
  $required_fields.keydown( function() { update(); });
  $required_fields.click( function() { update(); });
  update();
}



/***************
FAQ
****************/
lqu.faq = function() {
  $items = $('section.faq div.item');
  $items.each(function(i,item) {
    var $item = $(item);
    $item.addClass('hide-answers-on-mobile');
    var $answer_text = $item.find('div.answer-text');
    var $button = $item.find('div.show-more-link');
    $button.click( function() {
      $item.addClass('shown');
    });
  });
}

/***************
Mobile Nav
****************/
lqu.mobileNav = function() {
  //menu button
  var $button = $('a.mobile-nav-menu-button');
  var $nav = $('nav.main-mobile-nav');
  var $ul = $nav.find('>ul');
  var hide = function() {
    // Hide mobile nav
    $nav.removeClass('shown');
    $button.html('Menu');
    $('section.top-navigation').removeClass('mobile-open');
    $ul.stop().addClass('hidden');
    console.log('another hide');
    //$('html, body').css({ overflow: 'inherit', height: 'inherit' });
  }
  var show = function() {
    $nav.addClass('shown').delay(100).queue( function(next) {
      $ul.removeClass('hidden');
      next();
    });
    $button.html('Close');
    $('section.top-navigation').addClass('mobile-open');
  }
  $button.click( function() {
    if ( $nav.hasClass('shown') ) {
      hide();
    } else {
      show();
    }
  });

  // entrance animation
  $ul.addClass('hidden');
  console.log('intial hide');

  // custom furniture drop down
  var $drop_down_li = $nav.find('li.drop-down');
  $drop_down_li.click( function() {
    $(this).toggleClass('shown')
  })
  $drop_down_li.removeClass('shown'); //html starts shown; hide on load

}



/***************
Text Clamper for Read More ... see page-about.scss
Measure on load and reszise method is the most dependable
when dealing with multiple paragraphs
****************/
lqu.clamper = function() {
  $els = $('div.lqu-clamp');
  function update() {
    $els.each( function(i,el) {
      var $el = $(el)
      var $more = $el.next('div.more');
      $el.removeClass('clamped');
      var normal_height = $(el).height();
      if (!($el).hasClass('clamped-removed')) {
        //don't remove it twice, even after resize
        $el.addClass('clamped');
        var clamped_height = $(el).height();
        if (normal_height > clamped_height) {
          $more.addClass('shown');
          $more.click( function() {
            $el.removeClass('clamped');
            $el.addClass('clamped-removed');
            $more.removeClass('shown');
          });
        } else {
          $more.removeClass('shown')
        }
      }
    });
  }
  $(window).on('resize', function() { update(); });
  update();
}

/*
//Old -- doesn't work with multiple, short paragraphs
lqu.clamper = function() {
  $els = $('div.lqu-clamp');
  $els.each( function(i,el) {
    var $el = $(el)
    var $f = $el.find(">*:first-child");
    var $more = $el.next('div.more');
    $el.addClass('clamped');
    var fc_height_clamped = $f.height();
    var fc_height_normal = $f[0].scrollHeight;
    if (fc_height_normal > fc_height_clamped) {
      $more.addClass('shown');
      $more.click( function() {
        $el.removeClass('clamped');
        $more.removeClass('shown');
        // $el.toggleClass('clamped');
        // if ($el.hasClass('clamped')) {
        //   $more.html('Read more');
        // } else {
        //   $more.html('Collapse');
        // }
      });
    } else {
      $more.removeClass('shown')
    }
  });
}
*/

/***************
Limited Lists
****************/
lqu.limitedList = function() {
  $lls = $('div.lqu-limited-list');
  $lls.each( function(i,ll) {
    var $ll = $(ll)
    $ll.addClass('limited');
    var $more = $ll.next('span.more');
    $more.addClass('shown').css({cursor:'pointer'});
    $more.click( function() {
      $more.removeClass('shown');
      $ll.removeClass('limited');
    });
  });
}

/***************
Header
****************/
lqu.header = function() {
  var $intro_video = $('section.intro-video');
  var $top_div = $intro_video.find('div.curtain>div.top');
  var $left_div = $intro_video.find('div.curtain>div.top>div.left');
  var $right_div = $intro_video.find('div.curtain>div.top>div.right');
  var $below_q_div = $intro_video.find('div.curtain>div.top>div.below_q');
  var $q_div = $intro_video.find('div.curtain>div.top>div.q');
  var update = function() {
    var ch = $top_div.height();
    var cw = $top_div.width();
    var ca = cw/ch; //aspect ratio w/h
    var qa = 600/440;
    var rounding_error = 1;
    if (ca > qa) {
      //fix q height
      $q_div.css({height:ch,width:qa*ch,left:(cw-qa*ch)/2});
      $left_div.css({height:ch,width:rounding_error+(cw-qa*ch)/2});
      $right_div.css({height:ch,width:rounding_error+(cw-qa*ch)/2});
      $below_q_div.css({height:rounding_error})
    } else {
      //fix width
      $q_div.css({height:cw/qa,width:cw,left:0});
      $left_div.css({width:rounding_error});
      $right_div.css({width:rounding_error});
      $below_q_div.css({height:ch-cw/qa});
    }
  }
  //init
  $top_div.css({backgroundColor:'inherit'});
  update();
  $(window).on('resize', function() { update(); });
  setInterval( function() { update(); }, 1000);
}

/***************
FOOTER
****************/
lqu.footer = function() {
  var $button = $('section.footer nav.furniture>ul>li>span.toggle'); //only one
  var $list = $('section.footer nav.furniture>ul>li>ul'); //only one
  $button.click( function() {
    $list.toggleClass('shown');
  });
}

/***************
Commitment
****************/
lqu.commitment = function() {
  var $section = $('section.commitment')
  var $overlay = $section.find('div.overlay');
  var $overlay_images = $overlay.find('>div.images');
  var $overlay_text_stage = $overlay.find('>div.text_and_nav>div.text');
  var $overlay_text_items = $overlay.find('>div.text_and_nav>div.text>div.text-item');
  var number_of_items = $overlay_text_items.length;
  var $nav_numbers = $overlay.find('>div.text_and_nav>div.nav>span');
  var $items_container = $('div.items');
  var $items = $items_container.find('div.item');

  var updateOrientation = function() {
    var h = $(window).height();
    var w = $(window).width();
    if (w/h > 1) {
      $section.removeClass('portrait').addClass('landscape');
    } else {
      $section.removeClass('landscape').addClass('portrait');
    }

  }

  var update = function() {
    var window_top = $(window).scrollTop();
    var items_height = $items_container.height();
    var section_top = $section.offset().top;
    //var section_height = $section.height(); // should equal window height
    var window_height = $(window).height();
    var images_scroll_width = $overlay_images[0].scrollWidth;
    var images_width = $overlay_images.width();
    var single_image_width = $overlay_images.find('>div.image-item').width();
    //var overlay_scroll_width = $overlay_images[0].scrollWidth;
    //console.log(wt);
    //console.log(st);
    var position = window_top-section_top;
    if (position <0 ) { position = 0; }
    var position_relative = position/window_height;
    var image_position = Math.floor(position_relative); //number from 0 to the number of images
    if (image_position<1) {
      $overlay_images.scrollLeft( (single_image_width-images_width)*position_relative );
    } else {
      $overlay_images.scrollLeft(
        (single_image_width-images_width) + (position_relative-1)*single_image_width
      );
    }
    //console.log( 'position '+position );
    //console.log( 'position_relative '+position_relative );
    //console.log( 'image_postion '+ (Math.floor(position_relative)) );
    // Text Item
    var item_number_to_show = image_position+1;
    if (item_number_to_show > number_of_items) {
      item_number_to_show = number_of_items;
    }
    // console.log('Text item to show: '+item_number_to_show);
    $overlay_text_items.removeClass('shown');
    $overlay_text_items.eq(item_number_to_show-1).addClass('shown');
    $nav_numbers.removeClass('selected').eq(item_number_to_show-1).addClass('selected');
  }

  $nav_numbers.each( function(i,el) {
    var item_number = i;
    $(el).click( function() {
      var st;
      if (item_number == 0) {
        st = $items.eq(item_number).offset().top - $(window).height();
      } else {
        st = $items.eq(item_number).offset().top - $(window).height()*.32; //rounding error
      }
      $('html, body').stop().animate({
        scrollTop: st
      }, 1000);
    });
  });

  $items.css({'opacity':0});
  $(document).scroll( function() { update() });
  $(window).on('resize', function() {
    update();
    updateOrientation();
  });
  update();
  updateOrientation();
  setInterval( function() { update(); updateOrientation(); }, 2000);
}

/***************
Category Pathing
****************/
lqu.categoryPathing = function() {
  var $items = $('section.category-pathing div.title-and-items>div.item');
  var $image_divs = $('section.category-pathing div.title-and-items>div.item>div.image');
  $items.each( function(i,el) {
    var $el = $(el)
    var $a = $el.find('a');
    $a.on('mouseenter', function() {
      $image_divs.removeClass('shown');
      $el.find('div.image').addClass('shown');
    })
  });
  $image_divs.first().addClass('shown');
}
