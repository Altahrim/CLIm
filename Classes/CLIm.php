<?php
use \CLIm\Helpers\Colors;
use \CLIm\Helpers\Str;
use \CLIm\Helpers\Style;
use \CLIm\Widgets\Table;

/**
 * @see https://en.wikipedia.org/wiki/ANSI_escape_code
 */
class CLIm
{
    /**
     * Shortcut to an instance of Colors
     * @var \CLIm\Helpers\Colors
     */
    private $colors;

    /**
     * Shortcut to an instance of Style
     * @var \CLIm\Helpers\Style
     */
    private $style;

    /**
     * Script verbosity
     * Only texts with a verbosity superior or equal to this one will be printed
     * @var int
     */
    private $scriptVerbosity = self::VERB_NORMAL;

    /**
     * Current verbosity
     * Next texts will be printed only if this value is superior or equal to script verbosity
     * @var int
     */
    private $verbosity = self::VERB_NORMAL;

    /**
     * Verbosity levels
     * You can use more or different levels. The quieter level must have to lowest ID.
     */
    const VERB_QUIET = 1;
    const VERB_NORMAL = 2;
    const VERB_VERBOSE = 3;
    const VERB_DEBUG = 4;

    /**
     * Prompt description
     * @var array
     */
    private $prompt;

    /**
     * Singleton
     * @var \CLIm
     */
    private static $instance;

    /**
     * Escape character
     */
    const ESC = "\033";

    /**
     * Link to tput binary
     */
    const TPUT_BINARY = 'tput';


    private $documentRoot;

    /**
     * Return an instance of \CLIm
     * @return \CLIm
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self;
            self::$instance->init();
        }

        return self::$instance;
    }

    /**
     * Constructor
     */
    protected function __construct()
    {
        $this->colors = new Colors();
        $this->style = new Style();
        // FIXME Detect redirection and add support for it
        //$isRedirected = posix_isatty(STDOUT);
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        \CLIm\Helpers\Cursor::show();
        $this->reset()->lf();
    }

    /**
     * Magic method __toString
     * Helps to display console state
     * @return string
     */
    public function __toString()
    {
        ob_start();
        $this
            ->style(Style::BOLD)
            ->write('Colors: ', self::VERB_QUIET)
            ->style(Style::BOLD, true)
            ->writeLn($this->colors->getPalette(), self::VERB_QUIET)
            ->reset();
        return ob_get_clean();
    }

    /**
     * Configure terminal in a know state
     */
    private function init()
    {
        $this->reset();
        \CLIm\Helpers\Cursor::hide();
    }

    /**
     * Register \CLIm error handlers
     * @param string $documentRoot If specified, document root will be stripped from paths
     *   resulting in a more readable output
     */
    public function registerErrorHandlers($documentRoot = '')
    {
        $this->documentRoot = $documentRoot;
        set_exception_handler([$this, 'handleException']);
        set_error_handler([$this, 'handleError'], E_ALL);
        register_shutdown_function([$this, 'handleFatalError']);
    }

    /**
     * Display an error
     * @param int $code
     * @param string $message
     * @param string $file
     * @param int $line
     * @param string[] $context
     * @param bool $hideBacktrace
     * @return bool
     */
    public function handleError($code, $message, $file, $line, $context, $hideBacktrace = false)
    {
        if (!($code & error_reporting())) {
            return false;
        }

        $bt = [];
        if (!$hideBacktrace) {
            $bt = debug_backtrace();
            array_shift($bt);
        }

        $this->displayError($this->errorCodeToString($code), $message, 0, $file, $line, $bt, $context);
        return true;
    }

    /**
     * Display fatal error
     * It's possible to catch fatal errors by registering this function as a shutdown function
     * Then $this->handleError is used
     */
    public function handleFatalError()
    {
        $error = error_get_last();
        if (E_ERROR === $error['type']) {
            $this->handleError($error['type'], $error['message'], $error['file'], $error['line'], []);
        }
    }

    /**
     * Catch an exception and display it
     * @param Exception $e
     */
    public function handleException($e)
    {
        $this->displayError(get_class($e), $e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine(), $e->getTrace());
        if ($e->getPrevious()) {
            $this->writeLn('Previous exception was:');
            $this->handleException($e->getPrevious());
        }
        exit ($e->getCode() ? $e->getCode() : -1);
    }

