<?php

namespace Drupal\breezy_layouts\Service;

/**
 * Provides Tailwind classes.
 *
 * @see https://github.com/tailwindlabs/tailwindcss/discussions/10379#discussioncomment-6398338
 */
class BreezyLayoutsTailwindClassService implements BreezyLayoutsTailwindClassServiceInterface {

  /**
   * CSS property map.
   *
   * @var array
   */
  public function getPropertyMap() : array {
    $property_map = [
      'padding' => [
        'label' => 'Padding',
        'css_property' => 'padding',
        'method' => 'getPadding',
      ],
      'margin' => [
        'label' => 'Margin',
        'css_property' => 'margin',
        'method' => 'getMargin',
      ],
      'flex_direction' => [
        'label' => 'Flex direction',
        'css_property' => 'flex-direction',
        'method' => 'getFlexDirection',
      ],
      'flex_basis' => [
        'label' => 'Flex basis',
        'css_property' => 'flex-basis',
        'method' => 'getFlexBasis',
      ],
      'gap' => [
        'label' => 'Gap',
        'css_property' => 'gap',
        'method' => 'getGap',
      ],
      'order' => [
        'label' => 'Order',
        'css_property' => 'order',
        'method' => 'getOrder',
      ],
      'justify_content' => [
        'label' => 'Justify content',
        'css_property' => 'justify-content',
        'method' => 'getJustifyContent',
      ],
      'align_items' => [
        'label' => 'Align items',
        'css_property' => 'align-items',
        'method' => 'getAlignItems',
      ],
    ];
    return $property_map;
  }

  /**
   * {@inheritdoc}
   */
  public function getMargin() {
    $margins = [
      "m-0","m-px","m-0.5","m-1","m-1.5","m-2","m-2.5","m-3","m-3.5","m-4","m-5","m-6","m-7","m-8","m-9","m-10","m-11","m-12",
      "m-14","m-16","m-20","m-24","m-28","m-32","m-36","m-40","m-44","m-48","m-52","m-56","m-60","m-64","m-72","m-80","m-96","m-auto",

      "mx-0","mx-px","mx-0.5","mx-1","mx-1.5","mx-2","mx-2.5","mx-3","mx-3.5","mx-4","mx-5","mx-6","mx-7","mx-8","mx-9","mx-10","mx-11","mx-12",
      "mx-14","mx-16","mx-20","mx-24","mx-28","mx-32","mx-36","mx-40","mx-44","mx-48","mx-52","mx-56","mx-60","mx-64","mx-72","mx-80","mx-96","mx-auto",

      "my-0","my-px","my-0.5","my-1","my-1.5","my-2","my-2.5","my-3","my-3.5","my-4","my-5","my-6","my-7","my-8","my-9","my-10","my-11","my-12",
      "my-14","my-16","my-20","my-24","my-28","my-32","my-36","my-40","my-44","my-48","my-52","my-56","my-60","my-64","my-72","my-80","my-96","my-auto",

      "ms-0","ms-px","ms-0.5","ms-1","ms-1.5","ms-2","ms-2.5","ms-3","ms-3.5","ms-4","ms-5","ms-6","ms-7","ms-8","ms-9","ms-10","ms-11","ms-12",
      "ms-14","ms-16","ms-20","ms-24","ms-28","ms-32","ms-36","ms-40","ms-44","ms-48","ms-52","ms-56","ms-60","ms-64","ms-72","ms-80","ms-96","ms-auto",

      "me-0","me-px","me-0.5","me-1","me-1.5","me-2","me-2.5","me-3","me-3.5","me-4","me-5","me-6","me-7","me-8","me-9","me-10","me-11","me-12",
      "me-14","me-16","me-20","me-24","me-28","me-32","me-36","me-40","me-44","me-48","me-52","me-56","me-60","me-64","me-72","me-80","me-96","me-auto",

      "mt-0","mt-px","mt-0.5","mt-1","mt-1.5","mt-2","mt-2.5","mt-3","mt-3.5","mt-4","mt-5","mt-6","mt-7","mt-8","mt-9","mt-10","mt-11","mt-12",
      "mt-14","mt-16","mt-20","mt-24","mt-28","mt-32","mt-36","mt-40","mt-44","mt-48","mt-52","mt-56","mt-60","mt-64","mt-72","mt-80","mt-96","mt-auto",

      "mr-0","mr-px","mr-0.5","mr-1","mr-1.5","mr-2","mr-2.5","mr-3","mr-3.5","mr-4","mr-5","mr-6","mr-7","mr-8","mr-9","mr-10","mr-11","mr-12",
      "mr-14","mr-16","mr-20","mr-24","mr-28","mr-32","mr-36","mr-40","mr-44","mr-48","mr-52","mr-56","mr-60","mr-64","mr-72","mr-80","mr-96","mr-auto",

      "mb-0","mb-px","mb-0.5","mb-1","mb-1.5","mb-2","mb-2.5","mb-3","mb-3.5","mb-4","mb-5","mb-6","mb-7","mb-8","mb-9","mb-10","mb-11","mb-12",
      "mb-14","mb-16","mb-20","mb-24","mb-28","mb-32","mb-36","mb-40","mb-44","mb-48","mb-52","mb-56","mb-60","mb-64","mb-72","mb-80","mb-96","mb-auto",

      "ml-0","ml-px","ml-0.5","ml-1","ml-1.5","ml-2","ml-2.5","ml-3","ml-3.5","ml-4","ml-5","ml-6","ml-7","ml-8","ml-9","ml-10","ml-11","ml-12",
      "ml-14","ml-16","ml-20","ml-24","ml-28","ml-32","ml-36","ml-40","ml-44","ml-48","ml-52","ml-56","ml-60","ml-64","ml-72","ml-80","ml-96","ml-auto",

    ];
    return $margins;
  }

