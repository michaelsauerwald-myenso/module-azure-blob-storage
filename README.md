# MyEnso Azure Blob Storage

This package provides a simple way to use Azure Blob Storage as a remote storage for your Magento 2 application.
There is also support for svg images by "magestyapps/module-web-images" in composer.json.

Add this to you env.php file and configure it as you need:
```php

'remote_storage' => [
    'driver' => 'azure-blob-storage',
    'config' => [
        'account_name' => 'devstoreaccount1',
        'account_key' => 'Eby8vdM02xNOcqFe6SuKF4rPcU31qVbVSveG+8n8uBL2AZuZ4nSPZo98YwHst9JdXN3H3G8HC1E0T1G6+UD7Lw==',
        'blob_container' => 'category-images',
        'endpoint' => 'http://10.192.19.58:10000/devstoreaccount1/'
    ]
],

```
For local testing you can use:
 - Azure Storage Emulator. You can download it from [here](https://docs.microsoft.com/en-us/azure/storage/common/storage-use-emulator).  
 - Azurite. You can start it from ./docker/azurite/docker-compose.yml file.
