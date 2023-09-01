<?php

namespace App\Service\Protocols;

use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class AMC
{
    const PAYLOAD_LENGTH = 336;
    const DATE_LENGTH = 8;
    const CRC_LENGTH = 8;
    const LAT_LENGTH = 8;
    const LNG_LENGTH = 8;
    const ALT_LENGTH = 4;
    const SPEED_LENGTH = 4;
    const GIRO_LENGTH = 4;
    const TEMP_LENGTH = 4;
    const CHUNK_SIZE = 10;

    protected string $payload;

    /**
     * @param $payload
     */
    public function __construct($payload)
    {
        $this->payload = base64_decode($payload, true);
    }


    public function decode(): array
    {
        try {
            $this->validate($this->payload);
            return [
                'date' => $this->getDate($this->payload),
                'chunks' => $this->getChunks(substr($this->payload, self::DATE_LENGTH, (self::PAYLOAD_LENGTH - self::CRC_LENGTH))),
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

        if (strlen($payload) !== self::PAYLOAD_LENGTH){
            throw ValidationException::withMessages(['payload' => 'invalid payload length']);
        }

        $crc32 = hexdec(substr($payload,(self::PAYLOAD_LENGTH-self::CRC_LENGTH), self::CRC_LENGTH));
        if (crc32(substr($payload, 0, (self::PAYLOAD_LENGTH - self::CRC_LENGTH))) !== $crc32) {
            throw ValidationException::withMessages(['payload' => 'invalid crc32 in payload']);
        }
    }

    private function getChunks($payload): array
    {
        $chunks = [];
        $offset = 0;
        for ($i = 0; $i < self::CHUNK_SIZE; $i++) {
            $chunk = [
                'lat' => $this->convertCoordinates(substr($payload, $offset, self::LAT_LENGTH)),
                'lng' => $this->convertCoordinates(substr($payload, $offset += self::LAT_LENGTH, self::LNG_LENGTH)),
                'alt' => hexdec(substr($payload, $offset += self::LNG_LENGTH, self::ALT_LENGTH)),
                'speed' => hexdec(substr($payload, $offset += self::ALT_LENGTH, self::SPEED_LENGTH)),
                'giro' => hexdec(substr($payload, $offset += self::SPEED_LENGTH, self::GIRO_LENGTH)),
                'temp' => hexdec(substr($payload, $offset += self::GIRO_LENGTH, self::TEMP_LENGTH)),
            ];
            $chunks[] = $chunk;
        }
        return $chunks;
    }

    private function getDate($payload): string
    {
        return Carbon::createFromTimestamp(hexdec(substr($payload, 0, self::DATE_LENGTH)))->format('d/m/Y H:i:s');
    }

    private function convertCoordinates($hex): float
    {
        $decimalValue = unpack("N", hex2bin(substr($hex, 2)))[1];
        if ($decimalValue >= 0x80000000) {
            $decimalValue -= 0x100000000;
        }
        return number_format($decimalValue/30000/60, 7, '.', '');
    }
}
