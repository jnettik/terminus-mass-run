<?php

namespace Pantheon\TerminusMassRun\Commands;

use Pantheon\Terminus\Site\SiteAwareInterface;
use Pantheon\Terminus\Commands\Backup\CreateCommand;
use Pantheon\TerminusMassRun\Traits\TerminusMassRunTrait;

class TerminusMassRunBackupCreateCommand extends CreateCommand implements SiteAwareInterface {

  use TerminusMassRunTrait;

  /**
   * Creates a backup of all passed in sites.
   *
   * @authorize
   *
   * @command backup:mass:create
   *
   * @option env The Pantheon environments to target. Defaults to `live`.
   * @option string $element [all|code|files|database|db] Element to be backed up
   * @option integer $keep-for Retention period, in days, to retain backup
   */
  public function createBackup($options = ['env' => 'dev', 'element' => 'all', 'keep-for' => 365]) {
    foreach ($this->getAllSites() as $site) {
      $site_name = "{$site->getName()}.{$options['env']}";
      $this->log()->notice("Backing up {site_name}.", ['site_name' => $site_name]);
      $this->create($site_name, $options);
    }
  }

}
