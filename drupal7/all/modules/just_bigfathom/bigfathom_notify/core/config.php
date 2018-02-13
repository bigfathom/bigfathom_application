<?php
/**
 * @file
 * --------------------------------------------------------------------------------------
 * Created by Frank Font (mrfont@room4me.com)
 *
 * Copyright (c) 2015-2018 Room4me.com Software LLC, a Maryland USA company (room4me.com)
 * 
 * All rights reserved.  Contact author for more information.
 * This is BETA software.  No warranty or fitness for use is implied at this time.
 * --------------------------------------------------------------------------------------
 *
 */

defined('BIGFATHOM_NOTIFY_VERSION_INFO')
    or define('BIGFATHOM_NOTIFY_VERSION_INFO', 'PROTOTYPE 20180115.2');

defined('BIGFATHOM_NOTIFY_MODULE_PATH')
    or define('BIGFATHOM_NOTIFY_MODULE_PATH', drupal_get_path('module', 'bigfathom_notify'));

#methods: SMTP, TALKECHO
defined('BIGFATHOM_NOTIFY_METHOD')
    or define('BIGFATHOM_NOTIFY_METHOD', 'TALKECHO');

defined('BIGFATHOM_NOTIFY_TALKECHO_URL')
    or define('BIGFATHOM_NOTIFY_TALKECHO_URL', 'http://talkecho.com/ssm.php');

defined('BIGFATHOM_NOTIFY_SUBJECT_PREFIX')
    or define('BIGFATHOM_NOTIFY_SUBJECT_PREFIX', 'Bigfathom ');

