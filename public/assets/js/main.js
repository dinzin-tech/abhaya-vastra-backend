(function ($) {
	"use strict";
	var windowOn = $(window);

	/*======================================
	Preloader activation
	========================================*/
	$(window).on('load', function (event) {
		$('#preloader').delay(100).fadeOut(100);
	});

	/* Body overlay Js */
	$(".body-overlay").on("click", function () {
		$(".offcanvas__area").removeClass("opened");
		$(".body-overlay").removeClass("opened");
	});

	/* Sticky Header Js */
	var lastScrollTop = 200;
	$(window).scroll(function (event) {
		var scroll = $(this).scrollTop();
		if (scroll > lastScrollTop) {
			$('#header-sticky').removeClass('sticky');
		} else {
			$('#header-sticky').addClass('sticky');
		}

		if (scroll < 200) {
			$("#header-sticky").removeClass("sticky");
		}
		lastScrollTop = scroll;
	});

	/* Data Css js */
	$("[data-background").each(function () {
		$(this).css("background-image", "url( " + $(this).attr("data-background") + "  )");
	});

	$("[data-width]").each(function () {
		$(this).css("width", $(this).attr("data-width"));
	});

	$("[data-bg-color]").each(function () {
		$(this).css("background-color", $(this).attr("data-bg-color"));
	});

	/* settings append in body Js */
	function bd_settings_append($x) {
		var settings = $('body');
		let dark;
		$x === true ? dark = 'd-block' : dark = 'd-none';
		/* no need switcher then add 'd-none' */
		var settings_html = `<div class="bd-theme-settings-area transition-3">
		<div class="bd-theme-wrapper">
		<div class="bd-theme-header text-center">
		   <h4 class="bd-theme-header-title">Template Settings</h4>
		</div>

		<!-- THEME TOGGLER -->
		<div class="bd-theme-toggle mb-20 ${dark}">
		   <label class="bd-theme-toggle-main" for="bd-theme-toggler">
		   <span class="bd-theme-toggle-dark"><i class="fa-light fa-moon"></i> Dark </span>
				 <input type="checkbox" id="bd-theme-toggler">
				 <i class="bd-theme-toggle-slide"></i>
				 <span class="bd-theme-toggle-light active"><i class="fa-light fa-sun-bright"></i> Light</span>
		   </label>
		</div>

		<!--  RTL SETTINGS  mb-20 -->
		<div class="bd-theme-dir">
		   <label class="bd-theme-dir-main" for="bd-dir-toggler">
			  <span class="bd-theme-dir-rtl"> RTL</span>
				 <input type="checkbox" id="bd-dir-toggler">
				 <i class="bd-theme-dir-slide"></i>
			  <span class="bd-theme-dir-ltr active"> LTR</span>
		   </label>
		</div>

		<div class="bd-theme-settings">
		   <div class="bd-theme-settings-wrapper">
			  <div class="bd-theme-settings-open">
				 <button class="bd-theme-settings-open-btn">
					<span class="bd-theme-settings-gear">
					   <i class="fa-light fa-gear"></i>
					</span>
					<span class="bd-theme-settings-close">
					   <i class="fa-regular fa-xmark"></i>
					</span>
				 </button>
			  </div>
		   </div>
		</div>
	 </div>
		 </div>`;
		settings.append(settings_html);
	}

	function bd_rtl_settings() {
		$('#bd-dir-toggler').on("change", function () {
			toggle_rtl();
			location.reload(true);
		});

		function bd_set_scheme(bd_dir) {
			localStorage.setItem('bd_dir', bd_dir);
			document.documentElement.setAttribute("dir", bd_dir);

			var list = $("[href='assets/vendor/css/bootstrap.min.css']");
			$(list).attr("href", bd_dir === 'rtl' ? "assets/vendor/css/bootstrap.rtl.min.css" : "assets/vendor/css/bootstrap.min.css");
		}

		function toggle_rtl() {
			if (localStorage.getItem('bd_dir') === 'rtl') {
				bd_set_scheme('ltr'); /* change ltr to rtl */
			} else {
				bd_set_scheme('rtl');
			}
		}

		function bd_init_dir() {
			var savedDir = localStorage.getItem('bd_dir');
			bd_set_scheme(savedDir || 'ltr'); /* change ltr to rtl */
			document.getElementById('bd-dir-toggler').checked = savedDir === 'rtl';
		}

		bd_init_dir();
	}

	function bd_theme_toggler() {
		$('#bd-theme-toggler').on("change", function () {
			toggleTheme();
		});

		function bd_set_scheme(bd_theme) {
			localStorage.setItem('bd_theme_scheme', bd_theme);
			document.documentElement.setAttribute("bd-theme", bd_theme);
		}

		function toggleTheme() {
			var currentTheme = localStorage.getItem('bd_theme_scheme');
			bd_set_scheme(currentTheme === 'bd-theme-light' ? 'bd-theme-dark' : 'bd-theme-light');
		}

		function bd_init_theme() {
			var savedTheme = localStorage.getItem('bd_theme_scheme');
			bd_set_scheme(savedTheme || 'bd-theme-light'); /* change bd-theme-light to bd-theme-dark */
			document.getElementById('bd-theme-toggler').checked = savedTheme !== 'bd-theme-light';
		}

		bd_init_theme();
	}
	/* Append settings HTML  */
	bd_settings_append(true); /* if you want to enable dark mode, send "true" */

	/* Event listeners  */
	$(".bd-theme-settings-open-btn").on("click", function () {
		$(".bd-theme-settings-area").toggleClass("settings-opened");
	});

	/* Initialize RTL settings if the element is present  */
	if ($("#bd-dir-toggler").length > 0) {
		bd_rtl_settings();
	}

	/* Initialize dark/light mode toggler if the element is present  */
	if ($("#bd-theme-toggler").length > 0) {
		bd_theme_toggler();
	}

	var bd_rtl = localStorage.getItem('bd_dir');
	let rtl_setting = bd_rtl === 'rtl' ? true : false;


	/* Tooltip Activation Js */
	$(document).ready(function () {
		$('[data-toggle="tooltip"]').tooltip();
	});
	var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
	var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
		return new bootstrap.Tooltip(tooltipTriggerEl)
	})

	/* Parallax js */
	var b = document.getElementsByTagName("BODY")[0];
	b.addEventListener("mousemove", function (event) {
		parallaxed(event);

	});
	function parallaxed(e) {
		var amountMovedX = (e.clientX * -0.3 / 8);
		var amountMovedY = (e.clientY * -0.3 / 8);
		var x = document.getElementsByClassName("parallaxed");
		var i;
		for (i = 0; i < x.length; i++) {
			x[i].style.transform = 'translate(' + amountMovedX + 'px,' + amountMovedY + 'px)'
		}
	}

	/* Sidebar js */

	$("#sidebar__active").on("click", function () {
		if (window.innerWidth > 0 && window.innerWidth <= 1199) {
			$(".app-sidebar").toggleClass("close_sidebar");
		} else {
			$(".app-sidebar").toggleClass("collapsed");
		}
		$(".app__offcanvas-overlay").toggleClass("overlay-open");
	});

	$(".app__offcanvas-overlay").on("click", function () {
		$(".app-sidebar").removeClass("collapsed");
		$(".app-sidebar").removeClass("close_sidebar");
		$(".app__offcanvas-overlay").removeClass("overlay-open");
	});


	$("#custom_close_icon").on("click", function () {
		if (window.innerWidth > 0 && window.innerWidth <= 1199) {
			$(".app-sidebar").toggleClass("close_sidebar");
		} else {
			$(".app-sidebar").toggleClass("collapsed");
		}
		$(".app__offcanvas-overlay").toggleClass("overlay-open");
	});

	$(".app__offcanvas-overlay").on("click", function () {
		$(".app-sidebar").removeClass("collapsed");
		$(".app-sidebar").removeClass("close_sidebar");
		$(".app__offcanvas-overlay").removeClass("overlay-open");
	});


	/* Scrollbar js */
	var Scrollbar = window.Scrollbar;
	const customizeOptions = {
		'damping': 0.1,
		'thumbMinSize': 5,
		renderByPixels: true,
		alwaysShowTracks: false,
	}
	$(".card__scroll").map(function (i, element) {
		Scrollbar.init(element)
	})

	/* Notify dropdown Js */
	$("#notifydropdown").on("click", function () {
		$(".notification__dropdown").toggleClass("notifydropdown-enable");
		$(".body__overlay").toggleClass("notifydropdown-enable");
		$(".email__dropdown").removeClass("email-enable");
		$(".user__dropdown").removeClass("user-enable");
		$(".lang__dropdown").removeClass("lang-enable");

	});
	$(".body__overlay").on("click", function () {
		$(".notification__dropdown").removeClass("notifydropdown-enable");
		$(".body__overlay").removeClass("notifydropdown-enable");
	});

	/* Email dropdown Js */

	$("#emaildropdown").on("click", function () {
		$(".email__dropdown").toggleClass("email-enable");
		$(".body__overlay").toggleClass("email-enable");
		$(".user__dropdown").removeClass("user-enable");
		$(".lang__dropdown").removeClass("lang-enable");
		$(".notification__dropdown").removeClass("notifydropdown-enable");
	});
	$(".body__overlay").on("click", function () {
		$(".email__dropdown").removeClass("email-enable");
		$(".body__overlay").removeClass("email-enable");

	});

	/* User dropdown Js */
	$("#userportfolio").on("click", function () {
		$(".user__dropdown").toggleClass("user-enable");
		$(".body__overlay").toggleClass("user-enable");
		$(".notification__dropdown").removeClass("notifydropdown-enable");
		$(".email__dropdown").removeClass("email-enable");
		$(".lang__dropdown").removeClass("lang-enable");
	});
	$(".body__overlay").on("click", function () {
		$(".user__dropdown").removeClass("user-enable");
		$(".body__overlay").removeClass("user-enable");
	});

	/* lang dropdown Js */
	$("#langdropdown").on("click", function () {
		$(".lang__dropdown").toggleClass("lang-enable");
		$(".body__overlay").toggleClass("lang-enable");
		$(".notification__dropdown").removeClass("notifydropdown-enable");
		$(".email__dropdown").removeClass("email-enable");
		$(".user__dropdown").removeClass("user-enable");
	});
	$(".body__overlay").on("click", function () {
		$(".lang__dropdown").removeClass("lang-enable");
		$(".body__overlay").removeClass("lang-enable");
	});


	/* Dropdown action  js */
	$(".dropdown").click(function () {
		$(this).find(".dropdown-list").fadeToggle(100);
	});
	$(document).on("click", function (event) {
		var $trigger = $(".dropdown");
		if ($trigger !== event.target && !$trigger.has(event.target).length) {
			$(this).find(".dropdown-list").fadeOut(100);
		}
	});

	/* email filter btn Js */
	$(document).ready(function () {
		$(".email__sidebar .email__toggle-btn").on("click", function (e) {
			e.stopPropagation(); /* Prevents the event from reaching the document and closing the sidebar */
			$(".email__sidebar .email__left-side").toggleClass("open");
		});

		$(".app__slide-wrapper").on("click", function (e) {
			e.stopPropagation(); /* Prevents the event from reaching the document and reopening the sidebar */
			$(".email__sidebar .email__left-side").removeClass("open");
		});

		/* Close the sidebar if clicking anywhere else on the document */
		$(document).on("click", function () {
			$(".email__sidebar .email__left-side").removeClass("open");
		});
	});

})(jQuery);

function squarePlaceholderUrlAlt(image) {
    image.onerror = "";
    image.src = "{{asset('assets/images/placeholder-square.jpg')}}";
    return true;
}