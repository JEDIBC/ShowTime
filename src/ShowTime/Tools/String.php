<?php
namespace ShowTime\Tools;

class String
{
    protected $string;

    /**
     * @param string $string
     */
    public function __construct($string)
    {
        $this->string = $string;
    }

    /**
     * @param array|string $data
     * @return bool
     */
    public function contains($data)
    {
        if (empty($data)) {
            return true;
        }

        if (!is_array($data)) {
            $data = array($data);
        }

        return ((false !== strpos($this->string, array_pop($data))) && $this->contains($data));
    }
}