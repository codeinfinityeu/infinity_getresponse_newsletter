<?php
/**
 * @file
 * Provides block with subscribe form
 */

namespace Drupal\infinity_getresponse_newsletter\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides an 'infinity_getresponse_newsletter_subscriber_form_block' block.
 *
 * @Block(
 *   id = "infinity_getresponse_newsletter_subscriber_form_block",
 *   admin_label = @Translation("Newsletter - GetResponse subscriber block"),
 *   category = @Translation("Newsletter")
 * )
 */
class SubscriberBlock extends BlockBase
{
    /**
     * {@inheritdoc}
     */
    public function blockForm($form, FormStateInterface $form_state)
    {
        $form = parent::blockForm($form, $form_state);

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $subscribe_form = \Drupal::formBuilder()
            ->getForm('Drupal\infinity_getresponse_newsletter\Form\SubscribeForm');

        return [
          '#theme' => 'newsletter_block',
          '#form' => $subscribe_form
        ];
    }
}
