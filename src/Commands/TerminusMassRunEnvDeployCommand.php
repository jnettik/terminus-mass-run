<?php

namespace Pantheon\TerminusMassRun\Commands;

use Pantheon\Terminus\Exceptions\TerminusException;
use Pantheon\Terminus\Site\SiteAwareInterface;
use Pantheon\Terminus\Commands\Env\DeployCommand;
use Pantheon\TerminusMassRun\Traits\TerminusMassRunTrait;

class TerminusMassRunDrushCommand extends DeployCommand implements SiteAwareInterface {

  use TerminusMassRunTrait;

  /**
   * Mass deployment of Pantheon sites.
   *
   * @authorize
   *
   * @command env:mass:deploy
   * @aliases mass-deploy
   *
   * @param array $options
   * @return string Command output
   *
   * @option env The Pantheon environments to target. Expected values are `test` or `live`.
   * @option string $sync-content Clone database/files from Live environment when deploying Test environment
   * @option string $cc Clear caches after deploy
   * @option string $updatedb Run update.php after deploy (Drupal only)
   * @option string $note Custom deploy log message
   *
   * @usage terminus site:list --format=list | terminus env:mass:deploy --env=<env> --note=<note>
   */
  public function massDeploy($options = ['env' => 'live', 'sync-content' => FALSE, 'note' => 'Deploy from Terminus', 'cc' => FALSE, 'updatedb' => FALSE,]) {
    $output = '';

    foreach ($sites as $site) {
      try {
        $output .= $this->deploy("{$site->getName()}.{$options['env']}", $options);
      }
      catch (TerminusException $e) {
        // If the command doesn't run, we want to skip it and continue to run
        // the rest of the scripts.
        $this->log()->error('Deployment for {site_name}.{env} failed.', [
          'site_name' => $site->getName(),
          'env' => $options['env'],
        ]);
        continue;
      }
    }

    return $output;
  }

}
