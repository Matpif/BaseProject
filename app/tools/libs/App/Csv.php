<?php

namespace App\libs\App;

class Csv
{

    CONST FILE_WRITE = 'w';
    CONST FILE_READ = 'r';
    CONST FILE_READ_WRITE = 'rw';

    /**
     * @var String
     */
    private $_path;
    /**
     * @var String
     */
    private $_delimiter;
    /**
     * @var String
     */
    private $_enclosure;
    /**
     * @var String
     */
    private $_escape_char;
    /**
     * @var array
     */
    private $_data;
    /**
     * @var array
     */
    private $_header;

    /**
     * Csv constructor.
     * @param String $_path
     * @param String $_delimiter
     * @param String $_enclosure
     * @param String $_escape_char
     */
    public function __construct($_path, $_delimiter = ",", $_enclosure = '"', $_escape_char = "\\")
    {
        $this->_path = $_path;
        $this->_delimiter = $_delimiter;
        $this->_enclosure = $_enclosure;
        $this->_escape_char = $_escape_char;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function generateCsvFile()
    {
        $f = fopen($this->_path, self::FILE_WRITE);

        if ($f !== false) {
            if (is_array($this->_header)) {
                fputcsv($f, $this->_header, $this->_delimiter, $this->_enclosure, $this->_escape_char);
            }
            foreach ($this->_data as $d) {
                fputcsv($f, $d, $this->_delimiter, $this->_enclosure, $this->_escape_char);
            }
            fclose($f);
        } else {
            throw new \Exception("Can't open file : {$this->_path}");
        }

        return true;
    }

    /**
     * @param bool $firstLineIsHeader
     * @param int $length
     * @return array | boolean
     * @throws \Exception
     */
    public function readCsvFile($firstLineIsHeader = false, $length = 0)
    {
        $d = false;
        $f = fopen($this->_path, self::FILE_READ);
        $header = [];

        if ($f !== false) {
            $numLine = 0;
            while (($data = fgetcsv($f, $length, $this->_delimiter, $this->_enclosure,
                    $this->_escape_char)) !== false) {
                if ($firstLineIsHeader && $numLine == 0) {
                    $header = $data;
                }

                if ($numLine > 0 || !$firstLineIsHeader && $numLine == 0) {
                    if ($firstLineIsHeader) {
                        foreach ($header as $key => $h) {
                            $d[][$h] = $data[$key];
                        }
                    } else {
                        $d[] = $data;
                    }
                }
            }

            fclose($f);
        } else {
            throw new \Exception("Can't open file : {$this->_path}");
        }

        return $d;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * @param array $data
     */
    public function setData($data)
    {
        $this->_data = $data;
    }

    /**
     * @return array
     */
    public function getHeader()
    {
        return $this->_header;
    }

    /**
     * @param array $header
     */
    public function setHeader($header)
    {
        $this->_header = $header;
    }
}