<?php

namespace Drupal\dorp_theme_extras\Form;

use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class ExampleForm.
 *
 * The example form used in the style guide.
 */
class ExampleForm implements FormInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'example_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['checkbox'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Checkbox'),
      '#description' => $this->t('Nihil tempor porta ullam nostra eveniet repudiandae possimus'),
    ];
    $form['checkboxes'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Checkboxes'),
      '#options' => [
        'option 1' => $this->t('option 1'),
        'option 2' => $this->t('option 2'),
        'option 3' => $this->t('option 3'),
      ],
      '#description' => $this->t('Nihil tempor porta ullam nostra eveniet repudiandae possimus'),
    ];
    $form['color'] = [
      '#type' => 'color',
      '#title' => $this->t('Color'),
      '#description' => $this->t('Nihil tempor porta ullam nostra eveniet repudiandae possimus'),
    ];
    $form['date'] = [
      '#type' => 'date',
      '#title' => $this->t('Date'),
      '#default_value' => '',
      '#description' => $this->t('Nihil tempor porta ullam nostra eveniet repudiandae possimus'),
    ];
    $form['datelist'] = [
      '#type' => 'datelist',
      '#title' => $this->t('Datelist'),
      '#default_value' => '',
      '#description' => $this->t('Nihil tempor porta ullam nostra eveniet repudiandae possimus'),
    ];
    $form['datetime'] = [
      '#type' => 'datetime',
      '#title' => $this->t('Datetime'),
      '#default_value' => '',
      '#description' => $this->t('Nihil tempor porta ullam nostra eveniet repudiandae possimus'),
    ];
    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#description' => $this->t('Nihil tempor porta ullam nostra eveniet repudiandae possimus'),
    ];
    $form['entity'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Entity'),
      '#target_type' => 'node',
      '#description' => $this->t('Nihil tempor porta ullam nostra eveniet repudiandae possimus'),
    ];
    $form['file'] = [
      '#type' => 'file',
      '#title' => $this->t('File'),
      '#description' => $this->t('Nihil tempor porta ullam nostra eveniet repudiandae possimus'),
    ];
    $form['image_button'] = [
      '#type' => 'image_button',
      '#title' => $this->t('Image Button'),
      '#description' => $this->t('Nihil tempor porta ullam nostra eveniet repudiandae possimus'),
      '#src' => 'https://source.unsplash.com/random/50x50',
    ];
    $form['language'] = [
      '#type' => 'language_select',
      '#title' => $this->t('Language'),
      '#description' => $this->t('Nihil tempor porta ullam nostra eveniet repudiandae possimus'),
    ];
    $form['managed_file'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Managed File'),
      '#description' => $this->t('Nihil tempor porta ullam nostra eveniet repudiandae possimus'),
    ];
    $form['number'] = [
      '#type' => 'number',
      '#title' => $this->t('Number'),
      '#description' => $this->t('Nihil tempor porta ullam nostra eveniet repudiandae possimus'),
    ];
    $form['password'] = [
      '#type' => 'password',
      '#title' => $this->t('Password'),
      '#maxlength' => 64,
      '#size' => 64,
      '#description' => $this->t('Nihil tempor porta ullam nostra eveniet repudiandae possimus'),
    ];
    $form['password_confirm'] = [
      '#type' => 'password_confirm',
      '#title' => $this->t('Password Confirm'),
      '#maxlength' => 64,
      '#size' => 64,
      '#description' => $this->t('Nihil tempor porta ullam nostra eveniet repudiandae possimus'),
    ];
    $form['radio'] = [
      '#type' => 'radio',
      '#title' => $this->t('Radio'),
      '#description' => $this->t('Nihil tempor porta ullam nostra eveniet repudiandae possimus'),
    ];
    $form['radios'] = [
      '#type' => 'radios',
      '#title' => $this->t('Radios'),
      '#options' => [
        'option 1' => $this->t('option 1'),
        'option 2' => $this->t('option 2'),
        'option 3' => $this->t('option 3'),
      ],
      '#description' => $this->t('Nihil tempor porta ullam nostra eveniet repudiandae possimus'),
    ];
    $form['range'] = [
      '#type' => 'range',
      '#title' => $this->t('Range'),
      '#description' => $this->t('Nihil tempor porta ullam nostra eveniet repudiandae possimus'),
    ];
    $form['search'] = [
      '#type' => 'search',
      '#title' => $this->t('Search'),
      '#description' => $this->t('Nihil tempor porta ullam nostra eveniet repudiandae possimus'),
    ];
    $form['select'] = [
      '#type' => 'select',
      '#title' => $this->t('Select'),
      '#options' => [
        'option 1' => $this->t('option 1'),
        'option 2' => $this->t('option 2'),
        'option 3' => $this->t('option 3'),
      ],
      '#size' => 5,
      '#description' => $this->t('Nihil tempor porta ullam nostra eveniet repudiandae possimus'),
    ];
    $form['table'] = [
      '#type' => 'table',
      '#caption' => $this->t('Table'),
      '#description' => $this->t('Nihil tempor porta ullam nostra eveniet repudiandae possimus'),
      '#header' => [$this->t('Name'), $this->t('Phone')],
    ];
    for ($i = 1; $i <= 4; $i++) {
      $form['table'][$i]['name'] = [
        '#type' => 'textfield',
        '#title' => $this
          ->t('Name'),
        '#title_display' => 'invisible',
      ];
      $form['table'][$i]['phone'] = [
        '#type' => 'tel',
        '#title' => $this
          ->t('Phone'),
        '#title_display' => 'invisible',
      ];
    }
    $form['tableselect'] = [
      '#type' => 'tableselect',
      '#title' => $this->t('Tableselect'),
      '#description' => $this->t('Nihil tempor porta ullam nostra eveniet repudiandae possimus'),
      '#header' => [
        'first_name' => $this->t('First Name'),
        'last_name' => $this->t('Last Name'),
      ],
      '#options' => [
        1 => [
          'first_name' => 'Indy',
          'last_name' => 'Jones',
        ],
        2 => [
          'first_name' => 'Darth',
          'last_name' => 'Vader',
        ],
        3 => [
          'first_name' => 'Super',
          'last_name' => 'Man',
        ],
      ],
      '#empty' => $this->t('No users found'),
    ];
    $form['tel'] = [
      '#type' => 'Telephone',
      '#title' => $this->t('Tel'),
      '#description' => $this->t('Nihil tempor porta ullam nostra eveniet repudiandae possimus'),
    ];
    $form['text_format'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Text Format'),
      '#description' => $this->t('Nihil tempor porta ullam nostra eveniet repudiandae possimus'),
    ];
    $form['textarea'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Textarea'),
      '#description' => $this->t('Nihil tempor porta ullam nostra eveniet repudiandae possimus'),
    ];
    $form['text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Text'),
      '#maxlength' => 64,
      '#size' => 64,
      '#description' => $this->t('Nihil tempor porta ullam nostra eveniet repudiandae possimus'),
    ];
    $form['url'] = [
      '#type' => 'url',
      '#title' => $this->t('Url'),
      '#description' => $this->t('Nihil tempor porta ullam nostra eveniet repudiandae possimus'),
    ];
    $form['weight'] = [
      '#type' => 'weight',
      '#title' => $this->t('Weight'),
      '#delta' => 10,
      '#description' => $this->t('Nihil tempor porta ullam nostra eveniet repudiandae possimus'),
    ];
    $form['details-close'] = [
      '#type' => 'details',
      '#title' => $this->t('Details closed'),
      '#open' => FALSE,
      '#description' => $this->t('Nihil tempor porta ullam nostra eveniet repudiandae possimus'),
    ];
    $form['details-open'] = [
      '#type' => 'details',
      '#title' => $this->t('Details open'),
      '#open' => TRUE,
      '#description' => $this->t('Nihil tempor porta ullam nostra eveniet repudiandae possimus'),
    ];
    $form['fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Fieldset'),
      '#description' => $this->t('Nihil tempor porta ullam nostra eveniet repudiandae possimus'),
    ];
    $form['button'] = [
      '#type' => 'button',
      '#title' => $this->t('Button'),
      '#value' => $this->t('Button'),
      '#description' => $this->t('Nihil tempor porta ullam nostra eveniet repudiandae possimus'),
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#title' => $this->t('Submit'),
      '#value' => $this->t('Submit'),
    ];

    $form['#theme'] = 'styleguide_twig_form';
    $form['#cache']['max-age'] = 0;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {}

}
