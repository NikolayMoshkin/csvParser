<?php declare(strict_types=1);

namespace App\Imports;

use App\Connection;
use Carbon\Carbon;
use League\Csv\Reader;
use League\Csv\Statement;

class csvImport
{
    protected static $counter = 0;
    protected static $filePath;
    protected static $initOffset = 0;
    protected static $chunk;
    protected static $logView = 1000;


    public static function start(string $filePath, int $chunk = 10000){
        self::$chunk = $chunk;
        self::$filePath = $filePath;
        $csv = Reader::createFromPath(self::$filePath, 'r');
        self::import($csv, self::$initOffset, self::$chunk);
    }

    protected static function import($csv, int $offset, int $limit)
    {
//        $csv = Reader::createFromStream(fopen(self::$filePath, 'r+'));
//        $csv->setHeaderOffset(0); //set the CSV header offset
        dump('lines:' . self::$counter . '; mem usage:' . round(memory_get_usage() / pow(1024, 2)) . ' MB');
        $stmt = (new Statement())
            ->offset($offset)
            ->limit($limit);

        $records = $stmt->process($csv);

        if (empty($records->fetchOne())){
            return true;
        };

        $dataToInsert = array();
        foreach ($records as $offset => $record) {
            $resultArray = self::convertRow($record);

//            if (!Connection::where('timestamp', $resultArray[0])->exists()) {

            $dataToInsert[] = [
                    'timestamp' => $resultArray[0],
                    'time' => Carbon::createFromDate($resultArray[1]),
                    'domain_name' => $resultArray[2],
                    'file_size' => (int)$resultArray[3],
                    'file_path' => $resultArray[4],
                    'user_agent' => $resultArray[5],
                    'http_status' => (int)$resultArray[6],
                    'http_method' => $resultArray[7],
                    'content_type' => $resultArray[8],
                ];
//            }

            self::$counter++;

            if (self::$counter % self::$logView === 0) {
                dump('lines:' . self::$counter . '; mem peak usage:' . round(memory_get_peak_usage() / pow(1024, 2)) . ' MB');
                Connection::insert($dataToInsert);
                unset($dataToInsert);
                $dataToInsert = array();
            }
        }
        unset($stmt);
        unset($records);
        unset($dataToInsert);
        self::import($csv, self::$counter, self::$chunk);
    }

    protected static function convertRow($record)
    {
        $rowString = '';
        foreach ($record as $field) {
            $rowString .= $field;
        }

        $resultArrayWithGarbage = preg_split('/"?\s+"/', $rowString);

        $garbage = array(';', '"', " ");

        $resultArray = [];

        foreach ($resultArrayWithGarbage as $field) {
            $resultArray[] = str_replace($garbage, '', $field);
        }

        return $resultArray;
    }
}
