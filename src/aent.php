#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

use \TheAentMachine\Aent\BuilderAent;
use \TheAentMachine\AentDockerfile\Event\NewImageEvent;

$application = new BuilderAent(new NewImageEvent());
$application->run();
