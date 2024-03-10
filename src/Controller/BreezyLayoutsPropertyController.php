<?php

namespace Drupal\breezy_layouts\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Controller\ControllerBase;
use Drupal\breezy_layouts\Service\BreezyLayoutsTailwindClassServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides autocomplete for property names.
 */
final class BreezyLayoutsPropertyController extends ControllerBase {

  /**
   * Tailwind Class service.
   *
   * @var \Drupal\breezy_layouts\Service\BreezyLayoutsTailwindClassServiceInterface
   */
  protected $tailwindClassService;

  /**
   * Contructs a new BreezyLayoutsPropertyController object.
   *
   * @param \Drupal\breezy_layouts\Service\BreezyLayoutsTailwindClassServiceInterface $tailwind_classes
   *   The Tailwind class service.
   */
  public function __construct(BreezyLayoutsTailwindClassServiceInterface $tailwind_classes) {
    $this->tailwindClassService = $tailwind_classes;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('breezy_layouts.tailwind_classes')
    );
  }

  /**
   * Autocomplete callback.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The response object.
   */
  public function handleAutocomplete(Request $request) {
    $matches = [];
    $string = $request->query->get('q');
    $properties = $this->tailwindClassService->getPropertyMap();
    $matched_array = array_filter($properties, function ($value) use ($string) {
      return
        str_contains(strtolower($value['label']), strtolower($string))
        || str_contains($value['css_property'], strtolower($string));
    });

    foreach ($matched_array as $value => $parts) {
      $matches[] = [
        'value' => $value,
        'label' => $parts['label'],
      ];
    }
    return new JsonResponse($matches);
  }

}
