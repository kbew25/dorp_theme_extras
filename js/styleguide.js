/**
 * @file
 * Styleguide.js.
 */

 (function (Drupal, once) {
  'use strict';

  /**
   * Styleguide-Twig code hightlight.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Adds code hightlighting to code blocks.
   */
  Drupal.behaviors.sgthighlight = {
    attach: function (context) {
      context.querySelectorAll('.sgt-code pre code').forEach((block) => {
        hljs.highlightBlock(block);
      });
    },
  };

  /**
   * Styleguide-Twig jump to menu.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Adds a component menu to the styleguide.
   */
  Drupal.behaviors.sgtMenu = {
    attach: function (context) {
      once('sgtMenu', 'html', context).forEach(function () {
        // Create <ol>.
        var menu = document.createElement('ol');
        // Add class to ol.
        menu.classList.add('sgt-menu');
        // Get sgt heading.
        var heading = document.getElementsByClassName('sgt-heading')[0];
        // Insert ol after heading.
        heading.parentNode.insertBefore(menu, heading.nextSibling);
        // Get ol.
        var list = document.querySelector('.sgt-sections ol');

        // For each sgt title.
        document.querySelectorAll('.sgt-title').forEach(function (ele, i) {
          // Add id to sgt title.
          ele.setAttribute('id', 'c-' + i);
          // Get title text.
          var text = ele.innerText;
          // Create the link.
          var newlink = document.createElement('a');
          newlink.setAttribute('href', '#c-' + i);
          newlink.textContent = text;
          // Create the li.
          var newlist = document.createElement('li');
          // Add link to li.
          newlist.appendChild(newlink);
          // Add li to ol.
          list.appendChild(newlist);
        });
      });
    },
  };
})(Drupal, once);
