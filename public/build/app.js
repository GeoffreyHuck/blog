(window["webpackJsonp"] = window["webpackJsonp"] || []).push([["app"],{

/***/ "./assets/css/index.scss":
/*!*******************************!*\
  !*** ./assets/css/index.scss ***!
  \*******************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

// extracted by mini-css-extract-plugin

/***/ }),

/***/ "./assets/js/app.ts":
/*!**************************!*\
  !*** ./assets/js/app.ts ***!
  \**************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


exports.__esModule = true;

__webpack_require__(/*! ../css/index.scss */ "./assets/css/index.scss");

__webpack_require__(/*! ./app/flash */ "./assets/js/app/flash.ts");

__webpack_require__(/*! ./app/comment */ "./assets/js/app/comment.ts");

/***/ }),

/***/ "./assets/js/app/comment.ts":
/*!**********************************!*\
  !*** ./assets/js/app/comment.ts ***!
  \**********************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(/*! core-js/modules/es.array.for-each.js */ "./node_modules/core-js/modules/es.array.for-each.js");

__webpack_require__(/*! core-js/modules/es.array.from.js */ "./node_modules/core-js/modules/es.array.from.js");

__webpack_require__(/*! core-js/modules/es.string.iterator.js */ "./node_modules/core-js/modules/es.string.iterator.js");

__webpack_require__(/*! core-js/modules/web.dom-collections.for-each.js */ "./node_modules/core-js/modules/web.dom-collections.for-each.js");

window.addEventListener('load', function () {
  var commentReplyTo = document.getElementById('comment_replyTo');
  var replyToContainer = document.getElementById('reply-to-container');
  Array.from(document.getElementsByClassName('reply-link')).forEach(function (item) {
    item.addEventListener('click', function () {
      commentReplyTo.setAttribute('value', item.getAttribute('data-replyTo'));
      replyToContainer.style.display = 'block';
    });
  });
});

/***/ }),

/***/ "./assets/js/app/flash.ts":
/*!********************************!*\
  !*** ./assets/js/app/flash.ts ***!
  \********************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(/*! core-js/modules/es.array.for-each.js */ "./node_modules/core-js/modules/es.array.for-each.js");

__webpack_require__(/*! core-js/modules/es.array.from.js */ "./node_modules/core-js/modules/es.array.from.js");

__webpack_require__(/*! core-js/modules/es.string.iterator.js */ "./node_modules/core-js/modules/es.string.iterator.js");

__webpack_require__(/*! core-js/modules/web.dom-collections.for-each.js */ "./node_modules/core-js/modules/web.dom-collections.for-each.js");

__webpack_require__(/*! core-js/modules/web.timers.js */ "./node_modules/core-js/modules/web.timers.js");

window.addEventListener('load', function () {
  Array.from(document.getElementsByClassName('flash')).forEach(function (item) {
    setTimeout(function () {
      item.remove();
    }, 3000);
  });
});

