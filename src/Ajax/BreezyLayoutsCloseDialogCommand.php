<?php

namespace Drupal\breezy_layouts\Ajax;

use Drupal\Core\Ajax\CloseDialogCommand;

/**
 * Provides an Ajax command to close Breezy Layouts dialog and tray.
 *
 * This command is implemented in
 * Drupal.AjaxCommands.prototype.breezylayoutsCloseDialog.
 */
class BreezyLayoutsCloseDialogCommand extends CloseDialogCommand {

  /**
   * {@inheritdoc}
   */
  public function render() {
    return [
      'command' => 'breezylayoutsCloseDialog',
      'selector' => $this->selector,
      'persist' => $this->persist,
    ];
  }

}
