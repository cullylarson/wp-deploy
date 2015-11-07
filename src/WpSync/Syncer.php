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

    // TODO -- need to be able to do local_tmp setting
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
            return "{$machineConfig['ssh']['username']}@{$machineConfig['ssh']['username']}:{$machineConfig['uploads']}";
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
        $c = ssh2_connect($sshConfig['host'], 22, array('hostkey'=>'ssh-rsa'));
        ssh2_auth_agent($c, $sshConfig['username']);

        return $c;
    }

    public function doSync() {
        $ui = new UserInterface();

        $ui->say("o  Syncing upload folders from {$this->sourceName} to {$this->destName}.");
    }
}