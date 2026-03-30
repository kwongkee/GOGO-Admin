<?php 
class LetvPlayBusiness
{
    private $player;
    function __construct()
    {
        $this->player = new LivePlayComponent();
    }
    function createActivity($activityName, $coverImgUrl, $description)
    {
        $startTime = date('YmdHis');
        $endTime = date('YmdHis', strtotime('+2 years'));
        $codeRateTypes = 16;
        $playMode = 1;
        $r = $this->player->createActivity($activityName, $startTime, $endTime, $coverImgUrl, $description, $codeRateTypes, $playMode);
        return $r->activityId ? $r : false;
    }
}