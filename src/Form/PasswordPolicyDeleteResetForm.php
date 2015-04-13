<?php

namespace Drupal\password_policy\Form;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;


class PasswordPolicyDeleteResetForm extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'password_policy_delete_reset_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    //get policy and plugin
    //get current path
    $url = \Drupal\Core\Url::fromRoute('<current>');
    $current_path = $url->toString();
    $path_args = explode('/', $current_path);

    if (count($path_args) != 8) {
      drupal_set_message('Improper parameters', 'error');
      return array();
    }

    $constraint_id = $path_args[7];

    if (!is_numeric($constraint_id)) {
      drupal_set_message('No constraint found', 'error');
      return array();
    }

    $constraint = db_select('password_policy_reset', 'p')
      ->fields('p')
      ->condition('cid', $constraint_id)
      ->execute()
      ->fetchObject();

    if (empty($constraint)) {
      drupal_set_message('No constraint found', 'error');
      return array();
    }


    $form = array(
      'constraint_id' => array(
        '#type' => 'hidden',
        '#value' => (is_numeric($constraint_id)) ? $constraint_id : '',
      ),
      'description' => array(
        '#markup' => 'Are you sure you wish to delete this policy?'
      ),
      'submit' => array(
        '#type' => 'submit',
        '#value' => t('Confirm deletion of policy'),
      ),
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $constraint_id = $form_state->getValue('constraint_id');
    $result = db_delete('password_policy_reset')
      ->condition('cid', $constraint_id)
      ->execute();
    if ($result) {
      drupal_set_message('Your constraint has been deleted');
    }
    else {
      drupal_set_message('There was an issue deleting your constraint, please try again');
    }
    $form_state->setRedirect('password_policy.settings');
  }
}