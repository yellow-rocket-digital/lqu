/*accordion*/
(function($) {
	"use strict";
	var WidgetAccordionHandler = function($scope, $) {
        let container = $scope[0].querySelectorAll('.theplus-accordion-wrapper'),
		    AccordionType = container[0].dataset.accordionType,
            Connection = container[0].dataset.connection,
            RBGConnection = container[0].dataset.rowBgConn,
            accrodionList = container[0].querySelectorAll('.theplus-accordion-item'),
			$PlusAccordionListHeader = container[0].querySelectorAll('.theplus-accordion-item .plus-accordion-header'),
			
			AccordionSearchLen = container[0].dataset.searchAttrLen,			
			AccordionhoriTitleWidth = container[0].dataset.horiTitleWidth,
			Accordionhoriopen = container[0].dataset.horiOpenSpeed,
			Accordionhoriclose = container[0].dataset.horiCloseSpeed,
			AccordionId = container[0].dataset.accordionId,
			AccordionscrollTopSpeedAccr = container[0].dataset.scrollTopSpeedAccr,
			AccordionscrollTopOffsetAccr = container[0].dataset.scrollTopOffsetAccr,
			stagerVdAccr = container[0].dataset.stagerVdAccr,
			stagerGapAccr = container[0].dataset.stagerGapAccr,
            $TabAutoplay = container[0].dataset.tabAutoplay,
            $TabAutoplayDuration = container[0].dataset.tabAutoplayDuration,
            hash = window.location.hash;

            if(accrodionList.length > 0){
                accrodionList.forEach(function(self){
                    let AccHeader = self.querySelector('.plus-accordion-header');
                        if( AccHeader.classList.contains('active-default') ) {
                            let AdContent = self.querySelectorAll('.plus-accordion-content');
                                AccHeader.classList.add('active');
                        
                            if( AdContent.length > 0){
                                AdContent[0].classList.add('active')
                                AdContent[0].style.cssText = "display: block;"
                                slideDown(AdContent[0], 500)
  
                                let tab_index = self.querySelector('.plus-accordion-content.active').dataset.tab;
                                    if( Connection && document.querySelectorAll('.'+Connection).length ){
                                        setTimeout(function(){
                                            accordion_tabs_connection( tab_index, Connection );
                                        }, 150);
                                    }

                                    if(self && self.nextElementSibling){
                                        let FindPostclass = self.nextElementSibling.querySelectorAll(".list-carousel-slick > .post-inner-loop");
                                        if( FindPostclass.length > 0 ){
                                            $(FindPostclass[0]).slick('setPosition');
                                        }
                                    }

                                    if( RBGConnection && document.querySelectorAll('#'+RBGConnection).length ){
                                        background_accordion_tabs_conn( tab_index, RBGConnection );
                                    }
                            }
                        }
                });
            }

            if( AccordionType == 'accordion' ) {
                $PlusAccordionListHeader.forEach(function(self){
                    self.addEventListener("click", function(e){
                        let $this = this;
                        if( this.classList.contains('active') ) {
                            this.classList.remove('active');
                            if(this.nextElementSibling){
                                this.nextElementSibling.classList.remove('active')
                                slideUp(this.nextElementSibling, 500)
                            }						
                        }else {
                            accrodionList.forEach(function(self){
                                if( self.children[0].classList.contains('active') ){
                                    self.children[0].classList.remove('active')
                                }
                                
                                if( self.children[1] && self.children[1].classList.contains('active') ){
                                    self.children[1].classList.remove('active')
                                    slideUp(self.children[1], 500)
                                }
                            });

                            this.classList.toggle("active");
                            if(this.nextElementSibling){
                                this.nextElementSibling.classList.toggle("active");
                                if( this.nextElementSibling.style.display != "block" ){
                                    slideDown(this.nextElementSibling, 500);									
									if(container[0].classList.contains('tp-scrolltopacc')){
                                        setTimeout(() => {
                                           
                                            jQuery('body, html').animate({
                                                // scrollTop: jQuery(this).offset().top - AccordionscrollTopOffsetAccr
                                                scrollTop: (jQuery(this).offset().top - AccordionscrollTopOffsetAccr) - $this.clientHeight
                                            }, AccordionscrollTopSpeedAccr)
                                        }, 400)
									}									 
                                }

                                let FindPostclass = this.nextElementSibling.querySelectorAll(".list-carousel-slick > .post-inner-loop");
                                if( FindPostclass.length > 0 ){
                                    $(FindPostclass[0]).slick('setPosition');
                                }
                            }
                           
                            let tab_index = this.dataset.tab;
                                if( tab_index && Connection && document.querySelectorAll('.'+Connection).length ){
									accordion_tabs_connection(tab_index, Connection);
								 }

                            /*carousel-remote*/ 
                            let carodots = document.querySelectorAll(".tp-carousel-dots"),
                                tpremote = document.querySelectorAll(".theplus-carousel-remote");
                            if( carodots.length > 0 && tpremote.length > 0 ){	
                                if( tpremote[0].dataset.connection === Connection ){
                                    let carodotsitem = carodots[0].querySelectorAll(".tp-carodots-item");
                                        carodotsitem.forEach(function(self){
                                            if( self.classList.contains('active') ){
                                                self.classList.remove('active');
                                                self.classList.add('inactive');
                                            }
                                            if( self.classList.contains('default-active') ){
                                                self.classList.remove('default-active');
                                                self.classList.add('inactive');
                                            }
                                        }); 
                                        carodotsitem.forEach(function(self){
                                            if(tab_index == (Number(self.dataset.tab) + Number(1))){
                                                self.classList.remove('inactive');
                                                self.classList.add('active');
                                            }
                                        }); 
                                }
                            }
                            
                            if( RBGConnection &&  document.querySelectorAll('#'+RBGConnection).length ){
                                background_accordion_tabs_conn(tab_index, RBGConnection);
                            }
                        }
                    });	
                });	

                let $FindID = container[0].querySelectorAll(`${hash}.plus-accordion-header`);
                if( hash && $FindID.length > 0 ){
                    $FindID.forEach(function(self){
                        if( !self.classList.contains('active') ){
                            document.querySelector('html, body').animate({
                                scrollTop: $(hash).offset().top,
                            }, 1500);
                            self.click();
                        }
                    });   
                }
            }else if( AccordionType == 'hover' ) {
                $PlusAccordionListHeader.forEach(function(self){
                    self.addEventListener("mouseover", function( e ) {
                        if( this.classList.contains('active') ) {
                           
                        }else{
                            let ActiveNone = container[0].querySelectorAll('.plus-accordion-header.active'),
                                tab_index = this.dataset.tab;
                            if( ActiveNone.length > 0 ){
                                ActiveNone[0].classList.remove('active')
                                if(ActiveNone[0].nextElementSibling){
                                    ActiveNone[0].nextElementSibling.classList.remove('active')
                                    slideUp(ActiveNone[0].nextElementSibling, 500)
                                }                            
                            }

                            this.classList.toggle("active");
                            if(this.nextElementSibling){
                                this.nextElementSibling.classList.toggle("active");
                                if( this.nextElementSibling.style.display != "block" ){
                                    slideDown(this.nextElementSibling, 500)
                                    if(container[0].classList.contains('tp-scrolltopacc')){
                                        setTimeout(() => {
                                            jQuery('body, html').animate({
                                                // scrollTop: jQuery(this).offset().top - AccordionscrollTopOffsetAccr
                                                scrollTop: (jQuery(this).offset().top - AccordionscrollTopOffsetAccr) - $this.clientHeight
                                            }, AccordionscrollTopSpeedAccr)
                                        }, 400)
									}
                                }

                                let FindPostclass = this.nextElementSibling.querySelectorAll(".list-carousel-slick > .post-inner-loop");
                                if( FindPostclass.length > 0 ){
                                    $(FindPostclass[0]).slick('setPosition');
                                }
                            }
                            
                            if( Connection && document.querySelectorAll('.'+Connection).length ){
                                accordion_tabs_connection(tab_index, Connection);
                            }
                            
                            /*carousel-remote*/ 
                            let carodots = document.querySelectorAll(".tp-carousel-dots"),
                                tpremote = document.querySelectorAll(".theplus-carousel-remote");
                            if( carodots.length > 0 && tpremote.length > 0 ){	
                                if( tpremote[0].dataset.connection === Connection ){
                                    let carodotsitem = carodots[0].querySelectorAll(".tp-carodots-item");
                                        carodotsitem.forEach(function(self){
                                            if( self.classList.contains('active') ){
                                                self.classList.remove('active');
                                                self.classList.add('inactive');
                                            }
                                            if( self.classList.contains('default-active') ){ 
                                                /*confu*/
                                                self.classList.remove('default-active');
                                                self.classList.add('inactive');
                                            }
                                        }); 
                                        carodotsitem.forEach(function(self){
                                            if(tab_index == (Number(self.dataset.tab) + Number(1))){
                                                self.classList.remove('inactive');
                                                self.classList.add('active');
                                            }
                                        }); 
                                }
                            }
                            
                            if( RBGConnection && document.querySelectorAll('#'+RBGConnection).length ){
                                background_accordion_tabs_conn(tab_index, RBGConnection);
                            }
                        }
                    });
                });
            }
            
			let aecbutton = container[0].querySelectorAll(".tp-aec-button");
			if(aecbutton.length > 0){
				 aecbutton[0].querySelector(".tp-toggle-accordion").addEventListener("click", function(e){
					var ecbtn = this,
						ectitle = container[0].querySelectorAll(".elementor-tab-title"),
						ecdesc = container[0].querySelectorAll(".elementor-tab-content");
						
						if(ecbtn.classList.contains('active')){	
							ecbtn.classList.remove('active');
						}else{
							ecbtn.classList.add('active');
						}
						
						ectitle.forEach(function(self){
							if(ecbtn.classList.contains('active')){
								self.classList.add('active');
							}else{
								self.classList.remove('active');
							}
						});
						
						ecdesc.forEach(function(self){
							if(ecbtn.classList.contains('active')){	
								self.classList.add('active');
								slideDown(self, 500)
							}else{
								self.classList.remove('active');
								slideUp(self, 500)
							}
						});
				  }); 
			}
			
			if( container[0].classList.contains('tp-acc-hori')){
				let boxwidth = container[0].offsetWidth,
					titlewidth = container[0].querySelector(".plus-accordion-header").offsetWidth,
					totalDiv = container[0].querySelectorAll(".theplus-accordion-item").length,
					totaltitleWidth = Number(totalDiv) * Number(AccordionhoriTitleWidth),
                    finalwidth = Number(boxwidth) - ( container[0].querySelector(".theplus-accordion-item").clientWidth * Number(totalDiv) );
					//finalwidth = boxwidth - totaltitleWidth;

                    let HAccordianCon = container[0].querySelectorAll(".plus-accordion-content.active"); 
                    if( HAccordianCon.length > 0 ){
                        HAccordianCon[0].style.width = `${finalwidth}px`;
                        HAccordianCon[0].querySelector(".plus-content-editor").style.cssText = `position: absolute;left: ${titlewidth}px; opacity: 1;width: ${finalwidth}px`;
                        HAccordianCon[0].parentElement.classList.add('tp-acc-disable');
                    }

					$scope.find('.theplus-accordion-item').on('click',function(){
                        container[0].querySelector('.theplus-accordion-item.tp-acc-disable').classList.remove('tp-acc-disable');
						let sib = $(this).siblings();
						if(sib){
                            this.classList.add('tp-acc-disable');
							$(this).find('.plus-content-editor').css('position','absolute').css('width',finalwidth);
							$(this).find('.plus-accordion-content').animate({width:finalwidth, opacity: 1},Accordionhoriopen);
							sib.find('.plus-accordion-content').animate({width:0,opacity: 0},Accordionhoriclose);
							sib.find('.plus-content-editor').css('width',finalwidth);
						}
					 });
			}
			
			if(container[0].classList.contains('tp-stageraccr')){
                let windowHeightt = window.innerHeight,
                    elementTopp = container[0].getBoundingClientRect().top,
                    elementVisiblee = 150;

                    if (elementTopp < windowHeightt - elementVisiblee) {
                        tp_stageraccr_scroll();
                    }else{
                        window.addEventListener("scroll", tp_stageraccr_scroll);
                    }
			}

			if(container[0].classList.contains('tp-accr-slider')){
                let slidelength = container[0].querySelectorAll(".tp-accr-list-slider");
                let Totalslide = container[0].querySelectorAll(".tpasp-total-slide");
                if( Totalslide.length > 0 ){
                    Totalslide[0].innerText = Number(slidelength.length);
                }

                let Tpaspprev = container[0].querySelectorAll(".tpasp-prev");
                if( Tpaspprev.length > 0 ){
                    Tpaspprev[0].addEventListener("click", tp_slide_prev);
                }

                let TpasNext = container[0].querySelectorAll(".tpasp-next");
                if( TpasNext.length > 0 ){
                    TpasNext[0].addEventListener("click", tp_slide_next);
                }
			}

            if(container[0].classList.contains('tp-seachaccr')){
                jQuery.expr[':'].containsCaseInsensitive = function (con, b, ser) {
                    return jQuery(con).text().toUpperCase().indexOf(ser[3].toUpperCase()) >= 0;
                };

                let liveserach = container[0].querySelectorAll('#tpsb'+AccordionId);
                    if( liveserach.length > 0 ){
                        liveserach[0].addEventListener("change", tp_live_search);
                        liveserach[0].addEventListener("keyup", tp_live_search);
                        liveserach[0].addEventListener("paste", tp_live_search);
                        liveserach[0].addEventListener("click", tp_live_search);
                    }
            }

            var AutoplaysetIn;
            if(container[0].classList.contains('tp-tab-playloop') && $TabAutoplay == "yes"){
                let PlayItem = container[0].querySelectorAll('.theplus-accordion-item');
                if( PlayItem.length > 0 ){
                    PlayItem.forEach(function(self){
                        self.classList.add('plus-tab-header');
                    });

                    let windowHeightt = window.innerHeight,
                        elementTopp = container[0].getBoundingClientRect().top,
                        elementVisiblee = 150;
        
                        if (elementTopp < windowHeightt - elementVisiblee) {
                            tp_autoplay_load();
                        }else{
                            window.addEventListener("scroll", tp_autoplay_load);
                        }
                }

                let Headerclick = container[0].querySelectorAll('.theplus-accordion-item .plus-accordion-header');
                if( Headerclick.length > 0 ){
                    Headerclick.forEach(function(self){
                        self.addEventListener("click", tp_autoplay_header_click);
                    });
                }
            }

            if(container[0].classList.contains('tp-tab-playpause-button') && $TabAutoplay == "yes"){
                let TabWrap = container[0].querySelectorAll('.tp-tab-play-pause-wrap');
                if( TabWrap.length > 0 ){
                    TabWrap[0].addEventListener("click", tp_autoplay_playpause_btn);
                }
            }

            function tp_autoplay_playpause_btn(){
                let child1 = container[0].querySelectorAll('.tp-tab-play-pause:nth-child(1)'),
					child2 = container[0].querySelectorAll('.tp-tab-play-pause:nth-child(2)');

                    if(child1[0].classList.contains('active')) {
                        child1[0].classList.remove('active');
                        child2[0].classList.add('active');
                        this.classList.add('pausecls');

                        clearInterval(AutoplaysetIn);
                    }else if (child2[0].classList.contains('active')){
                        child2[0].classList.remove('active');
                        child1[0].classList.add('active');
                        this.classList.remove('pausecls');
                        tp_autoplay_button_click();
                    }
            }

            function tp_autoplay_header_click(){
                clearInterval(AutoplaysetIn);

                let TabActive = container[0].querySelectorAll('.plus-tab-header.active');
                if( TabActive.length > 0 ){
                    TabActive[0].classList.remove('active');
                    tp_playclasslist(TabActive[0], 'remove');
                }

                if( this.parentElement ){
                    this.parentElement.classList.add('active');
                }

                if( container[0].classList.contains('tp-tab-playpause-button') ){
                    let TabWrap = container[0].querySelectorAll('.tp-tab-play-pause-wrap');
                    if( TabWrap.length > 0 ){
                        TabWrap[0].classList.remove('pausecls');

                        let child1 = container[0].querySelectorAll('.tp-tab-play-pause:nth-child(1)'),
					        child2 = container[0].querySelectorAll('.tp-tab-play-pause:nth-child(2)');
                            if(child1.length > 0){
                                child1[0].classList.remove('active');
                            }
                            if(child2.length > 0){
                                child2[0].classList.add('active');
                            }   
                    }
                }
            }
           
            function tp_autoplay_load(){
                let TabHeader = container[0].querySelectorAll('.theplus-accordion-item.plus-tab-header');
                if( TabHeader.length > 0 ){
                    TabHeader[0].classList.add('active');

                    AutoplaysetIn = setInterval(function() {
                        let TabActive = container[0].querySelectorAll('.plus-tab-header.active');
                            if( TabActive.length > 0 ){
                                TabActive[0].classList.remove('active');
                                tp_playclasslist(TabActive[0], 'remove');
                                if( TabActive[0].nextElementSibling.classList.contains('plus-tab-header') ){
                                    TabActive[0].nextElementSibling.classList.add('active')
                                    tp_playclasslist(TabActive[0].nextElementSibling, 'add');
                                }else{
                                    clearInterval(AutoplaysetIn);
                                }
                            }else{
                                clearInterval(AutoplaysetIn);
                            }
                    }, $TabAutoplayDuration * 1000);                    
                }

                window.removeEventListener("scroll", tp_autoplay_load);
            }

            function tp_autoplay_button_click(){
                let TabActive = container[0].querySelectorAll('.plus-tab-header.active');
                    if( TabActive.length > 0 ){
                        TabActive[0].classList.remove('active');
                        tp_playclasslist(TabActive[0], 'remove');

                        TabActive[0].nextElementSibling.classList.add('active')
                        tp_playclasslist(TabActive[0].nextElementSibling, 'add');
                    }
                    
                    AutoplaysetIn = setInterval(function() {
                        let ggTabActive = container[0].querySelectorAll('.plus-tab-header.active');
                            ggTabActive[0].classList.remove('active');
                            tp_playclasslist(ggTabActive[0], 'remove');
                        
                            if( ggTabActive[0].nextElementSibling.classList.contains('plus-tab-header') ){
                                ggTabActive[0].nextElementSibling.classList.add('active')
                                tp_playclasslist(ggTabActive[0].nextElementSibling, 'add');
                            }else{
                                clearInterval(AutoplaysetIn);
                            }

                    }, $TabAutoplayDuration * 1000);
            }

            function tp_playclasslist(item, type){
                if( type == 'remove' ){
                    let Header = item.children[0],
                        content = item.children[1];

                        if(Header){
                            Header.classList.remove('active-default');
                            Header.classList.remove('active');
                        }
                        if(content){
                            content.classList.remove('active-default');
                            content.classList.remove('active');
                            content.style.display = 'none';
                        }
                }else if( type == 'add' ){
                    let Headerr = item.children[0],
                        contentt = item.children[1];

                        if(Headerr){
                            tp_fadeIn(Headerr, 300)
                            Headerr.classList.add('active-default');
                            Headerr.classList.add('active');
                        }
                        if(contentt){
                            tp_fadeIn(contentt, 300)
                            contentt.classList.add('active-default');
                            contentt.classList.add('active');
                            contentt.style.display = 'block';
                        }
                }
            }

            function tp_stageraccr_scroll(){
                let windowHeight = window.innerHeight,
                    elementTop = container[0].getBoundingClientRect().top,
                    elementVisible = 150;
              
                    if (elementTop < windowHeight - elementVisible) {
                        accrodionList.forEach(function(self, idx){
                        	setTimeout(function () {
                        		tp_fadeIn(self, stagerVdAccr);
                        	}, idx * stagerGapAccr);
                        });

                        window.removeEventListener("scroll", tp_stageraccr_scroll);
                    }
            }

            function tp_slide_prev() {
                let Tabactive = container[0].querySelectorAll('.tp-accr-list-slider.tpaccractive'),
                    PrevEs = (Tabactive[0].previousElementSibling) ? Tabactive[0].previousElementSibling : '',
                    id = 0;

                    if( Tabactive.length > 0 && Tabactive[0].classList.contains('tpaccractive') ){
                        Tabactive[0].classList.remove('tpaccractive');
                    }
                    if( Tabactive.length > 0 && PrevEs ){
                        PrevEs.classList.add('tpaccractive');
                        tp_fadeIn(PrevEs, 200);
                        id = (PrevEs.dataset && PrevEs.dataset.tabslide) ? PrevEs.dataset.tabslide : 0;
                    }

                let EnableNext = container[0].querySelectorAll('.tp-aec-slide-page .tpasp-next');
                    if( EnableNext.length > 0 && EnableNext[0].classList.contains('tpas-disabled')){
                        EnableNext[0].classList.remove('tpas-disabled');
                    }
                    
                let Activeslide = container[0].querySelectorAll('.tp-aec-slide-page .tpasp-active-slide');
                    if( Activeslide.length > 0 ){
                        Activeslide[0].innerText = id;
                        Activeslide[0].style.cssText = `opacity: 0`;

                        tp_fadeIn(Activeslide[0], 220)
                    }

                    if( Number(id) == 1 ){
                        let EnablePrev = container[0].querySelectorAll('.tp-aec-slide-page .tpasp-prev');
                        if( EnablePrev.length > 0 ){
                            EnablePrev[0].classList.add('tpas-disabled');
                        }
                    }
            }

            function tp_slide_next() {
                let Tabactive = container[0].querySelectorAll('.tp-accr-list-slider.tpaccractive'),
                    NextEs = (Tabactive[0].nextElementSibling) ? Tabactive[0].nextElementSibling : '',
                    id = 0;

                    if( Tabactive.length > 0 && Tabactive[0].classList.contains('tpaccractive') ){
                        Tabactive[0].classList.remove('tpaccractive');
                    }
                    if( Tabactive.length > 0 && NextEs ){
                        NextEs.classList.add('tpaccractive');
                        tp_fadeIn(NextEs, 200);

                        id = (NextEs.dataset && NextEs.dataset.tabslide) ? NextEs.dataset.tabslide : 0;
                    }

                let EnableNext = container[0].querySelectorAll('.tp-aec-slide-page .tpasp-prev');
                    if( EnableNext.length > 0 && EnableNext[0].classList.contains('tpas-disabled')){
                        EnableNext[0].classList.remove('tpas-disabled');
                    }

                let Activeslide = container[0].querySelectorAll('.tp-aec-slide-page .tpasp-active-slide');
                    if( Activeslide.length > 0 ){
                        Activeslide[0].innerText = id;
                        tp_fadeIn(Activeslide[0], 220);
                    }

                let slidecount = container[0].querySelector(".tpasp-total-slide").innerText;
                if( Number(id) == Number(slidecount) ){
                    let EnableNext = container[0].querySelectorAll('.tp-aec-slide-page .tpasp-next');
                        if( EnableNext.length > 0 ){
                            EnableNext[0].classList.add('tpas-disabled');
                        }
                }
            }   

            function tp_live_search() {
                let searchTerm = this.value, 
                    plusContent = container[0].querySelectorAll(".theplus-accordion-item .plus-accordion-content"),
                    plusHeader = container[0].querySelectorAll(".theplus-accordion-item .plus-accordion-header");

                if (searchTerm.length >= AccordionSearchLen) {
                    jQuery('.theplus-accordion-wrapper.tp-seachaccr .theplus-accordion-item .plus-accordion-header').each(function () {
                        let headid = '#'+this.id,
                            descid = '#'+this.nextElementSibling.id;

                            this.style.cssText = `display: none`;
                            this.classList.remove('active','active-default');

                            jQuery(headid + ':not(:containsCaseInsensitive(' + searchTerm + '))').removeClass('active active-default').css("display","none");
                            jQuery(descid + ':not(:containsCaseInsensitive(' + searchTerm + '))').removeClass('active active-default').css("display","none");

                            jQuery(headid + ':containsCaseInsensitive(' + searchTerm + ')').addClass('active').css("display","flex");                            
                            jQuery(descid+ ':containsCaseInsensitive(' + searchTerm + ')').addClass('active').css("display","flex");
                    });
                    
                    jQuery('.theplus-accordion-wrapper.tp-seachaccr .theplus-accordion-item .plus-accordion-content').each(function () {
                        let deccon = '#'+this.id,
                            headcon = '#'+this.previousElementSibling.id;

                            this.style.cssText = `display: none`;
                            this.classList.remove('active','active-default');
                        
                            jQuery(deccon + ':not(:containsCaseInsensitive(' + searchTerm + '))').removeClass('active active-default').css("display","none");
                            jQuery(headcon + ':not(:containsCaseInsensitive(' + searchTerm + '))').removeClass('active active-default').css("display","none");

                            jQuery(deccon + ':containsCaseInsensitive(' + searchTerm + ')').addClass('active').css("display","flex");
                            jQuery(headcon+ ':containsCaseInsensitive(' + searchTerm + ')').addClass('active').css("display","flex");
                    });

                    if( plusContent.length > 0 ){
                        plusContent.forEach(function(content){
                            if( content.classList.contains('active') ){
                                content.previousElementSibling.style.cssText = `display: flex`;
                                content.previousElementSibling.classList.add('active')
                            }
                        });
                    }
                    if( plusHeader.length > 0 ){
                        plusHeader.forEach(function(header){
                            if( header.classList.contains('active') ){
                                header.nextElementSibling.style.cssText = `display: flex`;
                                header.nextElementSibling.classList.add('active')
                            }
                        });
                    }
                }else {
                    if( plusContent.length > 0 ){
                        plusContent.forEach(function(content){
                            content.style.cssText = `display: none`;
                            content.classList.remove('active', 'active-default');
                        });
                    }
                    if( plusHeader.length > 0 ){
                        plusHeader.forEach(function(header){
                            header.style.cssText = `display: flex`;
                            header.classList.remove('active', 'active-default');
                        });
                    }	 
                }
            }

			function tp_fadeIn(element, duration=600) {
				element.style.display = '';
				element.style.opacity = 0;
				var last = +new Date();
				var tick = function() {
					element.style.opacity = +element.style.opacity + (new Date() - last) / duration;
					last = +new Date();
					if (+element.style.opacity < 1) {
						(window.requestAnimationFrame && requestAnimationFrame(tick)) || setTimeout(tick, 16);
					}
				};
				tick();
			}
			
            function slideUp (target, duration=500){
                target.style.transitionProperty = 'height, margin, padding';
                target.style.transitionDuration = duration + 'ms';
                target.style.boxSizing = 'border-box';
                target.style.height = target.offsetHeight + 'px';
                target.offsetHeight;
                target.style.overflow = 'hidden';
                target.style.height = 0;
                target.style.paddingTop = 0;
                target.style.paddingBottom = 0;
                target.style.marginTop = 0;
                target.style.marginBottom = 0;
                window.setTimeout( () => {
                    target.style.display = 'none';
                    target.style.removeProperty('height');
                    target.style.removeProperty('padding-top');
                    target.style.removeProperty('padding-bottom');
                    target.style.removeProperty('margin-top');
                    target.style.removeProperty('margin-bottom');
                    target.style.removeProperty('overflow');
                    target.style.removeProperty('transition-duration');
                    target.style.removeProperty('transition-property');
                }, duration);
            }

            function slideDown (target, duration=500){
                target.style.removeProperty('display');
                let display = window.getComputedStyle(target).display;
                if (display === 'none') display = 'block';
                    target.style.display = display;
                    let height = target.offsetHeight;
                        target.style.overflow = 'hidden';
                        target.style.height = 0;
                        target.style.paddingTop = 0;
                        target.style.paddingBottom = 0;
                        target.style.marginTop = 0;
                        target.style.marginBottom = 0;
                        target.offsetHeight;
                        target.style.boxSizing = 'border-box';
                        target.style.transitionProperty = "height, margin, padding";
                        target.style.transitionDuration = duration + 'ms';
                        target.style.height = height + 'px';
                        target.style.removeProperty('padding-top');
                        target.style.removeProperty('padding-bottom');
                        target.style.removeProperty('margin-top');
                        target.style.removeProperty('margin-bottom');
                        window.setTimeout( () => {
                        target.style.removeProperty('height');
                        target.style.removeProperty('overflow');
                        target.style.removeProperty('transition-duration');
                        target.style.removeProperty('transition-property');
                }, duration);
            }
	};

    window.addEventListener('elementor/frontend/init', (event) => {
        elementorFrontend.hooks.addAction('frontend/element_ready/tp-accordion.default', WidgetAccordionHandler);
    });
})(jQuery);