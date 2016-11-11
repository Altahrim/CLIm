<?php
namespace Clim\Helper;

/**
 * Store user answers
 */
class Answer
{
    /**
     * Pre-defined answers
     * @var string[]
     */
    private static $answers = [];

    /**
     * Enable or disable the recording of anwsers
     * @var bool
     */
    private static $recordEnabled = false;

    /**
     * Load some pre-defined answers
     * Format : [questionId1 => answer1, questionId2 => answer2…]
     * @param string[] $answers
     * @param bool $keepOld If false, previous answers will be erased
     */
    public static function loadAnswsers(array $answers, $keepOld = false)
    {
        self::$answers = $keepOld ? array_merge(self::$answers) : $answers;
    }

    /**
     * Load pre-defined answers from a JSON file
     * @param string $filePath
     * @param bool $keepOld
     * @throws \Exception
     */
    public static function loadAnswersFromFile($filePath, $keepOld = false)
    {
        if (!is_file($filePath)) {
            throw new \Exception('Impossible to load answers: file "' . $filePath . '" not found');
        }
        $json = file_get_contents($filePath);
        if (false === $json) {
            throw new \Exception('Impossible to load answers: file "' . $filePath . '" is unreadable');
        } elseif (empty($json)) {
            throw new \Exception('Impossible to load answers: file "' . $filePath . '" is empty');
        }

        $data = json_decode($json, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \Exception('Impossible to load answers: invalid json data (' . json_last_error_msg() . ')');
        }

        self::loadAnswsers($data, $keepOld);
    }

    /**
     * Add (or replace) a single answer
     * @param string $questionId
     * @param string $answer
     */
    public static function setAnswer($questionId, $answer)
    {
        self::$answers[$questionId] = $answer;
    }

    /**
     * Remove a single answer
     * Useful if an invalid answer was provided
     * @param string $questionId
     */
    public static function unsetAnswer($questionId)
    {
        unset(self::$answers[$questionId]);
    }

    /**
     * Check if an answer is associated with a question ID
     * @param string $questionId
     * @return string
     */
    public static function getAnswer($questionId)
    {
        return array_key_exists($questionId, self::$answers)
            ? (string) self::$answers[$questionId]
            : null;
    }

    /**
     * Enable (or disable) the recording of answers
     * @param bool $activate
     */
    public static function record($activate = true)
    {
        self::$recordEnabled = (bool) $activate;
    }

    /**
     * Save answers to specified file
     * @param $filepath
     * @throws \Exception
     */
    public static function saveAnswersToFile($filepath)
    {
        $json = json_encode(self::$answers, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
        if (false === file_put_contents($filepath, $json)) {
            throw new \Exception('Impossible to save answers');
        }
    }
}
