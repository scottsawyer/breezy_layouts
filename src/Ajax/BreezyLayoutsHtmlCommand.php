<?php

namespace Drupal\breezy_layouts\Ajax;

use Drupal\Core\Ajax\HtmlCommand;

/**
 * Provides an Ajax command for calling the jQuery html() method.
 *
 * This command is implemented in Drupal.AjaxCommands.prototype.breezylayoutsInsert.
 */
class BreezyLayoutsHtmlCommand extends HtmlCommand {

  /**
   * Implements Drupal\Core\Ajax\ComandInterface:render().
   */
  public function render() {
    return [
      'command' => 'breezylayoutsInsert',
      'method' => 'html',
      'selector' => $this->selector,
      'data' => $this->getRenderedContent(),
      'settings' => $this->settings,
    ];
  }

}
