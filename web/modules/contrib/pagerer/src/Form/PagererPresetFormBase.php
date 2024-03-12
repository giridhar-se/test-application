<?php

namespace Drupal\pagerer\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Pager\PagerManagerInterface;
use Drupal\pagerer\Entity\PagererPreset;
use Drupal\pagerer\Plugin\PagererStyleManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base form handler for image style add and edit forms.
 */
abstract class PagererPresetFormBase extends EntityForm {

  /**
   * Constructs a base class for pagerer preset add and edit forms.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $pagererPresetStorage
   *   The Pagerer preset entity storage.
   * @param \Drupal\Core\Pager\PagerManagerInterface $pagerManager
   *   The pager manager.
   * @param \Drupal\pagerer\Plugin\PagererStyleManager $styleManager
   *   The plugin manager for Pagerer style plugins.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(
    protected EntityStorageInterface $pagererPresetStorage,
    protected PagerManagerInterface $pagerManager,
    protected PagererStyleManager $styleManager,
    MessengerInterface $messenger
  ) {
    $this->setMessenger($messenger);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')->getStorage('pagerer_preset'),
      $container->get('pager.manager'),
      $container->get('pagerer.style.manager'),
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Pager name'),
      '#default_value' => $this->getPagererPresetEntity()->label(),
      '#required' => TRUE,
    ];
    $form['id'] = [
      '#type' => 'machine_name',
      '#machine_name' => [
        'exists' => [$this->pagererPresetStorage, 'load'],
        'source' => ['label'],
      ],
      '#default_value' => $this->getPagererPresetEntity()->id(),
      '#required' => TRUE,
    ];
    return parent::form($form, $form_state);
  }

  /**
   * Returns the PagererPreset entity being used by this form.
   *
   * @return \Drupal\pagerer\Entity\PagererPreset
   *   The form entity.
   */
  protected function getPagererPresetEntity(): PagererPreset {
    assert($this->entity instanceof PagererPreset);
    return $this->entity;
  }

}
