<?php

namespace App\Service\Answer\Import;

use App\Core\DataStructure\Queue;

class FillsUpTopic implements AnswerStrategy
{
    private Queue $queue;
    private array $questions = array();

    public function __construct()
    {
        $this->queue = new Queue();
    }

    public function resolve(string $content,int $answerType = 0)
    {
        $content = str_replace("（", "(", $content);
        $content = str_replace("）", ")", $content);
        $list = explode("\n", str_replace("\r\n", "\n", $content));
        foreach ($list as $line) {
            if ($line && $this->checkParentheses($line)) {
                $question = new Question();
                //匹配括号里内容
                $patten = "/(?<=\()[^\)]+/";
                if($answerType === 1){
                    $patten = "/(?<=#\()[^#\)]+/";
                }else if($answerType === 2){
                    $patten = "/(?<=#\{)[^#\}]+/";
                }else if($answerType === 3){
                    $patten = "/(?<=#\[)[^#\]]+/";
                }
                $answers = '';
                preg_match_all($patten, $line, $answers);
                if ($answers && is_array($answers[0]) && !empty($answers[0])) {
                    foreach ($answers[0] as $answer) {
                        if($answerType === 1) {
                            $line = str_replace("#(${answer})#", '()', $line);
                        }else if($answerType === 2){
                            $line = str_replace('#{' . $answer . '}#', '()', $line);
                        }else if($answerType === 3){
                            $line = str_replace("#[${answer}]#", '()', $line);
                        }else {
                            $line = str_replace("(${answer})", '()', $line);
                        }
                    }
                    $question->setAnswer(json_encode($answers[0]));
                }
                $question->setType(Question::$FILL_IN_BLANKS);
                $question->setContent($line);
                $this->questions[] = $question->toArray();

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
}
