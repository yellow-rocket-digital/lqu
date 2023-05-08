( function( $ ) {
	"use strict";
	var WidgetStyleListHandler = function ($scope, $) {
		var $target = $('.plus-stylist-list-wrapper', $scope);
		var $hover_inverse = $('.plus-stylist-list-wrapper.hover-inverse-effect', $scope);
		var $hover_inverse_global = $('.plus-stylist-list-wrapper.hover-inverse-effect-global', $scope);
		
		if($target.length){
			var $read_more =$target.find(".read-more-options");
			if($read_more.length){				
				var default_load =$target.find(".read-more-options").data("default-load");
				var $ul_listing =$target.find(".plus-icon-list-items");
				$ul_listing.each(function(){
				   $(this).find("li:gt("+default_load+")").hide();
				});
				$read_more.on("click", function(e){
					e.preventDefault();
					var $less_text=$(this).data("less-text");
					var $more_text=$(this).data("more-text");
					if($(this).hasClass("more")){
					   $(this).parent(".plus-stylist-list-wrapper").find(".plus-icon-list-items li").show();
					   $(this).text($less_text).addClass("less").removeClass("more");
					}else if($(this).hasClass("less")){
					   $(this).parent(".plus-stylist-list-wrapper").find(".plus-icon-list-items li:gt("+default_load+")").hide();
					   $(this).text($more_text).addClass("more").removeClass("less");
					}
				});
			}
		}
		if($(".plus-bg-hover-effect",$scope).length){
			$('.plus-icon-list-items >li',$target).on('mouseenter', function(e) {
				e.preventDefault();
				if (!$(this).hasClass('active')) {
					var index_el = $(this).index();

					$(this).addClass('active').siblings().removeClass('active');
					$(this).parents(".elementor-widget-tp-style-list").find('.plus-bg-hover-effect .hover-item-content').removeClass('active').eq(index_el).addClass('active');
				} else {
					return false
				}
			});
		}
		if($hover_inverse.length > 0){
			$('.plus-icon-list-items > li',$hover_inverse).on({
				mouseenter: function () {
					$(this).closest(".plus-icon-list-items").addClass("on-hover");
				},
				mouseleave: function () {
					$(this).closest(".plus-icon-list-items").removeClass("on-hover");
				}
			});
		}
		if($target.hasClass("hover-inverse-effect-global")){
			
			$('.plus-icon-list-items > li',$hover_inverse_global).on({
				mouseenter: function () {
					$('body').addClass("hover-stylist-global");
					var hover_class = $(this).closest(".plus-stylist-list-wrapper").data("hover-inverse");
					$(".hover-inverse-effect-global."+hover_class+" .plus-icon-list-items").addClass("on-hover");
				},
				mouseleave: function () {
					$('body').removeClass("hover-stylist-global");
					var hover_class = $(this).closest(".plus-stylist-list-wrapper").data("hover-inverse");
					$(".hover-inverse-effect-global."+hover_class+" .plus-icon-list-items").removeClass("on-hover");
				}
			});
		}
	};
	$(window).on('elementor/frontend/init', function () {
		elementorFrontend.hooks.addAction('frontend/element_ready/tp-style-list.default', WidgetStyleListHandler);
	});
})(jQuery);