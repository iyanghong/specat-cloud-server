<?php

namespace App\Service\Answer\Import;

class Question
{
    public static int $RADIO_QUESTION = 1;  //单选
    public static int $MULTI_SELECT = 2;    //多选
    public static int $FILL_IN_BLANKS = 3;  //填空题
    public static int $SHORT_ANSWER = 4;    //简答题
    public static int $TRUE_FALSE = 5;      //判断题

    /**
     * @var string 题目
     */
    private string $content = '';
    /**
     * @var string|array|null 题目选项
     */
    private string|array $options = '';
    /**
     * @var string 答案
     */
    private string $answer = '';
    /**
     * @var float|int 分值
     */
    private float|int $score = 5;
    /**
     * @var int 题型
     */
    private int $type = 1;
    /**
     * @var array|null 关键字
     */
    private ?array $keywords = null;

    private ?string $parse = '';

    /**
     * @return string|null
     */
    public function getParse(): ?string
    {
        return $this->parse;
    }

    /**
     * @param string|null $parse
     */
    public function setParse(?string $parse): void
    {
        $this->parse = $parse;
    }



    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    /**
     * @return array|string|null
     */
    public function getOptions(): array|string|null
    {
        return $this->options;
    }

    /**
     * @param array|string|null $options
     */
    public function setOptions(array|string|null $options): void
    {
        $this->options = $options;
    }

    /**
     * @return string
     */
    public function getAnswer(): string
    {
        return $this->answer;
    }

    /**
     * @param string $answer
     */
    public function setAnswer(string $answer): void
    {
        $this->answer = $answer;
    }

    /**
     * @return float|int
     */
    public function getScore(): float|int
    {
        return $this->score;
    }

    /**
     * @param float|int $score
     */
    public function setScore(float|int $score): void
    {
        $this->score = $score;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    public function setType(int $type): void
    {
        $this->type = $type;
    }

    /**
     * @return array|null
     */
    public function getKeywords(): ?array
    {
        return $this->keywords;
    }

    /**
     * @param array|null $keywords
     */
    public function setKeywords(?array $keywords): void
    {
        $this->keywords = $keywords;
    }

    public function toArray(): array
    {
        return [
            'content' => $this->content,
            'options' => $this->options,
            'answer' => $this->answer,
            'score' => $this->score,
            'type' => $this->type,
            'keywords' => $this->keywords
        ];
    }


    public function __toString(): string
    {
        // TODO: Implement __toString() method.
        return json_encode($this->toArray());
    }


}
