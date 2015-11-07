<?php

namespace WpSync;

class UserInterface {
    public function say($message) {
        echo "{$message}\n";
    }

    public function ask($question, $answer) {
        echo "{$question} ";
        $h = fopen("php://stdin","r");
        $line = trim(fgets($h));
        fclose($h);

        return $line == $answer;
    }

    public function quit($message) {
        die("{$message}\n");
    }
}