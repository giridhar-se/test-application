<?php

namespace Drupal\pagerer\Plugin\pagerer;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\pagerer\Entity\PagererPreset;
use Drupal\pagerer\Pagerer;
use Drupal\pagerer\Plugin\PagererStyleInterface;

/**
 * A multi-pane (left, center, and right) pager style.
 *
 * @PagererStyle(
 *   id = "multipane",
 *   title = @Translation("Pagerer multi-pane pager"),
 *   short_title = @Translation("Multi-pane"),
 *   help = @Translation("A multi-pane (left, center, and right) pager style, enabling each pane to contain a base style."),
 *   style_type = "composite"
 * )
 */
class Multipane extends PluginBase implements PagererStyleInterface {

  /**
   * The Pagerer pager object.
   *
   * @var \Drupal\pagerer\Pagerer
   */
  protected Pagerer $pager;

  /**
   * {@inheritdoc}
   */
  public function setPager(Pagerer $pager): PagererStyleInterface {
    $this->pager = $pager;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function preprocess(array &$variables): void {

    // Load preset if specified.
    if (!empty($this->configuration['preset'])) {
      $preset = PagererPreset::load($this->configuration['preset']);
    }

    // Fully qualify all panes.
    if (isset($preset)) {
      $cacheable = TRUE;
      foreach (['left', 'center', 'right'] as $pane) {
        // Determine pane's style.
        if ($preset_style = $preset->getPaneData($pane, 'style')) {
          $this->configuration['panes'][$pane]['style'] = $preset_style;
        }
        // If we are overriding preset's configuration via passed in pane
        // variables, we can't cache on its config entity.
        if (!empty($this->configuration['panes'][$pane]['config'])) {
          $cacheable = FALSE;
        }
        // Determine pane's config.
        if ($preset_config = $preset->getPaneData($pane, 'config')) {
          $this->configuration['panes'][$pane]['config'] = NestedArray::mergeDeep($preset_config, $this->configuration['panes'][$pane]['config']);
        }
      }
    }

    // Check if pager is needed; if not, return immediately.
    // It is the lowest required number of pages in any of the panes.
    $page_restriction = min(
      $this->configuration['panes']['left']['config']['display_restriction'] ?? 2,
      $this->configuration['panes']['center']['config']['display_restriction'] ?? 2,
      $this->configuration['panes']['right']['config']['display_restriction'] ?? 2
    );
    if ($this->pager->getTotalPages() < $page_restriction) {
      return;
    }

    // Build render array.
    foreach (['left', 'center', 'right'] as $pane) {
      if ($this->configuration['panes'][$pane]['style'] <> 'none') {
        $variables['pagerer'][$pane . '_pane'] = [
          '#type' => 'pager',
          '#theme' => 'pagerer_base',
          '#style' => $this->configuration['panes'][$pane]['style'],
          '#route_name' => $variables['pager']['#route_name'],
          '#route_parameters' => $variables['pager']['#route_parameters'] ?? [],
          '#element' => $variables['pager']['#element'],
          '#parameters' => $variables['pager']['#parameters'],
          '#config' => $this->configuration['panes'][$pane]['config'],
        ];
      }
      else {
        $variables['pagerer'][$pane . '_pane'] = [];
      }
    }

    // Add the preset entity cache metadata, if possible.
    if (isset($preset) && $cacheable) {
      CacheableMetadata::createFromRenderArray($variables)
        ->merge(CacheableMetadata::createFromObject($preset))
        ->applyTo($variables);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setConfigurationContext(PagererPreset $pagerer_preset, string $pagerer_preset_pane): void {
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

}
