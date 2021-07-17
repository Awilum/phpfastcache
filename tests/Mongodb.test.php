<?php

/**
 * @author Khoa Bui (khoaofgod)  <khoaofgod@gmail.com> https://www.phpfastcache.com
 * @author Georges.L (Geolim4)  <contact@geolim4.com>
 */

use Phpfastcache\CacheManager;
use Phpfastcache\Drivers\Mongodb\Config;
use Phpfastcache\Exceptions\PhpfastcacheDriverCheckException;
use Phpfastcache\Exceptions\phpFastCacheDriverException;
use Phpfastcache\Tests\Helper\TestHelper;

chdir(__DIR__);
require_once __DIR__ . '/../vendor/autoload.php';
$testHelper = new TestHelper('Mongodb driver');

try{
    $cacheInstance = CacheManager::getInstance('Mongodb', new Config([
        'databaseName' => 'pfc_test',
        'username' => 'travis',
        'password' => 'test',
    ]));
}catch(PhpfastcacheDriverCheckException $exception){
    $testHelper->exceptionHandler($exception);
    $testHelper->terminateTest();
}


$cacheKey = str_shuffle(uniqid('pfc', true));
$cacheValue = str_shuffle(uniqid('pfc', true));

try{
    $item = $cacheInstance->getItem($cacheKey);
    $item->set($cacheValue)->expiresAfter(300);
    $cacheInstance->save($item);
    $testHelper->assertPass('Successfully saved a new cache item into Mongodb server');
}catch(phpFastCacheDriverException $e){
    $testHelper->assertFail('Failed to save a new cache item into Mongodb server with exception: ' . $e->getMessage());
}

try{
    unset($item);
    $cacheInstance->detachAllItems();
    $item = $cacheInstance->getItem($cacheKey);

    if($item->get() === $cacheValue){
        $testHelper->assertPass('Getter returned expected value: ' . $cacheValue);
    }else{
        $testHelper->assertFail('Getter returned unexpected value, expecting "' . $cacheValue . '", got "' . $item->get() . '"');
    }
}catch(phpFastCacheDriverException $e){
    $testHelper->assertFail('Failed to save a new cache item into Mongodb server with exception: ' . $e->getMessage());
}

try{
    unset($item);
    $cacheInstance->detachAllItems();
    $cacheInstance->clear();
    $item = $cacheInstance->getItem($cacheKey);

    if(!$item->isHit()){
        $testHelper->assertPass('Successfully cleared the Mongodb server, no cache item found');
    }else{
        $testHelper->assertFail('Failed to clear the Mongodb server, a cache item has been found');
    }
}catch(phpFastCacheDriverException $e){
    $testHelper->assertFail('Failed to clear the Mongodb server with exception: ' . $e->getMessage());
}

try{
    $item = $cacheInstance->getItem($cacheKey);
    $item->set($cacheValue)->expiresAfter(300);
    $cacheInstance->save($item);

    if($cacheInstance->deleteItem($item->getKey())){
        $testHelper->assertPass('Deleter successfully removed the item from cache');
    }else{
        $testHelper->assertFail('Deleter failed to remove the item from cache');
    }
}catch(phpFastCacheDriverException $e){
    $testHelper->assertFail('Failed to remove a cache item from Mongodb server with exception: ' . $e->getMessage());
}

$testHelper->terminateTest();
