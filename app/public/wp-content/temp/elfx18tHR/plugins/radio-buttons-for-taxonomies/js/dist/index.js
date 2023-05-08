(()=>{"use strict";var e={n:t=>{var r=t&&t.__esModule?()=>t.default:()=>t;return e.d(r,{a:r}),r},d:(t,r)=>{for(var s in r)e.o(r,s)&&!e.o(t,s)&&Object.defineProperty(t,s,{enumerable:!0,get:r[s]})},o:(e,t)=>Object.prototype.hasOwnProperty.call(e,t)};const t=window.wp.element,r=window.lodash,s=window.wp.i18n,a=window.wp.components,n=window.wp.data,o=window.wp.compose,i=window.wp.apiFetch;var l=e.n(i);const m=window.wp.url;function h(e){const t=e.map((e=>({children:[],parent:null,...e}))),s=(0,r.groupBy)(t,"parent");if(s.null&&s.null.length)return t;const a=e=>e.map((e=>{const t=s[e.id];return{...e,children:t&&t.length?a(t):[]}}));return a(s[0]||[])}const d={per_page:-1,orderby:"name",order:"asc",_fields:"id,name,parent"};class c extends t.Component{constructor(){super(...arguments),this.findTerm=this.findTerm.bind(this),this.onChange=this.onChange.bind(this),this.onChangeFormName=this.onChangeFormName.bind(this),this.onChangeFormParent=this.onChangeFormParent.bind(this),this.onAddTerm=this.onAddTerm.bind(this),this.onToggleForm=this.onToggleForm.bind(this),this.setFilterValue=this.setFilterValue.bind(this),this.sortBySelected=this.sortBySelected.bind(this),this.state={loading:!0,availableTermsTree:[],availableTerms:[],adding:!1,formName:"",formParent:"",showForm:!1,filterValue:"",filteredTermsTree:[]}}onChange(e){const{onUpdateTerms:t,taxonomy:r}=this.props;t([e],r.rest_base)}onClear(){const{onUpdateTerms:e,taxonomy:t}=this.props;e([],t.rest_base)}onChangeFormName(e){const t=""===e.target.value.trim()?"":e.target.value;this.setState({formName:t})}onChangeFormParent(e){this.setState({formParent:e})}onToggleForm(){this.setState((e=>({showForm:!e.showForm})))}findTerm(e,t,s){return(0,r.find)(e,(e=>(!e.parent&&!t||parseInt(e.parent)===parseInt(t))&&e.name.toLowerCase()===s.toLowerCase()))}onAddTerm(e){e.preventDefault();const{onUpdateTerms:t,taxonomy:a,terms:n,slug:o}=this.props,{formName:i,formParent:c,adding:p,availableTerms:u}=this.state;if(""===i||p)return;const g=this.findTerm(u,c,i);if(g)return(0,r.some)(n,(e=>e===g.id))||t([g.id],a.rest_base),void this.setState({formName:"",formParent:""});this.setState({adding:!0}),this.addRequest=l()({path:`/wp/v2/${a.rest_base}`,method:"POST",data:{name:i,parent:c||void 0}}),this.addRequest.catch((e=>"term_exists"===e.code?(this.addRequest=l()({path:(0,m.addQueryArgs)(`/wp/v2/${a.rest_base}`,{...d,parent:c||0,search:i})}),this.addRequest.then((e=>this.findTerm(e,c,i)))):Promise.reject(e))).then((e=>{const n=(0,r.find)(this.state.availableTerms,(t=>t.id===e.id))?this.state.availableTerms:[e,...this.state.availableTerms],i=(0,s.sprintf)(
/* translators: %s: taxonomy name */
(0,s._x)("%s added","term"),(0,r.get)(this.props.taxonomy,["labels","singular_name"],"category"===o?(0,s.__)("Category"):(0,s.__)("Term")));this.props.speak(i,"assertive"),this.addRequest=null,this.setState({adding:!1,formName:"",formParent:"",availableTerms:n,availableTermsTree:this.sortBySelected(h(n))}),t([e.id],a.rest_base)}),(e=>{"abort"!==e.statusText&&(this.addRequest=null,this.setState({adding:!1}))}))}componentDidMount(){this.fetchTerms()}componentWillUnmount(){(0,r.invoke)(this.fetchRequest,["abort"]),(0,r.invoke)(this.addRequest,["abort"])}componentDidUpdate(e){this.props.taxonomy!==e.taxonomy&&this.fetchTerms()}fetchTerms(){const{taxonomy:e}=this.props;e&&(this.fetchRequest=l()({path:(0,m.addQueryArgs)(`/wp/v2/${e.rest_base}`,d)}),this.fetchRequest.then((e=>{const t=this.sortBySelected(h(e));this.fetchRequest=null,this.setState({loading:!1,availableTermsTree:t,availableTerms:e})}),(e=>{"abort"!==e.statusText&&(this.fetchRequest=null,this.setState({loading:!1}))})))}sortBySelected(e){const{terms:t}=this.props,r=e=>-1!==t.indexOf(e.id)||void 0!==e.children&&!!(e.children.map(r).filter((e=>e)).length>0);return e.sort(((e,t)=>{const s=r(e),a=r(t);return s===a?0:s&&!a?-1:!s&&a?1:0})),e}setFilterValue(e){const{availableTermsTree:t}=this.state,r=e.target.value,a=t.map(this.getFilterMatcher(r)).filter((e=>e)),n=e=>{let t=0;for(let r=0;r<e.length;r++)t++,void 0!==e[r].children&&(t+=n(e[r].children));return t};this.setState({filterValue:r,filteredTermsTree:a});const o=n(a),i=(0,s.sprintf)(
/* translators: %d: number of results */
(0,s._n)("%d result found.","%d results found.",o),o);this.props.debouncedSpeak(i,"assertive")}getFilterMatcher(e){const t=r=>{if(""===e)return r;const s={...r};return s.children.length>0&&(s.children=s.children.map(t).filter((e=>e))),(-1!==s.name.toLowerCase().indexOf(e.toLowerCase())||s.children.length>0)&&s};return t}renderTerms(e){const{terms:s=[],taxonomy:n}=this.props,o=n.hierarchical?"hierarchical":"non-hierarchical";return e.map((e=>{e.id;const i=-1!==s.indexOf(e.id)||!s.length&&e.id===n.default_term?e.id:0;return(0,t.createElement)("div",{key:e.id,className:"radio-taxonomies-choice editor-post-taxonomies__hierarchical-terms-choice "+(n.hierarchical?"":"editor-post-taxonomies__non-hierarchical-terms-choice")},(0,t.createElement)(a.RadioControl,{selected:i,options:[{label:(0,r.unescape)(e.name),value:e.id}],onChange:()=>{const t=parseInt(e.id,10);this.onChange(t)}}),!!e.children.length&&(0,t.createElement)("div",{className:"editor-post-taxonomies__"+o+"-terms-subchoices "},this.renderTerms(e.children)))}))}render(){const{slug:e,taxonomy:n,terms:o,instanceId:i,hasCreateAction:l,hasAssignAction:m}=this.props,h=n.hierarchical?"hierarchical":"non-hierarchical";if(!m)return null;const{availableTermsTree:d,availableTerms:c,filteredTermsTree:p,formName:u,formParent:g,loading:_,showForm:f,filterValue:b}=this.state,T=(t,s,a)=>(0,r.get)(n,["labels",t],"category"===e?s:a),w=T("add_new_item",(0,s.__)("Add new category"),(0,s.__)("Add new term")),y=T("new_item_name",(0,s.__)("Add new category"),(0,s.__)("Add new term")),x=T("parent_item",(0,s.__)("Parent Category"),(0,s.__)("Parent Term")),v=`— ${x} —`,C=w,F=`editor-post-taxonomies__${h}-terms-input-${i}`,S=`editor-post-taxonomies__${h}-terms-filter-${i}`,N=(0,r.get)(this.props.taxonomy,["labels","search_items"],(0,s.__)("Search Terms")),P=(0,r.get)(this.props.taxonomy,["name"],(0,s.__)("Terms")),k=c.length>=8,A=o.length?0:-1,E=(0,s.sprintf)(
/* translators: %s: taxonomy name */
(0,s._x)("No %s","term","radio-buttons-for-taxonomies"),(0,r.get)(this.props.taxonomy,["labels","singular_name"],"category"===e?(0,s.__)("Category"):(0,s.__)("Term")));return[k&&(0,t.createElement)("label",{key:"filter-label",htmlFor:S},N),k&&(0,t.createElement)("input",{type:"search",id:S,value:b,onChange:this.setFilterValue,className:"editor-post-taxonomies__hierarchical-terms-filter",key:"term-filter-input"}),(0,t.createElement)("div",{className:"editor-post-taxonomies__hierarchical-terms-list",key:"term-list",tabIndex:"0",role:"group","aria-label":P},this.renderTerms(""!==b?p:d),n.radio_no_term&&(0,t.createElement)("div",{key:"no-term",className:"editor-post-taxonomies__"+h+"-terms-choice "},(0,t.createElement)(a.RadioControl,{selected:A,options:[{label:E,value:-1}],onChange:()=>{this.onClear()}}))),!_&&l&&(0,t.createElement)(a.Button,{key:"term-add-button",onClick:this.onToggleForm,className:"editor-post-taxonomies__hierarchical-terms-add","aria-expanded":f,isLink:!0},w),f&&(0,t.createElement)("form",{onSubmit:this.onAddTerm,key:h+"-terms-form"},(0,t.createElement)("label",{htmlFor:F,className:"editor-post-taxonomies__hierarchical-terms-label"},y),(0,t.createElement)("input",{type:"text",id:F,className:"editor-post-taxonomies__hierarchical-terms-input",value:u,onChange:this.onChangeFormName,required:!0}),n.hierarchical&&!!c.length&&(0,t.createElement)(a.TreeSelect,{label:x,noOptionLabel:v,onChange:this.onChangeFormParent,selectedId:g,tree:d}),(0,t.createElement)(a.Button,{isSecondary:!0,type:"submit",className:"editor-post-taxonomies__hierarchical-terms-submit"},C))]}}const p=(0,o.compose)([(0,n.withSelect)(((e,t)=>{let{slug:s}=t;const{getCurrentPost:a}=e("core/editor"),{getTaxonomy:n}=e("core"),o=n(s);return{hasCreateAction:!!o&&(0,r.get)(a(),["_links","wp:action-create-"+o.rest_base],!1),hasAssignAction:!!o&&(0,r.get)(a(),["_links","wp:action-assign-"+o.rest_base],!1),terms:o?e("core/editor").getEditedPostAttribute(o.rest_base):[],taxonomy:o}})),(0,n.withDispatch)((e=>({onUpdateTerms(t,r){e("core/editor").editPost({[r]:t})}}))),a.withSpokenMessages,o.withInstanceId])(c);wp.hooks.addFilter("editor.PostTaxonomyType","RB4T",(function(e){return function(t){return RB4Tl18n.radio_taxonomies.indexOf(t.slug)>=0?wp.element.createElement(p,t):wp.element.createElement(e,t)}}))})();