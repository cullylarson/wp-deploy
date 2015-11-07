<?php

namespace WpSync;

use WpSync\Exception\ConfigException;

class Config {
    public $source = [];
    public $dest = [];

    /**
     * @return Config
     * @throws ConfigException
     */
    public static function fromEnv() {
        self::ensureRequired();

        $config = new Config();

        $config->source = self::getSet("SOURCE");
        $config->dest = self::getSet("DEST");
    }

    private static function getSearchReplace($type) {
        $search_replace = [];

        foreach($_ENV as $envName => $envValue) {
            // empty
            if(empty($envValue)) continue;

            // not what we're looking for
            if(!preg_match("/^WP_{$type}_SEARCH(.+)$/", $envName, $matches)) continue;

            // can't find the associated replace
            if(empty($_ENV["WP_{$type}_REPLACE{$matches[1]}"])) {
                throw new ConfigException("Could not find REPLACE associated with {$envName}.");
            }

            // found one!
            $search_replace[$envValue] = $_ENV["WP_{$type}_REPLACE{$matches[1]}"];
        }

        return $search_replace;
    }

    private static function getSrdb($type) {
        $srdb = getenv("{$type}_SRDB");

        if(empty($srdb)) {
            throw new ConfigException("{$type}_SRDB is not defined, but search/replace parameters have been provided.");
        }

        return $srdb;
    }

    private static function getSet($type) {
        if(getenv("{$type}_IS_LOCAL")) {
            $sshPart = false;
        }
        else {
            $sshPart = [
                "host" => getenv("{$type}_SSH_HOST"),
                "username" => getenv("{$type}_SSH_USERNAME"),
                // no password, because you should be using public key auth
            ];
        }

        $searchReplacePart = self::getSearchReplace($type);

        if(empty($searchReplacePart)) {
            $srdbPart = false;
        }
        else {
            $srdbPart = self::getSrdb($type);
        }

        return [
            "uploads" => getenv("{$type}_WP_UPLOADS"), // folder
            "tmp" => getenv("{$type}_TMP"), // folder
            "ssh" => $sshPart,
            "db" => [
                "host" => getenv("{$type}_MYSQL_HOST"),
                "username" => getenv("{$type}_MYSQL_USERNAME"),
                "password" => getenv("{$type}_MYSQL_PASSWORD"),
                "name" => getenv("{$type}_MYSQL_NAME"),
                "port" => getenv("{$type}_MYSQL_PORT"),
            ],
            "search_replace" => $searchReplacePart,
            "srdb" => $srdbPart,
        ];
    }

    private static function ensureRequired() {
        self::ensureRequiredSet("SOURCE");
        self::ensureRequiredSet("DEST");
    }

    private static function ensureRequiredSet($type) {
        $thoseRequired = [
            "{$type}_WP_UPLOADS",
            "{$type}_TMP",
            "{$type}_MYSQL_HOST",
            "{$type}_MYSQL_USERNAME",
            "{$type}_MYSQL_PASSWORD",
            "{$type}_MYSQL_NAME",
            "{$type}_MYSQL_PORT",
        ];

        $sshParams = [
            "{$type}_SSH_HOST",
            "{$type}_SSH_USERNAME",
        ];

        if(empty(getenv("{$type}_IS_LOCAL"))) {
            $thoseRequired = array_merge($sshParams, $thoseRequired);
        }
        else {
            self::ensureNotSet($sshParams);
        }

        foreach($thoseRequired as $requiredItem) {
            if(empty(getenv($requiredItem))) throw new ConfigException("Required parameter not set: {$requiredItem}");
        }
    }

    private static function ensureNotSet($params) {
        foreach($params as $item) {
            if(!empty(getenv($item))) throw new ConfigException("Parameter should not be set, but is: {$item}");
        }
    }
}