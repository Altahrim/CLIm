<?php
use \CLIm\Helpers\Colors;
use \CLIm\Helpers\Style;
use \CLIm\Widgets\Table;

/**
 * @see https://en.wikipedia.org/wiki/ANSI_escape_code
 */
class CLIm
{
    private $colors;
    private $style;

    private $verbosity = self::VERB_NORMAL;

    const VERB_QUIET = 4;
    const VERB_NORMAL = 3;
    const VERB_VERBOSE = 2;
    const VERB_DEBUG = 1;

    private $prompt = null;

    private static $instance;

    const ESC = "\033";
    const TPUT_BINARY = 'tput';

    /**
     * Return an instance of \CLIm
     * @return \CLIm
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self;
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
        $this->setPrompt('> ');
        //$isRedirected = posix_isatty(STDOUT);
        // FIXME
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
            ->writeLn($this->colors->getPalette(), self::VERB_QUIET);
        return ob_get_clean();
    }

    /**
     * Register \CLIm error handlers
     */
    public function registerErrorHandlers()
    {
        set_exception_handler([$this, 'handleException']);
        set_error_handler([$this, 'handleError'], E_ALL);
        register_shutdown_function([$this, 'handleFatalError']);
    }

    public function handleError($code, $message, $file, $line, $context, $hideBacktrace = false)
    {
        if (!($code & error_reporting())) {
            return;
        }

        $bt = [];
        if (!$hideBacktrace) {
            $bt = debug_backtrace();
            array_shift($bt);
        }

        $this->displayError($this->errorCodeToString($code), $message, 0, $file, $line, $bt, $context);

        // TODO Exit ?
    }

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
    public function handleException(\Exception $e)
    {
        $this->displayError('Exception', $e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine(), $e->getTrace());
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
        $this->skipLn(2);
        $this->color('#DD0000')->style(Style::BOLD)->write('### ' . $type);
        if ($code > 0) {
            $this->write(' ' . $code);
        }
        $this->write(' ###')
            ->reset()
            ->skipLn()
            ->writeLn($message)
            ->skipLn();

        $this
            ->style(Style::BOLD)
            ->write('File: ')
            ->style(Style::BOLD, true)
            ->write($file)
            ->style(Style::DIM)
            ->write(':')
            ->style(Style::DIM, true)
            ->writeLn($line)
            ->skipLn();

        // TODO Check verbosity before displaying
        if (!empty($backtrace)) {
            $this
                ->style(Style::BOLD)
                ->writeLn('Backtrace:')
                ->style(Style::BOLD, true);
            $bt = new Table();
            $bt
                ->addData($backtrace)
                ->draw();
            $this->skipLn();
        }

        if (!empty($context)) {
            foreach ($context as $var) {
                $this->dump($var);
            }
            $this->skipLn();
        }

        $this->skipLn(2);
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

    public function write($text, $verbosity = self::VERB_NORMAL)
    {
        echo $text;
        return $this;
    }

    public function writeLn($text, $verbosity = self::VERB_NORMAL)
    {
        return $this->write($text, $verbosity)->skipLn();
    }

    public function skipLn($nb = 1)
    {
        for (; $nb > 0; --$nb) {
            echo "\n";
        }
        return $this;
    }

    public function ask($question)
    {
        $this
            ->bell()
            ->writeLn($question, self::VERB_QUIET)
            ->displayPrompt();
        $answer = readline();
        return $answer;
    }

    /**
     * Display a question and some answers so the user can select one
     * @param string $question
     * @param array $opts
     * @param callable|null $invalidAnswer
     * @return array [Selected key, Selected value]
     */
    public function select($question, array $opts, callable $invalidAnswer = null)
    {
        $this
            ->bell()
            ->writeLn($question, self::VERB_QUIET);
        $i = 0;
        $len = strlen(count($opts));
        $answers = [];
        $buf = '';
        foreach ($opts as $k => $v) {
            $answers[++$i] = $k;
            $buf .= sprintf('%' . $len . "d. %s\n", $i, $v);
        }
        $this->write($buf)->displayPrompt();
        readline_callback_handler_install('', function () {});
        while (true) {
            $r = array(STDIN);
            $w = NULL;
            $e = NULL;
            $n = stream_select($r, $w, $e, 100);
            if ($n && in_array(STDIN, $r)) {
                $c = stream_get_contents(STDIN, 1);
                $this->writeLn($c);
                if (isset($answers[$c])) {
                    readline_callback_handler_remove();
                    return [$answers[$c], $opts[$answers[$c]]];
                }
                if (null !== $invalidAnswer) {
                    $invalidAnswer($c);
                }
                $this->write($buf)->displayPrompt();
            }
        }
    }

    /**
     * Personalize prompt
     * @param string $prompt
     * @param string|int|null $color
     * @param string|int|null $bgColor
     * @param int|null $flags
     */
    public function setPrompt($prompt, $color = null, $bgColor = null, $flags = null)
    {
        $this->prompt = [
            'text' => (string)$prompt,
            'color' => $color,
            'bgColor' => $bgColor,
            'flags' => $flags
        ];
    }

    /**
     * Display prompt
     */
    protected function displayPrompt()
    {
        echo
        $this->formatEscape($this->prompt['color'], $this->prompt['bgColor'], $this->prompt['flags']),
        $this->prompt['text'];
        $this->reset();
        echo ' ';
        return $this;
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
        echo self::ESC, '[2J', self::ESC, '[H';
        return $this;
    }

    /**
     * Move cursor to a new position
     * @param int $x
     * @param int $y
     * @return $this
     */
    public function moveTo($x = 1, $y = 1)
    {
        echo self::ESC . '[', $x, ';', $y, 'H';
        return $this;
    }

    /**
     * Add or remove some rendering flag
     * @param int|null $flags
     * @param bool $remove
     * @return $this
     * @see \CLIm\Helpers\Style
     */
    public function style($flags = null, $remove = false)
    {
        $flags = (int)$flags;
        echo $this->formatEscape(null, null, $flags, $remove);
        return $this;
    }

    private function formatEscape($color = null, $bgColor = null, $flags = null, $invertFlags = false)
    {
        $cmd = [];
        if (null !== $flags && $flags = $this->style->format($flags, (bool)$invertFlags)) {
            $cmd[] = $flags;
        }
        if (null !== $color && $color = $this->colors->format($color)) {
            $cmd[] = $color;
        }
        if (null !== $bgColor && $bgColor = $this->colors->format($bgColor, true)) {
            $cmd[] = $bgColor;
        }

        return self::ESC . '[' . implode(';', $cmd) . 'm';
    }

    public function color($color)
    {
        echo $this->formatEscape($color);
        return $this;
    }

    public function bgColor($color)
    {
        echo $this->formatEscape(null, $color);
        return $this;
    }

    public function reset()
    {
        echo self::ESC . '[0m';
        return $this;
    }

    public function getWidth()
    {
        return exec(self::TPUT_BINARY . ' cols');
    }

    /**
     * Set verbosity
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

    /*
    select

    ## Macros
    text
    title
    */

    public function clearLine()
    {
        echo self::ESC, '[2K', self::ESC, '[G';
    }

    public function table(array $data)
    {
        $table = new Table($this);
        $table->addData($data);
        $table->draw();
        return $this;
    }
}

