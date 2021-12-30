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
 *
 * https://git.drupalcode.org/project/drupal/-/blob/9.3.x/core/modules/config/src/Form/ConfigSingleImportForm.php
 */
class ExampleForm extends ConfirmFormBase {

  /**
   * The submitted action needing to be confirmed.
   *
   * @var string|null
   */
  protected $action = NULL;

  /**
   * The submitted data needing to be confirmed.
   *
   * @var array
   */
  protected $data = [];

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
  public static function create(ContainerInterface $container): ExampleForm {
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
  public function getFormName(): string {
    return 'example_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription(): TranslatableMarkup {
    $args = [
      '%data' => $this->data,
      '%action' => $this->action,
    ];
    return $this->t('This action of %action on %data is definitive.', $args);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText(): TranslatableMarkup {
    $args = [
      '%data' => $this->data,
      '%action' => $this->action,
    ];
    return $this->t('Run %action on %data', $args);
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion(): TranslatableMarkup {
    $args = [
      '%data' => $this->data,
      '%action' => $this->action,
    ];
    return $this->t('Are you sure you want to %action on %data?', $args);
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

    $form['data'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Data to process'),
      '#required' => TRUE,
    ];
    $form['action'] = [
      '#type' => 'radios',
      '#title' => $this->t('Choose action'),
      '#options' => [
        'delete' => $this->t('Delete'),
        'import' => $this->t('Import'),
        'invalid' => $this->t('Invalid action'),
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

    if ($form_state->getValue('data') === 'invalid') {
      $form_state->setErrorByName('data', $this->t('The value is not correct.'));
    }

    $action = $form_state->getValue('action');
    $method = $action . 'Data';
    if (!method_exists($this, $method)) {
      $form_state->setErrorByName('action', $this->t('Action %action not yet implemented!', ['%action' => $action]), 'error');
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
      $this->data = $form_state->getValue('data');
      return;
    }

    $method = $this->action . 'Data';
    if (!method_exists($this, $method)) {
      $this->messenger->addMessage($this->t('Action %action not yet implemented!', ['%action' => $this->action]), 'error');
      return;
    }
    $this->{$method}();
  }

  /**
   * Example action.
   */
  protected function deleteData(): void {
    $this->messenger->addMessage($this->t('Deleted %data!', ['%data' => $this->data]));
  }

  /**
   * Example action.
   */
  protected function importData(): void {
    $this->messenger->addMessage($this->t('Imported %data!', ['%data' => $this->data]));
  }
}
