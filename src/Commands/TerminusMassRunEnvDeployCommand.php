<?php

namespace Pantheon\TerminusMassRun\Commands;

use Pantheon\Terminus\Exceptions\TerminusException;
use Pantheon\Terminus\Site\SiteAwareInterface;
use Pantheon\Terminus\Commands\Env\DeployCommand;
use Pantheon\TerminusMassRun\Traits\TerminusMassRunTrait;

class TerminusMassRunEnvDeployCommand extends DeployCommand implements SiteAwareInterface {

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

    $sites = array_filter($this->getAllSites(), function ($site) {
      // Check it's a Drupal site.
      return in_array($site->get('framework'), ['drupal', 'drupal8']);
    });

    foreach ($sites as $site) {
      // Make sure site environment is created. If not, this will create it for
      // us and we don't want that.
      if (!$site->getEnvironments()->get($options['env'])->isInitialized()) {
        $this->log()->warning('{env} environment for {site_name} does not exist.', [
          'site_name' => $site->getName(),
          'env' => $options['env'],
        ]);

        continue;
      }

      // If we're deploying to the test environment and `sync-content` is
      // enabled, but there is no live environment created, disable the content
      // sync option for that site.
      if (
        $options['env'] === 'test' &&
        $options['sync-content'] &&
        !$site->getEnvironments()->get('live')->isInitialized()
      ) {
        $options['sync-content'] = FALSE;
      }

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
