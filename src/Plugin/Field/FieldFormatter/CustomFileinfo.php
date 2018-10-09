<?php

/**
 * @file
 * Contains \Drupal\Custom_fileinfo\Plugin\field\formatter\CustomFileinfo.
 *
 * @author SvenV
 */

namespace Drupal\Custom_fileinfo\Plugin\Field\FieldFormatter;

use Drupal\file\Plugin\Field\FieldFormatter\GenericFileFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'Custom_fileinfo' formatter.
 *
 * @FieldFormatter(
 *   id = "Custom_fileinfo",
 *   module = "Custom_fileinfo",
 *   label = @Translation("Generic file with info"),
 *   field_types = {
 *     "file"
 *   }
 * )
 */
class CustomFileinfo extends GenericFileFormatter {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'Custom_fileinfo_filesize' => FALSE,
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $settings = $this->getSettings();

    $form['Custom_fileinfo_filesize'] = [
      '#title' => t('Show file info'),
      '#type' => 'checkbox',
      '#description' => t('Display the file size and extension.'),
      '#default_value' => $settings['Custom_fileinfo_filesize'],
      '#required' => FALSE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    $settings = $this->getSettings();

    if ($settings['Custom_fileinfo_filesize']) {
      $summary[] = t('Show file info');
    }
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = array();
    $settings = $this->getSettings();

    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $file) {
      $item = $file->_referringItem;
      $description = $item->description;
      $file_info = array();

      if ( $settings['Custom_fileinfo_filesize'] ) {
        $file_size = format_size($file->getSize());
        $file_name = pathinfo($file->getFileUri());
        $file_extension = $file_name['extension'];

        $file_info = '(' . $file_extension . ', ' . $file_size . ')';
      }

      $elements[$delta] = array(
        '#theme' => 'file_link',
        '#file' => $file,
        '#description' => 'Download',
        '#cache' => array(
          'tags' => $file->getCacheTags(),
        ),
        '#file_info' => $file_info,
      );

      // Pass field item attributes to the theme function.
      if (isset($item->_attributes)) {
        $elements[$delta] += array('#attributes' => array());
        $elements[$delta]['#attributes'] += $item->_attributes;
        // Unset field item attributes since they have been included in the
        // formatter output and should not be rendered in the field template.
        unset($item->_attributes);
      }
    }

    return $elements;
  }

}
