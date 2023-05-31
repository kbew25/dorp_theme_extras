<?php

namespace Drupal\dorp_theme_extras\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormState;
use Drupal\Core\Pager\PagerManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Theme\ThemeManagerInterface;
use ScssPhp\ScssPhp\Compiler;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class StyleguideController.
 *
 * The controller for rendering a Style Guide from the custom theme.
 *
 * @package Drupal\dorp_theme_extras\Controller
 */
class StyleguideController implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The pager manager.
   *
   * @var \Drupal\Core\Pager\PagerManagerInterface
   */
  protected $pagerManager;

  /**
   * The theme manager used in this test.
   *
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  protected $themeManager;

  /**
   * The theme path for the active theme.
   *
   * @var string
   */
  protected $themePath;

  /**
   * Constructs a StyleguideController.
   *
   * @param \Drupal\Core\Pager\PagerManagerInterface|null $pager_manager
   *   The pager manager.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   * @param \Drupal\Core\Theme\ThemeManagerInterface $theme_manager
   *   The theme manager.
   */
  public function __construct(PagerManagerInterface $pager_manager, FormBuilderInterface $form_builder, ThemeManagerInterface $theme_manager) {
    $this->pagerManager = $pager_manager;
    $this->formBuilder = $form_builder;
    $this->themeManager = $theme_manager;
    $this->themePath = $this->themeManager->getActiveTheme()->getPath();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('pager.manager'),
      $container->get('form_builder'),
      $container->get('theme.manager')
    );
  }

  /**
   * Build the sections from the theme twig templates.
   *
   * @return array
   *   An array of template sections.
   */
  public function twigSections() :array {
    $section = [];

    if (!is_dir($this->themePath . '/src') || !is_dir($this->themePath . '/templates')) {
      return $section;
    }

    // Only look in templates and src.
    $files = Finder::create()
      ->files()
      ->name('*.twig')
      ->contains('Styleguide')
      ->in($this->themePath . '/src')
      ->in($this->themePath . '/templates');

    if ($files->hasResults()) {
      foreach ($files as $key => $file) {
        // Get the comment values.
        $comment = $this->parseComment($file);
        // Set comment values.
        $section[$key] = $comment;

        // Get file path.
        $file_path = $file->getPathname();
        // Set file path.
        $section[$key]['path'] = $file_path;

        // Get related json.
        $content = $this->getJson($file);
        $content['directory'] = $this->themePath;
        // Create attributes in templates.
        $attributes = $content['attributes'] ?? [];
        $content['attributes'] = new Attribute($attributes);

        // Get template html.
        $twig = \Drupal::service('twig');
        $response = $twig->render('/' . $file_path, $content);
        // Set template html.
        $section[$key]['markup'][0] = $response;
        $section[$key]['code'] = $response;

        // Repeat markup if there's multiple.
        if (!empty($content['sgt_repeat'])) {
          $wrapper = $content['sgt_repeat_wrapper'] ?? TRUE;
          $wrapper_classes = $content['sgt_repeat_class'] ?? NULL;
          $markup = $wrapper ? '<div class="sgt-repeat ' . $wrapper_classes . '">' . $response . '</div>' : $response;
          $section[$key]['markup'][0] = $markup;

          foreach ($content['sgt_repeat'] as $repeat_key => $value) {
            if (is_array($value)) {
              $attributes = $value['attributes'] ?? [];
              $value['attributes'] = new Attribute($attributes);
              $repeat_content = array_replace($content, $value);
              $repeat_response = $twig->render('/' . $file_path, $repeat_content);
              $repeat_markup = $wrapper ? '<div class="sgt-repeat ' . $wrapper_classes . '">' . $repeat_response . '</div>' : $repeat_response;
              $section[$key]['markup'][1 + $repeat_key] = $repeat_markup;
            }
          }
        }
      }
    }

    return $section;
  }

  /**
   * Convert the twig comment to an array.
   *
   * @param \SplFileInfo $file
   *   The file.
   *
   * @return array
   *   An array of the file comment.
   */
  public function parseComment(\SplFileInfo $file) :array {
    $comment = [];
    // Get file contents.
    $contents = file_get_contents($file);
    // Clean up comment to get values.
    $normalized = preg_replace('-^\s*\*+-m', '', $contents);
    // Get just the 'Styleguide: {}' part of the comment.
    preg_match_all('/Styleguide:\s*{(.*)}/msU', $normalized, $com);
    // Get each line.
    $lines = explode("\n", trim($com[1][0]));

    foreach ($lines as $line) {
      // Get key and value of each line.
      $line = explode(":", $line);
      $key = trim(strtolower($line[0]));
      $value = rtrim(trim($line[1]), ',');
      // Set values.
      $comment[$key] = $value;
    }

    return $comment;
  }

  /**
   * Get global json file to show some dummy variable content.
   *
   * @return array
   *   json_decoded array of values for content.
   */
  public function getGlobalJson() {
    $content = [];
    // Find json file.
    $global_files = Finder::create()
      ->files()
      ->name('_global.json')
      ->in($this->themePath . '/src/abstracts/');

    if ($global_files->hasResults()) {
      foreach ($global_files as $key => $jfile) {
        $content = json_decode($jfile->getContents(), TRUE);
      }
    }

    return $content;
  }

  /**
   * Get related json file to show some dummy variable content.
   *
   * @param \SplFileInfo $file
   *   Instance of SplFileInfo.
   *
   * @return array
   *   json_decoded array of values for content.
   */
  public function getJson(\SplFileInfo $file) :array {
    // Get global json.
    $content = $this->getGlobalJson();
    // Get related json files eg. button.json and button~variation.json.
    $filename = explode('~', $file->getBasename('.twig'));
    $json_variation_file = !empty($filename[1]) ? str_replace('twig', 'json', $file->getFilename()) : $filename[0] . '.json';
    // Only look in templates and src.
    $json = Finder::create()
      ->files()
      ->name($json_variation_file)
      ->in($this->themePath . '/src')
      ->in($this->themePath . '/templates');

    if ($json->hasResults()) {
      foreach ($json as $key => $jfile) {
        $json_vars = $jfile->getContents();

        // Look for _getForm() and replace it with actual form.
        preg_match('#_getForm\((.*?)\)#', $json_vars, $matches);
        if (!empty($matches[1])) {
          $form = $this->formBuilder->getForm("$matches[1]");
          $form['#type'] = 'form';
          $json_vars = str_replace('"' . $matches[0] . '"', json_encode($form), $json_vars);
        }

        $json = json_decode($json_vars, TRUE);
        $content = array_merge($content, $json);
      }
    }

    return $content;
  }

  /**
   * Get color palette from _colors.scss.
   *
   * @return array
   *   A color palette section.
   */
  public function setColors() :array {
    $palette = [];
    $vars = [];

    if (!is_dir($this->themePath . '/src/abstracts/')) {
      return $palette;
    }

    $files = Finder::create()
      ->files()
      ->name('_colors.scss')
      ->in($this->themePath . '/src/abstracts/');

    if ($files->hasResults()) {
      foreach ($files as $key => $file) {
        // Get Colors.scss contents.
        $contents = $file->getContents();
        $css_colors = [];

        if (class_exists('ScssPhp\ScssPhp\Compiler')) {
          try {
            // Create an instance of the Sass Compiler class.
            $scss = new Compiler();
            $scss->setImportPaths($this->themePath . '/src/abstracts/');

            // Get all color variables.
            preg_match_all('/(^\$.*):\s(.*$)/msU', $contents, $colors);
            // Create scss using colors.
            $string = '@import "colors.scss";';
            foreach ($colors[1] as $key => $color) {
              $color_arr = explode(';', ltrim($colors[2][$key]));
              $css_color = $color_arr[0];

              $all[trim($colors[1][$key], '$')] = $colors[2][$key];
              $string .= '.' . trim($colors[1][$key], '$') . '{ background-color:' . $css_color . ';}';
            }
            // Compile css.
            $css = $scss->compile($string);

            // Find the css values for the vars.
            preg_match_all('/(^\..*)\s\{(.*)\}$/msU', $css, $css_source);
            foreach ($css_source[1] as $key => $value) {
              $style = trim($css_source[2][$key]);

              // Get color value.
              preg_match('/(#|rgba|rgb|hsl)(.*)$/', $style, $css_color);

              $css_colors[str_replace('.', '$', $value)] = [
                '#style' => $style,
                '#color' => trim($css_color[0], ';'),
              ];
            }
          }
          catch (\Exception $e) {
          }
        }

        // Create colors into palette sections.
        preg_match_all('/(^\$.*|Styleguide):\s(.*$)/msU', $contents, $output);

        $i = 0;
        foreach ($output[1] as $key => $var) {
          if ($var == 'Styleguide') {
            $i++;
            $vars[$i]['heading'] = trim($output[2][$key], '*/');
          }
          else {
            if ($css_colors) {
              $vars[$i]['colors'][$output[1][$key]] = $css_colors[$output[1][$key]];
            }
            else {
              $color_arr = explode(';', ltrim($output[2][$key]));
              $color = $color_arr[0];
              $vars[$i]['colors'][$output[1][$key]] = [
                '#style' => 'background-color: ' . $color . ';',
                '#color' => $color,
              ];
            }
          }
        }

        $palette = [
          'title' => $this->t('Colors'),
          'markup' => [
            '#theme' => 'styleguide_twig_colors',
            '#colors' => $vars,
          ],
          'path' => $file->getPathname(),
          'section' => 'base',
        ];
      }
    }

    return $palette;
  }

  /**
   * Get icons from /icons directory.
   *
   * @return array
   *   Icons section.
   */
  public function getIcons() :array {
    $icons_section = [];

    $icon_dir = $this->themePath . '/src/assets/icons/';

    if (!is_dir($icon_dir)) {
      return $icons_section;
    }

    $icon_files = [];
    $files = Finder::create()
      ->files()
      ->name('*.svg')
      ->in($icon_dir);

    if ($files->hasResults()) {
      foreach ($files as $key => $file) {
        $filename = $file->getFilename();
        $icon_files[$filename] = $key;
      }

      ksort($icon_files);

      $icons_section = [
        'title' => $this->t('Icons'),
        'markup' => [
          '#theme' => 'styleguide_twig_icons',
          '#icons' => $icon_files,
        ],
        'path' => $icon_dir,
        'section' => 'icons',
      ];
    }

    return $icons_section;
  }

  /**
   * Build sections from Drupal elements.
   *
   * @return array
   *   An array of drupal sections.
   */
  public function drupalSections() :array {
    $sections['drupal']['heading'] = 'Drupal';
    // Messages.
    $sections['drupal']['component']['status_messages'] = [
      'title' => $this->t('Status Messages'),
      'markup' => [
        '#theme' => 'status_messages',
        '#message_list' => [
          'status' => [
            $this->t('Status message'),
            $this->t('Status message 2'),
          ],
          'warning' => [$this->t('Warning message')],
          'error' => [$this->t('Error message')],
        ],
        '#status_headings' => [
          'status' => 'Status',
          'warning' => 'Warning',
          'error' => 'Error',
        ],
      ],
    ];

    // Form elements.
    $form_state = new FormState();
    $form_state->setRebuild();
    $form = $this->formBuilder->getForm('Drupal\dorp_theme_extras\Form\ExampleForm');

    $sections['drupal']['component']['form'] = [
      'title' => $this->t('Form Elements'),
      'markup' => $form,
    ];

    // Create dummy pager.
    $this->pagerManager->createPager(100, 10);
    $form['pager'] = [
      '#type' => 'pager',
    ];
    $sections['drupal']['component']['pager'] = [
      'title' => $this->t('Pager'),
      'markup' => $form['pager'],
    ];

    return $sections;
  }

  /**
   * Sets the menu.
   *
   * @param array $templates
   *   Each section.
   *
   * @return array
   *   The menu array.
   */
  public function setMenu(array $templates = []) :array {
    $menu['drupal'] = 'Drupal';

    if (!empty($templates)) {
      foreach ($templates as $template) {
        $encoded_section = urlencode(strtolower($template['section']));
        $menu[$encoded_section] = $template['section'];
      }
    }

    ksort($menu);

    return $menu;
  }

  /**
   * Render the overview page.
   *
   * @return array
   *   The render array.
   */
  public function render() :array {
    $templates = $this->twigSections();
    $sections = $this->drupalSections();

    // Get color palette.
    $colors = $this->setColors();
    if (!empty($colors)) {
      $sections['Base']['component'][] = $colors;
    }

    // Get icons.
    $icons = $this->getIcons();
    if (!empty($icons)) {
      $sections['Base']['component'][] = $icons;
    }

    // Set each template as a section.
    if ($templates) {
      foreach ($templates as $template) {
        $sections[$template['section']]['heading'] = $template['section'];
        $sections[$template['section']]['component'][] = $template;
      }
    }

    ksort($sections);

    // Set Menu.
    $menu = $this->setMenu($templates);

    return [
      'content' => [
        '#theme' => 'styleguide_twig_sections',
        '#sections' => $sections,
        '#menu' => $menu,
      ],
      '#attached' => [
        'library' => [
          'dorp_theme_extras/styleguide',
        ],
      ],
    ];
  }

  /**
   * Render each section as a page.
   *
   * @param string $section
   *   Section title as argument.
   * @param int $key
   *   Component key as argument.
   *
   * @return array
   *   The render array.
   */
  public function arguments($section, $key = NULL) :array {
    $templates = $this->twigSections();
    $sections = ($section == 'drupal') ? $this->drupalSections() : [];

    // Check if there are any sections.
    if (empty($sections) && empty($templates)) {
      throw new NotFoundHttpException();
    }

    // Get color palette.
    if ($section == 'base') {
      $colors = $this->setColors();
      if (!empty($colors)) {
        $sections['base']['component'][0] = $colors;
      }
      $icons = $this->getIcons();
      if (!empty($icons)) {
        $sections['base']['component'][1] = $icons;
      }
    }

    if ($section != 'drupal' && $templates) {
      $component = FALSE;

      foreach ($templates as $template) {
        $encoded_section = urlencode(strtolower($template['section']));
        if ($encoded_section == $section) {
          $component = TRUE;
          $sections[$section]['heading'] = $template['section'];
          $sections[$section]['component'][] = $template;
        }
      }

      // If this section does not exist throw 404.
      if (!$component) {
        throw new NotFoundHttpException();
      }
    }

    ksort($sections);

    // Set Menu.
    $menu = $this->setMenu($templates);

    // If /styleguide/{section}/{key} return single component.
    if (isset($key)) {
      $sections[$section]['component'] = [$sections[$section]['component'][$key]];
    }

    return [
      'content' => [
        '#theme' => 'styleguide_twig_sections',
        '#sections' => $sections,
        '#menu' => $menu,
        '#view' => isset($key) ? 'single' : 'all',
      ],
      '#attached' => [
        'library' => [
          'dorp_theme_extras/styleguide',
        ],
      ],
    ];
  }

  /**
   * Get section title.
   *
   * @param string $section
   *   Section title as argument.
   */
  public function getTitle($section) {
    return ucfirst($section);
  }

}
