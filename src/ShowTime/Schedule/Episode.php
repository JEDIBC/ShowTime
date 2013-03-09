<?php
namespace ShowTime\Schedule;

class Episode
{

    /**
     * @var string
     */
    protected $showId;

    /**
     * @var string
     */
    protected $show;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var int
     */
    protected $season;

    /**
     * @var int
     */
    protected $episode;

    /**
     * @var int
     */
    protected $global;

    /**
     * @param string $showId
     * @param string $show
     * @param string $title
     * @param int    $season
     * @param int    $episode
     * @param int    $global
     */
    public function __construct($showId, $show, $title, $season, $episode, $global)
    {
        $this->showId  = $showId;
        $this->show    = $show;
        $this->title   = $title;
        $this->season  = $season;
        $this->episode = $episode;
        $this->global  = $global;
    }

    /**
     * @return int
     */
    public function getEpisode()
    {
        return $this->episode;
    }

    /**
     * @return int
     */
    public function getGlobal()
    {
        return $this->global;
    }

    /**
     * @return string
     */
    public function getShowId()
    {
        return $this->showId;
    }

    /**
     * @return int
     */
    public function getSeason()
    {
        return $this->season;
    }

    /**
     * @return string
     */
    public function getShow()
    {
        return $this->show;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    function __toString()
    {
        return $this->show . ' : ' . sprintf('S%dE%02d', $this->season, $this->episode) . ' ' . $this->title . ' (' . $this->global . ')';
    }
}