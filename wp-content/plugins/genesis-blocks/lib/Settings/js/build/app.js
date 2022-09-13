!function(e){var t={};function n(s){if(t[s])return t[s].exports;var r=t[s]={i:s,l:!1,exports:{}};return e[s].call(r.exports,r,r.exports,n),r.l=!0,r.exports}n.m=e,n.c=t,n.d=function(e,t,s){n.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:s})},n.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},n.t=function(e,t){if(1&t&&(e=n(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var s=Object.create(null);if(n.r(s),Object.defineProperty(s,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var r in e)n.d(s,r,function(t){return e[t]}.bind(null,r));return s},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,"a",t),t},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p="",n(n.s=20)}([function(e,t){e.exports=window.wp.element},function(e,t){e.exports=window.wp.data},function(e,t){e.exports=window.wp.components},function(e,t){e.exports=window.wp.compose},function(e,t){e.exports=window.wp.i18n},function(e,t){e.exports=function(e,t,n){return t in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e},e.exports.default=e.exports,e.exports.__esModule=!0},function(e,t,n){var s=n(15),r=n(16),i=n(17),o=n(19);e.exports=function(e,t){return s(e)||r(e,t)||i(e,t)||o()},e.exports.default=e.exports,e.exports.__esModule=!0},function(e,t){e.exports=window.regeneratorRuntime},function(e,t){e.exports=window.wp.blockEditor},function(e,t){e.exports=window.wp.plugins},function(e,t){e.exports=window.wp.dataControls},function(e,t){e.exports=window.wp.a11y},function(e,t){e.exports=window.wp.hooks},function(e,t){e.exports=window.lodash},function(e,t){e.exports=window.wp.coreData},function(e,t){e.exports=function(e){if(Array.isArray(e))return e},e.exports.default=e.exports,e.exports.__esModule=!0},function(e,t){e.exports=function(e,t){var n=null==e?null:"undefined"!=typeof Symbol&&e[Symbol.iterator]||e["@@iterator"];if(null!=n){var s,r,i=[],o=!0,a=!1;try{for(n=n.call(e);!(o=(s=n.next()).done)&&(i.push(s.value),!t||i.length!==t);o=!0);}catch(e){a=!0,r=e}finally{try{o||null==n.return||n.return()}finally{if(a)throw r}}return i}},e.exports.default=e.exports,e.exports.__esModule=!0},function(e,t,n){var s=n(18);e.exports=function(e,t){if(e){if("string"==typeof e)return s(e,t);var n=Object.prototype.toString.call(e).slice(8,-1);return"Object"===n&&e.constructor&&(n=e.constructor.name),"Map"===n||"Set"===n?Array.from(e):"Arguments"===n||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)?s(e,t):void 0}},e.exports.default=e.exports,e.exports.__esModule=!0},function(e,t){e.exports=function(e,t){(null==t||t>e.length)&&(t=e.length);for(var n=0,s=new Array(t);n<t;n++)s[n]=e[n];return s},e.exports.default=e.exports,e.exports.__esModule=!0},function(e,t){e.exports=function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")},e.exports.default=e.exports,e.exports.__esModule=!0},function(e,t,n){"use strict";n.r(t);var s={};n.r(s),n.d(s,"getSettings",(function(){return O})),n.d(s,"getCustom",(function(){return j})),n.d(s,"getFormInfo",(function(){return h})),n.d(s,"getSections",(function(){return y})),n.d(s,"getModifiedSettings",(function(){return _}));var r={};n.r(r),n.d(r,"updateSetting",(function(){return x})),n.d(r,"updateCustom",(function(){return E})),n.d(r,"resetFormSaveState",(function(){return C})),n.d(r,"saveSettings",(function(){return P}));var i=n(0),o=n(4),a=n(3),c=n(2),l=n(1),u=n(9),d=n(10),g=n(5),b=n.n(g);function p(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var s=Object.getOwnPropertySymbols(e);t&&(s=s.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,s)}return n}function f(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?p(Object(n),!0).forEach((function(t){b()(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):p(Object(n)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}
/**
 * Reducer for the Genesis Blocks data store.
 *
 * The reducer handles actions sent to the data store. Reducers must be pure so
 * they should have no side effects. Do not put API calls into the reducer.
 *
 * @since   1.0.0
 * @author  StudioPress
 * @license GPL-2.0-or-later
 */var m=f({form:{fail:!1,success:!1,is_saving:!1},custom:[],modifiedSettings:[]},genesisBlocksSettingsData),v=function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:m,t=arguments.length>1?arguments[1]:void 0;return"UPDATE_CUSTOM"===t.type?f(f({},e),{},{custom:f(f({},e.custom),{},b()({},t.setting.key,t.setting.value))}):"UPDATE_SETTING"===t.type?f(f({},e),{},{settings:f(f({},e.settings),{},b()({},t.setting.key,t.setting.value)),modifiedSettings:f(f({},e.modifiedSettings),{},b()({},t.setting.key,t.setting.value))}):"SAVING"===t.type?f(f({},e),{},{form:f(f({},e.form),{},{fail:!1,success:!1,is_saving:!0})}):"SAVED"===t.type?f(f({},e),{},{form:f(f({},e.form),{},{success:!0===t.success,fail:!0!==t.success,is_saving:!1}),modifiedSettings:t.success?[]:e.modifiedSettings}):"RESET"===t.type?f(f({},e),{},{form:f(f({},e.form),{},{fail:!1,success:!1,is_saving:!1})}):e},O=function(e){return e.settings||{}},j=function(e){return e.custom||{}},h=function(e){return e.form||{}};function y(e){return e.hasOwnProperty("sections")?e.sections:{}}var _=function(e){return e.modifiedSettings||[]},S=n(7),w=n.n(S),k=(n(14),w.a.mark(P));
/**
 * Actions let components change store state by sending a payload of data.
 *
 * This file exposes methods to send actions of a given type to the Genesis Blocks
 * data store. The reducer (reducer.js) then determines how to update store
 * state based on the type of action it receives.
 *
 * @since   1.0.0
 * @author  StudioPress
 * @license GPL-2.0-or-later
 */
function x(e){return{type:"UPDATE_SETTING",setting:e}}function E(e){return{type:"UPDATE_CUSTOM",setting:e}}function C(){return{type:"RESET"}}function P(e){var t;return w.a.wrap((function(n){for(;;)switch(n.prev=n.next){case 0:return n.next=2,{type:"SAVING"};case 2:t=null;try{wp.data.dispatch("core").saveSite(e),t=!0}catch(e){t=!1}return n.abrupt("return",{type:"SAVED",success:t});case 5:case"end":return n.stop()}}),k)}
/**
 * Registers the 'genesis-blocks/global-settings' WordPress data store.
 *
 * @see docs/modules/settings.md
 * @since   1.0.0
 * @author  StudioPress
 * @license GPL-2.0-or-later
 */var M={selectors:s,actions:r,reducer:v,controls:d.controls},T=(Object(l.registerStore)("genesis-blocks/global-settings",M),n(11)),D=n(12);var N=Object(a.compose)([Object(l.withSelect)((function(e){return{form:e("genesis-blocks/global-settings").getFormInfo(),settings:e("genesis-blocks/global-settings").getModifiedSettings(),custom:e("genesis-blocks/global-settings").getCustom()}}))])((
/**
 * SaveButton component
 *
 * Shows an adjacent notice with the result of the save operation.
 *
 * @since   1.0.0
 * @author  StudioPress
 * @license GPL-2.0-or-later
 */
function(e){var t=e.form,n=e.settings,s=e.custom,r=e.children,o=e.successMessage,a=e.failMessage,u=e.messageDuration,d=Object(i.useRef)();return Object(i.createElement)(i.Fragment,null,Object(i.createElement)(c.Button,{isPrimary:!0,isBusy:t.is_saving,disabled:t.is_saving,onClick:function(){Object(D.doAction)("genesisBlocks.savingSettings",n,s),clearTimeout(d.current),Object(l.dispatch)("genesis-blocks/global-settings").saveSettings(n)},className:"genesis-blocks-settings-save has-notices"},r),t.success||t.fail?function(){d.current=setTimeout((function(){return Object(l.dispatch)("genesis-blocks/global-settings").resetFormSaveState()}),1e3*u);var e=t.success?o:a;Object(T.speak)(e,"polite");var n="genesis-blocks-save-notice"+"".concat(t.success?" success":"")+"".concat(t.fail?" fail":"");return Object(i.createElement)("span",{className:n},e)}():"")}));
/**
 * Checkbox field
 *
 * @since   1.0.0
 * @author  StudioPress
 * @license GPL-2.0-or-later
 */var U=Object(a.compose)([Object(l.withDispatch)((function(){return{onUpdate:function(e){Object(l.dispatch)("genesis-blocks/global-settings").updateSetting({key:e.key,value:e.value})}}}))])((function(e){var t=e.settings,n=e.field,s=e.onUpdate;return Object(i.createElement)(c.CheckboxControl,{className:n.class,heading:n.heading,label:n.label,help:n.help,checked:!!t[n.id]&&t[n.id],onChange:function(e){return s({key:n.id,value:e})}})}));var A=
/**
 * Html field
 *
 * @since   1.0.0
 * @author  StudioPress
 * @license GPL-2.0-or-later
 */
function(e){var t=e.field;return Object(i.createElement)("div",{dangerouslySetInnerHTML:{__html:t.content}})},I=n(13),F=n(8),R=["image"],B=Object(o.__)("Image"),z=Object(o.__)("Select an image"),G=Object(o.__)("Choose image"),V=Object(o.__)("Replace image"),L=Object(o.__)("Remove image");var H=Object(l.withSelect)((function(e,t){var n=e("core").getMedia,s=t.settings[t.field.id];return{media:s?n(s):null,imageId:s}})),W=Object(l.withDispatch)((function(e,t){var n=e("genesis-blocks/global-settings").updateSetting;return{onUpdateImage:function(e){n({key:t.field.id,value:e.id})},onRemoveImage:function(){n({key:t.field.id,value:null})}}})),$=Object(a.compose)(H,W)((function(e){var t,n,s,r=e.field,o=e.imageId,a=e.media,l=e.onUpdateImage,u=e.onRemoveImage;if(a){var d=r.image_size||"thumbnail";Object(I.has)(a,["media_details","sizes",d])?(t=a.media_details.sizes[d].width,n=a.media_details.sizes[d].height,s=a.media_details.sizes[d].source_url):(t=a.media_details.width,n=a.media_details.height,s=a.source_url)}return Object(i.createElement)(i.Fragment,null,Object(i.createElement)("div",{className:"genesis-blocks-settings-image"},Object(i.createElement)("p",{className:"components-base-control__label"},r.label||B),Object(i.createElement)(F.MediaUpload,{title:r.label_media_modal||z,onSelect:l,allowedTypes:R,render:function(e){var l=e.open;return Object(i.createElement)("div",{className:"genesis-blocks-settings-image__container"},Object(i.createElement)(c.Button,{className:o?"genesis-blocks-settings-image__preview":"genesis-blocks-settings-image__toggle",onClick:l,"aria-label":r.label_button_aria||null,isSecondary:!0},!!o&&a&&Object(i.createElement)(c.ResponsiveWrapper,{naturalWidth:t,naturalHeight:n,isInline:!0},Object(i.createElement)("img",{src:s,alt:""})),!!o&&!a&&Object(i.createElement)(c.Spinner,null),!o&&(r.label_button||G)))},value:o}),!!o&&a&&!a.isLoading&&Object(i.createElement)(F.MediaUpload,{title:r.label_media_modal||z,onSelect:l,allowedTypes:R,modalClass:"genesis-blocks-settings-image__media-modal",render:function(e){var t=e.open;return Object(i.createElement)(c.Button,{onClick:t,isSecondary:!0,"aria-label":r.label_replace_aria||null},r.label_replace||V)}}),!!o&&Object(i.createElement)(c.Button,{onClick:u,isLink:!0,isDestructive:!0,"aria-label":r.label_remove_aria||null},r.label_remove||L),!!r.help&&Object(i.createElement)("p",{id:r.id+"__help",className:"components-base-control__help"},r.help)))})),q=n(6),J=n.n(q);var K={checkbox:U,html:A,image:$,radio:Object(a.compose)([Object(l.withDispatch)((function(){return{onUpdate:function(e){Object(l.dispatch)("genesis-blocks/global-settings").updateSetting({key:e.key,value:e.value})}}}))])((
/**
 * Radio field
 *
 * @since   1.0.0
 * @author  StudioPress
 * @license GPL-2.0-or-later
 */
function(e){var t=e.settings,n=e.field,s=e.onUpdate;return Object(i.createElement)(c.RadioControl,{className:n.class,label:n.label,help:n.help,selected:!!t[n.id]&&t[n.id],options:function(e){for(var t=[],n=0,s=Object.entries(e);n<s.length;n++){var r=J()(s[n],2),i=r[0],o=r[1];t.push({value:i,label:o})}return t}(n.options),onChange:function(e){return s({key:n.id,value:e})}})})),select:Object(a.compose)([Object(l.withDispatch)((function(){return{onUpdate:function(e){Object(l.dispatch)("genesis-blocks/global-settings").updateSetting({key:e.key,value:e.value})}}}))])((
/**
 * Select field
 *
 * @since   1.0.0
 * @author  StudioPress
 * @license GPL-2.0-or-later
 */
function(e){var t=e.settings,n=e.field,s=e.onUpdate;return Object(i.createElement)(c.SelectControl,{className:n.class,label:n.label,help:n.help?n.help:"",value:!!t[n.id]&&t[n.id],options:function(e){for(var t=[],n=0,s=Object.entries(e);n<s.length;n++){var r=J()(s[n],2),i=r[0],o=r[1];t.push({value:i,label:o})}return t}(n.options),onChange:function(e){return s({key:n.id,value:e})}})})),text:Object(a.compose)([Object(l.withDispatch)((function(){return{onUpdate:function(e){Object(l.dispatch)("genesis-blocks/global-settings").updateSetting({key:e.key,value:e.value})}}}))])((
/**
 * Text field
 *
 * @since   1.0.0
 * @author  StudioPress
 * @license GPL-2.0-or-later
 */
function(e){var t=e.settings,n=e.field,s=e.onUpdate;return Object(i.createElement)(c.TextControl,{className:n.class,label:n.label?n.label:"",help:n.help?n.help:"",onChange:function(e){return s({key:n.id,value:e})},value:t[n.id]?t[n.id]:""})})),textarea:Object(a.compose)([Object(l.withDispatch)((function(){return{onUpdate:function(e){Object(l.dispatch)("genesis-blocks/global-settings").updateSetting({key:e.key,value:e.value})}}}))])((
/**
 * Textarea field
 *
 * @since   1.0.0
 * @author  StudioPress
 * @license GPL-2.0-or-later
 */
function(e){var t=e.settings,n=e.field,s=e.onUpdate;return Object(i.createElement)(c.TextareaControl,{className:n.class,label:n.label?n.label:"",help:n.help?n.help:"",onChange:function(e){return s({key:n.id,value:e})},value:t[n.id]?t[n.id]:""})}))};var Q=Object(a.compose)([Object(l.withSelect)((function(){return{settings:Object(l.select)("genesis-blocks/global-settings").getSettings(),sections:Object(l.select)("genesis-blocks/global-settings").getSections()}}))])((function(e){var t=e.settings,n=e.sections;return Object(i.createElement)(i.Fragment,null,Object(i.createElement)(c.TabPanel,{className:"genesis-blocks-settings-sections",activeClass:"gb-nav-tab-active",onSelect:function(){Object(l.dispatch)("genesis-blocks/global-settings").resetFormSaveState()},tabs:Object.values(function(e){for(var t in e)e[t].className="gb-nav-tab gb-admin-button";return e}(n))},(function(e){return Object(i.createElement)("div",{className:"gb-admin-plugin-admin-body"},Object(i.createElement)("div",{className:"gb-admin-plugin-container"},function(e){if(e.hasOwnProperty("fields")&&Array.isArray(e.fields)){var n=e.fields.map((function(e,n){if(!K.hasOwnProperty(e.type))return"";var s=K[e.type];return Object(i.createElement)(s,{key:n,settings:t,field:e})}));if(n.length>0)return Object(i.createElement)(i.Fragment,null,n)}return Object(i.createElement)("p",null,Object(o.__)("No fields found for this section.","genesis-blocks"))}(e),Object(i.createElement)(c.SlotFillProvider,null,Object(i.createElement)(c.Slot,{name:"GenesisBlocksSettings_"+e.name.replace("genesis_blocks_settings_","")}),Object(i.createElement)(u.PluginArea,null)),Object(i.createElement)(N,{successMessage:Object(o.__)("Settings saved","genesis-blocks"),failMessage:Object(o.__)("Saving failed","genesis-blocks"),messageDuration:"2"},Object(o.__)("Save All","genesis-blocks"))))})))}));
/**
 * The React application for the Genesis Blocks settings page.
 *
 * @since   1.0.0
 * @author  StudioPress
 * @license GPL-2.0-or-later
 */Object(i.render)(Object(i.createElement)(Q,null),document.getElementById("root"))}]);