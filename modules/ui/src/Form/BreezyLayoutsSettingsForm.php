<?php

namespace Drupal\breezy_layouts_ui\Form;

use Drupal\breakpoint\BreakpointManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\Html;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines the configuration form.
 */
class BreezyLayoutsSettingsForm extends ConfigFormBase {

  /**
   * Drupal\Core\Extension\ModuleHandlerInterface definition.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Drupal\breakpoint\BreakpointManagerInterface definition.
   *
   * @var \Drupal\breakpoint\BreakpointManagerInterface
   */
  protected $breakpointManager;

  /**
   * Constructs a new settings form object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *    The config factory.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler extension.
   * @param \Drupal\breakpoint\BreakpointManagerInterface $breakpoint_manager
   *    The breakpoint manager service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ModuleHandlerInterface $module_handler, BreakpointManagerInterface $breakpoint_manager) {
    parent::__construct($config_factory);
    $this->moduleHandler = $module_handler;
    $this->breakpointManager = $breakpoint_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @var \Drupal\Core\Extension\ModuleHandlerInterface $module_handler */
    $module_handler = $container->get('module_handler');
    /** @var \Drupal\breakpoint\BreakpointManagerInterface $breakpoint_manager */
    $breakpoint_manager = $container->get('breakpoint.manager');
    /** @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    $config_factory = $container->get('config.factory');
    return new static($config_factory, $module_handler, $breakpoint_manager);
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'breezy_layouts_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['breezy_layouts.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form_wrapper_id = Html::getUniqueId($this->getFormId());
    $breakpoints_wrapper_id = 'breakpoints-wrapper';
    $config = $this->config('breezy_layouts.settings');
    $breakpoints = $config->get('breakpoints');
    //$form_state->setValue('breakpoints', $breakpoints);

    $form['#tree'] = TRUE;
    $form['#prefix'] = '<div id="' . $form_wrapper_id . '">';
    $form['#suffix'] = '</div>';
    $form['actions'] = [
      '#type' => 'actions',
      '#weight' => 100,
    ];

    $form['instructions'] = [
      '#markup' => $this->t("Select the breakpoints that should be included in each layout configuration."),
    ];

    $breakpoint_group = FALSE;

    if ($this->moduleHandler->moduleExists('breezy_components')) {
      $breakpoint_group = $this->config('breezy_components.settings')->get('breakpoint_group');
      if ($breakpoint_group) {
        $form['breakpoint_group'] = [
          '#type' => 'hidden',
          '#value' => $breakpoint_group,
        ];
        $form['breakpoint_group_instructions'] = [
          '#markup' => $this->t('Breakpoint group set in Breezy Components'),
        ];
        $form_state->set('breakpoint_group', $breakpoint_group);
      }

    }

    if (!$breakpoint_group) {
      if ($config->get('breakpoint_group')) {
        $breakpoint_group = $config->get('breakpoint_group');
        $form_state->set('breakpoint_group', $breakpoint_group);
      }
      $form['breakpoint_group'] = [
        '#type' => 'select',
        '#title' => $this->t('Breakpoint group'),
        '#default_value' => $breakpoint_group ?? '',
        '#options' => $this->breakpointManager->getGroups(),
        '#empty_option'=>$this->t('- Select -'),
        '#required' => TRUE,
        '#ajax' => [
          'callback' => '::changeBreakpointGroup',
          'wrapper' => $breakpoints_wrapper_id,
        ],
      ];
    }

    $form['breakpoints'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Breakpoints'),
      '#prefix' => '<div id="' . $breakpoints_wrapper_id . '">',
      '#suffix' => '</div>',
    ];

    $breakpoint_group = $form_state->get('breakpoint_group');
    if ($form_state->getValue('breakpoint_group')) {
      $breakpoint_group = $form_state->getValue('breakpoint_group');
    }
    if (!empty($breakpoint_group)) {

      $breakpoint_group_breakpoints = $this->breakpointManager->getBreakpointsByGroup($breakpoint_group);

      foreach($breakpoint_group_breakpoints as $breakpoint_name => $breakpoint) {
        $breakpoint_name = str_replace('.', '__', $breakpoint_name);
        $breakpoint_wrapper_id = 'breakpoints-' . $breakpoint_name;
        $form['breakpoints'][$breakpoint_name] = [
          '#type' => 'fieldset',
          '#title' => $breakpoint->getLabel(),
          '#tree' => TRUE,
          '#prefix' => '<div id="' . $breakpoint_wrapper_id . '">',
          '#suffix' => '</div>',
        ];

        $form['breakpoints'][$breakpoint_name]['enabled'] = [
          '#type' => 'checkbox',
          '#title' => $this->t('Enable'),
          '#default_value' => $breakpoints[$breakpoint_name]['enabled'] ?? FALSE,
        ];

        $form['breakpoints'][$breakpoint_name]['prefix'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Breakpoint prefix'),
          '#default_value' => $breakpoints[$breakpoint_name]['prefix'] ?? '',
          '#states' => [
            'visible' => [
              'input[name="breakpoints[' . $breakpoint_name . '][enabled]"]' => ['checked' => TRUE],
            ],
          ],
        ];

        $form['breakpoints'][$breakpoint_name]['gap'] = [
          '#type' => 'select',
          '#title' => $this->t('Gap'),
          '#description' => $this->t('Space between columns.'),
          '#default_value' => $breakpoints[$breakpoint_name]['gap'] ?? 'gap-0',
          '#options' => [
            'gap-0' => '0px',
            'gap-px' => '1px',
            'gap-0.5' => '2px',
            'gap-1' => '4px',
            'gap-2' => '8px',
            'gap-2.5' => '10px',
            'gap-3' => '12px',
            'gap-3.5' => '14px',
            'gap-4' => '16px',
            'gap-5' => '20px',
            'gap-6' => '24px',
            'gap-7' => '28px',
            'gap-8' => '32px',
            'gap-9' => '36px',
            'gap-10' => '40px',
            'gap-11' => '44px',
            'gap-12' => '48px',
          ],
          '#states' => [
            'visible' => [
              'input[name="breakpoints[' . $breakpoint_name . '][enabled]"]' => ['checked' => TRUE],
            ],
          ],
        ];

        $form['breakpoints'][$breakpoint_name]['columns'] = [
          '#type' => 'select',
          '#title' => $this->t('Columns'),
          '#description' => $this->t('Total number of columns available to a region.'),
          '#default_value' => $breakpoints[$breakpoint_name]['columns'] ?? '',
          '#empty_option'=>$this->t('- Select -'),
          '#options' => [
            1 => '1',
            2 => '2',
            3 => '3',
            4 => '4',
            5 => '5',
            6 => '6',
            12 => '12',
          ],
          '#states' => [
            'visible' => [
              'input[name="breakpoints[' . $breakpoint_name . '][enabled]"]' => ['checked' => TRUE],
            ],
          ],
          '#ajax' => [
            'callback' => '::columnsCallback',
            'event' => 'change',
            'wrapper' => $breakpoints_wrapper_id,
          ]
        ];

        $columns = '';
        // First check if there are columns already saved.
        if (isset($breakpoints[$breakpoint_name]['columns'])) {
          $columns = $breakpoints[$breakpoint_name]['columns'];
          $form_state->set(['breakpoints', $breakpoint_name, 'columns'], $columns);
        };
        // Columns may have been set in an ajax callback.
        if ($form_state->get(['breakpoints', $breakpoint_name, 'columns'])) {
          $columns = $form_state->get([
            'breakpoints',
            $breakpoint_name,
            'columns'
          ]);
        }
        if ($form_state->getValue(['breakpoints', $breakpoint_name, 'columns'])) {
          $columns = $form_state->getValue(['breakpoints', $breakpoint_name, 'columns']);
          $form_state->set(['breakpoints', $breakpoint_name, 'columns'], $columns);
        }

        if (!empty($columns)) {

          $form['breakpoints'][$breakpoint_name]['available_sizes_container'] = [
            '#type' => 'fieldset',
            '#title' => $this->t('Available sizes'),
            '#states' => [
              'visible' => [
                'input[name="breakpoints[' . $breakpoint_name . '][enabled]"]' => ['checked' => TRUE],
              ],
            ],
          ];
          $available_size_lines = 0;
          if (isset($breakpoints[$breakpoint_name]['available_sizes'])) {
            $available_size_lines = count($breakpoints[$breakpoint_name]['available_sizes']);

          }
          if ($form_state->get([$breakpoint_name, 'available_size_lines'])) {
            $available_size_lines = $form_state->get([
              $breakpoint_name,
              'available_size_lines'
            ]);
          }
          else {
            $form_state->set([$breakpoint_name, 'available_size_lines'], $available_size_lines);
          }
          if ($available_size_lines === 0) {
            $form_state->set([$breakpoint_name, 'available_size_lines'], $available_size_lines);
          }
          $table_id = $breakpoint_name . '-available-sizes';
          $form['breakpoints'][$breakpoint_name]['available_sizes_container']['available_sizes'] = [
            '#type' => 'table',
            '#header' => ['Size', 'Operations'],
            '#attributes' => ['id' => 'breakpoints-' . $breakpoint_name . '-available-sizes'],
            '#prefix' => '<div id="' . $table_id . '">',
            '#suffix' => '</div>',
            '#available_size_lines' => $available_size_lines,
            '#sort' => TRUE,
          ];
          for ($i = 0; $i < $available_size_lines; $i++) {
            $removed_sizes = $form_state->get([$breakpoint_name, 'removed_sizes']);
            if ($removed_sizes) {
              if (in_array($i, $removed_sizes)) {
                continue;
              }
            }
            $form['breakpoints'][$breakpoint_name]['available_sizes_container']['available_sizes'][$i]['sizes'] = [
              '#title' => $this->t('Size'),
              '#type' => 'select',
              '#attributes' => ['id' => 'breakpoints-' . $breakpoint_name . '-available-sizes'],
              '#empty_option'=>$this->t('- Select -'),
              '#description' => $this->t('Set the size options for each region.'),
              '#options' => $this->getAvailableSizes($columns),
              '#default_value' => $breakpoints[$breakpoint_name]['available_sizes'][$i] ?? '',
            ];

            $form['breakpoints'][$breakpoint_name]['available_sizes_container']['available_sizes'][$i]['operations'] = [
              '#type' => 'submit',
              '#value' => $this->t('Remove'),
              '#name' => $breakpoint_name . '-available-sizes-' . $i . '-remove',
              '#line' => $i,
              '#breakpoint_name' => $breakpoint_name,
              '#submit' => ['::removeSizeSubmit'],
              '#ajax' => [
                'callback' => '::removeSizeCallback',
                'wrapper' => $breakpoints_wrapper_id,
              ],
            ];
          }
          $form['breakpoints'][$breakpoint_name]['available_sizes_container']['add_size'] = [
            '#type' => 'submit',
            '#value' => $this->t('Add size ' . $available_size_lines),
            '#submit' => ['::addSizeSubmit'],
            '#breakpoint_name' => $breakpoint_name,
            '#name' => $breakpoint_name . '-available-sizes-add',
            '#ajax' => [
              'callback' => '::addSizeCallback',
              'wrapper' => $breakpoints_wrapper_id,
            ],
          ];

        }
        /*
        $form[$breakpoint_name]['available_offsets'] = [
          '#title' => $this->t('Available offsets'),
          '#description' => $this->t('Set the number of columns by which each region can be offset (or skip).'),
          '#states' => [
            'visible' => [
              'input[name="' . $breakpoint_name . '[enabled]"]' => ['checked' => TRUE],
            ],
          ],
        ];
        /**/
      }

    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('breezy_layouts.settings');
    $breakpoint_group = $form_state->getValue('breakpoint_group');
    $config->set('breakpoint_group', $breakpoint_group);
    $breakpoint_values = $form_state->getValue('breakpoints');
    $breakpoints = [];
    foreach($breakpoint_values as $breakpoint_name => $breakpoint_settings) {
      if (!$breakpoint_settings['enabled']) {
        continue;
      }
      $breakpoints[$breakpoint_name] = [
        'enabled' => $breakpoint_settings['enabled'],
        'prefix' => $breakpoint_settings['prefix'],
        'gap' => $breakpoint_settings['gap'],
        'columns' => $breakpoint_settings['columns'],
      ];

      if (isset($breakpoint_settings['available_sizes_container']['available_sizes'])) {
        foreach ($breakpoint_settings['available_sizes_container']['available_sizes'] as $i => $size) {
          if (isset($size['sizes'])) {
            $breakpoints[$breakpoint_name]['available_sizes'][] = $size['sizes'];
          }
        }
      }
    }
    $config->set('breakpoints', $breakpoints)->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * Callback for columns selection.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form_state.
   *
   * @return array
   */
  public function columnsCallback(array &$form, FormStateInterface $form_state) {
    return $form['breakpoints'];
  }

  /**
   * Callback for Breakpoint group selection.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return array
   */
  public function changeBreakpointGroup(array &$form, FormStateInterface $form_state) {
    $breakpoint_group = $form_state->getValue('breakpoint_group');
    $form_state->set('breakpoint_group', $breakpoint_group);
    $form_state->setRebuild();
    return $form['breakpoints'];
  }

  /**
   * Get available sizes.
   *
   * @param int $columns
   *
   * @return array
   */
  protected function getAvailableSizes(int $columns) {
    $available_sizes = [
      'basis-auto' => 'Auto',
      'basis-full' => '100%',
    ];

    for ($i = 1; $i < $columns; $i++) {
      $available_sizes['basis-' . $i . '/' . $columns] = $i . '/' . $columns;
    }
    return $available_sizes;
  }

  public function addSizeCallback(array &$form, FormStateInterface $form_state) {
    return $form['breakpoints'];
  }

  public function addSizeSubmit(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $breakpoint = $trigger['#breakpoint_name'];
    $lines = $form_state->get([$breakpoint, 'available_size_lines']);
    $form_state->set([$breakpoint, 'available_size_lines'], $lines + 1);
    $form_state->setRebuild();
  }

  public function removeSizeCallback(array &$form, FormStateInterface $form_state) {
    return $form['breakpoints'];
  }

  public function removeSizeSubmit(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $breakpoint = $trigger['#breakpoint_name'];
    $line = $trigger['#line'];
    $removed_sizes = $form_state->get([$breakpoint, 'removed_sizes']);
    $removed_sizes[] = $line;
    $form_state->set([$breakpoint, 'removed_sizes'], $removed_sizes);
    $form_state->setRebuild();
  }

}
