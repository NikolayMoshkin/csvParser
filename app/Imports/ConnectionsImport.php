<?php

declare(strict_types=1);

namespace App\Imports;

use App\Connection;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithProgressBar;

class ConnectionsImport implements ToModel, WithBatchInserts, WithChunkReading
{

    protected $counter = 0;
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $this->counter++;
        if ($this->counter % 1000 === 0){
            dump('lines:' . $this->counter . '; mem usage:' . round(memory_get_usage() / pow(1024,2)). ' MB');
        }

        $rowString = '';
        foreach ($row as $field) {
            $rowString .= $field;
        }

        $resultArrayWithGarbage = preg_split('/"?\s+"/', $rowString);

        $garbage = array(';', '"', " ");
        $resultArray = [];

        foreach ($resultArrayWithGarbage as $field) {
            $resultArray[] = str_replace($garbage, '', $field);
        }

        if (!Connection::where('timestamp', $resultArray[0])->exists()) {

            return new Connection([
                'timestamp' => $resultArray[0],
                'time' => Carbon::createFromDate($resultArray[1]),
                'domain_name' => $resultArray[2],
                'file_size' => (int)$resultArray[3],
                'file_path' => $resultArray[4],
                'user_agent' => $resultArray[5],
                'http_status' => (int)$resultArray[6],
                'http_method' => $resultArray[7],
                'content_type' => $resultArray[8],
            ]);
        }
    }

    /**
     * @inheritDoc
     */
    public function batchSize(): int
    {
        return 1000;
    }

    /**
     * @inheritDoc
     */
    public function chunkSize(): int
    {
        return 1000;
    }

}
