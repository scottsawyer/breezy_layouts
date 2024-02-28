/**
 * @file
 * JavaScript behaviors for Ajax.
 */

(function ($, Drupal, drupalSettings, once) {

  'use strict';

  /**
   * Provides Breezy Layouts Ajax link behavior.
   */
  Drupal.behaviors.breezyLayoutsAjaxLink = {
    attach: function (context) {
      $(once('breezy-layouts-ajax-link', '.breezy-layouts-ajax-link', context)).each(function () {
        var $element_settings = {};
        $element_settings.progress = {type: 'fullscreen'};

        var $href = $(this).attr('href');
        if ($href) {
          $element_settings.url = $href;
          $element_settings.event = 'click';
        }
        $element_settings.dialogType = $(this).data('dialog-type');
        $element_settings.dialogRenderer = $(this).data('dialog-renderer');
        $element_settings.dialog = $(this).data('dialog-options');
        $element_settings.base = $(this).attr('id');
        $element_settings.element = this;
        Drupal.ajax($element_settings);

        if ($element_settings.dialogRenderer === 'off_canvas') {
          $(this).on('click', function () {
            var $dialogs = $('.ui-dialog:visible').find('.ui-dialog-content');
            if ($dialogs.length) {
              $dialogs.each(function() {
                if ($.isFunction($.fn.dialog)) {
                  $(this).dialog('close');
                }
              });
            }
          });
        }
      });
    }
  };

  /**
   * Track the updated table row key.
   */
  var updateKey;

  /**
   * Track the add element key.
   */
  var addElement;


  /**
   * Command to insert new content into the DOM.
   *
   * @param {Drupal.Ajax} ajax
   *   {@link Drupal.Ajax} object created by {@link Drupal.ajax}.
   * @param {object} response
   *   The response from the Ajax request.
   * @param {string} response.data
   *   The data to use with the jQuery method.
   * @param {string} [response.method]
   *   The jQuery DOM manipulation method to be used.
   * @param {string} [response.selector]
   *   A optional jQuery selector string.
   * @param {object} [response.settings]
   *   An optional array of settings that will be used.
   * @param {number} [status]
   *   The XMLHttpRequest status.
   */
  Drupal.AjaxCommands.prototype.breezylayoutsInsert = function (ajax, response, status) {
    // Insert the HTML.
    this.insert(ajax, response, status);

    console.log('breezylayoutsInsert');
    // Add element.
    if (addElement) {
      var addSelector = (addElement === '_root_')
        ? '#breezy-layouts-ui-add-element'
        : '[data-drupal-selector="edit-breezy-layouts-ui-elements-' + addElement + '-add"]';
      $(addSelector).trigger('click');
    }

    // If not add element, then scroll to and highlight the updated table row.
    if (!addElement && updateKey) {
      var $element = $('tr[data-breezy-layouts-key="' + updateKey + '"]');

      // Highlight the updated element's row.
      $element.addClass('color-success');
      setTimeout(function () {$element.removeClass('color-success');}, 3000);

      // Focus first tabbable item for the updated elements and handlers.
      $element.find(':tabbable:not(.tabledrag-handle)').eq(0).trigger('focus');

      // Scroll element into view.
      //Drupal.breezylayoutsScrolledIntoView($element);
    }
    else {
      // Focus main content.
      $('#main-content').trigger('focus');
    }

    // Display main page's status message in a floating container.
    var $wrapper = $(response.selector);
    if ($wrapper.parents('.ui-dialog').length === 0) {
      var $messages = $wrapper.find('.messages');
      // If 'add element' don't show any messages.
      if (addElement) {
        $messages.remove();
      }
      else if ($messages.length) {
        var $floatingMessage = $('#breezy-layouts-ajax-messages');
        if ($floatingMessage.length === 0) {
          $floatingMessage = $('<div id="breezy-layouts-ajax-messages" class="breezy-layouts-ajax-messages"></div>');
          $('body').append($floatingMessage);
        }
        if ($floatingMessage.is(':animated')) {
          $floatingMessage.stop(true, true);
        }
        $floatingMessage.html($messages).show().delay(3000).fadeOut(1000);
      }
    }

    updateKey = null; // Reset element update.
    addElement = null; // Reset add element.
  };

  /**
   * Scroll to top ajax command.
   *
   * @param {Drupal.Ajax} [ajax]
   *   A {@link Drupal.ajax} object.
   * @param {object} response
   *   Ajax response.
   * @param {string} response.selector
   *   Selector to use.
   *
   * @see Drupal.AjaxCommands.prototype.viewScrollTop
   */
  Drupal.AjaxCommands.prototype.breezylayoutsScrollTop = function (ajax, response) {
    // Scroll top.
    Drupal.breezylayoutsScrollTop(response.selector, response.target);

    // Focus on the form wrapper content bookmark if
    // .js-breezy-layouts-autofocus is not enabled.
    // @see \Drupal\breezy_layouts\Form\BreezyLayoutsAjaxFormTrait::buildAjaxForm
    var $form = $(response.selector + '-content').find('form');
    if (!$form.hasClass('js-breezy-layouts-autofocus')) {
      $(response.selector + '-content').trigger('focus');
    }
  };

  /**
   * Command to refresh the current BreezyLayouts page.
   *
   * @param {Drupal.Ajax} [ajax]
   *   {@link Drupal.Ajax} object created by {@link Drupal.ajax}.
   * @param {object} response
   *   The response from the Ajax request.
   * @param {string} response.url
   *   The URL to redirect to.
   * @param {number} [status]
   *   The XMLHttpRequest status.
   */
  Drupal.AjaxCommands.prototype.breezylayoutsRefresh = function (ajax, response, status) {
    // Get URL path name.
    // @see https://stackoverflow.com/questions/6944744/javascript-get-portion-of-url-path
    var a = document.createElement('a');
    a.href = response.url;
    var forceReload = (response.url.match(/\?reload=([^&]+)($|&)/)) ? RegExp.$1 : null;
    if (forceReload) {
      response.url = response.url.replace(/\?reload=([^&]+)($|&)/, '');
      this.redirect(ajax, response, status);
      return;
    }
    if (a.pathname === window.location.pathname && $('.breezy-layouts-ajax-refresh').length) {
      updateKey = (response.url.match(/[?|&]update=([^&]+)($|&)/)) ? RegExp.$1 : null;
      addElement = (response.url.match(/[?|&]add_element=([^&]+)($|&)/)) ? RegExp.$1 : null;
      $('.breezy-layouts-ajax-refresh').trigger('click');
    }
    else {
      // Clear unsaved information flag so that the current BreezyLayouts page
      // can be redirected.
      // @see Drupal.behaviors.breezylayoutsUnsaved.clear
      if (Drupal.behaviors.breezylayoutsUnsaved) {
        Drupal.behaviors.breezylayoutsUnsaved.clear();
      }

      this.redirect(ajax, response, status);
    }
  };

  /**
   * Command to close a off-canvas and modal dialog.
   *
   * If no selector is given, it defaults to trying to close the modal.
   *
   * @param {Drupal.Ajax} [ajax]
   *   {@link Drupal.Ajax} object created by {@link Drupal.ajax}.
   * @param {object} response
   *   The response from the Ajax request.
   * @param {string} response.selector
   *   Selector to use.
   * @param {bool} response.persist
   *   Whether to persist the dialog element or not.
   * @param {number} [status]
   *   The HTTP status code.
   */
  Drupal.AjaxCommands.prototype.breezylayoutsCloseDialog = function (ajax, response, status) {
    if ($('#drupal-off-canvas').length) {
      // Close off-canvas system tray which is not triggered by close dialog
      // command.
      // @see Drupal.behaviors.offCanvasEvents
      $('#drupal-off-canvas').remove();
      $('body').removeClass('js-tray-open');
      // Remove all *.off-canvas events
      $(document).off('.off-canvas');
      $(window).off('.off-canvas');
      var edge = document.documentElement.dir === 'rtl' ? 'left' : 'right';
      var $mainCanvasWrapper = $('[data-off-canvas-main-canvas]');
      $mainCanvasWrapper.css('padding-' + edge, 0);

      // Resize tabs when closing off-canvas system tray.
      $(window).trigger('resize.tabs');
    }

    // https://stackoverflow.com/questions/15763909/jquery-ui-dialog-check-if-exists-by-instance-method
    if ($(response.selector).hasClass('ui-dialog-content')) {
      this.closeDialog(ajax, response, status);
    }
  };

  /**
   * Triggers confirm page reload.
   *
   * @param {Drupal.Ajax} [ajax]
   *   A {@link Drupal.ajax} object.
   * @param {object} response
   *   Ajax response.
   * @param {string} response.message
   *   A message to be displayed in the confirm dialog.
   */
  Drupal.AjaxCommands.prototype.breezylayoutsConfirmReload = function (ajax, response) {
    if (window.confirm(response.message)) {
      window.location.reload(true);
    }
  };

})(jQuery, Drupal, drupalSettings, once);
