this.wc=this.wc||{},this.wc.blocks=this.wc.blocks||{},this.wc.blocks["mini-cart"]=function(t){function e(e){for(var r,i,u=e[0],l=e[1],s=e[2],a=0,f=[];a<u.length;a++)i=u[a],Object.prototype.hasOwnProperty.call(o,i)&&o[i]&&f.push(o[i][0]),o[i]=0;for(r in l)Object.prototype.hasOwnProperty.call(l,r)&&(t[r]=l[r]);for(p&&p(e);f.length;)f.shift()();return c.push.apply(c,s||[]),n()}function n(){for(var t,e=0;e<c.length;e++){for(var n=c[e],r=!0,u=1;u<n.length;u++){var l=n[u];0!==o[l]&&(r=!1)}r&&(c.splice(e--,1),t=i(i.s=n[0]))}return t}var r={},o={26:0},c=[];function i(e){if(r[e])return r[e].exports;var n=r[e]={i:e,l:!1,exports:{}};return t[e].call(n.exports,n,n.exports,i),n.l=!0,n.exports}i.m=t,i.c=r,i.d=function(t,e,n){i.o(t,e)||Object.defineProperty(t,e,{enumerable:!0,get:n})},i.r=function(t){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})},i.t=function(t,e){if(1&e&&(t=i(t)),8&e)return t;if(4&e&&"object"==typeof t&&t&&t.__esModule)return t;var n=Object.create(null);if(i.r(n),Object.defineProperty(n,"default",{enumerable:!0,value:t}),2&e&&"string"!=typeof t)for(var r in t)i.d(n,r,function(e){return t[e]}.bind(null,r));return n},i.n=function(t){var e=t&&t.__esModule?function(){return t.default}:function(){return t};return i.d(e,"a",e),e},i.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},i.p="";var u=window.webpackWcBlocksJsonp=window.webpackWcBlocksJsonp||[],l=u.push.bind(u);u.push=e,u=u.slice();for(var s=0;s<u.length;s++)e(u[s]);var p=l;return c.push([820,0]),n()}({0:function(t,e){t.exports=window.wp.element},1:function(t,e){t.exports=window.wp.i18n},11:function(t,e,n){"use strict";n.d(e,"q",(function(){return c})),n.d(e,"p",(function(){return i})),n.d(e,"o",(function(){return u})),n.d(e,"l",(function(){return s})),n.d(e,"e",(function(){return p})),n.d(e,"f",(function(){return a})),n.d(e,"i",(function(){return f})),n.d(e,"h",(function(){return d})),n.d(e,"n",(function(){return b})),n.d(e,"m",(function(){return w})),n.d(e,"c",(function(){return O})),n.d(e,"d",(function(){return g})),n.d(e,"g",(function(){return m})),n.d(e,"j",(function(){return h})),n.d(e,"a",(function(){return j})),n.d(e,"k",(function(){return v})),n.d(e,"b",(function(){return S})),n.d(e,"t",(function(){return E})),n.d(e,"u",(function(){return k})),n.d(e,"r",(function(){return P})),n.d(e,"s",(function(){return _}));var r,o=n(3),c=Object(o.getSetting)("wcBlocksConfig",{buildPhase:1,pluginUrl:"",productCount:0,defaultAvatar:"",restApiRoutes:{},wordCountType:"words"}),i=c.pluginUrl+"images/",u=c.pluginUrl+"build/",l=c.buildPhase,s=null===(r=o.STORE_PAGES.shop)||void 0===r?void 0:r.permalink,p=o.STORE_PAGES.checkout.id,a=o.STORE_PAGES.checkout.permalink,f=o.STORE_PAGES.privacy.permalink,d=o.STORE_PAGES.privacy.title,b=o.STORE_PAGES.terms.permalink,w=o.STORE_PAGES.terms.title,O=o.STORE_PAGES.cart.id,g=o.STORE_PAGES.cart.permalink,m=o.STORE_PAGES.myaccount.permalink?o.STORE_PAGES.myaccount.permalink:Object(o.getSetting)("wpLoginUrl","/wp-login.php"),h=Object(o.getSetting)("shippingCountries",{}),j=Object(o.getSetting)("allowedCountries",{}),v=Object(o.getSetting)("shippingStates",{}),S=Object(o.getSetting)("allowedStates",{}),y=n(25),E=function(t,e){if(l>2)return Object(y.registerBlockType)(t,e)},k=function(t,e){if(l>1)return Object(y.registerBlockType)(t,e)},P=function(){return l>2},_=function(){return l>1}},21:function(t,e){t.exports=window.wp.blockEditor},25:function(t,e){t.exports=window.wp.blocks},3:function(t,e){t.exports=window.wc.wcSettings},33:function(t,e){t.exports=window.wp.primitives},488:function(t,e,n){"use strict";var r=n(0),o=n(33),c=Object(r.createElement)(o.SVG,{xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 24 24"},Object(r.createElement)("path",{fill:"none",d:"M0 0h24v24H0V0z"}),Object(r.createElement)("path",{d:"M15.55 13c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.37-.66-.11-1.48-.87-1.48H5.21l-.94-2H1v2h2l3.6 7.59-1.35 2.44C4.52 15.37 5.48 17 7 17h12v-2H7l1.1-2h7.45zM6.16 6h12.15l-2.76 5H8.53L6.16 6zM7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zm10 0c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"}));e.a=c},62:function(t,e,n){"use strict";var r=n(5),o=n.n(r),c=n(14),i=n.n(c),u=n(0),l=["srcElement","size"];function s(t,e){var n=Object.keys(t);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(t);e&&(r=r.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),n.push.apply(n,r)}return n}e.a=function(t){var e=t.srcElement,n=t.size,r=void 0===n?24:n,c=i()(t,l);return Object(u.isValidElement)(e)?Object(u.cloneElement)(e,function(t){for(var e=1;e<arguments.length;e++){var n=null!=arguments[e]?arguments[e]:{};e%2?s(Object(n),!0).forEach((function(e){o()(t,e,n[e])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(n)):s(Object(n)).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(n,e))}))}return t}({width:r,height:r},c)):null}},820:function(t,e,n){t.exports=n(850)},850:function(t,e,n){"use strict";n.r(e);var r=n(0),o=n(1),c=n(62),i=n(488),u=n(11),l=n(21),s={apiVersion:2,title:Object(o.__)("Mini Cart","woo-gutenberg-products-block"),icon:{src:Object(r.createElement)(c.a,{srcElement:i.a}),foreground:"#96588a"},category:"woocommerce",keywords:[Object(o.__)("WooCommerce","woo-gutenberg-products-block")],description:Object(o.__)("Display a mini cart widget.","woo-gutenberg-products-block"),supports:{html:!1,multiple:!1},example:{attributes:{isPreview:!0}},attributes:{isPreview:{type:"boolean",default:!1,save:!1}},edit:function(){var t=Object(l.useBlockProps)({className:"wc-block-mini-cart"});return Object(r.createElement)("div",t,Object(r.createElement)("button",{className:"wc-block-mini-cart__button"},Object(o.sprintf)(
/* translators: %d is the number of products in the cart. */
Object(o._n)("%d product","%d products",0,"woo-gutenberg-products-block"),0)))},save:function(){return null}};Object(u.u)("woocommerce/mini-cart",s)}});