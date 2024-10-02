<?php

/**
 * @file
 * Provides newsletter subscribe form
 */

namespace Drupal\infinity_getresponse_newsletter\Form;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\user\Entity\User;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountInterface;

use Drupal\infinity_getresponse_newsletter\NewsletterService;

/**
 * Class SubscribeForm
 *
 * @package Drupal\infinity_getresponse_newsletter\Form
 */
class SubscribeForm extends FormBase
{

    /**
     * @var \Drupal\Core\Config\ConfigFactoryInterface
     */
    protected $configFactory;

    /**
     * @var \Drupal\Core\Session\AccountInterface
     */
    protected $currentUser;

    /**
     * @var \Drupal\infinity_getresponse_newsletter\NewsletterService
     */
    protected $newsletterService;

    /**
     * @var \Drupal\Core\Messenger\MessengerInterface
     */
    protected $messenger;


    public function __construct(ConfigFactoryInterface $config_factory,
                                AccountInterface       $current_user,
                                MessengerInterface     $messenger,
                                NewsletterService      $newsletterService)
    {

        $this->config = $config_factory->get('infinity_getresponse_newsletter.settings');
        $this->currentUser = $current_user;
        $this->messenger = $messenger;
        $this->newsletterService = $newsletterService;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container)
    {
        return new static(
            $container->get('config.factory'),
            $container->get('current_user'),
            $container->get('messenger'),
            $container->get('infinity_getresponse_newsletter.newsletter'),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'infinity_getresponse_newsletter_subscriber_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form['#name'] = 'getresponse_subscriber_form';

        $form['subscriber_email'] = [
            '#type' => 'email',
            '#attributes' => [
                'placeholder' => $this->t('Enter valid email address'),
            ],
        ];

        if ($this->currentUser->isAuthenticated()) {
            $form['subscriber_email']['#default_value'] = $this->currentUser->getEmail();
        }

        $terms_text = $this->config->get('newsletter_terms');
        $form['terms'] = [
            '#type' => 'checkbox',
            '#title' => $this->t($terms_text),
        ];

        $terms_clause_text = $this->config->get('newsletter_clause_terms');
        $form['terms_clause'] = [
            '#type' => 'checkbox',
            '#title' => $this->t($terms_clause_text),
        ];

        $form['submit'] = [
            '#type' => 'submit',
            '#name' => 'newsletter_submit_btn',
            '#attributes' => [
                'class' => ['form-submit-btn'],
            ],
            '#value' => $this->t('Subscribe'),
        ];

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        $values = $form_state->getValues();

        if (!$this->newsletterService->validateEmail($values['subscriber_email'])) {
            $form_state->setErrorByName("subscriber_email", $this->t('Please provide valid email'));
        }

        if (empty($values['terms']) || (isset($values['terms']) && $values['terms'] != 1)) {
            $form_state->setErrorByName("terms", $this->t('Please accept terms of service.'));
        }

        if (empty($values['terms_clause']) || (isset($values['terms_clause']) && $values['terms_clause'] != 1)) {
            $form_state->setErrorByName("terms_clause", $this->t('Please accept information clause.'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {

        $values = $form_state->getValues();

        try {
            $response = $this->newsletterService->subscribe($values['subscriber_email']);

            switch ($response) {
                case NewsletterService::SUBSCRIBE_SUCCESS:
                    $this->messenger->addStatus($this->t(strip_tags($this->config->get('subscribe_success'))));
                    break;
                case NewsletterService::SUBSCRIBE_ERROR_EMAIL_EXIST:
                    $this->messenger->addWarning($this->t(strip_tags($this->config->get('subscribe_exist'))));
                    break;
                case NewsletterService::SUBSCRIBE_ERROR:
                    $this->messenger->addError($this->t(strip_tags($this->config->get('subscribe_error'))));
                    break;
            }
        } catch (\Exception $e) {

            \Drupal::logger('subscribe submit error')->info(json_encode($e));
        }
    }
}
