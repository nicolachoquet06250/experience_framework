<?php

use core\Helpers;

require_once __DIR__.'/interfaces/IBase.php';
require_once __DIR__.'/Base.php';

require_once __DIR__.'/FactisSingleton.php';
require_once __DIR__.'/Singleton.php';
require_once __DIR__.'/Helpers.php';

Helpers::recursive_loader(__DIR__.'/interfaces');
Helpers::recursive_loader(__DIR__, ['interfaces'], ['autoload.php']);