/***/ })

},[["./assets/js/app.ts","runtime","vendors~app"]]]);
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9hc3NldHMvY3NzL2luZGV4LnNjc3MiLCJ3ZWJwYWNrOi8vLy4vYXNzZXRzL2pzL2FwcC50cyIsIndlYnBhY2s6Ly8vLi9hc3NldHMvanMvYXBwL2NvbW1lbnQudHMiLCJ3ZWJwYWNrOi8vLy4vYXNzZXRzL2pzL2FwcC9mbGFzaC50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiOzs7Ozs7Ozs7QUFBQSx1Qzs7Ozs7Ozs7Ozs7Ozs7OztBQ0FBOztBQUVBOztBQUNBLHVFOzs7Ozs7Ozs7Ozs7Ozs7Ozs7O0FDSEEsTUFBTSxDQUFDLGdCQUFQLENBQXdCLE1BQXhCLEVBQWdDO0FBQzVCLE1BQU0sY0FBYyxHQUFHLFFBQVEsQ0FBQyxjQUFULENBQXdCLGlCQUF4QixDQUF2QjtBQUNBLE1BQU0sZ0JBQWdCLEdBQUcsUUFBUSxDQUFDLGNBQVQsQ0FBd0Isb0JBQXhCLENBQXpCO0FBRUEsT0FBSyxDQUFDLElBQU4sQ0FBVyxRQUFRLENBQUMsc0JBQVQsQ0FBZ0MsWUFBaEMsQ0FBWCxFQUEwRCxPQUExRCxDQUFrRSxVQUFDLElBQUQsRUFBSztBQUNuRSxRQUFJLENBQUMsZ0JBQUwsQ0FBc0IsT0FBdEIsRUFBK0I7QUFDM0Isb0JBQWMsQ0FBQyxZQUFmLENBQTRCLE9BQTVCLEVBQXFDLElBQUksQ0FBQyxZQUFMLENBQWtCLGNBQWxCLENBQXJDO0FBRUEsc0JBQWdCLENBQUMsS0FBakIsQ0FBdUIsT0FBdkIsR0FBaUMsT0FBakM7QUFDSCxLQUpEO0FBS0gsR0FORDtBQU9ILENBWEQsRTs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7O0FDQUEsTUFBTSxDQUFDLGdCQUFQLENBQXdCLE1BQXhCLEVBQWdDO0FBQzVCLE9BQUssQ0FBQyxJQUFOLENBQVcsUUFBUSxDQUFDLHNCQUFULENBQWdDLE9BQWhDLENBQVgsRUFBcUQsT0FBckQsQ0FBNkQsVUFBQyxJQUFELEVBQUs7QUFDOUQsY0FBVSxDQUFDO0FBQ1AsVUFBSSxDQUFDLE1BQUw7QUFDSCxLQUZTLEVBRVAsSUFGTyxDQUFWO0FBR0gsR0FKRDtBQUtILENBTkQsRSIsImZpbGUiOiJhcHAuanMiLCJzb3VyY2VzQ29udGVudCI6WyIvLyBleHRyYWN0ZWQgYnkgbWluaS1jc3MtZXh0cmFjdC1wbHVnaW4iLCJpbXBvcnQgJy4uL2Nzcy9pbmRleC5zY3NzJztcblxuaW1wb3J0ICcuL2FwcC9mbGFzaCc7XG5pbXBvcnQgJy4vYXBwL2NvbW1lbnQnO1xuIiwid2luZG93LmFkZEV2ZW50TGlzdGVuZXIoJ2xvYWQnLCAoKSA9PiB7XG4gICAgY29uc3QgY29tbWVudFJlcGx5VG8gPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnY29tbWVudF9yZXBseVRvJyk7XG4gICAgY29uc3QgcmVwbHlUb0NvbnRhaW5lciA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdyZXBseS10by1jb250YWluZXInKTtcblxuICAgIEFycmF5LmZyb20oZG9jdW1lbnQuZ2V0RWxlbWVudHNCeUNsYXNzTmFtZSgncmVwbHktbGluaycpKS5mb3JFYWNoKChpdGVtKSA9PiB7XG4gICAgICAgIGl0ZW0uYWRkRXZlbnRMaXN0ZW5lcignY2xpY2snLCAoKSA9PiB7XG4gICAgICAgICAgICBjb21tZW50UmVwbHlUby5zZXRBdHRyaWJ1dGUoJ3ZhbHVlJywgaXRlbS5nZXRBdHRyaWJ1dGUoJ2RhdGEtcmVwbHlUbycpKTtcblxuICAgICAgICAgICAgcmVwbHlUb0NvbnRhaW5lci5zdHlsZS5kaXNwbGF5ID0gJ2Jsb2NrJztcbiAgICAgICAgfSk7XG4gICAgfSk7XG59KTtcbiIsIndpbmRvdy5hZGRFdmVudExpc3RlbmVyKCdsb2FkJywgKCkgPT4ge1xuICAgIEFycmF5LmZyb20oZG9jdW1lbnQuZ2V0RWxlbWVudHNCeUNsYXNzTmFtZSgnZmxhc2gnKSkuZm9yRWFjaCgoaXRlbSkgPT4ge1xuICAgICAgICBzZXRUaW1lb3V0KCgpID0+IHtcbiAgICAgICAgICAgIGl0ZW0ucmVtb3ZlKCk7XG4gICAgICAgIH0sIDMwMDApO1xuICAgIH0pO1xufSk7XG4iXSwic291cmNlUm9vdCI6IiJ9