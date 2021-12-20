/**
template-uncompressed.js
Diese uncompressed-Datei dient der Bearbeitung. Verwende Plugin System - JsCssMinifyGhsvs, um daraus minifiziertes JS zu erzeugen, das dann in Plugin bs3ghsvs etc. geladen wird.
So sparst alles umschreiben in .min.

Siehe template.js des Templates
Siehe erst mal ausgelagertes unter /media/plg_system_bs3ghsvs/js/template-more.js
Siehe Wo-sind-welche-functions-JQuery.xlsx

2017-07 $.fn.addSprungmarkeToUrl ausgelagert in eigene Datei.

*/

/*
http://stackoverflow.com/questions/2854407/javascript-jquery-window-resize-how-to-fire-after-the-resize-is-completed
*/
var waitForFinalEvent = (function () {
	var timers = {};
	return function (callback, ms, uniqueId) {
		if (!uniqueId) {
			uniqueId = "Don't call this twice without a uniqueId";
		}
		if (timers[uniqueId]) {
			clearTimeout (timers[uniqueId]);
		}
		timers[uniqueId] = setTimeout(callback, ms);
	};
})();


var addColorClassesToCharacters = function (selector)
{
	var myStr = jQuery.trim(jQuery(selector).text()).split("");

	if (myStr.length)
	{
		var myContents = "";

		for (var i = 0, len = myStr.length; i < len; i++)
		{
			myContents += '<span class="coloredLetter letter-color-' + (Math.floor(Math.random() * 16) + 1) + '">' + myStr[i] + '</span>';
		}
		jQuery(selector).html(myContents);
	}
};


var addClassToFirstCharacter = function (myStr)
{
	myStr = jQuery.trim(myStr);

	if (myStr.length)
	{
		myStr = myStr.split("");
		myStr[0] = '<span class="first-letterGhsvs">' + myStr[0] + '</span>';
		return myStr.join("");
	}

	return myStr;
};

(function()
{
	const html = document.documentElement;
	// Opera mini doesn't accept both classes in one remove().
	html.classList.remove('no-js');
	html.classList.remove('jsNotActive');
	html.classList.add('jsActive');
})();

