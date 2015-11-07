<?php

namespace WpSync;

class Setup {
    public static function doSetup() {
        /*
         * Include the autoloader
         */
        $autoloadPaths = array(
            __DIR__ . "/../../../../autoload.php", // likely location, if installed as a vendor package
            __DIR__ . "/../../vendor/autoload.php", // dev location
        );

        foreach($autoloadPaths as $path) {
            if(file_exists($path)) {
                require($path);
                break;
            }
        }
        // if we didn't find the autoloader, your autoloader is in a stupid place, and you're on your own
    }
}