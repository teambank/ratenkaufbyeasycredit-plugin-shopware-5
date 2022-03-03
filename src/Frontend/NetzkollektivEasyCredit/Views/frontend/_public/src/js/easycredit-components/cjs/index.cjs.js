'use strict';

document.addEventListener('DOMContentLoaded', function () {
  document.querySelector('#ec-checkout-1').classList.add('show');
  document.querySelector('nav li[data-target="#ec-checkout-1"]').classList.add('current');
  const date = new Date();
  document.querySelector('#ec-merchant-4').setAttribute('date', date.toISOString());
  let navItems = document.querySelectorAll('nav ul li');
  let sectionItems = document.querySelectorAll('.stage section > *');
  navItems.forEach(item => {
    item.addEventListener('click', function () {
      navItems.forEach(item => {
        item.classList.remove('current');
      });
      item.classList.add('current');
      let id = item.dataset.target;
      sectionItems.forEach(item => {
        item.classList.remove('show');
      });
      document.querySelector(id).classList.add('show');
    });
  });
});
