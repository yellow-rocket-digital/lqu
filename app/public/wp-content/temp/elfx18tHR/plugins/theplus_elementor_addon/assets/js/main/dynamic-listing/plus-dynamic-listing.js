/*Dynamic Listing*/( function( $ ) {
	"use strict";
	var WidgetDynamicListingHandler = function ($scope, $) {
		var container = $scope.find('.dynamic-listing');
		if(container.hasClass('.dynamic-listing.dynamic-listing-style-1')){
			$('.dynamic-listing.dynamic-listing-style-1 .grid-item .blog-list-content').on('mouseenter',function() {
				$(this).find(".post-hover-content").slideDown(300)				
			});
			$('.dynamic-listing.dynamic-listing-style-1 .grid-item .blog-list-content').on('mouseleave',function() {
				$(this).find(".post-hover-content").slideUp(300)				
			});
		}
		$(document).ready(function () {
		if($('.tp-child-filter-enable').length){
			  $( ".tp-child-filter-enable.pt-plus-filter-post-category .category-filters li a" ).on( "click", function(event) {
				event.preventDefault();
				var get_filter = $(this).data("filter"),
				get_filter_remove_dot = get_filter.split('.').join(""),  
				get_sub_class = 'cate-parent-',
				get_filter_add_class = get_sub_class.concat(get_filter_remove_dot);

				if(get_filter_remove_dot=="*" && get_filter_remove_dot !=undefined){
					$(this).closest(".post-filter-data").find(".category-filters-child").removeClass( "active");
				}else{
					$(this).closest(".post-filter-data").find(".category-filters-child").removeClass( "active");
					$(this).closest(".post-filter-data").find(".category-filters-child."+get_filter_add_class).addClass( "active");
				}
			  });
		}
		});
	};
	$(window).on('elementor/frontend/init', function () {
		elementorFrontend.hooks.addAction('frontend/element_ready/tp-dynamic-listing.default', WidgetDynamicListingHandler);
	});
})(jQuery);