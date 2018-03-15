<?php
declare(strict_types=1);

namespace RrdPhpReader;


use RrdPhpReader\Exception\RrdRangeException;

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

    public function getRawData()
    {
        return $this->data;
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

    /**
     * Return a 32 bit unsigned integer at offset idx
     *
     * @param int $idx
     * @return int
     */
    public function getLongAt(int $idx): int
    {
        if ($idx > $this->length - 4) {
            throw new RrdRangeException("getLongAt index {$idx} out of range ({$this->length})");
        }
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
        if ($idx > $this->length - 8) {
            throw new RrdRangeException("getDoubleAt index {$idx} out of range ({$this->length})");
        }
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