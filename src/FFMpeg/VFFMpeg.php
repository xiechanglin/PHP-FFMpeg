<?php

/*
 * This file is part of PHP-FFmpeg.
 *
 * (c) Alchemy <info@alchemy.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FFMpeg;

use Alchemy\BinaryDriver\ConfigurationInterface;
use FFMpeg\Driver\FFMpegDriver;
use FFMpeg\Exception\InvalidArgumentException;
use FFMpeg\Exception\RuntimeException;
use FFMpeg\Media\VAudio;
use FFMpeg\Media\VVideo;
use Psr\Log\LoggerInterface;

class VFFMpeg extends FFMpeg
{

    /**
     * Opens a file in order to be processed.
     *
     * @param string $pathfile A pathfile
     *
     * @return Audio|Video
     *
     * @throws InvalidArgumentException
     */
    public function open($pathfile)
    {
        if (null === $streams = $this->ffprobe->streams($pathfile)) {
            throw new RuntimeException(sprintf('Unable to probe "%s".', $pathfile));
        }

        if (0 < count($streams->videos())) {
            return new VVideo($pathfile, $this->driver, $this->ffprobe);
        } elseif (0 < count($streams->audios())) {
            return new VAudio($pathfile, $this->driver, $this->ffprobe);
        }

        throw new InvalidArgumentException('Unable to detect file format, only audio and video supported');
    }
}
