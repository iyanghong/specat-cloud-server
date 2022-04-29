<?php

namespace App\Service\Answer\Import;

use App\Core\DataStructure\Queue;
use App\Core\DataStructure\Stack;
use Illuminate\Support\Str;

class MultipleChoice implements AnswerStrategy
{

    private Queue $queue;
    private array $questions = array();
    private int $answerType = 0;

    public function __construct($answerType = 0)
    {
        $this->answerType = $answerType;
        $this->queue = new Queue();
    }

    public function resolve(string $content): array
    {
        $content = str_replace("（", "(", $content);
        $content = str_replace("）", ")", $content);

        // TODO: Implement resolve() method.
        $list = explode("\n", str_replace("\r\n", "\n", $content));
        foreach ($list as $Key => $line) {
            if ($line) {
                $line = rtrim($line, "\t");
                if ($this->checkParentheses($line)) {
                    $question = $this->resolveQuestion();

                    if ($question) {
                        $this->questions[] = $question->toArray();
                    }
                }
                $this->queue->enqueue($line);
            }
        }
        $question = $this->resolveQuestion();
        if ($question) {
            $this->questions[] = $question->toArray();
        }
        return $this->questions;

    }


    private function resolveQuestion(): ?Question
    {
        if ($this->queue->isEmpty()) return null;
        $question = new Question();

        $questionOptions = [];
        while (!$this->queue->isEmpty()) {
            $line = $this->queue->dequeue();
            if ($this->checkParentheses($line)) {
                if ($this->answerType === 0) {
                    //匹配括号里内容
                    $answer = $this->getAnswer($line);
                    $line = str_replace("(${answer})", '()', $line);
                    if (strlen($answer) > 1) {
                        $question->setType(Question::$MULTI_SELECT); //多选题
                    } else {
                        $question->setType(Question::$RADIO_QUESTION); //单选题
                    }

                    $question->setAnswer($answer);
                    $question->setContent($line);

                } else {
                    $question->setContent($line);
                }

            } else {
                $line = str_replace("：", ":", $line);
                if (Str::startsWith($line, "答案解析")) {
                    $question->setParse(str_replace("答案解析:", "", $line));
                } else if (Str::startsWith($line, "答案")) {
                    $answer = str_replace("答案:", "", $line);
                    $question->setAnswer(trim($answer));
                } else {
                    $questionOptions[] = $line;
                }

            }
        }
        if (!empty($questionOptions)) {
            $question->setOptions($questionOptions);
        }
        return $question;
    }


    /**
     * 检查是否有完整括号
     * @param string $line
     * @return bool
     */
    private function checkParentheses(string $line): bool
    {
        $flag = (stripos($line, '(') !== false && stripos($line, ')') !== false);
        if (!$flag) return false;
        if ($this->isAnswer(trim($this->getAnswer($line)))) {
            return true;
        }
        return false;
    }


    private function getAnswer($line)
    {
        $patten = "/(?<=\()[^\)]+/";
        $answers = '';
        preg_match_all($patten, $line, $answers);
        if (!empty($answers[0]) && is_array($answers[0])) {
            $answerItem = $answers[0][sizeof($answers[0]) - 1];
            if ($this->isAnswer($answerItem)) {
                return $answerItem;
            }
        }
        return '';
    }

    private function isAnswer(string $content)
    {
        $len = strlen($content);
        if (!$len) return false;
        $letter = "ABCDEFGHIJKLMN";
        for ($i = 0; $i < $len; $i++) {
            if (stripos($letter, strtoupper($content[$i])) === false) {
                return false;
            }
        }
        return true;
    }

}
