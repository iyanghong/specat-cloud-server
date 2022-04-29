<?php

namespace App\Service\Answer\Import;

use App\Core\DataStructure\Queue;

class TrueFalseQuestions implements AnswerStrategy
{

    private Queue $queue;
    private array $questions = array();

    public function __construct()
    {
        $this->queue = new Queue();
    }

    public function resolve(string $content)
    {
        $content = str_replace("（", "(", $content);
        $content = str_replace("）", ")", $content);
        $list = explode("\n", str_replace("\r\n", "\n", $content));
        foreach ($list as $line) {
            if ($line && $this->checkParentheses($line)) {
                $question = new Question();
                //匹配括号里内容
                $patten = "/(?<=\()[^\)]+/";
                $answers = '';
                preg_match_all($patten, $line, $answers);
                if (!empty($answers[0]) && is_array($answers[0])) {
                    $answer = '';
                    $realAnswer = $answers[0][sizeof($answers[0]) - 1];
                    $answerItem = trim($realAnswer);
                    if ($this->isAnswer($answerItem)) {
                        $line = str_replace("(${realAnswer})", '()', $line);
                        $answer = $answerItem;
                    }
                    if ($answer == '对' || $answer == '正确' || $answer == 'T') {
                        $question->setAnswer(1);
                    } else if ($answer == '错' || $answer == '错误' || $answer == 'F') {
                        $question->setAnswer(0);
                    } else {
                        $question->setAnswer(0);
                    }
                    $question->setType(Question::$TRUE_FALSE); //判断题
                    $question->setContent($line);
                    $this->questions[] = $question->toArray();
                }

            }
        }
        return $this->questions;

    }


    /**
     * 检查是否有完整括号
     * @param string $line
     * @return bool
     */
    private function checkParentheses(string $line): bool
    {
        return (stripos($line, '(') != false && stripos($line, ')') != false);
    }

    private function getRealAnswer($line)
    {
        $patten = "/(?<=\()[^\)]+/";
        $answers = '';
        preg_match_all($patten, $line, $answers);
        if (!empty($answers[0]) && is_array($answers[0])) {
            return $answers[0][sizeof($answers[0]) - 1];
        }
        return "";
    }

    private function isAnswer(string $content)
    {
        $len = strlen($content);
        if (!$len) return false;
        $letter = "对正确错错误TF";
        for ($i = 0; $i < $len; $i++) {
            if (stripos($letter, strtoupper($content[$i])) === false) {
                return false;
            }
        }
        return true;
    }
}