    /**
     * Display an error or an exception
     * @param string $type
     * @param string $message
     * @param int $code
     * @param string $file
     * @param int $line
     * @param array $backtrace
     * @param array $context
     */
    protected function displayError($type, $message, $code, $file, $line, array $backtrace, array $context = [])
    {
        $this->lf(2);
        $this->color('#DD0000')->style(Style::BOLD)->write('### ' . $type);
        if ($code > 0) {
            $this->write(' ' . $code);
        }
        $this->write(' ###')
            ->reset()
            ->lf()
            ->writeLn($message)
            ->lf();

        $this
            ->style(Style::BOLD)
            ->write('File: ')
            ->style(Style::BOLD, true)
            ->write($file)
            ->style(Style::DIM)
            ->write(':')
            ->style(Style::DIM, true)
            ->writeLn($line)
            ->lf();

        // If script is in debug mode, display more context
        if ($this->scriptVerbosity >= self::VERB_DEBUG) {
            if (!empty($backtrace)) {
                $backtrace = array_map([$this, 'formatTrace'], $backtrace);
                $this
                    ->style(Style::BOLD)
                    ->writeLn('Backtrace:')
                    ->style(Style::BOLD, true);
                $bt = new Table();
                $bt
                    ->addData($backtrace)
                    ->draw();
                $this->lf();
            }

            if (!empty($context)) {
                foreach ($context as $var) {
                    $this->dump($var);
                }
                $this->lf();
            }
        }

        $this->lf(2);
    }

    protected function formatTrace($bt)
    {
        if (!isset($bt['file'])) {
            $source = '';
        } elseif (0 === strpos($bt['file'], $this->documentRoot)) {
            $source = 0 === strpos($bt['file'], $this->documentRoot)
                ? substr($bt['file'], strlen($this->documentRoot))
                : $bt['file'];
            $source .= ':' . $bt['line'];
        }

        $ret = [
            'source' => $source,
            'call' => $bt['function'],
            'args' => ''
        ];

        if (isset($bt['object'])) {
            $ret['call'] = $this->dump($bt['object']) . $bt['type'] . $ret['call'];
        } elseif (isset($bt['class'])) {
            $ret['call'] = $bt['class'] . $bt['type'] . $ret['call'];
        }


        $args = [];
        foreach ($bt['args'] as $arg) {
            ob_start();
            $this->dump($arg);
            $args[] = ob_get_clean();
        }
        $ret['args'] = implode(', ', $args);

        return $ret;
    }

    /**
     * Converts an error code in string
     * @param $code
     * @return string
     */
    protected function errorCodeToString($code)
    {
        switch ($code) {
            case E_ERROR:
                return 'Error';
            case E_WARNING:
                return 'Warning';
            case E_PARSE:
                return 'Parse Error';
            case E_NOTICE:
                return 'Notice';
            case E_CORE_ERROR:
                return 'Core Error';
            case E_CORE_WARNING:
                return 'Core Warning';
            case E_COMPILE_ERROR:
                return 'Compile Error';
            case E_COMPILE_WARNING:
                return 'Compile Warning';
            case E_USER_ERROR:
                return 'User Error';
            case E_USER_WARNING:
                return 'User Warning';
            case E_USER_NOTICE:
                return 'User Notice';
            case E_STRICT:
                return 'Strict Notice';
            case E_RECOVERABLE_ERROR:
                return 'Recoverable Error';
            default:
                return 'Unknown error (' . $code . ')';
        }
    }

    /**
     * Write some text.
     * Wrapper around vprintf.
     * @param $text
     * @param string[] ...$args
     * @return $this
     */
    public function write($text, ... $args)
    {
        if ($this->verbosity <= $this->scriptVerbosity) {
            vprintf($text, $args);
        }
        return $this;
    }


    /**
     * Write some text and end with a line feed
     * @param $text
     * @param string[] ...$args
     * @return $this
     */
    public function writeLn($text, ... $args)
    {
        if ($this->verbosity <= $this->scriptVerbosity) {
            $this->write($text, ...$args)->lf();
        }
        return $this;
    }

    /**
     * Prints $nb line feeds
     * @param int $nb
     * @return $this
     */
    public function lf($nb = 1)
    {
        if ($this->verbosity <= $this->scriptVerbosity) {
            for (; $nb > 0; --$nb) {
                echo PHP_EOL;
            }
        }
        return $this;
    }

    public function line()
    {
        return $this->writeLn(str_repeat('—', $this->getCols()));
    }

    /**
     * Ring a bell
     * @return $this
     */
    public function bell()
    {
        echo "\007";
        return $this;
    }

    /**
     * Clear screen
     * @return $this
     */
    public function clear()
    {
        return $this->esc('2J')->esc('H');
    }

    /**
     * Add or remove some rendering flag
     * @param int $flags
     * @param bool $add
     * @return $this
     * @see \CLIm\Helpers\Style
     */
    public function style($flags, $add = true)
    {
        return $this->esc($this->style->format($flags, (bool)$add));
    }

    /**
     * Set text color
     * @param int|string $color
     * @return $this
     */
    public function color($color)
    {
        return $this->esc($this->colors->format($color));
    }

