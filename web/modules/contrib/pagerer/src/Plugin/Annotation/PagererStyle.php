<?php

namespace Drupal\pagerer\Plugin\Annotation;

use Drupal\Component\Annotation\Plugin;
use Drupal\Core\Annotation\Translation;

/**
 * Defines a Plugin annotation object for the Pagerer style plugin.
 *
 * @Annotation
 *
 * @see \Drupal\pagerer\Plugin\PagererStyleManager
 */
class PagererStyle extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public string $id;

  /**
   * The title of the Pagerer style.
   *
   * The string should be wrapped in a @Translation().
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public Translation $title;

  /**
   * The short title of the Pagerer style.
   *
   * The string should be wrapped in a @Translation().
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public Translation $short_title;

  /**
   * A informative description of the Pagerer style.
   *
   * The string should be wrapped in a @Translation().
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public Translation $help;

  /**
   * The style type.
   *
   * Can be 'base' for a base pager style, or 'composite' for special
   * style combinations like e.g. the Pagerer multi-pane pager.
   *
   * @var string
   */
  public string $style_type;

}
