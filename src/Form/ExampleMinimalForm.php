<?php

declare(strict_types=1);

namespace Drupal\demo_confirm_form\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Example confirm form.
 */
class ExampleMinimalForm extends ConfirmFormBase {

  /**
   * The submitted action needing to be confirmed.
   *
   * @var string|null
   */
  protected $action = NULL;

  /**
   * JobOfferSettingsConfirmForm constructor.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The Messenger service.
   */
  public function __construct(MessengerInterface $messenger) {
    $this->setMessenger($messenger);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): ExampleMinimalForm {
    return new static(
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'example_confirm_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion(): TranslatableMarkup {
    return $this->t('Are you sure you want to %action?', ['%action' => $this->action]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl(): Url {
    return new Url('demo_confirm_form.settings_form');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    // When this is the confirmation step fall through to the confirmation form.
    if ($this->action) {
      return parent::buildForm($form, $form_state);
    }

    $form['action'] = [
      '#type' => 'radios',
      '#title' => $this->t('Choose action'),
      '#options' => [
        'delete' => $this->t('Delete'),
        'import' => $this->t('Import'),
      ],
      '#required' => TRUE,
    ];
    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Run selected action'),
      '#button_type' => 'primary',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    // The confirmation step needs no additional validation.
    if ($this->action) {
      return;
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    // If this form has not yet been confirmed, store the values and rebuild.
    if (!$this->action) {
      $form_state->setRebuild();
      $this->action = $form_state->getValue('action');
      return;
    }

    $method = $this->action . 'Data';
    $this->{$method}();
  }

  /**
   * Example action.
   */
  protected function deleteData(): void {
    $this->messenger->addMessage($this->t('Action %action done!', ['%action' => $this->action]));
  }

  /**
   * Example action.
   */
  protected function importData(): void {
    $this->messenger->addMessage($this->t('Action %action done!', ['%action' => $this->action]));
  }
}
