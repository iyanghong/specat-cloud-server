<?php

namespace App\Service\Answer\Import;

class AnswerImport
{
    private array $strategy = [];
    private int $answerType = 0;

    public function __construct()
    {
        $this->strategy[Question::$RADIO_QUESTION] = MultipleChoice::class;
        $this->strategy[Question::$MULTI_SELECT] = MultipleChoice::class;
        $this->strategy[Question::$FILL_IN_BLANKS] = FillsUpTopic::class;
        $this->strategy[Question::$TRUE_FALSE] = TrueFalseQuestions::class;
    }

    public function resolve(int $strategy, string $content): ?array
    {
        if (!isset($this->strategy[$strategy])) return null;
        $context = $this->strategy[$strategy];
        if ($context) {
            /**@var $strategy AnswerStrategy */
            $strategy = new $context();

            return $strategy->resolve($content,$this->answerType);
        }
        return null;
    }

    /**
     * @return array
     */
    public function getStrategy(): array
    {
        return $this->strategy;
    }

    /**
     * @param array $strategy
     */
    public function setStrategy(array $strategy): void
    {
        $this->strategy = $strategy;
    }

    /**
     * @return int
     */
    public function getAnswerType(): int
    {
        return $this->answerType;
    }

    /**
     * @param int $answerType
     */
    public function setAnswerType(int $answerType): void
    {
        $this->answerType = $answerType;
    }



}
