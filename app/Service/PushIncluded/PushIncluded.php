<?php


namespace App\Service\PushIncluded;


class PushIncluded
{

    private array $channelList = [
        'baidu' => BaiduPush::class
    ];
    /**
     * @var ChannelDriveInterface|null
     */
    private ?ChannelDriveInterface $drive = null;
    private array $links = [];
    public function __construct($channel = 'baidu',array $links = [])
    {
        if(isset($this->channelList[$channel])){
            $this->drive = new $this->channelList[$channel]();
        }
        $this->links = $links;
    }
    public function push()
    {
        return $this->drive->handle($this->links);
    }
}