<?php

namespace pachno\core\modules\main\cli;

use pachno\core\framework;

/**
 * Implementation of CLI command for checking if Pachno is up-to-date.
 *
 * @author Branko Majic <branko@majic.rs>
 * @version 4.2
 * @license http://opensource.org/licenses/MPL-2.0 Mozilla Public License 2.0 (MPL 2.0)
 * @package pachno
 * @subpackage core
 */

/**
 * CLI command for checking if Pachno is up-to-date.
 *
 * @package pachno
 * @subpackage core
 */
class CheckForUpdates extends \pachno\core\framework\cli\Command
{
    const UPTODATE = 0;
    const OUTDATED = 1;
    const ERROR = 2;

    protected function _setup()
    {
        $this->_command_name = 'check_for_updates';
        $this->_description = "Checks if newer version is available for upgrade.";
    }

    public function do_execute()
    {
        $latest_version = framework\Context::getLatestAvailableVersionInformation();

        if ($latest_version === null)
        {
            $uptodate = null;
            $title = framework\Context::getI18n()->__('Failed to check for updates');
            $message = framework\Context::getI18n()->__('The response from Pachno website was invalid');
            $title_color = "red";
            $exit_code = self::UPTODATE;
        }
        else
        {
            $update_available = framework\Context::isUpdateAvailable($latest_version);

            if ($update_available)
            {
                $uptodate = false;
                $title = framework\Context::getI18n()->__('Pachno is out of date');
                $message = framework\Context::getI18n()->__('The latest version is %ver. Update now from pachno.com.', ['%ver' => $latest_version->nicever]);
                $title_color = "yellow";
                $exit_code = self::OUTDATED;
            }
            else
            {
                $uptodate = true;
                $title = framework\Context::getI18n()->__('Pachno is up to date');
                $message = framework\Context::getI18n()->__('The latest version is %ver', ['%ver' => $latest_version->nicever]);
                $title_color = "green";
                $exit_code = self::ERROR;
            }
        }

        $this->cliEcho($title, $title_color, "bold");
        $this->cliEcho("\n");
        $this->cliEcho($message);
        $this->cliEcho("\n");

        exit($exit_code);
    }
}