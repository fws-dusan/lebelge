this.wc=this.wc||{},this.wc.wcBlocksMiddleware=function(e){var t={};function r(n){if(t[n])return t[n].exports;var o=t[n]={i:n,l:!1,exports:{}};return e[n].call(o.exports,o,o.exports,r),o.l=!0,o.exports}return r.m=e,r.c=t,r.d=function(e,t,n){r.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:n})},r.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},r.t=function(e,t){if(1&t&&(e=r(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var n=Object.create(null);if(r.r(n),Object.defineProperty(n,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var o in e)r.d(n,o,function(t){return e[t]}.bind(null,o));return n},r.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return r.d(t,"a",t),t},r.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},r.p="",r(r.s=165)}({1:function(e,t){e.exports=function(e,t,r){return t in e?Object.defineProperty(e,t,{value:r,enumerable:!0,configurable:!0,writable:!0}):e[t]=r,e},e.exports.default=e.exports,e.exports.__esModule=!0},12:function(e,t){e.exports=window.wp.apiFetch},13:function(e,t,r){"use strict";r.d(t,"c",(function(){return o})),r.d(t,"a",(function(){return c})),r.d(t,"b",(function(){return u}));var n=r(2),o="wc/store/cart",c={code:"cart_api_error",message:Object(n.__)("Unable to get cart data from the API.","woo-gutenberg-products-block"),data:{status:500}},u="wc-blocks_cart_update_timestamp"},165:function(e,t,r){"use strict";r.r(t);var n=r(1),o=r.n(n),c=r(12),u=r.n(c);function i(e,t){var r=Object.keys(e);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(e);t&&(n=n.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),r.push.apply(r,n)}return r}function a(e){for(var t=1;t<arguments.length;t++){var r=null!=arguments[t]?arguments[t]:{};t%2?i(Object(r),!0).forEach((function(t){o()(e,t,r[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(r)):i(Object(r)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(r,t))}))}return e}var l="",s=0;try{var f=window.localStorage.getItem("storeApiNonce"),p=f?JSON.parse(f):{};l=(null==p?void 0:p.nonce)||"",s=(null==p?void 0:p.timestamp)||0}catch(e){}var d=function(e,t){e!==l&&(s&&t<s||(l=e,s=t||Date.now()/1e3,window.localStorage.setItem("storeApiNonce",JSON.stringify({nonce:l,timestamp:s}))))},b=function(e){var t=e.headers||{};return e.headers=a(a({},t),{},{"X-WC-Store-API-Nonce":l}),e};u.a.use((function(e,t){var r,n;return function(e){var t=e.url||e.path;return!(!t||!e.method||"GET"===e.method)&&null!==/wc\/store\//.exec(t)}(e)&&(e=b(e),Array.isArray(null===(r=e)||void 0===r||null===(n=r.data)||void 0===n?void 0:n.requests)&&(e.data.requests=e.data.requests.map(b))),t(e,t)})),u.a.setNonce=function(e){var t="function"==typeof(null==e?void 0:e.get)?e.get("X-WC-Store-API-Nonce"):e["X-WC-Store-API-Nonce"],r="function"==typeof(null==e?void 0:e.get)?e.get("X-WC-Store-API-Nonce-Timestamp"):e["X-WC-Store-API-Nonce-Timestamp"];t&&d(t,r)},d(wcBlocksMiddlewareConfig.storeApiNonce,wcBlocksMiddlewareConfig.storeApiNonceTimestamp);var w=r(13);u.a.use((function(e,t){return function(e){var t=e.url||e.path||"",r=e.method||"GET";if(!t||"POST"!==r)return!1;var n,o=/wc\/store\/cart\//,c=null!==o.exec(t),u=null!==/wc\/store\/batch/.exec(t);return!!c||!!u&&((null==e||null===(n=e.data)||void 0===n?void 0:n.requests)||[]).some((function(e){var t=e.path||"";return null!==o.exec(t)}))}(e)&&window.localStorage.setItem(w.b,(Date.now()/1e3).toString()),t(e,t)}))},2:function(e,t){e.exports=window.wp.i18n}});