;(function($)
{
 // Hide elements without text.
 $.fn.emptytaghideghsvs = function(mainSelectors){
  if (typeof mainSelectors === "undefined" || mainSelectors === null)
  {
   mainSelectors="p, h1, h2, h3, h4, h5, h6, .article-info.muted, .container-fluid, .icons, .category-desc, .page-header";
  }
  // WORD-Sonderfall
  $("a[name='_GoBack']").remove();

  // HIER NUR TAGS! Klassen unten! Da trim misslingt bspw. mit A-Tag als Sprungmarke oder mit IMG
  $NotEmpty = "img, a, iframe, br, svg";

  /* :empty ist unzuverlässig */
  $(mainSelectors).each(function()
	{
  	//var st = jQuery.trim( $(this).text() );// nimmt auch Kinder mit.
		var st = $(this).text().trim();// nimmt auch Kinder mit.
   if(
    st == ""
    && !$($NotEmpty, $(this)).length
    && !$(this).children().is("[class^='icon-']")
    && !$(this).is("[class^='icon-']")
    && !$(this).children().is("[class*=' icon-']")
    && !$(this).is("[class*=' icon-']")
    && !$(this).hasClass("container-fluid")
   ){
    // Paar Classes aus meinen alten CSS-Geschichten
    if (
     !$(this).hasClass("floatClear") &&
     !$(this).hasClass("floatC") &&
     !$(this).hasClass("mod-articles-category-introtext") &&
     !$(this).hasClass("text-hide")
    )
    {
     $(this).hide();
    }
   }
  });
 }; //$.fn.emptytaghideghsvs

 //Maximale Höhe einer Gruppe
 $.fn.max = function(selector){
  return Math.max.apply(
   null,
   this.map(
    function(index, el){
     return selector.apply(el);
    }
   ).get()
  );
 }; //$.fn.max


	/*
	Siehe dazu Modul, das #PAGINATION_CLONE container in buttonGruppe setzt.
	2015-08-04: Wegen Konflikt mit Venobox, muss das aus load() aufgerufen werden.
	Das pagination wird sonst mehrfach beim Schließen der Venobox erzeugt.
	*/
	$.fn.paginationClone = function(what, where)
	{
		var $what = $(what);
		var $where = $(where);
		if ($what.length && $where.length)
		{
			$where.html($what.clone());
			$what.addClass("isCloned");
		}
	}

 /*
 Fügt .active zu Trennzeichen und Menüüberschriften hinzu.
 dividerSelectors:
 - Bei Trennzeichen setzt Joomla eine Klasse li.divider
 - Bei Menüüberschrift leider nicht. Muss man also, wenn man dies Script nutzen will,
 einen Override für mod_menu machen. Um z.B. auch li.menuheader zu haben.
 2015-08-18: Auch das Problem mit menuheader gelöst. Siehe nav-header
 */
 $.fn.addActiveStateToDivider = function()
 {
  var $menueheader = $("li.deeper");

  $menueheader.each(function()
	{
   if ($(this).find("li.active, li.alias-parent-active").length)
   {
    $(this).addClass("active");
		$(this).children("a, span").first().addClass("active");
   }
  });
 }

 /**
 Plugin Lazyload funktioniert bei display:none-Inhalten nicht.
 Also Bilder z.B. im Bootstrap Carousel zuvor laden, lazyload verhindern.
 Aber auch Logo muss geschützt werden, da damit Teaserhöhe berechnet wird.
 Siehe $.fn.teaserAutoheight()
 */
 $.fn.lazyloadPluginOff = function(myCarousel){
  $images = $(myCarousel + " img[src$=\'blank.gif\']");
  if ($images.length)
  {
   $images.each(function(){
    $(this).attr("src", $(this).attr("data-src")).removeAttr("data-src");
   });
  }
 };//$.fn.lazyloadPluginOff

 /**
 2015-08-30
 Bestimmten Menüs eine Sprungamrke anhängen, um bspw. unterhalb Headerbereich zu hüpfen.
 /blah/blubb#divmain
 myContainer: kommaseparierte Container
 */
 $.fn.addSprungmarkeToUrl = function(myContainer, sprungmarke)
 {

  if (typeof sprungmarke === "undefined" || sprungmarke === null)
  {
   var sprungmarke = "#BELOWHEADER";
  }
  var $myContainer = $(myContainer);
  // Klappt das .not()? Denke doch. Zeigte ein Test am Ende der function,
  var $myLinks = $("a[href^='/']", $myContainer).not("a[href*='#']").not("a[href='/']");

  $myLinks.each(function(){
   var Href = $(this).attr("href");
   // Auf der Home will ich immer Header! s.o. über .not gelöst
   //if (Href != "/")
   {
    $(this).attr("href", Href + sprungmarke);
   }
  });
 };


	/* Wenn ein Sticky-Element auf der Seite ist, müssen die Sprungmarken ein
	Scroll-Offset bekommen. Das muss man anhand der Höhe des sticky Elementes
	ermitteln.
	Durch das tocGhsvs.js bekommen die Zielmarken eine Klasse .isATocId.

	Im CSS bzw. SCSS vielleicht ein ca-Wert.
	.isATocId
	{
		scroll-margin-top: 140px;
	}

	e.g. '#astroid-sticky-header' or '.sticky-top'.
	e.g. '.isATocId' from mod_tocghsvs.
	e.g. 40 for 40 additional pixels to calculated height of stickyEle
	*/
	$.fn.stickyCompensation = function(stickyEle, idClasses, addPixels)
	{
		var $stickyEle = $(stickyEle);
		var $idClasses = $(idClasses);
	//console.log($stickyEle.length);
	//console.log($idClasses.length);
		if ($stickyEle.length && $idClasses.length)
		{
			var height = $.fn.getStickyHeight(stickyEle);
	//console.log(height);
			if (height)
			{
					height = (parseInt(height) + addPixels) + 'px';

					if ($('#tocGhsvsInlineStyles').length)
					{
						$('#tocGhsvsInlineStyles').remove();
					}

					var css = document.createElement('style');
					css.type = 'text/css';
					css.id = 'tocGhsvsInlineStyles';
					var styles = `${idClasses}{scroll-margin-top:${height} !important;}`;
					if (css.styleSheet) css.styleSheet.cssText = styles;
					else css.appendChild(document.createTextNode(styles));
					document.getElementsByTagName("head")[0].appendChild(css);
			}
		}
	}

	$.fn.getStickyHeight = function(stickyEle)
	{
		var height = 0;
		var $stickyEle = $(stickyEle);

		if ($stickyEle.length)
		{
			// e.g. Astroid-Templates.
			if ($stickyEle.hasClass('d-none'))
			{
					$stickyEle.addClass('invisible d-flex');
					height = $stickyEle.outerHeight();
					$stickyEle.removeClass('d-flex invisible');
			}
			else
			{
					height = $stickyEle.outerHeight();
			}
		}
		return height;
	}

	/*
	The id of accordion main container.
	E.g.for pagebreakghsvs.
	*/
	$.fn.scrollToSliderHead = function(selector)
	{
		const SliderHead = $("#" + selector + " .collapse.show")
			.prev(".card-header");

		if (SliderHead.length)
		{
			const height = jQuery.fn.getStickyHeight("#CfButtonGruppe.sticky-top");
			const offsetPosition = SliderHead.offset().top - height;
    	window.scrollTo({
				top: offsetPosition,
				behavior: "smooth"
			});
		}
	}

	$(document).ready(function()
	{
		// Jetzt im Template js und nur bei Bedarf!
		// $.fn.addActiveStateToDivider();
		$.fn.emptytaghideghsvs();
	}); //ready

	$(window).on("load", function(){
	}); //window load

})(jQuery);
