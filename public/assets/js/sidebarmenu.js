"use strict";

function initSidebarNav() {
  var url = window.location + "";

  // Reset dulu semua active/selected/in
  $("ul#sidebarnav a").removeClass("active");
  $("ul#sidebarnav li").removeClass("active selected");
  $("ul#sidebarnav ul").removeClass("in");

  // Auto select menu sesuai URL aktif
  var element = $("ul#sidebarnav a").filter(function () {
    return this.href === url;
  });

  element.parentsUntil(".sidebar-nav").each(function () {
    if ($(this).is("li") && $(this).children("a").length !== 0) {
      $(this).children("a").addClass("active");
      $(this).parent("ul#sidebarnav").length === 0
        ? $(this).addClass("active")
        : $(this).addClass("selected");
    } else if (!$(this).is("ul") && $(this).children("a").length === 0) {
      $(this).addClass("selected");
    } else if ($(this).is("ul")) {
      $(this).addClass("in");
    }
  });

  element.addClass("active");
}

// Click handler pakai event delegation — cukup dipasang SEKALI
$(document).on("click", "#sidebarnav a", function () {
  if (!$(this).hasClass("active")) {
    $("ul", $(this).parents("ul:first")).removeClass("in");
    $("a", $(this).parents("ul:first")).removeClass("active");
    $(this).next("ul").addClass("in");
    $(this).addClass("active");
  } else {
    $(this).removeClass("active");
    $(this).parents("ul:first").removeClass("active");
    $(this).next("ul").removeClass("in");
  }
});

$(document).on("click", "#sidebarnav > li > a.has-arrow", function (e) {
  e.preventDefault();
});

// Jalankan saat pertama load
$(function () {
  initSidebarNav();
});

// Jalankan ulang setiap wire:navigate selesai
document.addEventListener("livewire:navigated", function () {
  initSidebarNav();
});