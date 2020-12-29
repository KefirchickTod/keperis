<?php


namespace src\Core;


use src\Collection;
use src\Http\Stream;

/**
 * Class Cache
 * @package src\Core
 * @author Zahar Pylypchuck
 * @version 0.1
 */
class Cache
{


    const MODE_SERIALIZE = 1;
    const MODE_JSON = 2;
    const CACHE_DIR = ROOT_PATH . '/cache/xlsx';


    /**
     * Mode of convert to SERIALIZE or json
     * @var int
     */
    private $mode;
    /**
     * Converting input data
     * @var string
     */
    private $data;

    /**
     * Unique session id
     * @var string
     */
    private $sessid;

    /**
     *
     * @var Stream
     */
    private $stream;

    /**
     * Valid isset name in $_SESSION for delete temp file
     * @var string
     */
    private $name;

    /**
     * Cache constructor.
     * @param $data Collection|array|null
     */
    public function __construct($data = [], int $mode = Cache::MODE_JSON)
    {

        if (!isset($_SESSION['cache_file'])) {
            $_SESSION['cache_file'] = null;
        }

        $this->name = $_SESSION['cache_file'];
        $this->mode = $mode;
        $this->sessid = $_COOKIE['PHPSESSID'];
        $this->data = $this->proccess($data);
    }

    /**
     * Validation of data
     * @param $data
     * @return false|string
     */
    private function proccess($data)
    {
        if ($this->mode === Cache::MODE_SERIALIZE) {
            $data = serialize($data);
        }
        if ($this->mode === Cache::MODE_JSON) {
            $data = json_encode($data);
        }

        return $data;
    }

    /**
     * @param array|Collection
     */
    public function setData($data)
    {
        if ($data instanceof Collection) {
            $data = (array)$data->toArray();
        }

        $this->data = $this->proccess($data);
    }

    /**
     * Create and write in temp file file  (save to cache dir)
     */
    public function run()
    {
        if (!$this->cleanFiles()) {
            error_log("Cant clean file");
        }
        $this->stream = $this->createStream();

        $uri = $this->stream->getMetadata('uri');

        $name = pathinfo($uri, PATHINFO_FILENAME);

        if (!$this->stream->isWritable()) {
            throw new \RuntimeException("File for cache isn writble");
        }
        $this->stream->write($this->data);

        $_SESSION['cache_file'] = $name;

    }

    /**
     * Delete temp file from cache dir
     * @return bool
     */
    private function cleanFiles()
    {
        if (!$this->name) {
            return true;
        }

        $name = self::CACHE_DIR . "/{$this->name}.tmp";
        if(file_exists($name)){
            return unlink(self::CACHE_DIR . "/{$this->name}.tmp");
        }
        return true;

    }

    /**
     * @return Stream
     */
    private function createStream()
    {

        $tmpfile = tempnam(self::CACHE_DIR, $this->sessid);

        $stream = fopen($tmpfile, 'r+');
        rewind($stream);

        return new Stream($stream);
    }

    /**
     * @param string|null $name
     * @return false|array
     */
    public function get(string $name = null)
    {
        $name = $name ?: "{$this->name}.tmp";
        $this->stream = $this->getCreatedStream($name);
        if (!$this->stream->isWritable()) {
            throw new \RuntimeException("File for cache {$name} isn writble");
        }

        $content = $this->stream->getContents();
        if ($this->mode === Cache::MODE_SERIALIZE) {
            $content = unserialize($content);
        }else if($this->mode === Cache::MODE_JSON) {
            $content = json_decode($content, true);
        }



        return $content;


    }

    /**
     * @param string $name
     * @return Stream
     */
    private function getCreatedStream(string $name)
    {
        $file = fopen(self::CACHE_DIR . "/$name", 'r+');
        rewind($file);
        return new Stream($file);
    }

}