  /**
   * {@inheritdoc}
   */
  public function getPadding() {
    $paddings = [
      "p-0","p-px","p-0.5","p-1","p-1.5","p-2","p-2.5","p-3","p-3.5","p-4","p-5","p-6","p-7","p-8","p-9","p-10","p-11","p-12",
      "p-14","p-16","p-20","p-24","p-28","p-32","p-36","p-40","p-44","p-48","p-52","p-56","p-60","p-64","p-72","p-80","p-96",

      "px-0","px-px","px-0.5","px-1","px-1.5","px-2","px-2.5","px-3","px-3.5","px-4","px-5","px-6","px-7","px-8","px-9","px-10","px-11","px-12",
      "px-14","px-16","px-20","px-24","px-28","px-32","px-36","px-40","px-44","px-48","px-52","px-56","px-60","px-64","px-72","px-80","px-96",

      "py-0","py-px","py-0.5","py-1","py-1.5","py-2","py-2.5","py-3","py-3.5","py-4","py-5","py-6","py-7","py-8","py-9","py-10","py-11","py-12",
      "py-14","py-16","py-20","py-24","py-28","py-32","py-36","py-40","py-44","py-48","py-52","py-56","py-60","py-64","py-72","py-80","py-96",

      "ps-0","ps-px","ps-0.5","ps-1","ps-1.5","ps-2","ps-2.5","ps-3","ps-3.5","ps-4","ps-5","ps-6","ps-7","ps-8","ps-9","ps-10","ps-11","ps-12",
      "ps-14","ps-16","ps-20","ps-24","ps-28","ps-32","ps-36","ps-40","ps-44","ps-48","ps-52","ps-56","ps-60","ps-64","ps-72","ps-80","ps-96",

      "pe-0","pe-px","pe-0.5","pe-1","pe-1.5","pe-2","pe-2.5","pe-3","pe-3.5","pe-4","pe-5","pe-6","pe-7","pe-8","pe-9","pe-10","pe-11","pe-12",
      "pe-14","pe-16","pe-20","pe-24","pe-28","pe-32","pe-36","pe-40","pe-44","pe-48","pe-52","pe-56","pe-60","pe-64","pe-72","pe-80","pe-96",

      "pt-0","pt-px","pt-0.5","pt-1","pt-1.5","pt-2","pt-2.5","pt-3","pt-3.5","pt-4","pt-5","pt-6","pt-7","pt-8","pt-9","pt-10","pt-11","pt-12",
      "pt-14","pt-16","pt-20","pt-24","pt-28","pt-32","pt-36","pt-40","pt-44","pt-48","pt-52","pt-56","pt-60","pt-64","pt-72","pt-80","pt-96",

      "pr-0","pr-px","pr-0.5","pr-1","pr-1.5","pr-2","pr-2.5","pr-3","pr-3.5","pr-4","pr-5","pr-6","pr-7","pr-8","pr-9","pr-10","pr-11","pr-12",
      "pr-14","pr-16","pr-20","pr-24","pr-28","pr-32","pr-36","pr-40","pr-44","pr-48","pr-52","pr-56","pr-60","pr-64","pr-72","pr-80","pr-96",

      "pb-0","pb-px","pb-0.5","pb-1","pb-1.5","pb-2","pb-2.5","pb-3","pb-3.5","pb-4","pb-5","pb-6","pb-7","pb-8","pb-9","pb-10","pb-11","pb-12",
      "pb-14","pb-16","pb-20","pb-24","pb-28","pb-32","pb-36","pb-40","pb-44","pb-48","pb-52","pb-56","pb-60","pb-64","pb-72","pb-80","pb-96",

      "pl-0","pl-px","pl-0.5","pl-1","pl-1.5","pl-2","pl-2.5","pl-3","pl-3.5","pl-4","pl-5","pl-6","pl-7","pl-8","pl-9","pl-10","pl-11","pl-12",
      "pl-14","pl-16","pl-20","pl-24","pl-28","pl-32","pl-36","pl-40","pl-44","pl-48","pl-52","pl-56","pl-60","pl-64","pl-72","pl-80","pl-96",
    ];
    return $paddings;
  }

