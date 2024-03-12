<?php

namespace Drupal\pagerer\Form;

use Drupal\Core\Entity\EntityDeleteForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Render\ElementInfoManager;
use Drupal\Core\Render\ElementInfoManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Delete a Pagerer preset.
 */
class PagererPresetDeleteForm extends EntityDeleteForm {

  /**
   * Constructs a PagererPresetDeleteForm form.
   *
   * @param \Drupal\Core\Render\ElementInfoManagerInterface $elementInfoManager
   *   The element info manager.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(
    protected ElementInfoManagerInterface $elementInfoManager,
    MessengerInterface $messenger
  ) {
    $this->setMessenger($messenger);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.element_info'),
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    assert($this->elementInfoManager instanceof ElementInfoManager);

    parent::submitForm($form, $form_state);
    $config = $this->configFactory()->getEditable('pagerer.settings');
    if ($config->get('core_override_preset') == $this->getEntity()->id()) {
      $config->set('core_override_preset', 'core')->save();
      $this->elementInfoManager->clearCachedDefinitions();
      $this->messenger->addMessage($this->t("Pager %preset_label was being used as replacement of Drupal's core pager. Drupal's core pager has been reset as main pager.", ['%preset_label' => $this->getEntity()->label()]), 'warning');
    }
  }

}
