<?php

namespace Drupal\breezy_layouts\Ajax;

use Drupal\Core\Ajax\RedirectCommand;

/**
 * Provides an Ajax command for refreshing breezy layouts page.
 *
 * This command is implemented in Drupal.AjaxCommands.prototype.breezylayoutsRefresh.
 */
class BreezyLayoutsRefreshCommand extends RedirectCommand {

  /**
   * Implements \Drupal\Core\Ajax\ComamndInterface::render().
   */
  public function render() {
    return [
      'command' => 'breezylayoutsRefresh',
      'url' => $this->url,
    ];
  }
}
