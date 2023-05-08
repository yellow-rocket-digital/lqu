/* Search Filter */
(function($) {
	"use strict";
	var ajaxfilterMap = new Map();	
	var WidgetSearchFilterHandler = function($scope, $) {
		let container = $scope[0].querySelectorAll('.tp-search-filter'),
			form = container[0].querySelectorAll('.tp-search-form'),
			tagHandle = document.querySelector('.tp-filter-tag-wrap'),
			TagHandle = document.querySelectorAll('.tp-filter-tag-wrap'),
			connId = (container[0].dataset.basic) ? container[0].dataset.connection : 'tp_list',
			basic = (container[0].dataset.basic) ? JSON.parse(container[0].dataset.basic) : [],
			delayload = (basic && basic.delayload) ? Number(basic.delayload) * Number(1000) : Number(300),
			// enablearchive = (basic && basic.enablearchive) ? basic.enablearchive : 'no',
			fieldValue = {},
			$Increment = new Array(),
			$IncrementLoad = new Array();
    
		let	option=[];
		let seaList = document.querySelectorAll('.'+connId);
			seaList.forEach(function(item, index) {
				option[index] = (item.dataset.searchattr) ? JSON.parse(item.dataset.searchattr) : [];
				option[index]['filtertype'] = 'search_list';
				option[index]['ajaxButton'] = 1;
				// option[index]['enablearchive'] = enablearchive;
			});

		if(basic && basic.URLParameter){
			window.onload = function () {
				let url = new URL(window.location.href);
				if(url && url.search){
					let params = new URLSearchParams(url.search);

					form.forEach(function(self) {
						let seafield = (self && self.dataset && self.dataset.field) ? JSON.parse(self.dataset.field) : '',
							Filter_Tags=[];

							if(seafield.search){
								let Getsearch = document.querySelectorAll('.tp-search-input');
								if(Getsearch.length > 0){
									Getsearch.forEach(function(input,idx){
										let Geturl = (params) ? params.get('tp-search') : '';
										if(Geturl){
											Geturl.split(",").forEach(function(item1, idx1){
												input.value = item1;
											});
										}
									});
									fieldValue = inputhandle(seafield.search, Getsearch, Filter_Tags)
								}
							}

							if(seafield.alphabet){
								let GetHtml = document.querySelectorAll('.tp-alphabet-wrapper');
								if(GetHtml.length > 0){
									GetHtml.forEach(function(item, idx){
										let Geturl = (params) ? params.get('tp-alphabet') : '';
										if(Geturl){
											Geturl.split(",").forEach(function(item1, idx1){
												if( GetHtml[idx].querySelectorAll(`#tp-${item1}`).length > 0 ){
													if(GetHtml[idx].querySelector(`#tp-${item1}`).parentNode ){
														GetHtml[idx].querySelector(`#tp-${item1}`).parentNode.classList.add('active');
													}

													GetHtml[idx].querySelector(`#tp-${item1}`).checked = 1;
												}
											});
											fieldValue = alphabethandle(seafield.alphabet, Filter_Tags)
										}
									});
								}
							}

							if(seafield.checkBox){
								let GetHtml = document.querySelectorAll('.tp-wp-checkBox');
								if(GetHtml.length > 0){
									GetHtml.forEach(function(item,idx){
										let TPPrefix = (item && item.dataset && item.dataset.tpprefix) ? item.dataset.tpprefix : '',
											Geturl = (params) ? params.get(`checkbox-${TPPrefix.split("-")[1]}`) : '';	

											if(Geturl){
												Geturl.split(",").forEach(function( item1, idx1 ){
													if( GetHtml[idx].querySelectorAll('#'+ TPPrefix + item1).length > 0 ){
														GetHtml[idx].querySelector('#'+ TPPrefix + item1).checked = 1;
													}
												})
												fieldValue = checkBoxhandle( seafield.checkBox, Filter_Tags )
											}
									});
								}
							}

							if(seafield.radio){
								let GetHtml1 = document.querySelectorAll('.tp-wp-radio');
								if(GetHtml1.length > 0){
									GetHtml1.forEach(function(item,idx){
										let TPPrefix = (item && item.dataset && item.dataset.tpprefix) ? item.dataset.tpprefix : 'tp-',
											Geturl = (params) ? params.get(`radio-${TPPrefix.split("-")[1]}`) : '';

											if(Geturl){
												Geturl.split(",").forEach(function(item1){
													if( GetHtml1[idx] && GetHtml1[idx].querySelectorAll(`#${TPPrefix+item1}`).length > 0 ){
														GetHtml1[idx].querySelector(`#${TPPrefix+item1}`).checked = 1;
													}
												})
												fieldValue = radioHandler(seafield.radio, Filter_Tags)
											}
									});
								}
							}

							if(seafield.select){
								let GetHtml = document.querySelectorAll('.tp-select');
								if(GetHtml.length > 0){
									GetHtml.forEach(function(item,idx){
										let Name = (seafield.select[idx] && seafield.select[idx].name) ? seafield.select[idx].name : '',
											Geturl = (Name) ? params.get('select_'+Name+'_'+idx) : '';

											if(Geturl){
												let GetId = document.getElementById(Geturl),
													GetTxt = GetId.querySelector('.tp-dd-labletxt').textContent,
													getinput = item.querySelector('input'),
													getSpan = item.querySelector('.tp-select-dropdown span');
													
													getinput.value = Geturl;
													getinput.dataset.txtval = GetTxt;
													getSpan.textContent = GetTxt;
													
													fieldValue = selectHandler( seafield.select, Filter_Tags )
											}
									});
								}
							}

							if(seafield.date){
								let GetHtml = document.querySelectorAll('.tp-date-wrap');
								if(GetHtml.length > 0){
									GetHtml.forEach(function(datefield, idx){
										let layout = (seafield.date[idx] && seafield.date[idx].layout) ? seafield.date[idx].layout : '';
	
										if(layout == "style-1"){
											let GetDate = params.get('date');
												if(GetDate){
													let DateVal = GetDate.split(",");
														GetHtml[0].querySelector('.tp-date .tp-datepicker1').value = DateVal[0];
														GetHtml[0].querySelector('.tp-date1 .tp-datepicker1').value = DateVal[1];
												}
											fieldValue = dateHandler(seafield.date, Filter_Tags)
										}else if(layout == "style-2"){
											let GetDate = params.get('date'),
												DateInput = datefield.querySelectorAll(".tp-datepicker1");
												if( GetDate && DateInput.length > 0 ){
													let DateVal = GetDate.split(",");
														datefield.querySelector(".tp-datepicker1").setAttribute('value', DateVal[0] + '-' + DateVal[1] );
												}
											fieldValue = dateHandler(seafield.date, Filter_Tags)
										}

									});
								}
							}

							if(seafield.color){
								let GetHtml = document.querySelectorAll('.tp-woo-color');
								if(GetHtml.length > 0){
									GetHtml.forEach(function(item, idx){
										let TPPrefix = (item && item.dataset && item.dataset.tpprefix) ? item.dataset.tpprefix : '',
											Geturl = (params) ? params.get(`tp-${TPPrefix.split("-")[1]}`) : '';
											if(Geturl){
												Geturl.split(",").forEach(function( item1, idx1 ){
													if( GetHtml[idx].querySelectorAll(`#${TPPrefix+item1}`).length > 0 ){
														GetHtml[idx].querySelector(`#${TPPrefix+item1}`).checked = 1;
													}
												})
												fieldValue = WooHandle(seafield.color, Filter_Tags)
											}
									});
								}
							}

							if(seafield.image){
								let GetHtml = document.querySelectorAll('.tp-woo-image');
								if(GetHtml.length > 0){
									GetHtml.forEach(function(item, idx){
										let TPPrefix = (item && item.dataset && item.dataset.tpprefix) ? item.dataset.tpprefix : '',
											Geturl = (params) ? params.get(`tp-${TPPrefix.split("-")[1]}`) : '';
											if(Geturl){
												Geturl.split(",").forEach(function( item1, idx1 ){
													if( GetHtml[idx].querySelectorAll(`#${TPPrefix+item1}`).length > 0 ){
														GetHtml[idx].querySelector(`#${TPPrefix+item1}`).checked = 1;
													}
												})
												fieldValue = WooHandle(seafield.image, Filter_Tags)
											}
									});
								}
							}

							if(seafield.button){
								let GetHtml = document.querySelectorAll('.tp-woo-button');
								if(GetHtml.length > 0){
									GetHtml.forEach(function(item, idx){
										let TPPrefix = (item && item.dataset && item.dataset.tpprefix) ? item.dataset.tpprefix : '',
											Geturl = (params) ? params.get(`tp-${TPPrefix.split("-")[1]}`) : '';
											if(Geturl){
												Geturl.split(",").forEach(function( item1, idx1 ){
													if( GetHtml[idx].querySelectorAll(`#${TPPrefix+item1}`).length > 0 ){
														GetHtml[idx].querySelector(`#${TPPrefix+item1}`).checked = 1;
													}
												})
												fieldValue = WooHandle( seafield.button, Filter_Tags )
											}
									});
								}
							}

							if(seafield.rating){
								let GetHtml = document.querySelectorAll('.tp-star-rating');
								if(GetHtml.length > 0){
									GetHtml.forEach(function(item, idx){
										let TPPrefix = (item && item.dataset && item.dataset.tpprefix) ? item.dataset.tpprefix : '',
											Geturl = (params) ? params.get(`tp-${TPPrefix.split("-")[1]}`) : '';
										if(Geturl){
											Geturl.split(",").forEach(function( item1, idx1 ){
												if( GetHtml[idx].querySelectorAll(`#${TPPrefix+item1}`).length > 0 ){
													GetHtml[idx].querySelector(`#${TPPrefix+item1}`).checked = 1;
												}
											})
											fieldValue = WooHandle( seafield.rating, Filter_Tags )
										}
									});
								}
							}

							if(seafield.tabbing){
								let GetHtml = $scope[0].querySelectorAll('.tp-tabbing');
								if(GetHtml.length > 0){
									GetHtml.forEach(function(item, idx){
										let Name = (seafield.tabbing[idx] && seafield.tabbing[idx].name) ? seafield.tabbing[idx].name : '',
											Geturl = (Name) ? params.get('tab_'+Name+'_'+idx) : '';
											if(Geturl){
												Geturl.split(",").forEach(function(item1){
													GetHtml[idx].querySelector(`#tp-${item1}`).checked = 1;
												});
												fieldValue = WooHandle(seafield.tabbing, Filter_Tags)
											}
									});
								}
							}

							if(seafield.autocomplete){
								let Getsearch = document.querySelectorAll('#tp-autocomplete-input');
								if(Getsearch.length > 0){
									Getsearch.forEach(function(self, idx){
										let Name = self.getAttribute("name"),
											Geturl = (params && Name) ? params.get(`tp-${Name}`) : '';
											if(Geturl){
												self.value = Geturl;
											}
									});
									// fieldValue = MapHandle(seafield.autocomplete, Filter_Tags);
								}
							}

							if(TagHandle.length > 0){
								TagHandle.forEach(function(item) {
									item.innerHTML = Filter_Tags.join(' ');
                                    RemoveTagHandle('create');
								});
							}

							ajaxHandler(fieldValue);
					});

				}
			}
		}

		MomentDate()
		Autocomplete()

		let ArchiMore = document.querySelectorAll('.tp-archive-readmore');
			if(ArchiMore.length > 0){
				ArchiMore.forEach(function(self) {
					self.addEventListener("click", function(){
						if( this.parentElement ){
							let Getfield = this.parentElement.querySelectorAll('.tp-archive-hidden');
							if( Getfield.length > 0 ){
								Getfield.forEach(function(item) {
									if( item.classList.contains('tp-archive-hidden') ){
										item.classList.remove('tp-archive-hidden');
									}
								});
								this.remove();
							}
						}	
					});
				});
			}

		let GetAllGrid = document.querySelectorAll('.grid-item');
			if(GetAllGrid.length > 0){
				let GetTR = document.querySelectorAll('.tp-total-results-txt'),
					total = (seaList[0] && seaList[0].dataset && seaList[0].dataset.totalResult) ? seaList[0].dataset.totalResult : '';
				if(GetTR.length > 0){
					GetTR.forEach(function(self, index) {
						let One = self.previousElementSibling.textContent.replaceAll('{visible_product_no}', GetAllGrid.length),
							Two = One.replaceAll('{total_product_no}', total);
                            self.style.cssText = "display:block;";
							self.innerHTML = Two;
					})
				}
			}

		let Redmore = container[0].querySelectorAll('.tp-filter-readmore');
			if(Redmore.length > 0){
				Redmore.forEach(function(self) {
					let DataValue = self.dataset.showmore ? JSON.parse(self.dataset.showmore) : '',
						showOn = DataValue.ShowOn,
						Number = DataValue.ShowValue,
						classname = DataValue.className,
						txtMore = DataValue.ShowMoretxt,
						txtLess = DataValue.Showlesstxt,
                        SrlClass = DataValue.ScrollclassName,
                        SrlOn = DataValue.scrollOn,
                        Srlheight = DataValue.scrollheight,
						ParentData = self.parentElement.parentNode,
						ClassName = self.parentElement.previousElementSibling.className.split(" ")[0],
						showClick = ParentData.querySelectorAll('.'+ClassName);

						if(showOn == 'yes'){						
							showClick.forEach(function(one, index){
								if( index < Number ){
									if( classname == 'tabbing' ){
										one.style.cssText = "display:inline-flex;";
									}else{
										one.style.cssText = "display:block;";
									}
								}else{
									one.style.cssText = "display:none;";
								}
							})

							self.addEventListener("click", function(){
								if( this.classList.contains('ShowMore') ){
									showClick.forEach(function(one) {
										if( classname == 'tabbing' ){
											one.style.cssText = "display:inline-flex;";
										}else{
											one.style.cssText = "display:block;";
										}
									})
									this.innerHTML = txtLess;
									this.classList.add('Showless');
									this.classList.remove('ShowMore');

                                    if(SrlOn){
                                        let Perentclass = this.parentNode.parentNode;
                                            Perentclass.classList.add(SrlClass);
                                            $(Perentclass).css('height', Srlheight);
                                    }
								}else if( this.classList.contains('Showless') ){
									showClick.forEach(function(one, index) {
										if( index < Number ){
											if( classname == 'tabbing' ){
												one.style.cssText = "display:inline-flex;";
											}else{
												one.style.cssText = "display:block;";
											}
										}else{
											one.style.cssText = "display:none;";
										}
									})
									this.innerHTML = txtMore;
									this.classList.add('ShowMore');
									this.classList.remove('Showless');

                                    if(SrlOn){
                                        let Perentclass = this.parentNode.parentNode;
                                            Perentclass.classList.remove(SrlClass);
                                            $(Perentclass).css('height', '');
                                    }
								}
							})
						}
				})
			}

		let toggle = container[0].querySelectorAll('.tp-field-title');
			if(toggle.length > 0){
				toggle.forEach(function(self) {
					let DataValue = (self && self.dataset.showdata) ? JSON.parse(self.dataset.showdata) : '',
						ToggleOn = (DataValue) ? DataValue.ToggleOn : '',
						DataVal = (DataValue) ? DataValue.DefaultValue : '',
						NextClass = (self) ? self.nextSibling : '',
						GetIcon = (NextClass.childNodes.length && NextClass.querySelectorAll('.tp-search-icon').length ) ? NextClass.querySelectorAll('.tp-search-icon') : '';

						if(DataVal == 0 && ToggleOn == 1){
							$(self.nextElementSibling).slideToggle(400);
							$(self.querySelector('.tp-toggle-up')).slideToggle(0);
							$(self.querySelector('.tp-toggle-down')).slideToggle(0);
						}

						self.addEventListener("click", function(){
							if(ToggleOn){
								if( NextClass.classList.contains('tp-search-wrap') ) {
									if(GetIcon.length > 0){
										if(NextClass.style.display != 'none'){										
												GetIcon[0].style.cssText = "opacity:0;transform: translateY(-50%) translateX(-10px);";
										}else{
											setTimeout(function(){ 
												GetIcon[0].style.cssText = "opacity:1;transform: translateY(-50%) translateX(0px);transition: all 0.3s linear;";
											}, 500);
										}
									}
								}

								let upicon = this.querySelector('.tp-toggle-up'),
									downicon = this.querySelector('.tp-toggle-down');
									$(this.nextElementSibling).slideToggle(500);
									$(upicon).slideToggle(0);
									$(downicon).slideToggle(0);
							}
						});
				});
			}

		let BtnFilter = container[0].querySelectorAll('.tp-button-filter');
			if(BtnFilter.length > 0){
				let data = JSON.parse(BtnFilter[0].dataset.buttonFilter),
					Switcher = data.Switcher,
					num = data.Number,
					Showmore = data.showmore,
					Showless = data.showless,
					TabGrid = BtnFilter[0].parentNode.querySelectorAll('.field-col'),
					Btnclick = BtnFilter[0].querySelector('.tp-toggle-button');
			
					TabGrid.forEach(function(self, index) {
						if( index >= num ){
							self.style.cssText = "display:none";
						}
					})

					Btnclick.addEventListener("click", function(){
						let Findtxt = this.querySelectorAll('.tp-button-text');
						if( this.classList.contains('active') ){
							if(Findtxt.length > 0){
								Findtxt[0].textContent = Showmore;
							}
							this.classList.remove('active');
						}else{
							if(Findtxt.length > 0){
								Findtxt[0].textContent = Showless;
							}
							this.classList.add('active');
						}

						TabGrid.forEach(function(self, index) {
							if( index >= num ){								
								$(self).slideToggle(500);
							}
						})
					})
			}

		let childto = container[0].querySelectorAll('.tp-toggle');
			if(childto.length > 0){
				childto.forEach(function(self){
					self.addEventListener('click', function(){
						$(this.parentElement.parentElement.nextElementSibling).slideToggle(400);
						if(this.classList.contains('open')){
							this.classList.remove('open');
						}else{
							this.classList.add('open');
						}
					})
				})
			}

		let GetDropDown = container[0].querySelectorAll('.tp-select');
			if(GetDropDown.length > 0){
				$('.tp-select', $scope).on('click',function () {
					$(this).attr('tabindex', 1).focus();
					$(this).toggleClass('active');
					$(this).find('.tp-sbar-dropdown-menu').slideToggle(300);
				});
				$('.tp-select', $scope).focusout(function () {
					$(this).removeClass('active');
					$(this).find('.tp-sbar-dropdown-menu').slideUp(300);
				});
				$('.tp-select .tp-sbar-dropdown-menu .tp-searchbar-li', $scope).on('click',function () {
					this.parentNode.parentNode.querySelector('input').dataset.txtval = $(this).find('.tp-dd-labletxt').text()
					$(this).parents('.tp-select').find('span').text( $(this).text() );
					$(this).parents('.tp-select').find('input').attr('value', $(this).attr('id') ).change();
				});
			}

		let columnclass = document.querySelectorAll('.tp-column-label');
			if(columnclass.length > 0){
				columnclass.forEach(function(self) {
					self.addEventListener("click", function(){
						let GetActive = self.parentNode.querySelectorAll('.tp-column-label.active')
							GetActive.forEach(function(active) {
								active.classList.remove('active');
							});
								self.classList.add('active');

						let colvalue = self.querySelector('.tp-column-input').value;
						if(colvalue){
							seaList.forEach(function(item, index) {
								let Tempdata = JSON.parse(item.dataset.searchattr);

									Tempdata['desktop-column'] = colvalue;
									Tempdata['mobile-column'] = colvalue;
									Tempdata['tablet-column'] = colvalue;
									item.setAttribute( 'data-searchattr' , JSON.stringify(Tempdata));

									option[index]['desktop-column'] = colvalue;
									option[index]['mobile-column'] = colvalue;
									option[index]['tablet-column'] = colvalue;

									let GetGrid = document.querySelectorAll('.grid-item');
									if(GetGrid.length > 0){
										GetGrid.forEach(function(Grid) {
											Grid.classList.forEach(function(data) {
												let classFind = data.substring(0, data.length - 1);
													if(classFind == 'tp-col-lg-' || classFind == 'tp-col-lg-1'){
														Grid.classList.remove(data);
													}else if(classFind == 'tp-col-md-' || classFind == 'tp-col-md-1'){
														Grid.classList.remove(data);
													}else if(classFind == 'tp-col-sm-' || classFind == 'tp-col-sm-1'){
														Grid.classList.remove(data);
													}else if(classFind == 'tp-col-' || classFind == 'tp-col-1'){
														Grid.classList.remove(data);
													}

													Grid.classList.add("tp-col-lg-"+colvalue, "tp-col-md-"+colvalue, "tp-col-sm-"+colvalue, "tp-col-"+colvalue );
											});
										});

										Resizelayout(option)
									}
							});
						}
					}, false);
				});
			}

		let Ajax_Button = 1;
		let ajaxbtn = container[0].querySelectorAll('.tp-ajax-button');
			if(basic && basic.AjaxButton && ajaxbtn.length > 0){
				seaList.forEach(function(item, index) {
					option[index].ajaxButton = 0;
					Ajax_Button = 0;
				});

				ajaxbtn.forEach(function(self) {
					self.addEventListener("click", function(){
						AjaxButtonHandle('BeforeAjax', this);
						$(this).change();
					});
				});
			}

		let checkBoxclick = container[0].querySelectorAll(`.tp-checkBox .tp-group input`);
			if( checkBoxclick.length ){
				checkBoxclick.forEach(function(Pitem){
					Pitem.addEventListener("click", function(el){
						let closetParent = el.target.closest('.tp-group'),
							SubAll = closetParent.parentElement.querySelectorAll(`.tp-child-taxo input`);
							
							if( !el.currentTarget.checked ){
								el.target.classList.remove('tp-active-perent');
								if( SubAll.length ){
									SubAll.forEach(function(item){
										item.checked = false;
									});
								}
							}else{
								el.target.classList.add('tp-active-perent');
								if( SubAll.length ){
									SubAll.forEach(function(item){
										item.checked = true;
									});
								}
							}
					});
				});
			}

			if(form.length > 0){
				let PriceRange = form[0].querySelectorAll('.tp-range');
				if( PriceRange.length > 0 ){
					PriceRange.forEach(function(range,index) { 
						let rangeattr = JSON.parse(range.dataset.sliderattr),
							Field = (rangeattr && rangeattr.field) ? rangeattr.field : '';

							noUiSlider.create(range, {
								start: [ 0, parseInt(rangeattr.maxValue) ],
								connect: true,
								tooltips: true,
								step: parseInt(rangeattr.stepValue),
								range: { 
									'min': parseInt(rangeattr.minValue), 
									'max': parseInt(rangeattr.maxValue),
								},
								format: {
									from: function(value) {
										return parseInt(value);
									},
									to: function(value) {
										return parseInt(value);
									}
								}
							}, true).on('change', function (values, handle) { 
								let name = (rangeattr && rangeattr.field) ? rangeattr.field : '',
									PriceSymbol = (rangeattr && rangeattr.pricesymbol) ? rangeattr.pricesymbol : '',
									val1 = ( values && values[0] ) ? Math.floor(values[0]) : 0,
									val2 = ( values && values[1] ) ?  Math.floor(values[1]) : '';

									fieldValue[name] = rangeattr;
									if(values){
										fieldValue[name]['value'] = values;

										if(basic && basic.URLParameter){
											let Urldata = Math.floor(values[0]) + ',' + Math.floor(values[1])
											urlHandler(name+index, Urldata)
										}

										let TagVal = `${PriceSymbol} ${val1} - ${PriceSymbol} ${val2}`,
											TagName = FilterTagTitle( Field, PriceRange[index], TagVal);

										if(Ajax_Button){
                                            ajaxHandler(fieldValue, range)
										}

                                        if(tagHandle !== null){
                                            let priceTag = ('<div class="tp-filter-container"><a class="tp-tag-link" data-type="range" data-name="'+rangeattr.name+'" data-id="'+values+'"><span class="tp-filter-tag"> <i class="fa fa-times" aria-hidden="true"></i> '+TagName+'</span></a>'),
                                                Findclass = tagHandle.querySelectorAll('.tp-tag-link[data-type=range]'),
                                                GetTag = document.querySelectorAll('.tp-filter-removetag');
												
                                                if(Findclass.length > 0){
                                                    Findclass[0].parentNode.remove();
                                                }

                                                if(GetTag.length > 0){
                                                    if(GetTag[0].classList.contains('start')){
                                                        $(tagHandle).append(priceTag)
                                                    }else if(GetTag[0].classList.contains('end')){
                                                        $(tagHandle).prepend(priceTag)
                                                    }

                                                    RemoveTagHandle('create')
                                                }else{
													$(tagHandle).append(priceTag)
												}
                                        }
									}
							});

                            mergeTooltips(range, 15, ' - ');   
					});	
				}

				form.forEach(function(self) {
					let seafield = (self && self.dataset && self.dataset.field) ? JSON.parse(self.dataset.field) : [];
					
						$(form).change(function() {
							let Filter_Tags=[];
                            
                            if(Ajax_Button){
                                tpgbSkeleton_filter('visible');
                            }

							setTimeout(function() {
								let search = self.querySelectorAll('.tp-search-input');
								if(search.length > 0){
									fieldValue = inputhandle(seafield.search, search, Filter_Tags)
								}	

								if(seafield.alphabet){
									fieldValue = alphabethandle(seafield.alphabet, Filter_Tags)
								}

								if(seafield.checkBox){
									fieldValue = checkBoxhandle( seafield.checkBox, Filter_Tags )
								}

								if(seafield.radio){
									fieldValue = radioHandler( seafield.radio, Filter_Tags )
								}

								if(seafield.select){
									fieldValue = selectHandler( seafield.select, Filter_Tags )
								}

								if(seafield.date){
									fieldValue = dateHandler(seafield.date, Filter_Tags)
								}

								if(seafield.color){
									fieldValue = WooHandle( seafield.color, Filter_Tags )
								}

								if(seafield.image){
									fieldValue = WooHandle( seafield.image, Filter_Tags )
								}

								if(seafield.button){
									fieldValue = WooHandle( seafield.button, Filter_Tags )
								}

								if(seafield.tabbing){
									fieldValue = WooHandle(seafield.tabbing, Filter_Tags)
								}

								if(seafield.rating){
									fieldValue = WooHandle(seafield.rating, Filter_Tags)
								}
								
								if(seafield.autocomplete){
									fieldValue = MapHandle(seafield.autocomplete, Filter_Tags);
								}
								
								if(PriceRange.length > 0){
									PriceRange.forEach(function(range,index) { 
										let rangeattr = (range && range.dataset && range.dataset.sliderattr) ? JSON.parse(range.dataset.sliderattr) : '',
											MinValue = (rangeattr && rangeattr.minValue) ? Number(rangeattr.minValue) : '',
											MaxValue = (rangeattr && rangeattr.maxValue) ? Number(rangeattr.maxValue) : '',
                                            PriceSymbol = (rangeattr && rangeattr.pricesymbol) ? rangeattr.pricesymbol : '',
                                            Field = (rangeattr && rangeattr.field) ? rangeattr.field : '',
											OrgMin =  range.querySelectorAll('.noUi-handle-lower'),
											OrgMax = range.querySelectorAll('.noUi-handle-upper');

											let currentMin=MinValue,
												currentMax=MaxValue;
											if(OrgMin.length > 0 && OrgMin[0].ariaValueNow){
                                                currentMin = Number(OrgMin[0].ariaValueNow);
											}
											if(OrgMax.length > 0 && OrgMax[0].ariaValueNow){
                                                currentMax = Number(OrgMax[0].ariaValueNow);
											}

                                            let TagVal = `${PriceSymbol} ${currentMin} - ${PriceSymbol} ${currentMax}`,
											    TagName = FilterTagTitle( Field, PriceRange[index], TagVal);
                                            if(currentMin || currentMax){
												Filter_Tags.push(`<div class="tp-filter-container"><a class="tp-tag-link" data-type="range" data-name="range" data-id="price"><span class="tp-filter-tag"> <i class="fa fa-times" aria-hidden="true"></i> ${TagName} </span></a>`);
											}
									});
								}

								if(TagHandle.length > 0){
									TagHandle.forEach(function(item) {
										item.innerHTML = Filter_Tags.join("");
										RemoveTagHandle('create')
									});
								}

								if(Ajax_Button){
									ajaxHandler(fieldValue);
								}
							}, 400)
						});
				});
			}

		var MapHandle = function(data, Filter_Tags){
			data.forEach(function(self) {
				let Name = self.name ? self.name : '',
					Field = self.field ? self.field : '',
					GetHtml = document.querySelectorAll(`input[name="${Name}"]`);
                    
					fieldValue[Field] = self;
					if(GetHtml.length > 0){
						let Getdata = (GetHtml[0] && GetHtml[0].dataset && GetHtml[0].dataset.location) ? JSON.parse(GetHtml[0].dataset.location) : '';
							if(Getdata){
								let Geovalue = (Getdata && Getdata.geo && Getdata.geo.toString()) ? Getdata.geo.toString() : ""; 
									fieldValue[Field]['locationdata'] = Getdata;
									fieldValue[Field]['value'] = new Array();
									fieldValue[Field]['value'].push( Geovalue );

								FilterTagHTML(Getdata.name, Name, Getdata.name, Filter_Tags, 'autocomplete');
							}
  
							if(basic.URLParameter){
								let Urldata = ( Getdata && Getdata.fullAddress ) ? Getdata.fullAddress : '';
								urlHandler( `tp-${Name}`, Urldata )
							}
					}
			});

			return fieldValue;
		}

		var WooHandle = function(check, checkList){	
			check.map(function(item, index){
				let Name = item.name ? item.name : '',
					Type = item.field ? item.field : '';
				let tagtype='',
					GetHtml='',
					chidden='';
				if(Name){
					if(Type == 'tabbing' || Type == 'woo_SgTabbing'){
						tagtype = 'tabbing';
						GetHtml = $scope[0].querySelectorAll('.tp-tabbing');
						chidden = (GetHtml && GetHtml[index]) ? GetHtml[index].querySelectorAll(`input[name="${Name}"]:checked`) : '';
						TabbingHandle(GetHtml,index)
					}else if(Type == 'color'){
						tagtype = 'color';
						GetHtml = $scope[0].querySelectorAll('.tp-woo-color');
						chidden = (GetHtml && GetHtml[index]) ? GetHtml[index].querySelectorAll(`input[name="${Name}"]:checked`) : '';
					}else if(Type == 'image'){
						tagtype = 'image';
						GetHtml = $scope[0].querySelectorAll('.tp-woo-image');
						chidden = (GetHtml && GetHtml[index]) ? GetHtml[index].querySelectorAll(`input[name="${Name}"]:checked`) : '';
						
						DuplicateCheck(Type, chidden, Name);
					}else if(Type == 'button'){
						tagtype = 'button';
						GetHtml = $scope[0].querySelectorAll('.tp-woo-button');
						chidden = (GetHtml && GetHtml[index]) ? GetHtml[index].querySelectorAll(`input[name="${Name}"]:checked`) : '';
					}else if(Type == 'rating'){
						tagtype = 'rating';
						GetHtml = $scope[0].querySelectorAll('.tp-star-rating');
						chidden = (GetHtml && GetHtml[index]) ? GetHtml[index].querySelectorAll(`input[name="${Name}"]:checked`) : '';
					}else{
						// tagtype = 'checkbox';
						// chidden = $scope[0].querySelectorAll("input[name='"+Name+"']:checked");
					}

					fieldValue[Name] = item;
					fieldValue[Name]['value'] = new Array();
				
					let Urldata='';
					if(chidden.length > 0){
						chidden.forEach(function(item1){
							let GetTag = (item1) ? item1.getAttribute("data-title") : '',
								TagName = FilterTagTitle( Type, GetHtml[index], GetTag);
							
							fieldValue[Name]['value'].push(item1.value);
							FilterTagHTML(item1.value, Name, TagName, checkList, tagtype);
						});

						Urldata = fieldValue[Name]['value'].toString();
					}

					if(basic.URLParameter){
						if(Type == 'tabbing' || Type == 'woo_SgTabbing'){
							urlHandler('tab_'+Name+'_'+index, Urldata)
						}else if(Type == 'rating'){
							urlHandler(`tp-${Name}`, Urldata)
						}else{
							urlHandler(`tp-${Name}`, Urldata)
						}
					}
				}
			});

			return fieldValue;
		}

		var alphabethandle = function(check, checkList){
			let GetHtml = $scope[0].querySelectorAll('.tp-alphabet-wrapper');

			check.forEach(function(field, index){
				let Name = field.name ? field.name : '',
					Field = field.field ? field.field : '';

					if(Name){
						let chidden = GetHtml[index].querySelectorAll(`input[name="${Name}"]:checked`),
                            alphabetAtv = GetHtml[index].querySelectorAll('.tp-alphabet-item.active');

                            fieldValue[Name] = field;
                            fieldValue[Name]['value'] = new Array();

                            if(alphabetAtv.length > 0){
                                alphabetAtv.forEach(function(self){                                    
                                    self.classList.remove('active');
                                });
                            }

                            if(chidden.length > 0){
                                chidden.forEach(function(item){
									let TagVal = item.getAttribute("data-title"),
										TagName = FilterTagTitle( Field, GetHtml[index], TagVal);

										item.parentNode.classList.add('active')
										fieldValue[Name]['value'].push(item.value)

										FilterTagHTML(item.value, Name, TagName, checkList, 'alphabet');
                                });
                            }

							if(basic.URLParameter){
								let Urldata = fieldValue[Name]['value'].toString();
								urlHandler(`tp-${Name}`, Urldata)
							}
					}
            });

			return fieldValue;
		}

        var checkBoxhandle = function(check, checkList){
			let GetHtml = $scope[0].querySelectorAll('.tp-wp-checkBox');

            check.forEach(function(field,index){
				let TPPrefix = (GetHtml && GetHtml[index] && GetHtml[index].dataset.tpprefix) ? GetHtml[index].dataset.tpprefix : 'tp-',
					Name = field.name ? field.name : '',
					Field = field.field ? field.field : '';

					if(Name){
						let chidden = (GetHtml[index]) ? GetHtml[index].querySelectorAll("input[name='"+Name+"']:checked") : '';
						if(chidden) {
							tp_checkBox_tick(GetHtml,index,Name);

						/* Query */
						fieldValue[Name] = field;
						fieldValue[Name]['value'] = new Array();

						chidden.forEach(function(item){
							let TagVal = (item) ? item.getAttribute("data-title") : '',
								TagName = FilterTagTitle( Field, GetHtml[index], TagVal);
							
								fieldValue[Name]['value'].push(item.value);
								FilterTagHTML(item.value, Name, TagName, checkList, 'checkBox');
						});

						if(basic.URLParameter){
							let Urldata = fieldValue[Name]['value'].toString();
								urlHandler(`checkbox-${Name}`, Urldata)
						}
					}
                }
            })

            return fieldValue;
        }

		var tp_checkBox_tick = function(GetHtml,index,Name){
			let ALLPerent = (GetHtml[index]) ? GetHtml[index].querySelectorAll(`.tp-group input[name="${Name}"]`) : '';
				if( ALLPerent.length > 0 ){
					/* Perent checked */
					ALLPerent.forEach(function(Pitem){
						let parentVal = Pitem.checked,
							closetParent = Pitem.closest('.tp-group'),
							SubAll = closetParent.parentElement.querySelectorAll(`.tp-child-taxo input[name="${Name}"]`);
							if( SubAll.length ){
								let itemVal = [];
									SubAll.forEach(function(item,index){
										itemVal[index] = item.checked;
									});

								let allEqual = arr => arr.every(val => val === arr[0]),
									result = allEqual(itemVal),
									unique = [...new Set(itemVal)];

								if( parentVal ){
									if( itemVal && ((unique.length == 1 && unique[0] == false)) ){
										/* Sub all checked */
										SubAll.forEach(function(item){
											item.checked = true;
										});
									}else if(!result && unique.length == 2 ){
										if(Pitem.classList.contains('tp-active-perent')){
											Pitem.classList.remove('tp-active-perent')
											Pitem.checked = false;
										}else{
											SubAll.forEach(function(item){
												item.checked = true;
											});
										}
									}
								}else if( !parentVal ){
									if( result && unique.length == 1 && unique[0] != false ){
										parentVal =Pitem.checked = true;
									}
									if( !parentVal && result && unique.length == 1 && unique[0] == true ){
										SubAll.forEach(function(item){
											item.checked = false;
										});
									}
								}
							}
					});
				}
		}

		var radioHandler = function(radio, rotag){
            radio.forEach(function(radiofield, index){
				let Name = radiofield.name ? radiofield.name : '',
					type = radiofield.field ? radiofield.field : '';

					if(Name){
						let rohidden='',
							GetHtml='',
							TagName='',
							TagVal='';
						if(type == 'radio'){
							GetHtml = $scope[0].querySelectorAll('.tp-wp-radio');
							rohidden = (GetHtml && GetHtml[0]) ? GetHtml[0].querySelector(`input[name="${Name}"]:checked`) : '';

							TagVal = (rohidden) ? rohidden.getAttribute("data-title") : '';
							TagName = FilterTagTitle( type, GetHtml[index], TagVal);
						}

						if(rohidden !== null){
							fieldValue[Name] = radiofield;
							fieldValue[Name]['value'] = (rohidden.value) ? rohidden.value : '';	

							FilterTagHTML(rohidden.value, Name, TagName, rotag, type);

							if(basic.URLParameter){
								let Urldata = fieldValue[Name]['value'].toString();
								if(type == 'radio'){
									urlHandler(`radio-${Name}`, Urldata)
								}
							}
						}
					}
            })

            return fieldValue;
        }

		var selectHandler = function(select, seleTag){
			let GetHtml = $scope[0].querySelectorAll('.tp-select');
			select.forEach(function(selectfield,idx){
				let TagName = '',
					Name = (selectfield.name) ? selectfield.name : '',
					Field = (selectfield.field) ? selectfield.field : '';

					if(Name){
						let selehidden = GetHtml[idx].querySelectorAll('#'+Name)[0],
							TagVal = (selehidden && selehidden.dataset && selehidden.dataset.txtval) ? selehidden.dataset.txtval : '';
							fieldValue[Name] = selectfield;

							if(Field == "woo_SgDropDown"){
								if(selehidden && selehidden.value){
									TagName = FilterTagTitle( Field, GetHtml[idx], TagVal);

									fieldValue[Name]['value'] = [selehidden.value];
									FilterTagHTML(selehidden.value, Name, TagName, seleTag, 'select');
								}else{
									fieldValue[Name]['value'] = '';
								}
							}else{	
								if(selehidden && selehidden.value){
									TagName = FilterTagTitle( Field, GetHtml[idx], TagVal);

									fieldValue[Name]['value'] = selehidden.value;
									FilterTagHTML(selehidden.value, Name, TagName, seleTag, 'select');
								}else{
									fieldValue[Name]['value'] = '';
								}
							}

							if(basic.URLParameter){
								let Urldata = fieldValue[Name]['value'].toString();
									urlHandler('select_'+Name+'_'+idx, Urldata)
						}
					}
			})

            return fieldValue;
        }

		var inputhandle = function(data, input, inputTag){
			data.forEach(function(item, index){
				let Name = item.name ? item.name : '',
					Field = item.field ? item.field : '',
					GenericData = (input[index] && input[index].dataset.genericfilter) ? JSON.parse(input[index].dataset.genericfilter) : [];
					if(Name){
						let val = (input[index] && input[index].value) ? input[index].value : '',
							TagName = FilterTagTitle( Field, input[index], val);
							fieldValue[Name] = item;
							fieldValue[Name]['value'] = val;
							fieldValue[Name]['Generic'] = GenericData;

							if(val){
								FilterTagHTML('search', 'search', TagName, inputTag, 'search');
							}

						if(basic.URLParameter){
							urlHandler(`tp-${Name}`,val)
						}
					}
			});
			return fieldValue;
		}

		var dateHandler = function(date, dateTag){
			date.map(function(datefield){
				let Name = (datefield.name) ? datefield.name : '',
					layout = (datefield.layout) ? datefield.layout : '',
					datesele = $scope[0].querySelectorAll('#'+Name);
					fieldValue[Name] = new Array();
					fieldValue[Name] = datefield;
					fieldValue[Name]['value'] = new Array();

					if(datesele.length > 0){
						let start, last, Title='';  
						if(layout == "style-1"){
							start = (datesele[0] && datesele[0].value) ? datesele[0].value : '';
							last = (datesele[1] && datesele[1].value) ? datesele[1].value : '';
						}else if(layout == "style-2"){
							let GetDate = (datesele[0] &&  datesele[0].value) ? datesele[0].value.split("-") : '';
								start =	(GetDate[0]) ? GetDate[0].trim() : '';
								last = (GetDate[1]) ? GetDate[1].trim() : '';
						}

						if(basic.FilterTitle){
							let GetTitle = datesele[0].parentNode.parentNode.previousElementSibling.querySelector('.tp-title-text').textContent;
								Title = GetTitle + ' : '+ start + ' & ' + last;
						}else{
							Title = start+' & '+last;
						}

						if(start && last){
							fieldValue[Name]['value'].push(start,last);

							let id = start+','+last;
							FilterTagHTML(id, Name, Title, dateTag, 'date');
						}

						if(basic.URLParameter){
							let Urldata = fieldValue[Name]['value'].toString();
								urlHandler( 'date', Urldata )
						}
					}
			});
			
			return fieldValue
		}

		var TabbingHandle = function(GetHtml,index){
			let tabTick_wrap = GetHtml[index].querySelectorAll('.tp-tabbing-wrapper');
			if( tabTick_wrap.length > 0 ){
				tabTick_wrap.forEach(function(self){
					if( self && self.querySelector('.tp-tabbing-input:checked') ){
						self.classList.add('active');
	
						let textfiled = self.querySelectorAll('.tp-tabbing-button'),
							TickContener = self.querySelectorAll('.tp-tick-contener');;
							if(textfiled.length > 0 && self.parentNode.classList.contains('tp-tick-enable') && TickContener.length == 0){
								self.lastElementChild.firstChild.insertAdjacentHTML("afterend", `<span class="tp-tick-contener"><i class="fas fa-check"></i></span>`);
							}
					}else{
						self.classList.remove('active');
						let GetContener = self.querySelectorAll('.tp-tick-contener');
							if(GetContener.length > 0){
								GetContener[0].remove()
							}
					}
				})	
			}
		}

		var FilterTagTitle = function(Field, GetHtml, TagVal){
			let TagTxt = '';

				if(basic.FilterTitle){
					let Titletxt='',
						listOne = ['alphabet', 'button', 'color' , 'checkBox', 'DropDown', 'woo_SgDropDown', 'image', 'radio', 'rating', 'tabbing', 'woo_SgTabbing'],
						listTwo = ['range', 'search'];
					if(listOne.includes(Field)){
						let GetPrevSib = (GetHtml && GetHtml.previousElementSibling) ? GetHtml.previousElementSibling : '';
						if(GetPrevSib && GetPrevSib.classList.contains('tp-field-title')){
							let GetTitleHtml = GetPrevSib.querySelectorAll('.tp-title-text');
							if(GetTitleHtml.length > 0){
								Titletxt = GetTitleHtml[0].textContent;
							}
						}
					}else if(listTwo.includes(Field)){
						let GetPrentPrev = (GetHtml && GetHtml.parentNode && GetHtml.parentNode.previousElementSibling) ? GetHtml.parentNode.previousElementSibling : '';
						if(GetPrentPrev && GetPrentPrev.classList.contains('tp-field-title')){
							let GetTitleHtml = GetPrentPrev.querySelectorAll('.tp-title-text');
							if(GetTitleHtml.length > 0){
								Titletxt = GetTitleHtml[0].textContent;
							}
						}
					}
					TagTxt = Titletxt + ' : ' + TagVal;
				}else{
					TagTxt = TagVal;
				}
			return TagTxt;
		}

		var FilterTagHTML = function(Id, Name, Val, location, type){
			location.push( `<div class="tp-filter-container"><a class="tp-tag-link" data-name="${Name}" data-id="${Id}" data-type="${type}"><span class="tp-filter-tag"><i class="fa fa-times" aria-hidden="true"></i> ${Val} </span></a></div>`);
		}

		var ajaxHandler = function(data, priceRange) {
			tpgbSkeleton_filter('visible');

			option.forEach(function(item, index) {
				option[index]['seapara'] = data;

				if(option && option[index]){
					option[index]['new_offset'] = 0;
					option[index]['loadMore_sf'] = 0;
					option[index]['lazyload_sf'] = 0;
				}
			});

			setTimeout(function() {
				jQuery.ajax({
					url : theplus_ajax_url,
					method : "POST",
					async: true,
					cache: false,
					data : {
						action : 'theplus_filter_post',
						option : option,
						nonce : basic.security,
					},
					beforeSend: function(jqXHR) {
						if( ajaxfilterMap != null && ajaxfilterMap.size != 0 && ajaxfilterMap.size != 'undefined' && typeof ajaxfilterMap.abort !== "undefined"  ) {
							ajaxfilterMap.abort();
						}
						ajaxfilterMap = jqXHR;
					},
					success: function(data){
						if(data){
                            RemoveTagHandle('success');
							let TotalRecord = 0;
							seaList.forEach(function(item, index) {
								if(option && option[index] && option[index].listing_type !== 'search_list'){
									return;
								}

								if( data && data[index] && data[index].widgetName == 'googlemap' ){
									let PlusMapMarkers = [];
									let MapOptions = (data[index].options) ? data[index].options : '',
										Places = (data[index].places) ? data[index].places : '',
										Marks = (data[index].Maplocation.marks) ? data[index].Maplocation.marks : '',
										Address = (data[index].Maplocation.address) ? [...new Set(data[index].Maplocation.address)] : '',
										letlong = (data[index].Maplocation.letlong) ? data[index].Maplocation.letlong : '';

									if(Marks){
										// let test = [];
											// Marks.forEach(function(item, gidx){
												// if( !test.includes(`${item[0]},${item[1]}`) ){
												// 	test.push(`${item[0]},${item[1]}`);
												// }else{
												// 	Marks.splice(index, gidx);
												// }
											// });
										var map = new google.maps.Map( document.getElementById(item.dataset.id), {
											zoom: 3,
											center: new google.maps.LatLng(letlong[0], letlong[1]),
											mapTypeId: google.maps.MapTypeId[MapOptions.mapTypeId],
										});

										let bounds = new google.maps.LatLngBounds();
										let infoWindow = new google.maps.InfoWindow();
										let Duplicatelet = [],
											Duplicatelong = [];
											Marks.forEach(function(item, gidx){
												if( Duplicatelet.length == 0 || (!Duplicatelet.includes(item[0]) && !Duplicatelong.includes(item[1])) ){
													Duplicatelet.push(item[0]);
													Duplicatelong.push(item[1]);

													let position = new google.maps.LatLng(item[0], item[1]);
														bounds.extend(position);
													let marker = new google.maps.Marker({position: position, map});
														PlusMapMarkers.push(marker);

													google.maps.event.addListener(marker, 'click', (function(marker, gidx) {
														return function() { 
															infoWindow.setContent(`<div class="gmap_info_content"><p>${Address[gidx]}</p></div>`);
															infoWindow.open(map, marker);
														};
													})(marker, gidx));
													// map.fitBounds(bounds);
												}
											});

										if(MapOptions){
                                            if( MapOptions.marker_clustering == 'true'){
                                                new MarkerClusterer(map, PlusMapMarkers, { imagePath:"https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m" });
                                            }
                                            
											map.setOptions({
												panControl : MapOptions.panControl,
												draggable : MapOptions.draggable,
												scrollwheel : MapOptions.scrollwheel,
												zoomControl : MapOptions.zoomControl,
												mapTypeControl : MapOptions.mapTypeControl,	
												fullscreenControl : MapOptions.fullscreenControl,
												scaleControl :  MapOptions.scaleControl,
												streetViewControl : MapOptions.streetViewControl,
											});
										}
									}
								}else{
									if(data && data[index] && data[index].HtmlData){
										TotalRecord = (TotalRecord + data[index].totalrecord);

										seaList[index].innerHTML='';
										item.innerHTML = data[index].HtmlData;
										Custom_style(item);
										AnimationEffect(item, option, index);

										if(Number(option[index].display_post) >= Number(data[index].totalrecord)){
											LoadingHide(item,index, data,priceRange)
										}else{
											let ParentHtml = (item.parentNode) ? item.parentNode : '',
												LoadMoreAjax = ParentHtml.querySelectorAll('.ajax_load_more');
												if(LoadMoreAjax.length > 0){
													loadmoreHandler(ParentHtml,item,index,data,LoadMoreAjax)
												}

											let LazyLoadAjax = ParentHtml.querySelectorAll('.ajax_lazy_load');
												if(LazyLoadAjax.length > 0){
													lazyloadeHandler(ParentHtml,item,index,data,LazyLoadAjax)
												}

											let Pagin = item.parentNode.querySelectorAll('.theplus-pagination');
												if(Pagin.length > 0){
													PaginationHandler(Pagin,item,index,data,option)
												}
										}
									}else{
										PostsNotFound(item, index);
									}
								}
							});

                            SearchTotalResults(TotalRecord);
							AjaxButton();
						}else{
							seaList.forEach(function(item,index) {
								if(option && option[index] && option[index].listing_type !== 'search_list'){
									return;
								}else{
									PostsNotFound(item, index)
								}	
							});
						}
					},
					complete: function() {
					}
				}).then(function(){
					let tagList = document.querySelectorAll('.tp-tag-link');
					if(tagList.length > 0){
						for (var i=0; i<tagList.length; i++) {
							$(tagList[i]).unbind('click').click(function (e) {
								e.preventDefault();
								var close = this,
									key = close.getAttribute('data-name'),
									Id = close.getAttribute('data-id'),
									type = close.getAttribute('data-type');

									if(type == 'checkBox'){
                                        let GetHtml = document.querySelectorAll('.tp-wp-checkBox'),
                                            GetValues = (fieldValue[key] && fieldValue[key].value) ? Object.values(fieldValue[key].value) : [],
                                            Findidx = (GetValues) ? GetValues.indexOf(Id) : [];

											if( GetHtml.length > 0 ){
												GetHtml.forEach(function(item){
													let TPPrefix = (item && item.dataset && item.dataset.tpprefix) ? item.dataset.tpprefix : '';
														if( document.getElementById(TPPrefix + Id) && document.getElementById(TPPrefix + Id).checked ){
															document.getElementById(TPPrefix + Id).checked = false;
														}
												});
											}

											if( basic.URLParameter ){
                                                if( GetValues.length > 1 ){
                                                    GetValues.splice(Findidx, 1);
                                                    urlHandler(`checkbox-${key}`, GetValues)
                                                }else{
                                                    urlHandler(`checkbox-${key}`, '')
                                                }
											}

                                            fieldValue[key].value.splice(Findidx, 1);
									}else if(type == 'select'){
										let gethtml = document.getElementById(key),
											getSpan = gethtml.parentNode.querySelector('.tp-select-dropdown span'),
											getLi = gethtml.parentNode.querySelectorAll('.tp-sbar-dropdown-menu li')[0].textContent;

											gethtml.value = '';
											gethtml.dataset.txtval = '';
											getSpan.textContent = getLi;

											fieldValue[key] = [];
									}else if(type == 'range'){
                                        if(priceRange && priceRange.noUiSlider){
                                            priceRange.noUiSlider.reset();
                                        }
									}else if(type == 'date'){
										let date = document.querySelectorAll(`#${key}`);
											date[0].value = '';
											date[1].value = '';
											fieldValue[key] = fieldValue[key].toString();
									}else if(type == 'search'){
										document.getElementById(key).value = '';
									}else if(type == 'alphabet'){
                                        document.getElementById('tp-'+Id).checked = false;
										document.getElementById('tp-'+Id).parentNode.classList.remove('active');

                                        let idnum = fieldValue[key]['value'].indexOf(Id);
                                                    fieldValue[key]['value'].splice(idnum, 1);
									}else if(type == 'tabbing'){
										let GetHtml = $scope[0].querySelectorAll('.tp-tabbing');
											GetHtml.forEach(function(item){
												if(item.classList.contains('tp-wootab-sorting')){
													item.querySelector('#'+key).parentNode.parentNode.classList.remove('active')
													item.querySelector('#'+key).checked = false;
												}else{
													item.querySelector('#tp-'+Id).parentNode.parentNode.classList.remove('active')
													item.querySelector('#tp-'+Id).checked = false;
												}
											})

										let idnum = fieldValue[key]['value'].indexOf(Id);
											fieldValue[key]['value'].splice(idnum, 1);
									}else if(type == 'button'){
										let GetHtml = document.querySelectorAll('.tp-woo-button'),
											GetValues = (fieldValue[key] && fieldValue[key].value) ? Object.values(fieldValue[key].value) : [],
											Findidx = (GetValues) ? GetValues.indexOf(Id) : [];

											if( GetHtml.length > 0 ){
												GetHtml.forEach(function(item){
													let TPPrefix = (item && item.dataset && item.dataset.tpprefix) ? item.dataset.tpprefix : '';
														if( document.getElementById(TPPrefix + Id) && document.getElementById(TPPrefix + Id).checked ){
															document.getElementById(TPPrefix + Id).checked = false;
														}
												});
											}

											if( basic.URLParameter ){
                                                if( GetValues.length > 1 ){
                                                    GetValues.splice(Findidx, 1);
                                                    urlHandler(`tp-${key}`, GetValues)
                                                }else{
                                                    urlHandler(`tp-${key}`, '')
                                                }
											}

											fieldValue[key].value.splice(Findidx, 1);
									}else if(type == 'color'){
										let GetHtml = document.querySelectorAll('.tp-woo-color'),
											GetValues = (fieldValue[key] && fieldValue[key].value) ? Object.values(fieldValue[key].value) : [],
											Findidx = (GetValues) ? GetValues.indexOf(Id) : [];

											if( GetHtml.length > 0 ){
												GetHtml.forEach(function(item){
													let TPPrefix = (item && item.dataset && item.dataset.tpprefix) ? item.dataset.tpprefix : '';
														if( document.getElementById(TPPrefix + Id) && document.getElementById(TPPrefix + Id).checked ){
															document.getElementById(TPPrefix + Id).checked = false;
														}
												});
											}

											if( basic.URLParameter ){
                                                if( GetValues.length > 1 ){
                                                    GetValues.splice(Findidx, 1);
                                                    urlHandler(`tp-${key}`, GetValues)
                                                }else{
                                                    urlHandler(`tp-${key}`, '')
                                                }
											}

											fieldValue[key].value.splice(Findidx, 1);
									}else if(type == 'image'){
										let GetHtml = document.querySelectorAll('.tp-woo-image'),
											GetValues = (fieldValue[key] && fieldValue[key].value) ? Object.values(fieldValue[key].value) : [],
											Findidx = (GetValues) ? GetValues.indexOf(Id) : [];

											if( GetHtml.length > 0 ){
												GetHtml.forEach(function(item){
													let TPPrefix = (item && item.dataset && item.dataset.tpprefix) ? item.dataset.tpprefix : '';
														if( document.getElementById(TPPrefix + Id) && document.getElementById(TPPrefix + Id).checked ){
															document.getElementById(TPPrefix + Id).checked = false;
														}
												});
											}

											if( basic.URLParameter ){
                                                if( GetValues.length > 1 ){
                                                    GetValues.splice(Findidx, 1);
                                                    urlHandler(`tp-${key}`, GetValues)
                                                }else{
                                                    urlHandler(`tp-${key}`, '')
                                                }
											}

											fieldValue[key].value.splice(Findidx, 1);
									}else if(type == 'rating'){
										let GetHtml = $scope[0].querySelectorAll('.tp-star-rating');
											GetHtml.forEach(function(item){
												item.querySelector('.stars-'+Id).checked = 0;
											});
											fieldValue[key]['value'] = 0;
									}else if(type == 'radio'){
										let GetHtml = document.querySelectorAll('.tp-wp-radio');
											if(GetHtml.length > 0){
												GetHtml.forEach(function(item, idx){
													let TPPrefix = (item && item.dataset && item.dataset.tpprefix) ? item.dataset.tpprefix : '';
														document.querySelector(`#${TPPrefix + Id}`).checked = 0;
												});
											}

											if(basic.URLParameter){
                                                urlHandler(`radio-${key}`, '');
											}

											fieldValue[key].value = [];
									}else if(type == 'tagremove' || type == 'fix_tagremove' ){
                                        let TagForm = document.querySelectorAll('.tp-search-form');
                                       
                                            if(TagForm.length > 0){
                                                TagForm.forEach(function(item, index) {
                                                    let Number = item.parentNode.querySelectorAll(".tp-filter-tag-wrap");
                                                        if(Number.length > 0 && Number[index]){
                                                            Number[index].innerHTML = '';
                                                    	}

													let select = item.querySelectorAll('.tp-select');
														if(select.length > 0){
															select.forEach(function(slt, idx) {
																let getinput = slt.querySelector('input'),
																	getSpan = slt.querySelector('.tp-select-dropdown span'),
																	getLi = (slt && 
																				slt.querySelectorAll('.tp-sbar-dropdown-menu li')[0] &&
																				slt.querySelectorAll('.tp-sbar-dropdown-menu li')[0].textContent ) ? slt.querySelectorAll('.tp-sbar-dropdown-menu li')[0].textContent : '';

																	if(getinput && getinput.value){
																		getinput.value = '';
																	}			
																	if(getinput && getinput.dataset && getinput.dataset.txtval){
																		getinput.dataset.txtval = '';
																	}			
																	if(getSpan && getSpan.textContent){
																		getSpan.textContent = getLi;
																	}	
																	if(getinput && getinput.name){
																		fieldValue[getinput.name] = [];
																	}
															});
														}

                                                    let RangeReset = item.querySelectorAll('.tp-range');
                                                        if(RangeReset.length > 0 && priceRange && priceRange.noUiSlider){
                                                            priceRange.noUiSlider.reset();
                                                        }
                                                        if( RangeReset.length > 0 ){
                                                            RangeReset.forEach(function(slt, idx) {
                                                                urlHandler(`range${idx}`, '')
                                                            });
                                                        }

                                                    let RadioReset = item.querySelectorAll('.tp-wp-radio');
                                                        if( RadioReset.length > 0 ){
                                                            RadioReset.forEach(function(Radio, idx) {
                                                                let val = (Radio && Radio.previousElementSibling.previousElementSibling) ? Radio.previousElementSibling.previousElementSibling.value : '';
                                                                    if(fieldValue && fieldValue[val] && fieldValue[val].value){
                                                                        fieldValue[val].value = [];
                                                                    }
                                                            })
                                                        }

													let emptyArray = (item && item.dataset && item.dataset.field) ? Object.keys(JSON.parse(item.dataset.field)) : '';	
														if(emptyArray.length > 0){
															item.reset();
                                                            item.dispatchEvent(new Event("change"), { 'bubbles': true })
														}
                                                });
                                            }
									}else if(type == 'autocomplete'){
										let GetAll = document.querySelectorAll('#tp-autocomplete-input');
											if( GetAll.length > 0 ){
												GetAll.forEach(function(item){
													item.value = "";
													let getName = (item) ? item.getAttribute("name") : '';
														if( key == getName ){		
															item.value = "";
															fieldValue[type].value = ""
														}
												});
											}
									}

								ajaxHandler(fieldValue)

								if(close){
									if(type != 'fix_tagremove'){
										close.parentElement.remove();
										tagList.forEach(function(item){
											if(item){
												let tagAllname = (item.dataset && item.dataset.name) ? item.dataset.name : '',
													tagAlltype = (item.dataset && item.dataset.type) ? item.dataset.type : '',
													tagAllid = (item.dataset && item.dataset.id) ? item.dataset.id : '';
	
													if(key == tagAllname && type == tagAlltype && Id == tagAllid){
														item.parentElement.remove();
													}
											}
										})
									}
								}
							});
						}
					}
				});	
			}, 800)
		}

		var loadmoreHandler = function(ParentHtml,item,index,data,LoadMoreAjax) {
			option[index].loadMore_sf = 1;
			$IncrementLoad[index] = 0;

			loadmore_Html( LoadMoreAjax, index )

			let DataLoad = ParentHtml.querySelectorAll(`.filter-loadmore-${index}`);
				if(DataLoad.length > 0 ){
					jQuery(DataLoad[0]).unbind("click");
					jQuery(DataLoad[0]).bind('click', function() {
						let $this = this,
							loadDs = ($this) ? $this.dataset : [],
							loadingtxt = (loadDs) ? loadDs.tp_loading_text : '',
							loadtxt = (loadDs) ? loadDs.loaded_posts : '';
				
						if(Number($IncrementLoad[index]) == Number(0)){
							option[index].new_offset =  Number(option[index].display_post);
						}

						jQuery.ajax({
							url : theplus_ajax_url,
							method : 'post',
							async: false,
							data : {
								action : 'theplus_filter_post',
								option : option,
								nonce : basic.security,
							},
							beforeSend: function() {
								$this.textContent = loadingtxt;
							},
							success: function (item2) {
								$this.textContent = option[index].loadmoretxt;

								if(item2 && item2[index] && item2[index].HtmlData){
									$(item).append(item2[index].HtmlData);

									$IncrementLoad[index]++;
									option[index].new_offset = Number($IncrementLoad[index]) * Number(option[index].post_load_more) + Number(option[index]['display_post']);

                                    Custom_style(item);
									if( Number(option[index].new_offset) >= Number(data[index].totalrecord) ){
										$this.classList.add('hide');
										$($this.parentNode).append(`<div class="plus-all-posts-loaded">${loadtxt}</div>`);
									}

									let loadTR = item2[index].totalrecord;
                                    SearchTotalResults(loadTR);
                                    MetroResize(option, item, index);

									if( item.parentElement && item.parentElement.classList.contains('animate-general') ){
										AnimationEffect(item, option, index);
									}
								}
							},
							complete: function() {
								let $window = $(window);
                                    option.forEach(function(itemR, index) {
                                        let layout = (itemR && itemR.layout) ? itemR.layout : '',
                                            MainClass = (seaList && seaList[index] && seaList[index].parentNode) ? seaList[index].parentNode : '';
                                        if( layout == 'grid' || layout == 'masonry' ){
                                            if( MainClass.classList.contains('list-isotope') ){
                                                $(seaList[index]).isotope('reloadItems').isotope();
                                                $window.resize();
                                            }
                                        }else if(layout == 'metro'){
                                            if( MainClass.classList.contains('list-isotope-metro') ){
                                                setTimeout(function(){	
                                                    theplus_setup_packery_portfolio('all');	
                                                }, delayload);
                                            }
                                        }
                                    });
							},
						});
					});
				}
		}

		var loadmore_Html = function(LoadMoreAjax,index) {
			let LoadMore = (LoadMoreAjax[0].children) ? LoadMoreAjax[0].children : '';
				if(LoadMore.length > 0){
					LoadMore[0].classList.remove('post-load-more');
					LoadMore[0].classList.remove('hide');
					LoadMore[0].classList.add(`filter-loadmore-${index}`);
					LoadMore[0].classList.add('tp-morefilter');

					let DoneMsg = LoadMoreAjax[0].querySelectorAll('.plus-all-posts-loaded')
					if( DoneMsg.length > 0 ){
						DoneMsg.forEach(function(data) {
							data.remove();
						});
					}
				}
		}

		var lazyloadeHandler = function(ParentHtml, item, index, data, LazyLoad) {
			if(LazyLoad.length > 0){
				LazyLoad[0].removeAttribute("style")

				option[index].lazyload_sf = 1;
				$Increment[index] = 0;

				let Getchild = (LazyLoad[0] && LazyLoad[0].children[0]) ? LazyLoad[0].children[0] : '',
					GetNxtSb = (Getchild && Getchild.nextSibling) ? Getchild.nextSibling : '';

				if(Getchild.style.display == 'none'){
					Getchild.style.cssText = "display:block";
					(GetNxtSb) ? GetNxtSb.remove() : '';
				}

				Getchild.classList.add('filter-loadmore-'+index);
				Getchild.classList.add('tp-morefilter');
				Getchild.classList.remove('post-lazy-load');
			}
			
			let DataLoad = ParentHtml.querySelectorAll('.filter-loadmore-'+index);
			if(DataLoad.length > 0){
				var windowWidth, windowHeight, documentHeight, scrollTop, containerHeight, containerOffset, $window = $(window);
				var recalcValues = function() {
					windowWidth = $window.width();
					windowHeight = $window.height();
					documentHeight = $('body').height();
					containerHeight = $(".list-isotope,.list-isotope-metro").height();
					containerOffset = $(".list-isotope,.list-isotope-metro").offset().top + 50;
					setTimeout(function() {
						containerHeight = $(".list-isotope,.list-isotope-metro").height();
						containerOffset = $(".list-isotope,.list-isotope-metro").offset().top + 50;
					}, 50);
				};
				recalcValues();
				$window.resize(recalcValues);

				$window.bind('scroll', function(e) {
					e.preventDefault();
					recalcValues();
					scrollTop = $window.scrollTop();
					containerHeight = $(".list-isotope,.list-isotope-metro").height();
					containerOffset = $(".list-isotope,.list-isotope-metro").offset().top + 50;
					
						if($(".list-isotope,.list-isotope-metro").find('.filter-loadmore-'+index).length && scrollTop < documentHeight && (scrollTop + 60 > (containerHeight + containerOffset - windowHeight))) {
							var lazyFeed_click = ParentHtml.querySelector('.filter-loadmore-'+index),
								lazyDataset = (lazyFeed_click && lazyFeed_click.dataset) ? lazyFeed_click.dataset : '',
								loadtxt = (lazyDataset && lazyDataset.loaded_posts) ? lazyDataset.loaded_posts : '';

							if(Number(option[index].new_offset) == Number(0)){
								option[index].new_offset = Number(option[index].display_post);
							}

							if(Number(option[index].new_offset) >= Number(data[index].totalrecord)){ 
								return; 
							}

							if ($(lazyFeed_click).data('requestRunning')) {
								return;
							}
								$(lazyFeed_click).data('requestRunning', true);

							jQuery.ajax({
								url : theplus_ajax_url,
								method : 'post',
								async: false,
								data : {
									action : 'theplus_filter_post',
									option : option,
                                    nonce : basic.security,
								},
								beforeSend: function() {
								},
								success: function (item2) {
									if(item2 && item2[index] && item2[index].HtmlData){
										$(item).append(item2[index].HtmlData);
                                        Custom_style(item);

                                        let loadTR = item2[index].totalrecord;
                                            SearchTotalResults(loadTR);
                                            MetroResize(option, item, index)

											
										if( item.parentElement && item.parentElement.classList.contains('animate-general') ){
											AnimationEffect(item, option, index);
										}
									}

									$Increment[index]++;
									option[index].new_offset = Number($Increment[index]) * Number(option[index].post_load_more) + Number(option[index].display_post);

									if(Number(option[index].new_offset) >= Number(item2[index].totalrecord)){
										lazyFeed_click.style.cssText = "display:none";
										$(LazyLoad).append('<div class="plus-all-posts-loaded">'+loadtxt+'</div>');
									}
								},
								complete: function() {
									$(lazyFeed_click).data('requestRunning', false);

                                    option.forEach(function(itemR, index) {
                                        let layout = (itemR && itemR.layout) ? itemR.layout : '',
                                            MainClass = (seaList && seaList[index] && seaList[index].parentNode) ? seaList[index].parentNode : '';

                                        if(layout == 'grid' || layout == 'masonry'){
                                            if( MainClass.classList.contains('list-isotope') ){
                                                $(seaList[index]).isotope('reloadItems').isotope();
												$window.resize();
                                            }
                                        }else if(layout == 'metro'){
                                            if( MainClass.classList.contains('list-isotope-metro') ){
                                                setTimeout(function(){	
                                                    theplus_setup_packery_portfolio('all');	
                                                }, delayload);
                                            }
                                        }
                                    });
								},
							});
						}
				});
			}
		}

		var PaginationHandler = function(Pagin,item,index,data,option) {
			Pagin[0].removeAttribute("style")
			option[index]['Paginate_sf'] = 1;
			PaginationHtml(Pagin,index,data,option);

			let Buttonajax = Pagin[0].querySelectorAll('.tp-pagelink-'+index);
				Buttonajax.forEach(function(self) {
					self.addEventListener("click", function(e){
						e.preventDefault();
                        tpgbSkeleton_filter("visible");
						let PageNumber = Number(this.dataset.page),
							offset = (Number(PageNumber) - Number(1) ) * Number(option[index]['display_post']);
							option[index]['new_offset'] = offset;

							let active = Pagin[0].querySelectorAll('.current');
								if(active.length > 0){
									active[0].classList.remove('current');
									active[0].classList.add('inactive');
									this.classList.add('current');
								}

							let GetGrid = seaList[index].querySelectorAll('.tp-page-'+index+'-'+PageNumber)
								if(GetGrid.length > 0){
									let Gridload = seaList[index].querySelectorAll('.tp-page-active');
										Gridload.forEach(function(Grid) {
											if(Grid.classList.contains('tp-page-'+index+'-'+PageNumber)){
												Grid.style.cssText = "display:block";
											}else{
												Grid.style.cssText = "display:none";
											}
										});

                                        MetroResize(option, item, index)
										PaginationHandler(Pagin,item,index,data,option)
									return;
								}

								PaginationAjax(Pagin,item,index,data,option);
					});				
				});

			let Nextajax = Pagin[0].querySelectorAll('.tp-sf-next-'+index);
				Nextajax.forEach(function(self) {
					self.addEventListener("click", function(e){
						e.preventDefault();
                        tpgbSkeleton_filter("visible");
						let PageNumber = Number(this.dataset.page),
							NewNumber = Number(PageNumber) + Number(1),
							offset = Number(PageNumber) * Number(option[index]['display_post']),
							active = Pagin[0].querySelectorAll('.current'),
							inactive = Pagin[0].querySelectorAll('.inactive');
							option[index]['new_offset'] = offset;

							if(active.length > 0){
								active[0].classList.remove('current');
								active[0].classList.add('inactive');

								inactive.forEach(function(self) {
									if(Number(self.dataset.page) == NewNumber){
										self.classList.add('current');
									}
								});
							}

						let GetGrid = seaList[index].querySelectorAll('.tp-page-'+index+'-'+NewNumber)
							if(GetGrid.length > 0){
								let Gridload = seaList[index].querySelectorAll('.tp-page-active');
									Gridload.forEach(function(Grid) {
										if(Grid.classList.contains('tp-page-'+index+'-'+NewNumber)){
											Grid.style.cssText = "display:block";
										}else{
											Grid.style.cssText = "display:none";
										}
									});

                                    MetroResize(option, item, index)
									PaginationHandler(Pagin,item,index,data,option)
								return;
							}

							PaginationAjax(Pagin,item,index,data,option);
					});
				});

			let Prevajax = Pagin[0].querySelectorAll('.tp-sf-prev-'+index);
				Prevajax.forEach(function(self) {
					self.addEventListener("click", function(e){
						e.preventDefault();
                        tpgbSkeleton_filter("visible");
						let PageNumber = Number(this.dataset.page),
							PrevNumber = Number(PageNumber) - Number(1),
							offset = (Number(PrevNumber) - 1) * Number(option[index]['display_post']),
							active = Pagin[0].querySelectorAll('.current'),
							inactive = Pagin[0].querySelectorAll('.inactive');
							option[index]['new_offset'] = offset;
						
							if(active.length > 0){
								active[0].classList.remove('current');
								active[0].classList.add('inactive');

								inactive.forEach(function(self) {
									if(Number(self.dataset.page) == PrevNumber){
										self.classList.add('current');
									}
								});
							}

							let GetGrid = seaList[index].querySelectorAll('.tp-page-'+index+'-'+PrevNumber)
							if(GetGrid.length > 0){
								let Gridload = seaList[index].querySelectorAll('.tp-page-active');
									Gridload.forEach(function(Grid) {
										if(Grid.classList.contains('tp-page-'+index+'-'+PrevNumber)){
											Grid.style.cssText = "display:block";
										}else{
											Grid.style.cssText = "display:none";
										}
									});

                                    MetroResize(option, item, index)
									PaginationHandler(Pagin,item,index,data,option)
								return;
							}
							
							PaginationAjax(Pagin,item,index,data,option);
					});
				});

                tpgbSkeleton_filter("hidden");
		}

		var PaginationAjax = function(Pagin,item,index,data,option) {
			jQuery.ajax({
				url : theplus_ajax_url,
				method : 'post',
				async: true,
				cache: false,
				data : {
					action : 'theplus_filter_post',
					option : option,
                    nonce : basic.security,
				},
				beforeSend: function() {
				},
				success: function (res2) {
					$(seaList[index]).append(res2[index].HtmlData);

					let GetGrid = seaList[index].querySelectorAll('.grid-item');
						GetGrid.forEach(function(Grid) {
							Grid.style.cssText = "display:none";
						});

                        MetroResize(option, item, index)
				},
				complete: function() {
                    let layout = (option && option[index] && option[index].layout) ? option[index].layout : '',
                        MainClass = (seaList && seaList[index] && seaList[index].parentNode) ? seaList[index].parentNode : '';

                        if( (layout == 'grid' || layout == 'masonry') && MainClass.classList.contains('list-isotope') ){
							setTimeout(function(){
                                $(seaList[index]).isotope('reloadItems').isotope();
                            }, delayload);
                        }else if( layout == 'metro' && MainClass.classList.contains('list-isotope-metro') ){
                            setTimeout(function(){
                                theplus_setup_packery_portfolio('all');	
                            }, delayload);
                        }

                        PaginationHandler(Pagin,item,index,data,option)
				},
			});
		}

		var PaginationHtml = function(Pagin,index,data,option) {
			let HtmlLoad = option[index].PageHtmlLoad,
				PageLimit = Math.ceil(data[index].totalrecord / option[index].display_post),
				$Number='',
				$Next='',
				$Prev='';

				if(HtmlLoad){
					let NTmp='',
						PTmp='',
						active = Pagin[0].querySelectorAll('.current');
						if(active.length > 0){
							let PageNum = Number(active[0].dataset.page),
                                PageAaray;

                                if(Number(PageNum) != 0 || Number(PageNum) != 1){
                                    PageAaray = [ (Number(PageNum) - Number(1)), Number(PageNum), (Number(PageNum) + Number(1)) ];
                                }else{
                                    PageAaray = [ 1, 2, 3 ];
                                }
                            
							for (let i=1; i<=PageLimit; i++) {
								if(PageNum == i){
									$Number += '<span class="current" data-page="'+Number(i)+'">'+ Number(i) +'</span>';
								}else{
                                    if( PageAaray.includes(i) ){
                                        $Number += `<a href="#" class="inactive tp-pagelink-${index}" data-page="${Number(i)}">${Number(i)}</a>`;
                                    }else{
                                        $Number += `<a href="#" class="inactive tp-pagelink-${index} tp-filter-hide" data-page="${Number(i)}">${Number(i)}</a>`;
                                    }
								}

								PTmp = (PageNum == 1) ? ' tp-filter-hide':'';
								NTmp = (PageNum == PageLimit) ? ' tp-filter-hide':'';
							}

							$Next = '<a href="#" class="paginate-next tp-sf-next-'+index + NTmp+'" data-page="'+Number(PageNum)+'">NEXT<i class="fa fa-long-arrow-right" aria-hidden="true"></i></a>',
							$Prev = '<a href="#" class="paginate-prev tp-sf-prev-'+index + PTmp+'" data-page="'+Number(PageNum)+'"><i class="fas fa-long-arrow-alt-left" aria-hidden="true"></i> PREV</a>';

							let GetGrid = seaList[index].querySelectorAll('.grid-item');
								GetGrid.forEach(function(Grid, idx) {
									if( Grid.classList.contains('tp-page-active') === false ){
										Grid.style.cssText = "display:block";
										Grid.classList.add('tp-page-active');
										Grid.classList.add('tp-page-'+index+'-'+PageNum);
									}
								});
						}
				}else{
					option[index]['PageHtmlLoad'] = 1;
					for (let i=1; i<=PageLimit; i++) {
						if(i == 1){
							$Number += '<span class="current" data-page="'+Number(i)+'">'+ Number(i) +'</span>';
						}else{
                            let Hideclass="";
                            if( i > 3 ){
                                Hideclass = "tp-filter-hide";
                            }
                            $Number += `<a href="#" class="inactive tp-pagelink-${index} ${Hideclass}" data-page="${Number(i)}">${Number(i)}</a>`;
						}
					}

					let GetGrid = seaList[index].querySelectorAll('.grid-item');
						if(GetGrid.length > 0){
							GetGrid.forEach(function(Grid, idx) {
								Grid.classList.add('tp-page-active');
								Grid.classList.add('tp-page-'+index+'-1');
							});
						}

					$Next = '<a href="#" class="paginate-next tp-sf-next-'+index+'" data-page="'+Number(1)+'">NEXT<i class="fa fa-long-arrow-right" aria-hidden="true"></i></a>',
					$Prev = '<a href="#" class="paginate-prev tp-sf-prev-'+index+' tp-filter-hide" data-page="'+Number(1)+'><i class="fas fa-long-arrow-alt-left" aria-hidden="true"></i> PREV</a>';
				}

				Pagin[0].innerHTML = $Prev + $Number + $Next;
		}

		var urlHandler = function(key, val) {
			let url = new URL(window.location),
				params = new URLSearchParams(url.search);

				if(val){
					params.set(key, val)
				}else{
					params.delete(key)
				}

				url.search = params.toString();
				window.history.pushState({}, '', url);
		}

        var RemoveTagHandle = function($val){
            let GetTag = document.querySelectorAll('.tp-filter-tag-wrap');
                if(GetTag.length > 0){
                    GetTag.forEach(function(item, idx){
                        let GetInnerGet = item.querySelectorAll('.tp-filter-tag');
                        if($val == 'create'){ 
                            let GetReset = item.querySelectorAll('.tp-tag-reset'),
								removetagclass = (item && item.nextSibling) ? item.nextSibling : '';
                                if( GetInnerGet.length > 0 && GetReset.length == 0 ){
									let Resettxt = (removetagclass && removetagclass.dataset && removetagclass.dataset.resetbtndata) ? JSON.parse(removetagclass.dataset.resetbtndata).Resettext : "";
                                    if( removetagclass && removetagclass.classList.contains('start') ){
                                        $(item).prepend(`<span class="tp-tag-reset-contener"><a class="tp-tag-link" data-type="tagremove" data-name="tagremove" data-id="tagremove"><span class="tp-tag-reset"><i class="fa fa-times" aria-hidden="true"></i> ${Resettxt}</span></a></span>`);
                                    }else if( removetagclass && removetagclass.classList.contains('end') ){
										$(item).append(`<span class="tp-tag-reset-contener"><a class="tp-tag-link" data-type="tagremove" data-name="tagremove" data-id="tagremove"><span class="tp-tag-reset"><i class="fa fa-times" aria-hidden="true"></i> ${Resettxt}</span></a></span>`);
                                    }
                                }
                        }else if($val == 'success'){
                            if(GetInnerGet.length == 0){
                                item.innerHTML = '';
                            }
                        }
                    });
                }
        }

        var SearchTotalResults = function(TotalRecord=0){
			let Notfound = document.querySelectorAll('.grid-item :not(.theplus-posts-not-found)'),
				GetTR = document.querySelectorAll('.tp-total-results-txt');
				if(Notfound.length == 0){
					GetTR.forEach(function(self, index) {
						let One = self.previousElementSibling.textContent.replaceAll('{visible_product_no}', 0),
							Two = One.replaceAll('{total_product_no}', 0);
							self.innerHTML = Two;
					})
				}else{
					let GetAllGrid = document.querySelectorAll('.grid-item');
						GetTR.forEach(function(self, index) {
							let One = self.previousElementSibling.textContent.replaceAll('{visible_product_no}', GetAllGrid.length),
								Two = One.replaceAll('{total_product_no}', TotalRecord);
								self.innerHTML = Two;
						})
				}
        }

		var AjaxButton = function(){
			let FindActive = container[0].querySelectorAll('.tp-ajax-button.active');
			if(FindActive.length > 0){
				FindActive.forEach(function(self) {
					AjaxButtonHandle('AfterAjax', self);
				});
			}
		}

		var AjaxButtonHandle = function(Type , $this){
			if(basic && basic.AjaxButton && $this){
				let DataVal = ($this.dataset && $this.dataset.ajaxbutton) ? JSON.parse($this.dataset.ajaxbutton) : '',
					AjaxBtnTxt = (DataVal && DataVal.AjaxBtnTxt) ? DataVal.AjaxBtnTxt : '',
					loaddingtxt = (DataVal && DataVal.AjaxLoaddingtxt) ? DataVal.AjaxLoaddingtxt : '',
					Ajaxloadicon = (DataVal && DataVal.Ajaxloadicon) ? DataVal.Ajaxloadicon : '';
					
				let FindSpinner = $this.querySelectorAll('.tp-ajaxbtn-spinner-loader'),
					FndTxt = $this.querySelectorAll('.tp-ajaxbtn-text'),
					Spinnercss = "display:none",
					FinalTxt = '';

				if( Type == "BeforeAjax" ){
					Ajax_Button = 1;
					$this.classList.add('active');
					FinalTxt = loaddingtxt;
					Spinnercss = "display:inline-flex";
				}else if( Type == "AfterAjax" ){
					Ajax_Button = 0;
					$this.classList.remove('active');
					FinalTxt = AjaxBtnTxt;
					Spinnercss = "display:none";
				}

				if( FndTxt.length > 0 && FinalTxt){
					FndTxt.forEach(function(item) {
						item.textContent = FinalTxt;
					});
				}

				if (Ajaxloadicon && FindSpinner.length > 0){
					FindSpinner.forEach(function(item){
						item.style.cssText = Spinnercss;
					});
				}

			}
		}

        var Custom_style = function(item) {
            let GetStyle = (item.parentElement && item.parentElement.dataset && item.parentElement.dataset.style) ? item.parentElement.dataset.style : '';
                if(GetStyle == 'custom'){
                    let CustomImg = item.querySelectorAll('.grid-item .tp-post-image.tp-feature-image-as-bg');
                    if(CustomImg.length > 0){            
                        CustomImg.forEach(function(img, imgIdx) {
                            let $tp_fi_bg_type = (img && img.dataset && img.dataset.tpFiBgType) ? img.dataset.tpFiBgType : '';
                                if($tp_fi_bg_type){
                                    if($tp_fi_bg_type == 'tp-fibg-section'){
                                        $(img.closest('section.elementor-element.elementor-top-section')).append(img);
                                    }else if($tp_fi_bg_type == 'tp-fibg-inner-section'){
                                        $(img.closest('section.elementor-element.elementor-inner-section')).append(img);
                                    }else if($tp_fi_bg_type == 'tp-fibg-column'){
                                        $(img.closest('.elementor-column')).append(img);
                                    }
                                }
                        });
                    }
                }
        }

		var tpgbSkeleton_filter = function(val1) {
			let skeleton = document.querySelectorAll('.tp-skeleton');
				if( skeleton.length > 0 ){
					skeleton.forEach(function(self) {
						if( self.style.visibility == 'visible' && self.style.opacity == 1 ){
							if(val1 == "hidden"){
								self.style.cssText = "visibility: hidden; opacity: 0;";
							}
						}else{
							if(val1 == "visible"){
								self.style.cssText = "visibility: visible; opacity: 1;";
							}
						}
					});
				}
		}

        var MetroResize = function(option, Html, idx) {
            if( option && option[idx].layout == 'metro' && Html && Html.parentNode.classList.contains('list-isotope-metro')){
                theplus_setup_packery_portfolio('all');	
            }
        };

		var Resizelayout = function(option) {
			option.forEach(function(item, index) {
				if( item.layout == 'grid' || item.layout == 'masonry' ){
					if( seaList[index].parentNode.classList.contains('list-isotope') ){
						setTimeout(function(){
							$(seaList[index]).isotope('reloadItems').isotope();
                        }, delayload);
					}
				}else if(item.layout == 'metro'){
					if( seaList[index].parentNode.classList.contains('list-isotope-metro') ){
						setTimeout(function(){
							theplus_setup_packery_portfolio('all');	
                        }, delayload);
					}
				}
			});
		}

		var PostsNotFound = function(item, idx) {
			let GetMsg = (option && option[idx] && option[idx].No_PostFound) ? option[idx].No_PostFound : '',
				ErrorMSg = (GetMsg=='' && basic && basic.errormsg) ? basic.errormsg : GetMsg;

				item.innerHTML = `<div class="grid-item tp-col-lg-12 tp-col-md-12 tp-col-sm-12 tp-col-12"><h3 class="theplus-posts-not-found">${ErrorMSg}</h3></div>`;

			LoadingHide(item,idx);
		}

		var LoadingHide = function(item, idx) {
			let LoadMore = item.parentNode.querySelectorAll('.ajax_load_more');
			let LazyLoad = item.parentNode.querySelectorAll('.ajax_lazy_load');
			let Pagin = item.parentNode.querySelectorAll('.theplus-pagination');

			if(LoadMore.length > 0){
				if(LoadMore[0].children[0]){
					LoadMore[0].children[0].classList.add('hide');
				}
			}else if(LazyLoad.length > 0){
				LazyLoad[0].style.cssText = "display:none";

				if(LazyLoad[0].children[0]){
					LazyLoad[0].children[0].classList.remove('post-lazy-load');
					LazyLoad[0].children[0].classList.remove('tp-morefilter');
					LazyLoad[0].children[0].classList.remove('filter-loadmore-'+idx);
				}
			}else if(Pagin.length > 0){
				Pagin[0].style.cssText = "display:none";
			}
		}

        var AnimationEffect = function(item, option, index) {
			var c,d;
			if( item.parentElement.classList.contains('animate-general') ){
				if($(item).find(".animated-columns").length){
					var p = $(item).parents(".animate-general");
					var delay_time = p.data("animate-delay");
					var animation_stagger = p.data("animate-stagger");
					var d = p.data("animate-type");
					var animate_offset = p.data("animate-offset");
					var duration_time = p.data("animate-duration");
					c = p.find('.animated-columns:not(.animation-done)');
					if( p.data("animate-columns") == "stagger" ){
						c.css("opacity","0");
						setTimeout(function(){	
							if(!c.hasClass("animation-done")){
								c.addClass("animation-done").velocity(d,{ delay: delay_time,display:'auto',duration: duration_time,stagger: animation_stagger});
							}
						}, 500);
					}else if(p.data("animate-columns")=="columns"){
						c.css("opacity","0");
						setTimeout(function(){	
						c.each(function() {
							var bc=$(this);
							bc.waypoint(function(direction) {
								if( direction === 'down'){
									if(!bc.hasClass("animation-done")){
										bc.addClass("animation-done").velocity(d,{ delay: delay_time,duration: duration_time,drag:true,display:'auto'});
									}
								}
							}, {offset: animate_offset } );
						});
						}, 500);
					}
				}else{
					var b = $(item).parents(".animate-general");
					var delay_time = b.data("animate-delay");
						d = b.data("animate-type"),
						animate_offset = b.data("animate-offset"),
						b.waypoint(function(direction ) {
							if( direction === 'down'){
								if(!b.hasClass("animation-done")){
									b.addClass("animation-done").velocity(d, {delay: delay_time,display:'auto'});
								}
							}
						}, {triggerOnce: true,  offset: animate_offset } );
				}
			}else{
				tpgbSkeleton_filter('visible');
				Resizelayout(option);
				MetroResize(option, item, index);
				tpgbSkeleton_filter('hidden');
			}
        }

		var DuplicateCheck = function(Type, item, Name) {
			if(Type == "image"){
				let GetAll = document.querySelectorAll(`.tp-woo-image input[name="${Name}"]`);

				if( item.length > 0 ){
					var GetValue = []
					item.forEach(function(self){
						GetValue.push(self.value);
					});
					
					if(GetAll.length > 0 && GetValue.length > 0 ){
						GetAll.forEach(function(element){
							if( GetValue.includes(element.value) ) {
								element.checked = true;
							}else{
								element.checked = false;
							}
						});
					}
				}else{
					GetAll.forEach(function(element){
						element.checked = false;
					});
				}
			}
		}

		let NearmeBtn = container[0].querySelectorAll(`.tp-nearme`);
            if( NearmeBtn.length ){
                let Getautocomplete = container[0].querySelectorAll(`.tp-search-input-autocomplete`);
                    NearmeBtn.forEach(function(item){
                        item.addEventListener("click", function(){
                            var locationInfo = {
                                geo: null,
                                country: null,
                                state: null,
                                city: null,
                                postalCode: null,
                                street: null,
                                streetNumber: null,
                                name: null,
                                reset: function() {
                                    this.geo = null;
                                    this.country = null;
                                    this.state = null;
                                    this.city = null;
                                    this.postalCode = null;
                                    this.street = null;
                                    this.streetNumber = null;
                                    this.fullAddress = null;
                                    this.name = null;
                                }
                            };
                            
                            navigator.geolocation.getCurrentPosition(function(position) {
                                let lat = position.coords.latitude,
                                    lng = position.coords.longitude,
                                    latlng = new google.maps.LatLng(lat, lng),
                                    geocoder = new google.maps.Geocoder();

                                    geocoder.geocode({ 'latLng': latlng },  (results, status) => {
                                        results.forEach(function(item1){
                                            item1.types.forEach(function(item2){
                                                if(item2 == "locality"){
                                                    Getautocomplete.forEach(function(item3){
														let fullads = (item1.formatted_address) ? item1.formatted_address : '';

														locationInfo.state = (item1.address_components) ? item1.address_components[0]["long_name"] : '';
                                                        locationInfo.fullAddress = fullads;
                                                        item3.value = fullads;
														locationInfo.geo = [lat, lng];

														item3.dataset.location = "";
                                                        item3.dataset.location = JSON.stringify(locationInfo);
														$(form).trigger("change")
                                                    });
                                                }
                                            });
                                        });
                                    });
                            });
                        });
                });
            }

		function Autocomplete(){
			var locationInfo = {
				geo: null,
				country: null,
				state: null,
				city: null,
				postalCode: null,
				street: null,
				streetNumber: null,
				name: null,
				reset: function() {
					this.geo = null;
					this.country = null;
					this.state = null;
					this.city = null;
					this.postalCode = null;
					this.street = null;
					this.streetNumber = null;
					this.fullAddress = null;
					this.name = null;
				}
			};

			var autocomplete = '';
			var googleAutocomplete = {
				autocompleteField: function(fieldId) {
					let Getinput = container[0].querySelectorAll(`#${fieldId}`);
					if( Getinput.length > 0 ){
						Getinput.forEach(function(self) {
							( autocomplete = new google.maps.places.Autocomplete( self )), { 
								types: ["geocode"] };

							google.maps.event.addListener(autocomplete, "place_changed", function() {
							var place = (autocomplete) ? autocomplete.getPlace() : "",
								address = ( place && place.address_components ) ? place.address_components : "",
								lat = ( place && place.geometry && place.geometry.location && place.geometry.location.lat() ) ? place.geometry.location.lat() : "",
								lng = ( place && place.geometry && place.geometry.location && place.geometry.location.lng() ) ? place.geometry.location.lng() : "";
								
								locationInfo.reset();

								locationInfo.geo = [lat, lng];
								if( address ){
									for (var i = 0; i < address.length; i++) {
										var component = address[i].types[0];
										switch (component) {
											case "country":
												locationInfo.country = address[i]["long_name"];
												break;
											case "administrative_area_level_1":
												locationInfo.state = address[i]["long_name"];
												break;
											case "locality":
												locationInfo.city = address[i]["long_name"];
												break;
											case "postal_code":
												locationInfo.postalCode = address[i]["long_name"];
												break;
											case "route":
												locationInfo.street = address[i]["long_name"];
												break;
											case "street_number":
												locationInfo.streetNumber = address[i]["long_name"];
												break;
											default:
												break;
										}
									}
								}

								locationInfo.fullAddress = ( place && place.formatted_address ) ? place.formatted_address : "";
								locationInfo.name = ( place && place.name ) ? place.name : ""; 

								self.dataset.location = "";
								self.dataset.location = JSON.stringify(locationInfo);
							});
						});
					}
				}
			};
			googleAutocomplete.autocompleteField("tp-autocomplete-input");
		}

		function MomentDate(){
			let GetDatestyle = container[0].querySelectorAll('.tp-date-wrap.style-2');
			if( GetDatestyle.length > 0 ){
				$('#tp-datepicker1').on('cancel.daterangepicker', function(ev, picker) {
					$(this).val('');
					$(this).trigger( "change" )
				});

				$('#tp-datepicker1').on('apply.daterangepicker', function(ev, picker) {
					$(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
					$(this).trigger( "change" )
				});

				let GetData = (GetDatestyle[0] && GetDatestyle[0].dataset && GetDatestyle[0].dataset.customdate) ? JSON.parse(GetDatestyle[0].dataset.customdate) : '',
					DefaultSelectOn = (GetData && GetData.DefaultSelect) ? GetData.DefaultSelect : 0,
					DisplayDate = (GetData && GetData.DisplayDate) ? GetData.DisplayDate : 0,
					DisplayYear = (GetData && GetData.DisplayYear) ? GetData.DisplayYear : 0,
					AutoApplyBtn = (GetData && GetData.AutoApplyBtn) ? true : false,
					DefaultSelect = (GetData && GetData.showDropdown) ? true : false,
					ShowCalendars = (GetData && GetData.ShowCalendars) ? true : false,
					ShowRanges = (GetData && GetData.showranges) ? true : false,
					ShowWeekNumber = (GetData && GetData.ShowWeekNumber) ? true : false,
					linkedCalendar = (GetData && GetData.linkedCalendar) ? true : false,
					ShowCustomRangeLabel = (GetData && GetData.ShowCustomRangeLabel) ? true : false,
					ApplyBtntxt = (GetData && GetData.ApplyBtntxt) ? GetData.ApplyBtntxt : '',
					CancelBtntxt = (GetData && GetData.CancelBtntxt) ? GetData.CancelBtntxt : '',
					ApplyBtnclass = (GetData && GetData.ApplyBtnclass) ? GetData.ApplyBtnclass : '',
					CancelBtnclass = (GetData && GetData.CancelBtnclass) ? GetData.CancelBtnclass : '',
					CustomLabelTxt = (GetData && GetData.CustomLabelTxt) ? GetData.CustomLabelTxt : '',
					DropsPosition = (GetData && GetData.DropsPosition) ? GetData.DropsPosition : 'auto',
					OpensPosition = (GetData && GetData.OpensPosition) ? GetData.OpensPosition : 'left';

				let StartDate, EndDate = 0;
				if(DefaultSelectOn){
					StartDate = (GetData && GetData.StartDate) ? GetData.StartDate : 0;
					EndDate = (GetData && GetData.EndDate) ? GetData.EndDate : 0;
				}

				let minDate, maxDate = 0;
				if(DisplayDate){
					minDate = (GetData && GetData.Min_date) ? GetData.Min_date : 0;
					maxDate = (GetData && GetData.Max_date) ? GetData.Max_date : 0;
				}

				let minDateYear, maxDateYear = '';
				if(DisplayYear){
					minDateYear = (GetData && GetData.Min_Year) ? Number(GetData.Min_Year) : '';
					maxDateYear = (GetData && GetData.Max_Year) ? Number(GetData.Max_Year) : '';
				}

				let ranges='';
				if(ShowRanges && GetData && GetData.RangesOption){
					ranges = {};
					if(GetData.RangesOption.today){
						ranges['Today'] = [moment(), moment()];
					}
					if(GetData.RangesOption.yesterday){
						ranges['Yesterday'] = [moment().subtract(1, 'days'), moment().subtract(1, 'days')];
					}
					if(GetData.RangesOption.ThisMonth){
						ranges['Last Month'] = [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')];
					}
					if(GetData.RangesOption.LastMonth){
						ranges['This Month'] = [moment().startOf('month'), moment().endOf('month')];
					}
					if(GetData.RangesOption.Last30Days){
						ranges['Last 30 Days'] = [moment().subtract(29, 'days'), moment()];
					}
					if(GetData.RangesOption.Last7Days){
						ranges['Last 7 Days'] = [moment().subtract(6, 'days'), moment()];
					}
				}

				let monthNames, daysOfWeek='';
				if(ShowCalendars){
					monthNames , daysOfWeek = [];
					if(GetData && GetData.locale && GetData.locale.Week){
						daysOfWeek = [
							( GetData.locale.Week[0] ) ? GetData.locale.Week[0] : 'Su',
							( GetData.locale.Week[0] ) ? GetData.locale.Week[1] : 'Mo',
							( GetData.locale.Week[2] ) ? GetData.locale.Week[2] : 'Tu',
							( GetData.locale.Week[3] ) ? GetData.locale.Week[3] : 'We',
							( GetData.locale.Week[4] ) ? GetData.locale.Week[4] : 'Th',
							( GetData.locale.Week[5] ) ? GetData.locale.Week[5] : 'Fr',
							( GetData.locale.Week[6] ) ? GetData.locale.Week[6] : 'Sa',
						]
					}

					if(GetData && GetData.locale && GetData.locale.Months){
						monthNames = [
							( GetData.locale.Months[0] ) ? GetData.locale.Months[0] : 'January',
							( GetData.locale.Months[1] ) ? GetData.locale.Months[1] : 'February',
							( GetData.locale.Months[2] ) ? GetData.locale.Months[2] : 'March',
							( GetData.locale.Months[3] ) ? GetData.locale.Months[3] : 'April',
							( GetData.locale.Months[4] ) ? GetData.locale.Months[4] : 'May',
							( GetData.locale.Months[5] ) ? GetData.locale.Months[5] : 'June',
							( GetData.locale.Months[6] ) ? GetData.locale.Months[6] : 'July',
							( GetData.locale.Months[7] ) ? GetData.locale.Months[7] : 'August',
							( GetData.locale.Months[8] ) ? GetData.locale.Months[8] : 'September',
							( GetData.locale.Months[9] ) ? GetData.locale.Months[9] : 'October',
							( GetData.locale.Months[10] ) ? GetData.locale.Months[10] : 'November',
							( GetData.locale.Months[11] ) ? GetData.locale.Months[11] : 'December',
						]						
					}

				}
				
				$('.tp-custom-date', $scope).daterangepicker({
					"showDropdowns": DefaultSelect,
					"autoApply": AutoApplyBtn,
					"alwaysShowCalendars": ShowCalendars,
					"showWeekNumbers": ShowWeekNumber,
					"linkedCalendars": linkedCalendar,
					"singleDatePicker": false,
					"drops": DropsPosition,
					"opens": OpensPosition,

					"showCustomRangeLabel": ShowCustomRangeLabel,
					ranges,

					"minYear": minDateYear,
					"maxYear": maxDateYear,
					"startDate" : StartDate,
					"endDate" : EndDate,
					"minDate": minDate,
					"maxDate": maxDate,

					"autoUpdateInput": false,
					"locale": {
						"separator": " - ",
						"applyLabel": ApplyBtntxt,
        				"cancelLabel": CancelBtntxt,
						"customRangeLabel": CustomLabelTxt,
		
						daysOfWeek,
						monthNames,
					},

					"applyButtonClasses": ApplyBtnclass,
					"cancelClass": CancelBtnclass,
				}, function(start, end, label) {				
				});
				
			}
		}

		function mergeTooltips(slider, threshold, separator) {

            var textIsRtl = getComputedStyle(slider).direction === 'rtl';
            var isRtl = slider.noUiSlider.options.direction === 'rtl';
            var isVertical = slider.noUiSlider.options.orientation === 'vertical';
            var tooltips = slider.noUiSlider.getTooltips();
            var origins = slider.noUiSlider.getOrigins();
        
            // Move tooltips into the origin element. The default stylesheet handles this.
            tooltips.forEach(function (tooltip, index) {
                if (tooltip) {
                    origins[index].appendChild(tooltip);
                }
            });
        
            slider.noUiSlider.on('update', function (values, handle, unencoded, tap, positions) {
        
                var pools = [[]];
                var poolPositions = [[]];
                var poolValues = [[]];
                var atPool = 0;
        
                // Assign the first tooltip to the first pool, if the tooltip is configured
                if (tooltips[0]) {
                    pools[0][0] = 0;
                    poolPositions[0][0] = positions[0];
                    poolValues[0][0] = values[0];
                }
        
                for (var i = 1; i < positions.length; i++) {
                    if (!tooltips[i] || (positions[i] - positions[i - 1]) > threshold) {
                        atPool++;
                        pools[atPool] = [];
                        poolValues[atPool] = [];
                        poolPositions[atPool] = [];
                    }
        
                    if (tooltips[i]) {
                        pools[atPool].push(i);
                        poolValues[atPool].push(values[i]);
                        poolPositions[atPool].push(positions[i]);
                    }
                }
        
                pools.forEach(function (pool, poolIndex) {
                    var handlesInPool = pool.length;
        
                    for (var j = 0; j < handlesInPool; j++) {
                        var handleNumber = pool[j];
        
                        if (j === handlesInPool - 1) {
                            var offset = 0;
        
                            poolPositions[poolIndex].forEach(function (value) {
                                offset += 1000 - value;
                            });
        
                            var direction = isVertical ? 'bottom' : 'right';
                            var last = isRtl ? 0 : handlesInPool - 1;
                            var lastOffset = 1000 - poolPositions[poolIndex][last];
                            offset = (textIsRtl && !isVertical ? 100 : 0) + (offset / handlesInPool) - lastOffset;
        
                            // Center this tooltip over the affected handles
                            tooltips[handleNumber].innerHTML = poolValues[poolIndex].join(separator);
                            tooltips[handleNumber].style.display = 'block';
                            tooltips[handleNumber].style[direction] = offset + '%';
                        } else {
                            // Hide this tooltip
                            tooltips[handleNumber].style.display = 'none';
                        }
                    }
                });
            });
        }
	};

	$(window).on('elementor/frontend/init', function () {
		elementorFrontend.hooks.addAction('frontend/element_ready/tp-search-filter.default', WidgetSearchFilterHandler);
	});

})(jQuery);	