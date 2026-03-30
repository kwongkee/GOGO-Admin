<?php
/**
 * [WeEngine System] Copyright (c) 2014 lotodo.com
 * WeEngine is NOT a free software, it under the license terms, visited http://www.lotodo.com/ for more details.
 */
define('FRAME', 'mc');
$frames = buildframes(array(FRAME));
$frames = $frames[FRAME];
define('ACTIVE_FRAME_URL', url("material/{$action}"));


