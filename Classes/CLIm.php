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

    public function registerErrorHandlers()
    {
        set_exception_handler([$this, 'handleException']);
    }

    public function handleError() {}
    public function handleException(\Exception $e)
    {
        $this->skipLn(2);
        $this->color('#DD0000')->style(Style::BOLD)->write('Exception');
        if ($e->getCode() > 0) {
            $this->write(' ' . $e->getCode());
        }
        $this->reset();
        $this->skipLn();
        $this->writeLn($e->getMessage());
        $this->skipLn();

        $this
            ->style(Style::BOLD)
            ->write('File: ')
            ->style(Style::BOLD, true)
            ->write($e->getFile())
            ->style(Style::DIM)
            ->write(':')
            ->style(Style::DIM, true)
            ->writeLn($e->getLine())
            ->skipLn();

        $trace = $e->getTrace();
        $this
            ->style(Style::BOLD)
            ->writeLn('Backtrace:')
            ->style(Style::BOLD, true);
        $bt = new Table();
        $bt
            ->addData($trace)
            ->draw();
        $this->skipLn(2);
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

    public function select($question, array $opts = [])
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
        readline_callback_handler_install('', function() {});
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
                $this->writeLn('Réponse invalide');
                $this->write($buf)->displayPrompt();
            }
        }
    }


    public function setPrompt($prompt, $color = null, $bgColor = null, $flags = null)
    {
        $this->prompt = [
            'text'    => (string) $prompt,
            'color'   => $color,
            'bgColor' => $bgColor,
            'flags'   => $flags
        ];
    }

    protected function displayPrompt()
    {
        echo
            $this->formatEscape($this->prompt['color'], $this->prompt['bgColor'], $this->prompt['flags']),
            $this->prompt['text'];
        $this->reset();
        echo ' ';
    }

    public function bell()
    {
        echo "\007";
        return $this;
    }

    public function clear()
    {
        echo self::ESC, '[2J', self::ESC, '[H';
        return $this;
    }

    public function moveTo($x = 1, $y = 1)
    {
        echo self::ESC . '[', $x, ';', $y, 'H';
    }

    public function style($flags = null, $remove = false)
    {
        $flags = (int) $flags;
        echo $this->formatEscape(null, null, $flags, $remove);
        return $this;
    }

    private function formatEscape($color = null, $bgColor = null, $flags = null, $invertFlags = false)
    {
        $cmd = [];
        if (null !== $flags && $flags = $this->style->format($flags, (bool) $invertFlags)) {
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

    public function verbosity()
    {
        // TODO
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

