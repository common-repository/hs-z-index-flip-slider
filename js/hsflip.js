(function($) {

var $slidereffect = hsslider.effect;
var $sliderduration = parseInt(hsslider.duration);
var $sliderstart = parseInt(hsslider.start);
var $slidereffectDirection = hsslider.direction;

if ($sliderstart == 1) 
    $sliderstart = true;
else
    $sliderstart = false;


var z = 0; //for setting the initial z-index's
var inAnimation = false; //flag for testing if we are in a animation
var imgLoaded = 0; //for checking if all images are loaded
  
  //$('#pictures').append('<div id="loader"></div>'); //append the loader div, it overlaps all pictures
  
  $('#pictures img').each(function() { //set the initial z-index's
    z++; //at the end we have the highest z-index value stored in the z variable
    $(this).css('z-index', z); //apply increased z-index to <img>
    
    $(new Image()).attr('src', $(this).attr('src')).load(function() { //create new image object and have a callback when it's loaded
      imgLoaded++; //one more image is loaded
      
      if(imgLoaded == z) { //do we have all pictures loaded?
        $('#loader').hide(); 
        $('#loader').fadeOut('slow'); //if so fade out the loader div
        $('#loader').css({zIndex:0}).fadeOut('slow');
        $('#loader').attr('style','display:none'); //if so fade out the loader div
      }
    });
  });

    function swapFirstLast(isFirst) {
    if(inAnimation) return false; //if already swapping pictures just return
    else inAnimation = true; //set the flag that we process a image

    var processZindex, direction, newZindex, inDeCrease; //change for previous or next image

    if($slidereffect == "verticle") {

        if(isFirst) {processZindex = z;direction = '-';newZindex = 1;inDeCrease = 1;} //set variables for "next" action
        else {processZindex = 1;direction = '';newZindex = z;inDeCrease = -1;} //set variables for "previous" action

        $('#pictures img').each(function() { //process each image
          if($(this).css('z-index') == processZindex) { //if its the image we need to process
            $(this).animate({'top' : direction + $(this).height() + 'px'}, 'slow', function() { //animate the img above/under the gallery (assuming all pictures are equal height)
              $(this).css('z-index', newZindex) //set new z-index
                .animate({'top' : '0'}, 'slow', function() { //animate the image back to its original position
                  inAnimation = false; //reset the flag
                });
            });
          } else { //not the image we need to process, only in/de-crease z-index
            $(this).animate({'top' : '0'}, 'slow', function() { //make sure to wait swapping the z-index when image is above/under the gallery
              $(this).css('z-index', parseInt($(this).css('z-index')) + inDeCrease); //in/de-crease the z-index by one
            });
          }
        });

    } else {

        if(isFirst) { processZindex = z; direction = ''; newZindex = 1; inDeCrease = 1; } //set variables for "next" action
        else { processZindex = 1; direction = '-'; newZindex = z; inDeCrease = -1; } //set variables for "previous" action

        $('#pictures img').each(function() { //process each image
          if($(this).css('z-index') == processZindex) { //if its the image we need to process
            $(this).animate({'left' : direction + $(this).height() + 'px'}, 'slow', function() { //animate the img above/under the gallery (assuming all pictures are equal height)
              $(this).css('z-index', newZindex) //set new z-index
                .animate({'left' : '0'}, 'slow', function() { //animate the image back to its original position
                  inAnimation = false; //reset the flag
                });
            });
          } else { //not the image we need to process, only in/de-crease z-index
            $(this).animate({'left' : '0'}, 'slow', function() { //make sure to wait swapping the z-index when image is above/under the gallery
              $(this).css('z-index', parseInt($(this).css('z-index')) + inDeCrease); //in/de-crease the z-index by one
            });
          }
        });

    }

    return false; //don't follow the clicked link
    }
  
    $('.next a').click(function() {
    return swapFirstLast(true); //swap first image to last position
    });
    
    $('.prev a').click(function() {
    return swapFirstLast(false); //swap last image to first position
    });
    
    $('.gallery').mouseover(function() {
       $('.next a').css( "opacity","10"); 
    });
    $('.gallery').mouseout(function() {
       $('.next a').css( "opacity","0.2"); 
    });
     $('.gallery').mouseover(function() {
       $('.prev a').css( "opacity","10"); 
    });
    $('.gallery').mouseout(function() {
       $('.prev a').css( "opacity","0.2"); 
    });
  
    if($sliderstart) {
        if($slidereffectDirection == "next") {
            var myInterval = setInterval(function() {
            swapFirstLast(true); //put first image to the end
            }, $sliderduration);

        } else {
          var myInterval = setInterval(function() {
            swapFirstLast(false); //put first image to the end
          }, $sliderduration);
        }
    }
  
})(jQuery);