<?php

/*
 *	Made by Partydragen
 *  https://github.com/NamelessMC/Nameless/
 *  NamelessMC version 2.0.0-pr13
 *
 *  License: MIT
 *
 *  Minecraft Integration
 */

class DiscordIntegration extends IntegrationBase {

    public function __construct() {
        $this->_name = 'Discord';

        parent::__construct();
    }

    public function onLink(User $user) {

    }

    public function onUnlink(User $user) {

    }
}