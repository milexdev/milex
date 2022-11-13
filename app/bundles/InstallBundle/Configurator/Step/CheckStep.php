<?php

namespace Milex\InstallBundle\Configurator\Step;

use Milex\CoreBundle\Configurator\Configurator;
use Milex\CoreBundle\Configurator\Step\StepInterface;
use Milex\CoreBundle\Helper\FileHelper;
use Milex\CoreBundle\Security\Cryptography\Cipher\Symmetric\OpenSSLCipher;
use Milex\InstallBundle\Configurator\Form\CheckStepType;
use Symfony\Component\HttpFoundation\RequestStack;

class CheckStep implements StepInterface
{
    /**
     * Flag if the configuration file is writable.
     *
     * @var bool
     */
    private $configIsWritable;

    /**
     * Path to the kernel root.
     *
     * @var string
     */
    private $kernelRoot;

    /**
     * @var OpenSSLCipher
     */
    private $openSSLCipher;

    /**
     * Absolute path to cache directory.
     * Required in step.
     *
     * @var string
     */
    public $cache_path = '%kernel.root_dir%/../var/cache';

    /**
     * Absolute path to log directory.
     * Required in step.
     *
     * @var string
     */
    public $log_path = '%kernel.root_dir%/../var/logs';

    /**
     * Set the domain URL for use in getting the absolute URL for cli/cronjob generated URLs.
     *
     * @var string
     */
    public $site_url;

    /**
     * Recommended minimum memory limit for Milex.
     *
     * @var string
     */
    public static $memory_limit = '512M';

    /**
     * @param Configurator $configurator Configurator service
     * @param string       $kernelRoot   Kernel root path
     * @param RequestStack $requestStack Request stack
     */
    public function __construct(
        Configurator $configurator,
        $kernelRoot,
        RequestStack $requestStack,
        OpenSSLCipher $openSSLCipher
    ) {
        $request = $requestStack->getCurrentRequest();

        $this->configIsWritable = $configurator->isFileWritable();
        $this->kernelRoot       = $kernelRoot;
        if (!empty($request)) {
            $this->site_url     = $request->getSchemeAndHttpHost().$request->getBasePath();
        }
        $this->openSSLCipher    = $openSSLCipher;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormType()
    {
        return CheckStepType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function checkRequirements()
    {
        $messages = [];

        if (version_compare(PHP_VERSION, '7.2.21', '<')) {
            $messages[] = 'milex.install.php.version.not.supported';
        }

        // Allow for the vendor folder to live
        // above the application folder.
        if (!is_dir(dirname($this->kernelRoot).'/vendor/composer') && !is_dir(dirname($this->kernelRoot).'/../vendor/composer')) {
            $messages[] = 'milex.install.composer.dependencies';
        }

        if (!$this->configIsWritable) {
            $messages[] = 'milex.install.config.unwritable';
        }

        if (!is_writable(str_replace('%kernel.root_dir%', $this->kernelRoot, $this->cache_path))) {
            $messages[] = 'milex.install.cache.unwritable';
        }

        if (!is_writable(str_replace('%kernel.root_dir%', $this->kernelRoot, $this->log_path))) {
            $messages[] = 'milex.install.logs.unwritable';
        }

        $timezones = [];

        foreach (\DateTimeZone::listAbbreviations() as $abbreviations) {
            foreach ($abbreviations as $abbreviation) {
                $timezones[$abbreviation['timezone_id']] = true;
            }
        }

        if (!isset($timezones[date_default_timezone_get()])) {
            $messages[] = 'milex.install.timezone.not.supported';
        }

        if (!function_exists('json_encode')) {
            $messages[] = 'milex.install.function.jsonencode';
        }

        if (!function_exists('session_start')) {
            $messages[] = 'milex.install.function.sessionstart';
        }

        if (!function_exists('ctype_alpha')) {
            $messages[] = 'milex.install.function.ctypealpha';
        }

        if (!function_exists('token_get_all')) {
            $messages[] = 'milex.install.function.tokengetall';
        }

        if (!function_exists('simplexml_import_dom')) {
            $messages[] = 'milex.install.function.simplexml';
        }

        if (false === $this->openSSLCipher->isSupported()) {
            $messages[] = 'milex.install.extension.openssl';
        }

        if (!function_exists('curl_init')) {
            $messages[] = 'milex.install.extension.curl';
        }

        if (!function_exists('finfo_open')) {
            $messages[] = 'milex.install.extension.fileinfo';
        }

        if (!function_exists('mb_strtolower')) {
            $messages[] = 'milex.install.extension.mbstring';
        }

        if (extension_loaded('xdebug')) {
            if (ini_get('xdebug.show_exception_trace')) {
                $messages[] = 'milex.install.xdebug.exception.trace';
            }

            if (ini_get('xdebug.scream')) {
                $messages[] = 'milex.install.xdebug.scream';
            }
        }

        return $messages;
    }

    /**
     * {@inheritdoc}
     */
    public function checkOptionalSettings()
    {
        $messages = [];

        if (extension_loaded('xdebug')) {
            $cfgValue = ini_get('xdebug.max_nesting_level');

            if ($cfgValue <= 100) {
                $messages[] = 'milex.install.xdebug.nesting';
            }
        }

        if (!extension_loaded('zip')) {
            $messages[] = 'milex.install.extension.zip';
        }

        // We set a default timezone in the app bootstrap, but advise the user if their PHP config is missing it
        if (!ini_get('date.timezone')) {
            $messages[] = 'milex.install.date.timezone.not.set';
        }

        if (!class_exists('\\DomDocument')) {
            $messages[] = 'milex.install.module.phpxml';
        }

        if (!function_exists('iconv')) {
            $messages[] = 'milex.install.function.iconv';
        }

        if (!function_exists('utf8_decode')) {
            $messages[] = 'milex.install.function.xml';
        }

        if (!function_exists('imap_open')) {
            $messages[] = 'milex.install.extension.imap';
        }

        if ('https' !== substr($this->site_url, 0, 5)) {
            $messages[] = 'milex.install.ssl.certificate';
        }

        if (!defined('PHP_WINDOWS_VERSION_BUILD')) {
            if (!function_exists('posix_isatty')) {
                $messages[] = 'milex.install.function.posix.enable';
            }
        }

        $memoryLimit    = FileHelper::convertPHPSizeToBytes(ini_get('memory_limit'));
        $suggestedLimit = FileHelper::convertPHPSizeToBytes(self::$memory_limit);
        if ($memoryLimit > -1 && $memoryLimit < $suggestedLimit) {
            $messages[] = 'milex.install.memory.limit';
        }

        if (!class_exists('\\Locale')) {
            $messages[] = 'milex.install.module.intl';
        }

        if (class_exists('\\Collator')) {
            try {
                if (is_null(new \Collator('fr_FR'))) {
                    $messages[] = 'milex.install.intl.config';
                }
            } catch (\Exception $exception) {
                $messages[] = 'milex.install.intl.config';
            }
        }

        if (-1 !== (int) ini_get('zend.assertions')) {
            $messages[] = 'milex.install.zend_assertions';
        }

        return $messages;
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate()
    {
        return 'MilexInstallBundle:Install:check.html.php';
    }

    /**
     * {@inheritdoc}
     */
    public function update(StepInterface $data)
    {
        $parameters = [];

        foreach ($data as $key => $value) {
            // Exclude keys from the config
            if (!in_array($key, ['configIsWritable', 'kernelRoot'])) {
                $parameters[$key] = $value;
            }
        }

        return $parameters;
    }
}
