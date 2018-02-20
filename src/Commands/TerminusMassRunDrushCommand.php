<?php

namespace Pantheon\TerminusMassRun\Commands;

use Pantheon\Terminus\Exceptions\TerminusProcessException;
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
    $output = '';
    $sites = array_filter($this->getAllSites(), function ($site) {
      // Check it's a Drupal site.
      return in_array($site->get('framework'), ['drupal', 'drupal8']);
    });

    foreach ($sites as $site) {
      try {
        $output .= $this->drushCommand("{$site->getName()}.{$options['env']}", $cmd);
      }
      catch (TerminusProcessException $e) {
        // If the command doesn't run, we want to skip it and continue to run
        // the rest of the scripts.
        $this->log()->error('Drush command for {site_name} could not be run.', [
          'site_name' => $site->getName(),
        ]);
        continue;
      }
    }

    return $output;
  }

}
