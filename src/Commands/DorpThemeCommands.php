<?php

namespace Drupal\dorp_theme_extras\Commands;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\MissingDependencyException;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ModuleInstallerInterface;
use Drupal\Core\Extension\ThemeInstallerInterface;
use Drupal\Core\Theme\Registry;
use Drush\Commands\DrushCommands;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Drush commands for Dorp Theme setup.
 */
class DorpThemeCommands extends DrushCommands {

  /**
   * The Theme Installer.
   *
   * @var \Drupal\Core\Extension\ThemeInstallerInterface
   */
  protected $themeInstaller;

  /**
   * The Theme Registry.
   *
   * @var \Drupal\Core\Theme\Registry
   */
  protected $themeRegistry;

  /**
   * The Module Installer.
   *
   * @var \Drupal\Core\Extension\ModuleInstallerInterface
   */
  protected $moduleInstaller;

  /**
   * The Module Handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The Symfony Filesystem component.
   *
   * @var \Symfony\Component\Filesystem\Filesystem
   */
  protected $filesystem;

  /**
   * MsThemeCommands constructor.
   *
   * @param \Drupal\Core\Extension\ThemeInstallerInterface $theme_installer
   *   The theme installer.
   * @param \Drupal\Core\Theme\Registry $theme_registry
   *   The theme handler.
   * @param \Drupal\Core\Extension\ModuleInstallerInterface $module_installer
   *   The module installer.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(
    ThemeInstallerInterface $theme_installer,
    Registry $theme_registry,
    ModuleInstallerInterface $module_installer,
    ModuleHandlerInterface $module_handler,
    ConfigFactoryInterface $config_factory) {
    parent::__construct();
    $this->themeInstaller = $theme_installer;
    $this->themeRegistry = $theme_registry;
    $this->moduleInstaller = $module_installer;
    $this->moduleHandler = $module_handler;
    $this->configFactory = $config_factory;
    // Set the Symfony filesystem component.
    $this->filesystem = new Filesystem();
  }

  /**
   * Drush command to create a new theme by copying the dorp theme.
   *
   * @param string $name
   *   The new theme name.
   *
   * @command theme:new-theme
   *
   * @usage drush theme:new-theme "New Theme Name"
   *   Create a new theme based on the themes/custom/dorp folder.
   *
   * @aliases nt
   *
   * @throws \Exception
   *   Throws an exception when the theme can't be created.
   */
  public function newTheme($name) {
    $machine_name = preg_replace('/[^a-z0-9_]+/', '', strtolower($name));

    // Find Drupal install and base theme path.
    $base_theme_path = DRUPAL_ROOT . '/themes/custom/dorp';

    if (!is_dir($base_theme_path)) {
      throw new \Exception(dt("Can not find themes/custom/dorp check directory path"));
    }

    // Check to see if the components module exists.
    if (!$this->moduleHandler->moduleExists('components')) {
      if (!is_dir(DRUPAL_ROOT . '/modules/contrib/components')) {
        throw new MissingDependencyException('Missing drupal/components dependency. Please install and enable the `components` module and try again');
      }
      else {
        // Install components module.
        try {
          $this->moduleInstaller->install(['components']);
          $this->logger()->success(dt('Installed components module'));
        }
        catch (\Exception $e) {
          $this->logger()->warning(dt('Unable to install the `components` module'));
        }
      }
    }

    $subtheme_path = DRUPAL_ROOT . '/themes/custom/' . $machine_name;

    if (!is_dir($subtheme_path)) {
      // Copy the theme to new directory.
      $this->filesystem->mirror($base_theme_path, $subtheme_path);

      // Remove the .git folder.
      $this->filesystem->remove($subtheme_path . '/.git');

      // Rename the files and fill in the theme name.
      $this->filesystem->rename("$subtheme_path/dorp.info.yml", "$subtheme_path/$machine_name.info.yml");
      $this->filesystem->rename("$subtheme_path/dorp.breakpoints.yml", "$subtheme_path/$machine_name.breakpoints.yml");
      $this->filesystem->rename("$subtheme_path/dorp.theme", "$subtheme_path/$machine_name.theme");
      $this->filesystem->rename("$subtheme_path/dorp.libraries.yml", "$subtheme_path/$machine_name.libraries.yml");

      // Replace THEME in info file.
      $this->fileStringReplace("$subtheme_path/$machine_name.info.yml", 'dorp', "$machine_name");
      // Replace THEME name in info file.
      $this->fileStringReplace("$subtheme_path/$machine_name.info.yml", 'Dorp', "$name");
      // Replace THEME in theme file.
      $this->fileStringReplace("$subtheme_path/$machine_name.theme", 'dorp', "$machine_name");
      // Replace THEME in page template.
      $this->fileStringReplace("$subtheme_path/templates/layout/page.html.twig", 'dorp', "$machine_name");
      // Replace THEME in breakpoints template.
      $this->fileStringReplace("$subtheme_path/$machine_name.breakpoints.yml", 'dorp', "$machine_name");

      // Log success.
      $this->logger()->success(dt("!name has been created at !path", [
        '!name' => $name,
        '!path' => $subtheme_path,
      ]));

      // Reset the theme registry.
      $this->themeRegistry->reset();

      // Install our new theme.
      try {
        $this->themeInstaller->install([$machine_name]);
        $this->logger()->success(dt('Successfully enabled theme: !name', ['!name' => $name]));
      }
      catch (\Exception $e) {
        throw new \Exception('Unable to install theme.');
      }

      // Ask to set theme as default.
      if ($this->io()->confirm(dt("Would you like to set !name as your active theme?", ['!name' => $name]))) {
        // Set the config.
        $config = $this->configFactory->getEditable('system.theme');
        $config->set('default', $machine_name);
        $config->save();
        $this->logger()->success(dt("!name was set as your active theme.", ['!name' => $name]));

        $this->io()->note(dt("You can run `drush sgt` to add the styleguide templates to your theme."));
      }
      $this->io()->note(dt("Don't forget to delete the dorp folder."));
    }
    else {
      $this->logger()->error(dt("!path already exists.", ['!path' => $subtheme_path]));
    }
  }

