(window.webpackWcBlocksJsonp=window.webpackWcBlocksJsonp||[]).push([[18],{160:function(e,t,c){"use strict";var n=c(9),o=c.n(n),r=c(14),a=c.n(r),s=c(0),i=c(26),l=c(6),u=c.n(l),p=(c(205),["className","disabled","name","permalink"]);t.a=function(e){var t=e.className,c=void 0===t?"":t,n=e.disabled,r=void 0!==n&&n,l=e.name,b=e.permalink,d=void 0===b?"":b,m=a()(e,p),O=u()("wc-block-components-product-name",c);return r?Object(s.createElement)("span",o()({className:O},m,{dangerouslySetInnerHTML:{__html:Object(i.decodeEntities)(l)}})):Object(s.createElement)("a",o()({className:O,href:d},m,{dangerouslySetInnerHTML:{__html:Object(i.decodeEntities)(l)}}))}},205:function(e,t){},375:function(e,t,c){"use strict";var n=c(5),o=c.n(n),r=c(11);function a(e,t){var c=Object.keys(e);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(e);t&&(n=n.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),c.push.apply(c,n)}return c}function s(e){for(var t=1;t<arguments.length;t++){var c=null!=arguments[t]?arguments[t]:{};t%2?a(Object(c),!0).forEach((function(t){o()(e,t,c[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(c)):a(Object(c)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(c,t))}))}return e}var i={headingLevel:{type:"number",default:2},showProductLink:{type:"boolean",default:!0},productId:{type:"number",default:0}};Object(r.s)()&&(i=s(s({},i),{},{align:{type:"string"},color:{type:"string"},customColor:{type:"string"},fontSize:{type:"string"},customFontSize:{type:"number"}})),t.a=i},380:function(e,t,c){"use strict";var n=c(5),o=c.n(n),r=c(0),a=(c(2),c(6)),s=c.n(a),i=c(44),l=c(21),u=c(11),p=function(e){var t=e.color,c=e.fontSize;return Object(u.s)()?{color:t,fontSize:c}:{}},b=c(84),d=c(160),m=c(66);c(538),t.a=Object(b.withProductDataContext)((function(e){var t,c,n,a=e.className,b=e.headingLevel,O=void 0===b?2:b,f=e.showProductLink,j=void 0===f||f,y=e.align,w=e.color,g=e.customColor,v=e.fontSize,k=e.customFontSize,h=Object(i.useInnerBlockLayoutContext)().parentClassName,S=Object(i.useProductDataContext)().product,P=Object(m.a)().dispatchStoreEvent,z="h".concat(O),C=Object(l.getColorClassName)("color",w),E=Object(l.getFontSizeClass)(v),N=s()((t={"has-text-color":w||g,"has-font-size":v||k},o()(t,C,C),o()(t,E,E),t));return S.id?Object(r.createElement)(z,{className:s()(a,"wc-block-components-product-title",(c={},o()(c,"".concat(h,"__product-title"),h),o()(c,"wc-block-components-product-title--align-".concat(y),y&&Object(u.s)()),c))},Object(r.createElement)(d.a,{className:s()(o()({},N,Object(u.s)())),disabled:!j,name:S.name,permalink:S.permalink,rel:j?"nofollow":null,style:p({color:g,fontSize:k}),onClick:function(){P("product-view-link",{product:S})}})):Object(r.createElement)(z,{className:s()(a,"wc-block-components-product-title",(n={},o()(n,"".concat(h,"__product-title"),h),o()(n,"wc-block-components-product-title--align-".concat(y),y&&Object(u.s)()),o()(n,N,Object(u.s)()),n)),style:p({color:g,fontSize:k})})}))},538:function(e,t){},876:function(e,t,c){"use strict";c.r(t);var n=c(874),o=c(380),r=c(375);t.default=Object(n.a)(r.a)(o.a)}}]);