<?php

namespace Drupal\multiple_cron\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class CronMultipleConfigForm.
 */
class CronMultipleConfigForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    //cron config form id
    return 'cron_multiple_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  //get the list of the custom modules which will use to create a cron
  public function getModulelist() {
    //this is fake commit
    $module_listing = \Drupal::service('extension.list.module')->getList();
    $modules = array();
    foreach($module_listing as $module) {

      if(strtolower($module->info['package']) == 'custom') {
        $modules[$module->info['project']] = $module->info['name'];
      }

    }
    return $modules;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#required' => TRUE,
    ];

    $form['module_listing'] = [
      '#type' => 'select',
      '#title' => $this->t('Module Listing'),
      '#options' => $this->getModulelist(),
      '#required' => TRUE,
    ];

    $form['callback_function'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Callback Function'),
      '#description' => $this->t('Please make sure callback function should be in module file of custom module.'),
      '#required' => TRUE,
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];
    
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    //this is the check that if i reset after push then what it will do
      $values = $form_state->getValues();
      $regex = '/^[a-zA-Z\s]+$/';
      if (!preg_match($regex, $values['name'])) {
        $form_state->setErrorByName('name',  $this->t('Only space and alphabets letter are allowed'));
      }
      $name =  str_replace(' ','_',strtolower($values['name']));
      $module_name = $form_state->getValues()['module_listing'];
      $config_file = 'ultimate_cron.job.'.$module_name.'_'.$name;
      $config_file_path = \Drupal\Core\Site\Settings::get('config_sync_directory');
      if(file_exists($config_file_path.'/'.$config_file.'.yml')) {
        $form_state->setErrorByName('title',  $this->t('this cron is already exist please try with some other title')); 
      }

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    //hello this is master branch
    $values = $form_state->getValues();
    $module_name = $values['module_listing'];
    $title =  str_replace(' ','_',strtolower($values['name']));
    $name =  str_replace(' ','_',strtolower($values['name']));
    $config_file = 'ultimate_cron.job.'.$module_name.'_'.$title;
    $config_file_path = \Drupal\Core\Site\Settings::get('config_sync_directory');
    $config = \Drupal::service('config.factory')->getEditable('ultimate_cron.job.'.$module_name.'_'.$name);
    $config->set('status',true)
    ->set('dependencies.modules',$module_name)
    ->set('title',$values['name'])
    ->set('id',$module_name.'_'.$name)
    ->set('weight',0)
    ->set('module',$module_name)
    ->set('callback',$values['callback_function'])->save();
  }
}
