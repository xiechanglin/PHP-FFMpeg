<?php

/*
 * This file is part of PHP-FFmpeg.
 *
 * (c) Alchemy <info@alchemy.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FFMpeg\Filters\Audio;

use FFMpeg\Coordinate\TimeCode;
use FFMpeg\Format\AudioInterface;
use FFMpeg\Media\Audio;

class AudioMixFilter implements AudioFilterInterface {


    /**
     * @var int
     */
    private $count;

    /**
     * @var int
     */
    private $priority;


    public function __construct(int $count, $priority = 0) {
        $this->count = $count;
        $this->priority = $priority;
    }

    /**
     * @inheritDoc
     */
    public function getPriority() {
        return $this->priority;
    }

     /**
      * input file count
      *
      * @return int
      */
     public function getCount() {
         return $this->count;
     }

     /**
      * @inheritDoc
      */
     public function apply(Audio $audio, AudioInterface $format) {
         $commands = ['-filter_complex','amix=inputs='.$this->getCount().':duration=first:dropout_transition=3 '];
         return $commands;
     }

}
