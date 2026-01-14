<?php

use App\Models\GCSBucketModel;
use App\Models\UccFileManager;

class FilesController {
    public function fetch($partner_id) {
        (new Auth)->isLoggedIn();
        return json(
            UccFileManager::where('partner_id',$partner_id)->get()
        );
    }

    /**
     * This method parses file contents, then returns
     * 'headers' and 'sample_data' for field mapping
     */
    public function parse_file($file_id)
    {
        $file = UccFileManager::find($file_id);
        $gcs = new GCSBucketModel();
        json($gcs->csv_preview(
            $gcs->parse_file($file->name)
        ));
    }

    public function download($file_id)
    {
        $file = UccFileManager::find($file_id);
        $gcs = new GCSBucketModel();
        $contents = $gcs->parse_file($file->name);

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file->name . '"');
        header('Content-Length: ' . strlen($contents));

        echo $contents;
        exit;
    }
}