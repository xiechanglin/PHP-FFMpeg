<?php

/*
 * This file is part of PHP-FFmpeg.
 *
 * (c) Alchemy <info@alchemy.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FFMpeg\Media;

use Alchemy\BinaryDriver\Exception\ExecutionFailureException;
use FFMpeg\Filters\Audio\AudioFilters;
use FFMpeg\Format\FormatInterface;
use FFMpeg\Filters\Audio\SimpleFilter;
use FFMpeg\Exception\RuntimeException;
use FFMpeg\Exception\InvalidArgumentException;
use FFMpeg\Filters\Audio\AudioFilterInterface;
use FFMpeg\Filters\FilterInterface;
use FFMpeg\Format\ProgressableInterface;

class VAudio extends Audio
{
  private $inputFile = [];

  private $addCommands= [];

  /**
   * Opens a file in order to be processed.
   *
   * @param string $pathfile A pathfile
   *
   * @return Audio|Video
   *
   * @throws InvalidArgumentException
   */
  public function addInputFile(array $pathfile)
  {
      foreach ($pathfile as $file) {
          if (null === $streams = $this->ffprobe->streams($file)) {
              throw new RuntimeException(sprintf('Unable to probe "%s".', $file));
          }
      }
      $this->inputFile = $pathfile;
      return $this;
  }

  /**
   * return input image path
   *
   * @return String
   **/
  public function getInputFile()
  {
      return $this->inputFile;
  }

  /**
   * add new command in order to be processed.
   *
   * @param array $command one command
   *
   * @return Audio|Video
   *
   * @throws InvalidArgumentException
   */
  public function addCommand(array $command)
  {
      if (empty($command)) {
          throw new RuntimeException(sprintf('Empty "%s".', 'command'));
      }
      $this->addCommands[] = $command;
      return $this;
  }

  /**
   * return add commands
   *
   * @return array
   **/
  public function getAddCommands()
  {
      return $this->addCommands;
  }

    /**
     * Exports the audio in the desired format, applies registered filters.
     *
     * @param FormatInterface $format
     * @param string          $outputPathfile
     *
     * @return Audio
     *
     * @throws RuntimeException
     */
    public function save(FormatInterface $format, $outputPathfile)
    {
        $listeners = null;

        if ($format instanceof ProgressableInterface) {
            $listeners = $format->createProgressListener($this, $this->ffprobe, 1, 1);
        }

        $commands = array('-y', '-i', $this->pathfile);
        if($files = $this->getInputFile()){
            foreach ($files as $file) {
                $commands = array_merge($commands,['-i', $file]);
            }
        }

        $filters = clone $this->filters;
        $filters->add(new SimpleFilter($format->getExtraParams(), 10));

        if ($this->driver->getConfiguration()->has('ffmpeg.threads')) {
            $filters->add(new SimpleFilter(array('-threads', $this->driver->getConfiguration()->get('ffmpeg.threads'))));
        }
        if (null !== $format->getAudioCodec()) {
            $filters->add(new SimpleFilter(array('-acodec', $format->getAudioCodec())));
        }

        foreach ($filters as $filter) {
            $commands = array_merge($commands, $filter->apply($this, $format));
        }

        if ($addCommands = $this->getAddCommands()) {
            foreach ($addCommands as $addCommand) {
                $commands = array_merge($commands, $addCommand);
            }
        }

        if (null !== $format->getAudioKiloBitrate()) {
            $commands[] = '-b:a';
            $commands[] = $format->getAudioKiloBitrate() . 'k';
        }
        if (null !== $format->getAudioChannels()) {
            $commands[] = '-ac';
            $commands[] = $format->getAudioChannels();
        }
        $commands[] = $outputPathfile;

        try {
            $this->driver->command($commands, false, $listeners);
        } catch (ExecutionFailureException $e) {
            $this->cleanupTemporaryFile($outputPathfile);
            throw new RuntimeException('Encoding failed', $e->getCode(), $e);
        }

        return $this;
    }

    /**
     * Exports the audio in the desired format, applies registered filters.
     *
     * @param FormatInterface $format
     * @param string          $outputPathfile
     *
     * @return Audio
     *
     * @throws RuntimeException
     */
    public function loop(FormatInterface $format, $filename, $times=1, $outputPathfile)
    {
        $listeners = null;

        $commands = array('-f', 'concat', '-i', $filename);

        if (null !== $format->getAudioKiloBitrate()) {
            $commands[] = '-b:a';
            $commands[] = $format->getAudioKiloBitrate() . 'k';
        }
        if (null !== $format->getAudioChannels()) {
            $commands[] = '-ac';
            $commands[] = $format->getAudioChannels();
        }
        $commands[] = $outputPathfile;

        try {
            $this->driver->command($commands, false, $listeners);
        } catch (ExecutionFailureException $e) {
            $this->cleanupTemporaryFile($outputPathfile);
            throw new RuntimeException('Encoding failed', $e->getCode(), $e);
        }

        return $this;
    }
}