  /**
   * {@inheritdoc}
   */
  public function getAlignItems() {
    $align_items = [
      "items-start","items-end","items-center","items-baseline","items-stretch",
    ];
    return $align_items;
  }

  /**
   * {@inheritdoc}
   */
  public function getJustifyContent() {
    $justify_items = [
      "justify-normal","justify-start","justify-end","justify-center","justify-between","justify-around","justify-evenly","justify-stretch",
    ];
    return $justify_items;
  }

  /**
   * {@inheritdoc}
   */
  public function getGap() {
    $gaps = [
      "gap-0","gap-px","gap-0.5","gap-1","gap-1.5","gap-2","gap-2.5","gap-3","gap-3.5","gap-4","gap-5","gap-6","gap-7","gap-8","gap-9","gap-10","gap-11","gap-12",
      "gap-14","gap-16","gap-20","gap-24","gap-28","gap-32","gap-36","gap-40","gap-44","gap-48","gap-52","gap-56","gap-60","gap-64","gap-72","gap-80","gap-96",

      "gap-x-0","gap-x-px","gap-x-0.5","gap-x-1","gap-x-1.5","gap-x-2","gap-x-2.5","gap-x-3","gap-x-3.5","gap-x-4","gap-x-5","gap-x-6","gap-x-7","gap-x-8","gap-x-9","gap-x-10","gap-x-11","gap-x-12",
      "gap-x-14","gap-x-16","gap-x-20","gap-x-24","gap-x-28","gap-x-32","gap-x-36","gap-x-40","gap-x-44","gap-x-48","gap-x-52","gap-x-56","gap-x-60","gap-x-64","gap-x-72","gap-x-80","gap-x-96",

      "gap-y-0","gap-y-px","gap-y-0.5","gap-y-1","gap-y-1.5","gap-y-2","gap-y-2.5","gap-y-3","gap-y-3.5","gap-y-4","gap-y-5","gap-y-6","gap-y-7","gap-y-8","gap-y-9","gap-y-10","gap-y-11","gap-y-12",
      "gap-y-14","gap-y-16","gap-y-20","gap-y-24","gap-y-28","gap-y-32","gap-y-36","gap-y-40","gap-y-44","gap-y-48","gap-y-52","gap-y-56","gap-y-60","gap-y-64","gap-y-72","gap-y-80","gap-y-96",
    ];
    return $gaps;
  }

  /**
   * {@inheritdoc}
   */
  public function getFlexBasis() {
    $flex_basis = [
      "basis-0","basis-1","basis-2","basis-3","basis-4","basis-5","basis-6","basis-7","basis-8","basis-9",
      "basis-10","basis-11","basis-12","basis-14","basis-16","basis-20","basis-24","basis-28","basis-32","basis-36",
      "basis-40","basis-44","basis-48","basis-52","basis-56","basis-60","basis-64","basis-72","basis-80","basis-96",
      "basis-auto","basis-px","basis-0.5","basis-1.5","basis-2.5","basis-3.5",
      "basis-1/2","basis-1/3","basis-2/3","basis-1/4","basis-2/4","basis-3/4",
      "basis-1/5","basis-2/5","basis-3/5","basis-4/5","basis-1/6","basis-2/6","basis-3/6","basis-4/6","basis-5/6",
      "basis-1/12","basis-2/12","basis-3/12","basis-4/12","basis-5/12","basis-6/12","basis-7/12","basis-8/12","basis-9/12","basis-10/12","basis-11/12",
      "basis-full",
    ];
    return $flex_basis;
  }

  /**
   * {@inheritdoc}
   */
  public function getFlexDirection() {
    $flex_direction = [
      "flex-row","flex-row-reverse","flex-col","flex-col-reverse",
    ];
    return $flex_direction;
  }

  /**
   * {@inheritdoc}
   */
  public function getOrder() {
    $order = [
      "order-1","order-2","order-3","order-4","order-5","order-6","order-7","order-8","order-9",
      "order-10","order-11","order-12","order-first","order-last","order-none",
    ];
    return $order;
  }

  /**
   * {@inheritdoc}
   */
  public function getClassOptions(string $property) : array {
    $class_options = [];
    $classes = [];
    $map = $this->getPropertyMap();
    /*
    $selected_map = array_filter( $map, function ($a) use($property) {
      return $a['css_property'] == $property;
    });
    /**/
    $selected_map = [];
    if (isset($map[$property])) {
      $selected_map = $map[$property];
    }

    if (!empty($selected_map)) {
      //$properties = reset($selected_map);
      //$method = $properties['method'];
      $method = $selected_map['method'];
      $classes = $this->$method();
    }

    if (!empty($classes)) {
      foreach($classes as $class) {
        $class_options[$class] = $class;
      }
    }

    return $class_options;
  }

  /**
   * {@inheritdoc}
   */
  public function getPropertyOptions() : array {
    $map = $this->getPropertyMap();
    $options = [];
    foreach ($map as $key => $properties) {
      $options[$key] = $properties['label'];
    }
    return $options;
  }

}
