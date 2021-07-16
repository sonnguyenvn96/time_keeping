<?php
require_once getcwd() . '/vendor/autoload.php';

use Google\Cloud\Storage\StorageClient;

class Google_cloud_storage
{
    // Upload Google Storage
    public function uploadToGoogleStorage($fileData, $filename, $folder='vina-life', $metadata = [])
    {
        $filename =date('Ymd',strtotime('now')).'/'.$folder . '/' .round(microtime(true) * 1000) . '-' . rand(1111111, 9999999) . '-' . $filename;
        $storage = new StorageClient([
            'keyFilePath' => GOOGLE_STORAGE_FILE_CERT
        ]);
        $bucketName = GOOGLE_STORAGE_BUCKET_NAME;
        $bucket = $storage->bucket($bucketName);
        $option = [
            'name' => $filename,
            'predefinedAcl' => 'publicRead'
        ];
        if (!in_array($metadata, array(null, ''))) {
            $option['metadata'] = $metadata;
        }
        $object = $bucket->upload($fileData, $option);

        return '/'.$filename;
    }
}