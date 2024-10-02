<?php

/**
 * @file
 * Provides settings form
 */

namespace Drupal\infinity_getresponse_newsletter\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * SettingsForm class
 */
class SettingsForm extends ConfigFormBase
{
    /**
     * Config instance.
     *
     * @var \Drupal\Core\Config\ConfigFactoryInterface
     */
    protected $cfg;

    /**
     * Creates a new SettingsForm instance.
     *
     * @param ConfigFactoryInterface $config_factory
     */
    public function __construct(ConfigFactoryInterface $config_factory)
    {
        parent::__construct($config_factory);

        $this->cfg = $this->config('infinity_getresponse_newsletter.settings');
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container)
    {
        return new static(
            $container->get('config.factory')
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getEditableConfigNames()
    {
        return ['infinity_getresponse_newsletter.settings'];
    }

    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'infinity_getresponse_newsletter__settings';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form['info'] = [
            '#title' => $this->t('Info'),
            '#type' => 'markup',
            '#markup' => '<pre class="code">https://apireference.getresponse.com/#operation/getContactList</pre>',
        ];

        $form['newsletter_terms'] = [
            '#type' => 'text_format',
            '#title' => $this->t('Newsletter terms'),
            '#rows' => 3,
            '#maxlength' => 500,
            '#default_value' => $this->cfg->get('newsletter_terms'),
            '#format' => 'full_html',
            '#allowed_formats' => ['full_html'],
            '#required' => TRUE,
        ];

        $form['newsletter_clause_terms'] = [
            '#type' => 'text_format',
            '#title' => $this->t('Newsletter RODO terms'),
            '#rows' => 3,
            '#maxlength' => 500,
            '#default_value' => $this->cfg->get('newsletter_clause_terms'),
            '#format' => 'full_html',
            '#allowed_formats' => ['full_html'],
            '#required' => TRUE,
        ];

        $form['newsletter_settings'] = [
            '#type' => 'details',
            '#title' => t('Newsletter settings'),
            '#open' => TRUE,
        ];

        $form['newsletter_settings']['campaign_list'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Subscriber list'),
            '#default_value' => $this->cfg->get('campaign_list'),
            '#required' => TRUE,
            '#attributes' => [
                'placeholder' => '',
            ],
        ];

        $form['newsletter_settings']['api_key'] = [
            '#type' => 'textfield',
            '#title' => $this->t('API key'),
            '#default_value' => $this->cfg->get('api_key'),
            '#required' => TRUE,
            '#attributes' => [
                'placeholder' => '',
            ],
        ];

        /**
         * Text per API response status
         */

        $form['newsletter_text_monits'] = [
            '#type' => 'horizontal_tabs',
        ];

        $form['subscribe_success_text_monits'] = [
            '#type' => 'details',
            '#title' => $this->t('Subscription success'),
            '#description' => '-',
            '#collapsible' => TRUE,
            '#collapsed' => FALSE,
            '#group' => 'newsletter_text_monits',
        ];

        $form['subscribe_success_text_monits']['subscribe_success'] = [
            '#type' => 'text_format',
            '#title' => $this->t('Subscription success'),
            '#rows' => 3,
            '#maxlength' => 255,
            '#default_value' => $this->cfg->get('subscribe_success'),
            '#format' => 'basic_html',
            '#allowed_formats' => ['basic_html'],
        ];

        $form['subscribe_exist_text_monits'] = [
            '#type' => 'details',
            '#title' => $this->t('Subscriber already exists'),
            '#description' => '-',
            '#collapsible' => TRUE,
            '#collapsed' => FALSE,
            '#group' => 'newsletter_text_monits',
        ];

        $form['subscribe_exist_text_monits']['subscribe_exist'] = [
            '#type' => 'text_format',
            '#title' => $this->t('Subscriber already exist'),
            '#rows' => 3,
            '#maxlength' => 255,
            '#default_value' => $this->cfg->get('subscribe_exist'),
            '#format' => 'basic_html',
            '#allowed_formats' => ['basic_html'],
        ];

        // Error
        $form['subscribe_error_text_monits'] = [
            '#type' => 'details',
            '#title' => $this->t('General error'),
            '#description' => '-',
            '#collapsible' => TRUE,
            '#collapsed' => FALSE,
            '#group' => 'newsletter_text_monits',
        ];

        $form['subscribe_error_text_monits']['subscribe_error'] = [
            '#type' => 'text_format',
            '#title' => $this->t('General error'),
            '#rows' => 3,
            '#maxlength' => 255,
            '#default_value' => $this->cfg->get('subscribe_error'),
            '#format' => 'basic_html',
            '#allowed_formats' => ['basic_html'],
        ];

        return parent::buildForm($form, $form_state);
    }

    /**
     * Form validation
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        $values = $form_state->getValues();

        // Validate API settings
        if (strlen($values['campaign_list']) < 2) {
            $form_state->setErrorByName('campaign_list', $this->t('Provide valid subscriber list!'));
        }
        if (strlen($values['api_key']) < 2) {
            $form_state->setErrorByName('api_key', $this->t('Provide valid API key!'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        parent::submitForm($form, $form_state);

        $values = $form_state->getValues();

        // API Settings
        $this->cfg->set('campaign_list', $values['campaign_list']);
        $this->cfg->set('api_key', $values['api_key']);

        // Terms
        $this->cfg->set('newsletter_terms', $values['newsletter_terms']['value']);
        $this->cfg->set('newsletter_clause_terms', $values['newsletter_clause_terms']['value']);

        // Text monits
        $this->cfg->set('subscribe_success', $values['subscribe_success']['value']);
        $this->cfg->set('subscribe_error', $values['subscribe_error']['value']);
        $this->cfg->set('subscribe_exist', $values['subscribe_exist']['value']);

        $this->cfg->save();
    }
}
