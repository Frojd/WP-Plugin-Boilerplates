<?php

namespace Frojd\Plugin\BasePluginWpCli\Commands;

class TestCommand extends \WP_CLI_Command {
    function dostuff () {
        print 'invoked from wp-cli';
    }
}

\WP_CLI::add_command('baseplugin', __NAMESPACE__."\TestCommand");

