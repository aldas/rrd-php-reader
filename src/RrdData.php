<?php
declare(strict_types=1);

namespace RrdPhpReader;


class RrdData
{
    /**
     * @var bool
     */
    private $switch_endian = false;

    /**
     * @var int
     */
    private $length;

    /**
     * @var string
     */
    private $data;

    public function __construct(string $data)
    {
        $this->data = $data;
        $this->length = \strlen($data);
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function setSwitchEndian(bool $switchEndian)
    {
        $this->switch_endian = $switchEndian;
    }

    /**
     * @return bool
     */
    public function isSwitchEndian(): bool
    {
        return $this->switch_endian;
    }

    /**
     * Return a 8 bit unsigned integer at offset idx
     *
     * @param int $idx
     * @return int
     */
    public function getByteAt(int $idx): int
    {
        return (int)$this->data[$idx];
    }

    private function getEndianByteAt(int $iOffset, int $width, int $delta = 0)
    {
        if ($this->switch_endian) {
            $index = $iOffset + $width - $delta - 1;
        } else {
            $index = $iOffset + $width;
        }
        return $this->getByteAt($index);
    }

    /**
     * Return a 32 bit unsigned integer at offset idx
     *
     * @param int $idx
     * @return int
     */
    public function getLongAt(int $idx): int
    {
        $raw = substr($this->data, $idx, 4);
        $format = 'V'; // unsigned long (always 32 bit, little endian byte order)
        if ($this->switch_endian) {
            $format = 'N'; // unsigned long (always 32 bit, big endian byte order)
        }
        $unpack = unpack($format, $raw);
        return $unpack[1];
    }

    /**
     * Return a double float at offset idx
     *
     * @param int $idx
     * @return float
     */
    public function getDoubleAt(int $idx): float
    {
        $raw = substr($this->data, $idx, 8);
        $format = 'e'; // double (machine dependent size, little endian byte order)
        if ($this->switch_endian) {
            $format = 'E'; // double (machine dependent size, big endian byte order)
        }
        $unpack = unpack($format, $raw);
        return $unpack[1];
    }

    /**
     * Return a string of at most maxsize characters that was 0-terminated in the source
     *
     * @param int $idx
     * @param int $maxSize
     * @return string
     */
    public function getCStringAt(int $idx, int $maxSize): string
    {
        $raw = substr($this->data, $idx, $maxSize);
        $unpack = unpack('A*', $raw);
        return $unpack[1];
    }

}