  /**
   * Add styleguide templates to the current theme.
   *
   * @command theme:styleguide
   * @aliases sgt
   */
  public function sgtCommand() {
    $theme_path = DRUPAL_ROOT . '/' . \Drupal::theme()->getActiveTheme()->getPath();
    $partials_dir = $theme_path . '/_source/partials';
    $sgt_templates = DRUPAL_ROOT . '/modules/custom/dorp_theme_extras/files';

    // Add styleguide directory with Grid and Typo examples.
    if (!is_dir($styleguide_dir = $partials_dir . '/styleguide')) {
      $this->filesystem->mirror($sgt_templates . '/styleguide', $styleguide_dir);
      $this->logger()->success(dt("Created !path", ['!path' => $styleguide_dir]));
    }

    // Add buttons component.
    if ($this->io()->confirm(dt("Would you like to add the buttons template to your theme?"))) {
      $buttons_path = $partials_dir . '/components/buttons';

      if (is_dir($buttons_path)) {
        $this->copy($sgt_templates . '/buttons/buttons.json', $buttons_path . '/buttons.json');
        $this->copy($sgt_templates . '/buttons/buttons.twig', $buttons_path . '/buttons.twig');
      }
      else {
        $this->logger()->error(dt("!path does not exists, please create this directory first", ['!path' => $buttons_path]));
      }
    }

    // Add _global.json file to the theme.
    if ($this->io()->confirm(dt("Would you like to add the styleguide global.json file to your theme?"))) {
      $this->copy($sgt_templates . '/_global.json', $partials_dir . '/abstracts/_global.json');
    }
  }

  /**
   * Internal helper: Replace strings in a file.
   *
   * @param string $file_path
   *   The file to search.
   * @param string $find
   *   The string to find.
   * @param string $replace
   *   The string to replace with.
   */
  private function fileStringReplace($file_path, $find, $replace) {
    $file_contents = file_get_contents($file_path);
    $file_contents = str_replace($find, $replace, $file_contents);
    file_put_contents($file_path, $file_contents);
  }

  /**
   * Internal helper: copy() if does not exist.
   *
   * @param string $from
   *   The file to copy.
   * @param string $to
   *   The file to copy to.
   */
  private function copy($from, $to) {
    if (!file_exists($to)) {
      $this->filesystem->copy($from, $to);
      $this->logger()->success(dt("Created !file", ['!file' => $to]));
    }
    else {
      $this->logger()->notice(dt("!file already exists.", ['!file' => $to]));
    }
  }

}
