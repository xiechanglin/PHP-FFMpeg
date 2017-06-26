<?php

/*
 * This file is part of PHP-FFmpeg.
 *
 * (c) Strime <contact@strime.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FFMpeg\Filters\Video;

use FFMpeg\Coordinate\Dimension;
use FFMpeg\Media\Video;
use FFMpeg\Format\VideoInterface;
use FFMpeg\Coordinate\Point;

class PadFilter implements VideoFilterInterface
{
    /** @var Dimension */
    private $dimension;
    /** @var integer */
    private $priority;
        /** @var Dimension */
    private $point;

    public function __construct(Dimension $dimension, Point $point, $priority = 0)
    {
        $this->dimension = $dimension;
        $this->priority = $priority;
        $this->point = $point;
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @return Dimension
     */
    public function getDimension()
    {
        return $this->dimension;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Video $video, VideoInterface $format)
    {
        $commands = array();

        $commands[] = '-vf';
        $commands[] = 'scale=iw*min(' . $this->dimension->getWidth() . '/iw\,' . $this->dimension->getHeight() .'/ih):ih*min(' . $this->dimension->getWidth() . '/iw\,' . $this->dimension->getHeight() .'/ih),pad=' . $this->dimension->getWidth() . ':' . $this->dimension->getHeight() . ':' . $this->point->getX() . ':' . $this->point->getY() ;

        return $commands;
    }
}
