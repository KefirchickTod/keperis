<?php


use App\DictionaryCollection;
use App\Models\Dictionary\DictionaryModel;
use PHPUnit\Framework\TestCase;
use src\Collection;

class DictionaryCollectionTest extends TestCase
{

    public function testBoot()
    {

        $collection = new Collection();


        $dictionary = new DictionaryCollection();


        $booted = DictionaryCollection::boot();

        $this->assertInstanceOf(get_class($dictionary), $booted);


    }
}
