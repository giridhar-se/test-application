<?php

namespace Drupal\pagerer\Plugin;

use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\pagerer\Entity\PagererPreset;
use Drupal\pagerer\Pagerer;

/**
 * Provides an interface defining an Pagerer pager style.
 */
interface PagererStyleInterface extends PluginFormInterface {

  /**
   * Sets the Pagerer pager to be rendered.
   *
   * @param \Drupal\pagerer\Pagerer $pager
   *   The pager object.
   *
   * @return \Drupal\pagerer\Plugin\PagererStyleInterface
   *   The pager style object.
   */
  public function setPager(Pagerer $pager): PagererStyleInterface;

  /**
   * Prepares to render the pager.
   *
   * @param array $variables
   *   An associative array containing:
   *   - style: The PagererStyle plugin id to be used to render the pager. Only
   *     for base style plugins.
   *   - element: An optional integer to distinguish between multiple pagers on
   *     one page.
   *   - parameters: An associative array of query string parameters to append
   *     to the pager links.
   *   - config: An associative array of configuration elements for the pager
   *     style.
   */
  public function preprocess(array &$variables): void;

  /**
   * Sets the current PagererPreset and pane being configured.
   *
   * @param \Drupal\pagerer\Entity\PagererPreset $pagerer_preset
   *   The PagererPreset.
   * @param string $pagerer_preset_pane
   *   The PagererPreset pane.
   */
  public function setConfigurationContext(PagererPreset $pagerer_preset, string $pagerer_preset_pane): void;

}
