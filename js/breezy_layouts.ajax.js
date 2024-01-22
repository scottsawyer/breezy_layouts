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
            $('.ui-dialog:visible').find('.ui-dialog-content').dialog('close');
          });
        }
      });
    }
  };

})(jQuery, Drupal, drupalSettings, once);
