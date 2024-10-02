<?php

/**
 * @file
 * Provides newsletter subscribe service
 */

namespace Drupal\infinity_getresponse_newsletter;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class NewsletterService.
 *
 * @package Drupal\infinity_getresponse_newsletter
 */
class NewsletterService
{

    public const SUBSCRIBE_SUCCESS = 1;

    public const SUBSCRIBE_ERROR = 2;

    public const SUBSCRIBE_ERROR_EMAIL_EXIST = 3;

    /**
     * Config factory.
     *
     * @var ConfigFactoryInterface
     */
    protected $config;

    /**
     * Api url
     *
     * @var string
     */
    protected $api_url;

    /**
     * Api key
     *
     * @var string
     */
    protected $api_key;

    /**
     * List id - token
     *
     * @var string
     */
    protected $campaign_list;

    /**
     * Constructor
     *
     * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
     */
    public function __construct(ConfigFactoryInterface $configFactory)
    {

        $this->config = $configFactory->get('infinity_getresponse_newsletter.settings');

        $this->api_url = 'https://api.getresponse.com/v3/contacts';
        $this->api_key = $this->config->get('api_key');
        $this->campaign_list = $this->config->get('campaign_list');
    }

    /**
     * Validate email address
     * @param string $email
     * @return bool
     */
    public function validateEmail(string $email)
    {

        return \Drupal::service('email.validator')
                ->isValid($email) && filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Subscribe email to mailing list
     * @param string $email
     * @return int
     */
    public function subscribe(string $email)
    {
        try {

            $headers = [
                'Content-Type: application/json',
                'X-Auth-Token: api-key ' . $this->api_key,
            ];

            $ip = \Drupal::request()->getClientIp();

            $data = [
                'email' => $email,
                'ipAddress' => $ip,
                'campaign' => [
                    'campaignId' => $this->campaign_list,
                ],
            ];

            $ch = curl_init($this->api_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

            $response = curl_exec($ch);
            curl_close($ch);

            // No errors
            if (!curl_errno($ch)) {

                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                switch ($http_code) {

                    case 202:
                    {
                        // Success
                        return NewsletterService::SUBSCRIBE_SUCCESS;
                    }
                    case 409:
                    {
                        // Conflict - list already contain email
                        return NewsletterService::SUBSCRIBE_ERROR_EMAIL_EXIST;
                    }
                    case 400:
                    case 401:
                    case 429:
                    {
                        // 400 - Request validation issue
                        // 401 - Auth error
                        // 429 - Throttling limit

                        \Drupal::logger('newsletter error')->info($response);
                        return NewsletterService::SUBSCRIBE_ERROR;
                    }
                }
            }

        } catch (\Exception $e) {

        }

        return NewsletterService::SUBSCRIBE_ERROR;
    }
}
