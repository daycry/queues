<?php

declare(strict_types=1);

/**
 * This file is part of Daycry Queues.
 *
 * (c) Daycry <daycry9@proton.me>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Daycry\Queues\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Autoload;
use Exception;

class QueuePublishCommand extends BaseCommand
{
    protected $group       = 'Queues';
    protected $name        = 'queues:publish';
    protected $description = 'Publish config file.';
    protected $usage       = 'queues:publish';
    protected $sourcePath  = '';
    protected $assetsPath  = '';

    public function run(array $params): void
    {
        $this->determineSourcePath();

        // Config
        if (CLI::prompt('Publish Config file?', ['y', 'n']) === 'y') {
            $this->publishConfig();
        }
    }

    protected function publishConfig(): void
    {
        $path = "{$this->sourcePath}/Config/Queue.php";

        $content = file_get_contents($path);
        $content = str_replace('namespace Daycry\Queues\Config', 'namespace Config', $content);
        $content = str_replace('extends BaseConfig', 'extends \\Daycry\\Queues\\Config\\Queue', $content);

        $this->writeFile('Config/Queue.php', $content);
    }

    /**
     * Determines the current source path from which all other files are located.
     */
    protected function determineSourcePath(): void
    {
        $this->sourcePath = realpath(__DIR__ . '/../');

        if ($this->sourcePath === '/' || empty($this->sourcePath)) {
            CLI::error('Unable to determine the correct source directory. Bailing.');

            exit();
        }
    }

    /**
     * Write a file, catching any exceptions and showing a
     * nicely formatted error.
     */
    protected function writeFile(string $path, string $content): void
    {
        $config  = new Autoload();
        $appPath = $config->psr4[APP_NAMESPACE];

        $directory = dirname($appPath . $path);

        if (! is_dir($directory)) {
            mkdir($directory);
        }

        try {
            write_file($appPath . $path, $content);
        } catch (Exception $e) {
            $this->showError($e);

            exit();
        }

        $path = str_replace($appPath, '', $path);

        CLI::write(CLI::color('  created: ', 'green') . $path);
    }
}
