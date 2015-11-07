<?php

namespace WpSync;

use Dotenv;

class Environment {
    /**
     * Loads .env files from a few places.
     *
     * @param array $extraPlacesToLook
     * @return bool
     */
    public static function loadDotEnv($extraPlacesToLook=[]) {
        $envFolders = array_merge([
            getcwd(),
            __DIR__ . "/..",
        ], $extraPlacesToLook);

        foreach($envFolders as $folder) {
            $envFile = $folder . "/.env";
            if(is_file($envFile)) {
                $dotenv = new Dotenv\Dotenv($folder);
                $dotenv->load();
                return true;
            }
        }
    }
}
