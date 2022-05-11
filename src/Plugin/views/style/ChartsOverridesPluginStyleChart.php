<?php

namespace Drupal\charts_overrides\Plugin\views\style;

use Drupal\charts\Plugin\views\style\ChartsPluginStyleChart;
use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Html;
use Drupal\core\form\FormStateInterface;
use Drupal\Core\Render\Element;

/**
 * Style plugin to render view as a chart.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "chart_overrides",
 *   title = @Translation("Chart Overrides"),
 *   help = @Translation("Render a chart of your data."),
 *   theme = "views_view_charts_overrides",
 *   display_types = { "normal" }
 * )
 */
class ChartsOverridesPluginStyleChart extends ChartsPluginStyleChart {

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['flex_field']['default'] = '';

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $data_options = $this->displayHandler->getFieldLabels();
    $data_options[''] = $this->t('None');

    $form['flex_field'] = [
      '#title' => t('Flex Field'),
      '#type' => 'textfield',
      '#default_value' => $this->options['flex_field'],
      '#size' => 60,
      '#maxlength' => 255,
    ];

    $form_state->set('default_options', $this->options);

  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $chart = parent::render();

    $form_options = $this->options;

    // Allow argument tokens in the flex_field.
    $flex_field = $form_options['flex_field'] ?? '';
    $tokens = $this->getAvailableGlobalTokens();
    if (!empty($this->view->build_info['substitutions'])) {
      $tokens += $this->view->build_info['substitutions'];
    }
    $flex_field = $this->viewsTokenReplace($flex_field, $tokens);
    $options['flex_field']['text'] = $flex_field;

    $chart['#id'] = Html::getId($this->view->id() .  '_' .$this->view->current_display);

    $chart['#raw_options'] = $options;

    return $chart;
  }

  function termNames($tid) {
    $query = \Drupal::database()->select('taxonomy_term_field_data', 'td');
    $query->addField('td', 'name');
    $query->condition('td.tid', $tid);
    // For better performance, define the vocabulary where to search.
    // $query->condition('td.vid', $vid);
    $term = $query->execute();

    return $term->fetchField();
  }

}
