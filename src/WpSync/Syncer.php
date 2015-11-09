<?php

namespace WpSync;

use Wordpress;

class Syncer {
    private $sourceName;
    private $destName;
    private $config;

    /**
     * @var Wordpress\Deploy\FolderSync
     */
    private $folderSync;

    /**
     * @var Wordpress\Deploy\DatabaseSync
     */
    private $databaseSync;

    public function __construct($sourceName, $destName, Config $config) {
        $this->sourceName = $sourceName;
        $this->destName = $destName;
        $this->config = $config;

        $this->folderSync = new Wordpress\Deploy\FolderSync(
            $this->buildFolderParam($config->source),
            $this->buildFolderParam($config->dest),
            ['delete' => true, 'exclude' => ['.gitkeep']]
        );

        $this->databaseSync = new Wordpress\Deploy\DatabaseSync($this->buildDatabaseSyncParam($config));
    }

    private function buildFolderParam(array $machineConfig) {
        if(!empty($machineConfig['ssh'])) {
            return "{$machineConfig['ssh']['username']}@{$machineConfig['ssh']['host']}:{$machineConfig['uploads']}";
        }
        else {
            return $machineConfig['uploads'];
        }
    }

    private function buildDatabaseSyncParam(Config $config) {
        return [
            "source" => $this->buildMachineDbSync($config->source),
            "dest" => $this->buildMachineDbSync($config->dest),
            "search_replace" => empty($config->dest["search_replace"]) ? [] : $config->dest["search_replace"],
            "local_tmp" => $config->localTmp,
        ];
    }

    private function buildMachineDbSync(array $machineConfig) {
        $sshParam = empty($machineConfig['ssh']) ? null : $this->buildSshConnection($machineConfig['ssh']);

        return [
            "tmp" => $machineConfig['tmp'],
            "srdb" => $machineConfig['srdb'],
            "local" => empty($machineConfig['ssh']),
            "ssh" => $sshParam,
            "keep_dump" => false,
            "db" => $machineConfig["db"],
        ];
    }

    private function buildSshConnection($sshConfig) {
        $c = @ssh2_connect($sshConfig['host'], 22, array('hostkey'=>'ssh-rsa'));
        if(!is_resource($c)) throw new \Exception("Could not connect to host: {$sshConfig['host']}");

        $sshAuthSuccess = @ssh2_auth_agent($c, $sshConfig['username']);
        if(!$sshAuthSuccess) throw new \Exception("Could not authenticate on host: {$sshConfig['host']}");

        return $c;
    }

    public function doSync() {
        $ui = new UserInterface();

        $statusCallback = function(Wordpress\Deploy\FolderSync\Status $status) {
            echo $status->Timestamp . " -- ";

            if( $status->isError() ) echo "ERROR: ";
            if( $status->isWarning() ) echo "WARNING: ";
            if( $status->isRawOutput() ) echo "\n================\n";

            echo $status->Message . "\n";

            if( $status->isRawOutput() ) echo "================\n";
        };

        $ui->say("o  Syncing upload folders from {$this->sourceName} to {$this->destName}.");
        $folderSyncSuccess = $this->folderSync->sync($statusCallback);

        $ui->say("o  Syncing database from {$this->sourceName} to {$this->destName}.");
        $databaseSyncSuccess = $this->databaseSync->sync($statusCallback);

        if(!$folderSyncSuccess || !$databaseSyncSuccess) {
            $ui->say("o  Completed with errors.");
        }
        else {
            $ui->say("o  Completed successfully!");
        }
    }
}