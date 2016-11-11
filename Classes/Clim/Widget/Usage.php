<?php
namespace Clim\Widget;

use Clim\Helper\Style;
use Clim\Widget;

/**
 * Display command usage
 */
class Usage extends Widget
{
    /**
     * Command name
     * @var string
     */
    private $name;

    /**
     * Command version
     * @var string
     */
    private $version;

    /**
     * Command usages
     * @var array
     */
    private $usages;

    /**
     * Short description
     * @var string
     */
    private $shortDesc;

    /**
     * Long description
     * @var string
     */
    private $longDesc;

    /**
     * Additional sections
     * @var string[]
     */
    private $sections;

    /**
     * Available sub commands
     * @var string[]
     */
    private $commands;

    /**
     * Available options
     * @var array
     */
    private $options;

    /**
     * Minimal lenghts for display
     * @var int[]
     */
    private $minLengths;

    const STR_OPTIONS = 'Options:';
    const STR_COMMANDS = 'Commands:';
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->name = isset($_SERVER['argv'][0]) ? $_SERVER['argv'][0] : 'cmd';
        $this->usages = [];
        $this->sections = [];
        $this->commands = [];
        $this->options = [];
        $this->minLengths = [
            'short' => 0,
            'long' => 0,
            'cmd' => 0
        ];
    }

    const STR_USAGE = 'Usage:';

    /**
     * Add an usage
     * The command name is automatically included
     * @param string $str
     */
    public function addUsage($str)
    {
        $this->usages[] = $str;
    }

    /**
     * Set command name (instead of argv[0])
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Set version
     * @param string $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * Set short description
     * @param string $str
     */
    public function setShortDesc($str)
    {
        $this->shortDesc = $str;
    }

    /**
     * Set long description
     * @param $str
     */
    public function setLongDesc($str)
    {
        $this->longDesc = $str;
    }

    /**
     * Add a new section
     * @param string $name
     * @param string $text
     */
    public function addSection($name, $text)
    {
        $this->sections[$name] = $text;
    }

    /**
     * Add an option
     * Available fields are:
     *  - short: short version
     *  - long: long version
     *  - desc: description
     * @param array $option
     */
    public function addOption(array $option)
    {
        if (isset($option['short'])) {
            $this->minLengths['short'] = max($this->minLengths['short'], mb_strlen($option['short']));
        }
        if (isset($option['long'])) {
            $this->minLengths['long'] = max($this->minLengths['long'], mb_strlen($option['long']));
        }
        $this->options[] = $option;
    }

    /**
     * Add a sub-command
     * @param string $cmd
     * @param string $desc
     */
    public function addCommand($cmd, $desc)
    {
        $this->minLengths['cmd'] = max($this->minLengths['cmd'], mb_strlen($cmd));
        $this->commands[$cmd] = $desc;
    }

    /**
     * Draw usage
     */
    public function draw()
    {
        $this->out->verbosity(\Clim::VERB_QUIET, $oldVerb);

        if ($this->version) {
            $this->out->writeLn('%s %s', $this->name, $this->version)->lf();
        }

        if ($this->shortDesc) {
            $this->out
                ->color(154)
                ->writeLn($this->shortDesc)
                ->reset()
                ->lf();
        }

        if (!empty($this->usages)) {
            $this->drawSectionTitle(self::STR_USAGE);
            foreach ($this->usages as $usage) {
                $this->out
                    ->write('  ' . $this->name . ' ')
                    ->style(Style::BOLD)
                    ->writeLn($usage)
                    ->reset();
            }
            $this->out->lf();
        }

        if ($this->longDesc) {
            $this->out->writeLn($this->longDesc)->lf();
        }

        if (!empty($this->commands)) {
            ksort($this->commands);
            $this->drawSectionTitle(self::STR_COMMANDS);
            foreach ($this->commands as $cmd => $desc) {
                $this->out
                    ->style(Style::BOLD)
                    ->write('  %-' . $this->minLengths['cmd'] . 's: ', $cmd)
                    ->style(Style::BOLD, false)
                    ->writeLn($desc);
            }
            $this->out->lf();
        }
        
        if (!empty($this->options)) {
            usort($this->options, function($a, $b) {
                if (isset($a['long'])) {
                    if (isset($b['long'])) {
                        return strcasecmp($a['long'], $b['long']);
                    }
                    if (isset($b['short'])) {
                        return strcasecmp($a['long'], $b['short']);
                    }
                    return -1;
                }
                if (isset($a['short'])) {
                    if (isset($b['short'])) {
                        return strcasecmp($a['short'], $b['short']);
                    }
                    if (isset($b['long'])) {
                        return strcasecmp($a['short'], $b['long']);
                    }
                    return -1;
                }

                return 1;
            });

            $this->drawSectionTitle(self::STR_OPTIONS);
            $this->drawOptions($this->options);
            $this->out->lf();
        }

        foreach ($this->sections as $title => $section) {
            $this->drawSectionTitle($title);
            $this->out->writeLn($section)->lf();
        }

        $this->out->verbosity($oldVerb);
    }

    /**
     * Draw section title
     * @param string $title
     */
    protected function drawSectionTitle($title)
    {
        $this->out
            ->color(37)
            ->style(Style::BOLD)
            ->writeLn($title)
            ->reset();
    }

    /**
     * Draw options
     * @param array $options
     */
    protected function drawOptions(array $options)
    {
        foreach ($options as $opt) {
            $this->drawOption($opt);
        }
    }

    /**
     * Draw one option
     * @param array $opt
     */
    protected function drawOption(array $opt)
    {
        $short = isset($opt['short']) ? $opt['short'] : '';
        $long = isset($opt['long']) ? $opt['long'] : '';
        $sep = empty($short) || empty($long)
            ? $this->minLengths['long'] < 1 ? '' : '  '
            : ', ';

        $this->out->writeLn(
            '  %-' . $this->minLengths['short'] . 's%s%-' . $this->minLengths['long'] . 's: %s',
            $short,
            $sep,
            $long,
            $opt['desc']
        );
    }
}
