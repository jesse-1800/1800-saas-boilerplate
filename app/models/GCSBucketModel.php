<?php namespace App\Models;

use Google\Cloud\Storage\Bucket;
use Google\Cloud\Storage\StorageClient;

class GCSBucketModel {
    private Bucket $bucket;
    private StorageClient $storage;
    private string $bucket_name = 'ucc-file-explorer-uploads';
    private string $gcs_key = 'app/config/ucc-explorer-svc-key.json';

    /**
     * Instantiate GCS and destination paths
     */
    public function __construct()
    {
        try {
            $this->storage = new StorageClient([
                'keyFilePath' => "{$_SERVER['DOCUMENT_ROOT']}/$this->gcs_key"
            ]);
            $this->bucket = $this->storage->bucket($this->bucket_name);
        } catch (Exception $e) {
            die(json([
                'result' => false,
                'message' => "GCS failed: {$e->getMessage()}"
            ]));
        }
    }

    /**
     * Upload to a specific partner folder
     */
    public function upload($file_name,$tmp_file_path)
    {
        return $this->bucket->upload(
            fopen($tmp_file_path,'r'),
            ['name' => $file_name,'contentType' => 'text/csv']
        );
    }

    /**
     * Fetch file contents from GCS bucket
     */
    public function parse_file($file_name)
    {
        try {
            $object = $this->bucket->object($file_name);
            if (!$object->exists()) {
                die(json([
                    'result' => false,
                    'message' => "File not found in GCS: $file_name"
                ]));
            }
            return $object->downloadAsString();
        }
        catch (\Exception $e) {
            die(json([
                'result' => false,
                'message' => "Failed to fetch file: {$e->getMessage()}"
            ]));
        }
    }

    /**
     * Fetch CSV headers and first row of data for field mapping
     */
    public function csv_preview($csv_content)
    {
        try {
            $lines = explode("\n", trim($csv_content));
            if (count($lines) < 2) {
                die(json([
                    'result'  => false,
                    'message' => 'CSV must contain headers and at least one data row',
                ]));
            }
            $headers = str_getcsv($lines[0]);

            // Cleanup
            foreach ($headers as $index => $header) {
                $headers[$index] = preg_replace('/^\xEF\xBB\xBF/', '', $header);
            }

            return [
                'headers' => $headers,
                'data' => $this->csv_to_array($csv_content),
            ];
        }
        catch (\Exception $e) {
            die(json([
                'result'  => false,
                'message' => 'Failed to fetch a CSV preview',
            ]));
        }
    }

    /**
     * Parse CSV content into array accessible by column header
     * Returns array where keys are column headers (e.g., "BUYID")
     * and values are arrays of data for that column
     */
    public function csv_to_array($csv_content)
    {
        try {
            // Remove BOM if present
            $csv_content = preg_replace('/^\xEF\xBB\xBF/', '', $csv_content);
            $lines = explode("\n", trim($csv_content));
            if (count($lines) < 2) {
                die(json([
                    'result'  => false,
                    'message' => 'CSV must contain headers and at least one data row',
                ]));
            }
            $headers = str_getcsv($lines[0]);
            $data = [];

            // Parse each row and create associative array
            for ($i = 1; $i < count($lines); $i++) {
                if (trim($lines[$i]) === '') {
                    continue; // Skip empty lines
                }
                $row = str_getcsv($lines[$i]);
                $row_data = [];

                // Map each value to its corresponding header
                foreach ($headers as $index => $header) {
                    $row_data[$header] = $row[$index] ?? null;
                }
                $data[] = $row_data;
            }
            return $data;
        }
        catch (\Exception $e) {
            die(json([
                'result'  => false,
                'message' => 'Failed to parse CSV data',
            ]));
        }
    }
}