    /**
     * Set background color
     * @param int|string $color
     * @return $this
     */
    public function bgColor($color)
    {
        return $this->esc($this->colors->format($color, true));
    }

    /**
     * Reset text color and style
     * @return $this
     */
    public function reset()
    {
        return $this->esc('0m');
    }

    /**
     * Print an ANSI sequence
     * @param $code
     * @return $this
     */
    public function esc($code, $return = false)
    {
        $str = self::ESC . '[' . $code;
        if ($return) {
            return $str;
        }
        if (is_resource(STDERR)) {
            fwrite(STDERR, $str);
        } else  {
            echo $str;
        }
        return $this;
    }

    /**
     * Return the number of available columns in terminal
     * @return int
     */
    public function getCols()
    {
        return (int) exec(self::TPUT_BINARY . ' cols');
    }

    /**
     * Return the number of available lines in current terminal
     * @return int
     */
    public function getRows()
    {
        return (int) exec(self::TPUT_BINARY . ' lines');
    }

    /**
     * Set script verbosity
     * @param int $newVerbosity
     * @param int $oldVerbosity
     * @return $this
     */
    public function setScriptVerbosity($newVerbosity, &$oldVerbosity = 0)
    {
        $oldVerbosity = $this->scriptVerbosity;
        $this->scriptVerbosity = $newVerbosity;
        return $this;
    }

    /**
     * Get script verbosity
     */
    public function getScriptVerbosity()
    {
        return $this->scriptVerbosity;
    }

    /**
     * Set current verbosity
     * @param int $newVerbosity
     * @param int $oldVerbosity
     * @return $this
     */
    public function verbosity($newVerbosity, &$oldVerbosity = 0)
    {
        $oldVerbosity = $this->verbosity;
        $this->verbosity = (int)$newVerbosity;
        return $this;
    }

    public function getVerbosity()
    {
        return $this->verbosity;
    }

    public function clearLine()
    {
        return $this->esc('2K')->esc('G');
    }

    public function table(array $data)
    {
        $table = new Table($this);
        $table->addData($data);
        $table->draw();
        return $this;
    }

    /**
     * Dump one or more variables
     * Note : reset style and color
     */
    public function dump(... $vars)
    {
        foreach ($vars as $var) {
            $type = gettype($var);
            switch ($type) {
                default:
                    $this->write('%s(%s)', $type, $var);
                    break;
                case 'string':
                    $len = Str::len($var, true);
                    if ($len > 15) {
                        $var = Str::sub($var, 0, 14) . '…';
                    }
                    $this->color(23)->write('String(%d) "%s"', $len, $var);
                    break;
                case 'boolean':
                    $this->color(45)->write($var ? 'true' : 'false');
                    break;
                case 'object':
                    $objId = spl_object_hash($var);
                    $this->color(73)->write('Object(%s)', $objId);
                    break;
                case 'array':
                    $this->color(166)->write('Array(%d)', count($var));
                    break;
                case 'NULL':
                    $this->color(95)->write('NULL');
            }
        }
        $this->reset();

        return $this;
    }

    /**
     * Shortcut to display a title
     * @param $text
     * @param array ...$args
     * @todo Handle window with
     */
    public function title($text, ...$args)
    {
        $text = vsprintf($text, $args);
        $len = Str::len($text, true);
        $this
            ->color(39);
        $this
            ->writeLn('     ╭' . str_repeat('─', $len + 2) . '╮')
            ->writeLn('     │ ' . $text . ' │')
            ->writeLn('     ╰' . str_repeat('─', $len + 2) . '╯');
        $this
            ->reset();
    }

    /**
     * Shortcut to display a debug message
     * Verbosity is changed to VERB_DEBUG for this message
     * @param $text
     * @param array ...$args
     */
    public function debug($text, ...$args)
    {
        $this
            ->verbosity(self::VERB_DEBUG, $old)
            ->color(250);
        $this->writeLn($text, ...$args);
        $this
            ->verbosity($old)
            ->reset();
    }

    /**
     * Shortcut to display an alert message
     * @param $text
     * @param array ...$args
     */
    public function alert($text, ...$args)
    {
        $this->color(214);
        $this->writeLn($text, ...$args);
        $this->reset();
    }

    /**
     * Shortcut to display an alert message
     * @param $text
     * @param array ...$args
     */
    public function success($text, ...$args)
    {
        $this->color(40);
        $this->writeLn($text, ...$args);
        $this->reset();
    }

    /**
     * Shortcut to display an error message
     * @param $text
     * @param array ...$args
     */
    public function error($text, ...$args)
    {
        $this->color(160);
        $this->writeLn($text, ...$args);
        $this->reset();
    }
}
