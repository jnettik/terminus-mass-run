<?php

namespace Pantheon\TerminusMassRun\Commands;

use Pantheon\Terminus\Site\SiteAwareInterface;
use Pantheon\Terminus\Commands\Remote\DrushCommand;
use Pantheon\TerminusMassRun\Traits\TerminusMassRunTrait;

class TerminusMassRunDrushCommand extends DrushCommand implements SiteAwareInterface {

  use TerminusMassRunTrait;

  /**
   * Get an ouput of site data.
   *
   * @authorize
   *
   * @command remote:mass:drush
   * @aliases mass-drush
   *
   * @param array $cmd The Drush command to run on sites.
   * @param array $options
   * @return string Command output
   *
   * @option env The Pantheon environments to target.
   *
   * @usage terminus site:list --format=list | terminus remote:mass:drush --env=<env> -- cr Clear cache on all sites.
   */
  public function runCommand(array $cmd, $options = ['env' => 'live']) {
    $sites = array_filter($this->getAllSites(), function ($site) {
      // Check it's a Drupal site.
      return in_array($site->get('framework'), ['drupal', 'drupal8']);
    });
    $output = '';

    foreach ($sites as $site) {
      $output .= $this->drushCommand("{$site->getName()}.{$options['env']}", $cmd);
    }

    return $output;
  }

}
