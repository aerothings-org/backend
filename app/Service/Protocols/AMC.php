<?php

namespace App\Service\Protocols;

use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class AMC
{
    const DATE_LENGTH = 8;
    const COUNT_CHUNKS_LENGTH = 2;
    const CRC_LENGTH = 4;
    const LAT_LENGTH = 8;
    const LNG_LENGTH = 8;
    const ALT_LENGTH = 4;
    const SPEED_LENGTH = 4;
    const GIRO_LENGTH = 4;
    const TEMP_LENGTH = 4;

    protected string $payload;
    protected int $chunk_size = 1;
    protected int $payload_length;

    /**
     * @param $payload
     */
    public function __construct($payload)
    {
        $this->calcCountChunks($payload);
        $this->payload = substr(base64_decode($payload, true),0,$this->payload_length);
    }

    public function decode(): array
    {
        try {
            $this->validate($this->payload);
            return [
                'date' => $this->getDate($this->payload),
                'chunks' => $this->getChunks(substr($this->payload, self::DATE_LENGTH + self::COUNT_CHUNKS_LENGTH, ($this->payload_length - self::CRC_LENGTH - self::DATE_LENGTH - self::COUNT_CHUNKS_LENGTH))),
                'error' => false,
            ];
        } catch (\Exception $exception) {
            return [
                'error' => true,
                'message' => $exception->getMessage()
            ];
        }
    }


    /**
     * @throws ValidationException
     */
    private function validate($payload): void
    {
        if (!$payload){
            throw ValidationException::withMessages(['payload' => 'Invalid payload, unable to decode']);
        }

        if (strlen($payload) < $this->payload_length){
            throw ValidationException::withMessages(['payload' => 'invalid payload length']);
        }
        $crc_received = substr($payload,($this->payload_length-self::CRC_LENGTH), self::CRC_LENGTH);
        $payload_without_crc = substr($payload,0, $this->payload_length-self::CRC_LENGTH);
        $crc_calculate = $this->calculateCRC16XMODEM($payload_without_crc);
        if ($crc_received !== $crc_calculate) {
            throw ValidationException::withMessages(['payload' => 'invalid crc32 in payload']);
        }
    }

    private function getChunks($payload): array
    {
        $chunks = [];
        $offset = 0;
        for ($i = 0; $i < $this->chunk_size; $i++) {
            $chunk = [
                'lat' => $this->convertCoordinates(substr($payload, $offset, self::LAT_LENGTH)),
                'lng' => $this->convertCoordinates(substr($payload, $offset += self::LAT_LENGTH, self::LNG_LENGTH)),
                'alt' => $this->convertFromBigToLittle(substr($payload, $offset += self::LNG_LENGTH, self::ALT_LENGTH)),
                'speed' => $this->convertFromBigToLittle(substr($payload, $offset += self::ALT_LENGTH, self::SPEED_LENGTH)),
                'giro' => $this->convertFromBigToLittle(substr($payload, $offset += self::SPEED_LENGTH, self::GIRO_LENGTH)),
                'temp' => $this->convertFromBigToLittle(substr($payload, $offset += self::GIRO_LENGTH, self::TEMP_LENGTH)),
            ];
            $offset += self::TEMP_LENGTH;
            $chunks[] = $chunk;
        }
        return $chunks;
    }

    private function getDate($payload): string
    {
        $data = $this->convertFromBigToLittle(substr($payload, 0, self::DATE_LENGTH));
        return Carbon::createFromTimestamp($data)->format('d/m/Y H:i:s');
    }

    private function convertCoordinates($hex): float
    {
        $hexadecimal = $this->convertFromBigToLittle($hex, false);
        $hexadecimal = str_pad($hexadecimal, 8, "0", STR_PAD_LEFT);
        $decimalValue = unpack("N", hex2bin($hexadecimal))[1];
        if ($decimalValue >= 0x80000000) {
            $decimalValue -= 0x100000000;
        }
        return number_format($decimalValue/30000/60, 7, '.', '');
    }

    private function calculateCRC16XMODEM($data): string
    {
        $data = hex2bin($data);
        $crc = 0x0000;
        $polynomial = 0x1021;
        for ($i = 0; $i < strlen($data); $i++) {
            $crc ^= (ord($data[$i]) << 8);
            for ($j = 0; $j < 8; $j++) {
                if ($crc & 0x8000) {
                    $crc = ($crc << 1) ^ $polynomial;
                } else {
                    $crc = $crc << 1;
                }
            }
        }
        $crc &= 0xFFFF;
        $crc = dechex($crc);
        $a = substr($crc, 0 ,2);
        $b = substr($crc, 2 ,4);
        return strtoupper($b.$a);
    }

    private function convertFromBigToLittle($data, $dec = true): string
    {
        if ($dec) {
            return hexdec(join(array_reverse(str_split($data, 2))));
        }
        return join(array_reverse(str_split($data, 2)));
    }

    private function calcCountChunks($payload): void
    {
        $payload = base64_decode($payload);
        $this->chunk_size = hexdec(substr($payload,self::DATE_LENGTH, self::COUNT_CHUNKS_LENGTH));
        $chunk_size_length=(self::LAT_LENGTH+self::LNG_LENGTH+self::ALT_LENGTH+self::SPEED_LENGTH+self::GIRO_LENGTH+self::TEMP_LENGTH);
        $this->payload_length = (self::DATE_LENGTH + self::COUNT_CHUNKS_LENGTH +($chunk_size_length*$this->chunk_size)+self::CRC_LENGTH);
    }


}
