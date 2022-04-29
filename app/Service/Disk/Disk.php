<?php


namespace App\Service\Disk;


use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class Disk
{
    public $msg;
    private Filesystem $Disk;
    private $baseDirectory = null;
    private $base = '';


    /**
     * @Notes: 创建文件或目录
     * @Interface create
     * @param int $type 1 => 文件 0 : => 目录
     * @param $name string
     * @param $content
     * @return bool
     * @Author: TS
     * @Time: 2020-06-19   13:09
     */
    private function create(string $name,$content = null) : bool
    {
        $type = 1;
        if($content === null){
            $type = 0;
        }
        //检查文件/目录是否重复
        if ($this->check($name, $type)) {
            $this->msg = ($type ? '文件' : '目录') . '已存在';
            return false;
        }
        if ($type) {
            $this->Disk->put($name, $content);
        } else {
            $this->Disk->makeDirectory($name);
        }
        $this->msg = ($type ? '文件' : '目录') . '：' . $name . ' 创建成功';
        return true;
    }

    /**
     * @Notes: 创建文件
     * @Interface mkFile
     * @param $path string 路径
     * @param $content string 内容
     * @return bool
     * @Author: TS
     * @Time: 2020-06-19   13:11
     */
    public function mkFile(string $path,string $content) : bool
    {
        return $this->create($path, $content);
    }

    /**
     * @Notes: 创建文件夹
     * @Interface mkDir
     * @param $path string 路径
     * @param $cont string ent
     * @return bool
     * @Author: TS
     * @Time: 2020-06-19   13:11
     */
    public function mkDir(string $path) :bool
    {
        return $this->create( $path);
    }

    /**
     * @Notes: 获取文件内容
     * @Interface get
     * @param $path string 文件路径
     * @return null|string
     * @throws \League\Flysystem\FileNotFoundException
     * @Author: TS
     * @Time: 2020-06-19   13:12
     */
    public function get($path) : ?string
    {
        //检查文件是否存在
        if ($this->Disk->exists($path)) {
            $file = $this->Disk->get($path);
            return $file;
        }
        $this->msg = '文件不存在';
        return null;
    }


    /**
     * @Notes: 更新文件内容
     * @Interface update
     * @param $path string 文件路径
     * @param $content string 内容
     * @return bool
     * @throws \League\Flysystem\FileNotFoundException
     * @Author: TS
     * @Time: 2020-06-19   13:13
     */
    public function update(string $path,string $content) :bool
    {
        //检查文件是否存在
        if ($this->Disk->exists($path)) {
            $file = $this->Disk->update($path, $content);
            $this->msg = '修改成功';
            return true;
        }
        $this->msg = '文件不存在';
        return false;
    }

    /**
     * @Notes: 移动目标
     * @Interface move
     * @param $path
     * @param $newpath
     * @return bool
     * @Author: TS
     * @Time: 2020-06-19   13:14
     */
    public function move(string $path,string $newpath) : bool
    {
        //检查文件/目录是否存在
        if (!$this->check($path, 2)) {
            $this->msg = '目标不存在';
            return false;
        }
        if ($this->Disk->exists($path)) {
            $file = $this->Disk->move($path, $newpath);
            $this->msg = '移动成功';
            return true;
        }
        $this->msg = '移动失败';
        return false;
    }


    /**
     * @Notes: 复制目标
     * @Interface copy
     * @param $path string 当前路径
     * @param $newpath string 复制路径
     * @return bool
     * @Author: TS
     * @Time: 2020-06-19   13:14
     */
    public function copy(string $path,string $newpath) :bool
    {
        //检查文件/目录是否存在
        if (!$this->check($path, 2)) {
            $this->msg = '目标不存在';
            return false;
        }
        if ($this->Disk->exists($path)) {
            $file = $this->Disk->copy($path, $newpath);
            $this->msg = '拷贝成功';
            return true;
        }
        $this->msg = '拷贝失败';
        return false;
    }


    /**
     * @Notes: 向文件追加内容
     * @Interface fill
     * @param $path string 路径
     * @param $content string 追加内容
     * @param int $type 追加方式 1 => 在后面追加内容 0 => 在前面追加内容
     * @return bool
     * @Author: TS
     * @Time: 2020-06-19   13:16
     */
    public function fill(string $path,string $content,int $type = 1) :bool
    {
        //检查文件是否存在
        if ($this->Disk->exists($path)) {
            if ($type === 1) {
                $this->Disk->append($path, $content);
            } else {
                $this->Disk->prepend($path, $content);
            }
            $this->msg = '添加成功';

            return true;
        }
        $this->msg = '文件不存在';
        return false;
    }


    /**
     * @Notes: 删除目标
     * @Interface del
     * @param $path string 目标路径
     * @param int $type 目标类型 1 => 文件  0 => 目录
     * @return array
     * @Author: TS
     * @Time: 2020-06-19   13:17
     */
    public function del(string $path,int $type = 1):bool
    {
        //检查文件/目录是否存在
        if (!$this->check($path, 2)) {
            $this->msg = ($type ? '文件' : '目录') . '不已存在';
            return false;
        }
        if ($type) {
            $this->Disk->delete($path);
        } else {
            $this->Disk->deleteDirectory($path);
        }
        $this->msg = '删除成功';
        return true;

    }

    /**
     * 获取指定目录下的子目录与文件
     * @param string $directory 指定目录
     * @return array  res
     */
    public function catalog(string $directory = '/') : array
    {
        $disk = Storage::disk('local');
        // 获取目录下的文件
        $files = $this->Disk->files($directory);
        $directories = $this->Disk->directories($directory);
        foreach ($files as $key => $value) {
            $size = Storage::size($value);
            $time = Storage::lastModified($value);
            $time = Carbon::createFromTimestamp($time);
            $files[$key] = [
                'name' => str_replace($directory . '/', '', $value),
                'size' => $size,
                'time' => $time,
                'type' => 'file',
                'path' => $value
            ];
        }
        foreach ($directories as $key => $value) {
            $directories[$key] = [
                'name' => str_replace($directory . '/', '', $value),
                'type' => 'directory',
                'path' => $value
            ];
        }
        return array_merge($directories, $files);
    }


    /**
     * @Notes:  检查目标是否存在
     * @Interface check
     * @param $path string 路径
     * @param int $type 目标类型 1 => 文件 2 => 文件或目录
     * @return bool
     * @Author: TS
     * @Time: 2020-06-19   13:20
     */
    public function check(string $path, int $type = 1) :bool
    {
        $flag = false;
        if ($type) {
            if ($this->Disk->exists($path)) {
                $flag = true;
            }
        } else if ($type == 2) {
            if ($this->Disk->exists($path) || File::isDirectory($this->baseDirectory . '/' . $path)) {
                $flag = true;
            }
        } else {
            if (File::isDirectory($this->baseDirectory . '/' . $path)) {
                $flag = true;
            }
        }
        return $flag;
    }

    /**
     * @Notes:解压
     * @Interface decZip
     * @param $path
     * @param string $decPath
     * @return array
     * @Author: TS
     * @Time: 2020-06-19   13:21
     */
    public function decZip(string $path,string $decPath = '') : bool
    {
        $zip = new \ZipArchive;
        $zip->open($this->base_path($path), \ZipArchive::CREATE);
        $zip->extractTo($this->base_path($decPath));
        $zip->close();
        $this->msg = '解压成功';
        return true;
    }

    /**
     * @Notes: 压缩
     * @Interface enZip
     * @param $path
     * @param $filename
     * @param array $option
     * @return bool
     * @Author: TS
     * @Time: 2020-06-19   13:22
     */
    public function enZip(string $path,string $filename,array $option = []) :bool
    {
        $zip = new \ZipArchive();
        $flag = false;
        if ($zip->open($this->base_path($filename . '.zip'), \ZipArchive::CREATE | \ZipArchive::OVERWRITE)) {
            if (is_array($path)) {
                foreach ($path as $key => $value) {
                    $this->addFileToZip($this->base_path($value), $zip, $option);//
                }
            } else {
                $this->addFileToZip($this->base_path($path), $zip, $option);//
            }
            //调用方法，对要打包的根目录进行操作，并将ZipArchive的对象传递给方法
            $zip->close(); //关闭处理的zip文件
            $flag = true;
        }
        return $flag;
    }

    /**
     * @Notes: 向压缩文件添加文件
     * @Interface addFileToZip
     * @param $path
     * @param $zip
     * @param $option
     * @Author: TS
     * @Time: 2020-06-19   13:23
     */
    public function addFileToZip(string $path,object $zip,array $option) : void
    {
        $handler = opendir($path);

        while (($filename = readdir($handler)) !== false) {
            if ($filename != "." && $filename != ".." && $filename != "...") {//文件夹文件名字为'.'和‘..’，不要对他们进行操作
                if (!empty($option['ignore'])) {
                    if (in_array($this->getZipFilePath($path, $filename), $option['ignore'])) {
                        continue;
                    }

                }
                if (is_dir($path . "/" . $filename)) {
                    $this->addFileToZip($path . "/" . $filename, $zip, $option);
                } else {
                    $zipFileName = $this->getZipFilePath($path, $filename);
                    if (!empty($option['lastTime'])) {
                        if (strtotime($option['lastTime']) > strtotime($this->getFileUpdateTime($zipFileName))) {
                            continue;
                        }
                    }
//                    if (!empty($option['progress'])) {
//                        appendProgress($option['progress'], "正在压缩" . $zipFileName . "\n");
//                    }
                    $zip->addFile($path . "/" . $filename, $zipFileName);
                }
            }
        }

        @closedir($path);
    }

    /**
     * @Notes: 获取压缩文件目录
     * @Interface getZipFilePath
     * @param $path
     * @param $filename
     * @return bool|string
     * @Author: TS
     * @Time: 2020-06-19   13:23
     */
    private function getZipFilePath(string $path,string $filename) :string
    {
        $zipFileName = explode($this->base_path(), $path)[1] . '/' . $filename;
        $zipFileName = (substr($zipFileName, 0, 1) == '/' ? substr($zipFileName, 1, strlen($zipFileName) - 1) : $zipFileName);
        return $zipFileName;
    }

    /**
     * @Notes: 获取文件更新时间
     * @Interface getFileUpdateTime
     * @param $path
     * @return Carbon|int
     * @Author: TS
     * @Time: 2020-06-19   13:24
     */
    public function getFileUpdateTime(string $path) : ?string
    {
        $time = Storage::lastModified($path);
        $time = Carbon::createFromTimestamp($time);
        return $time;
    }

    /**
     * @Notes:下载文件
     * @Interface download
     * @param $url
     * @param string $save_dir
     * @param string $filename
     * @param int $type
     * @return array|bool
     * @Author: TS
     * @Time: 2020-06-19   13:25
     */
    public function download(string $url,string $save_dir = '',string $filename = '',int $type = 1) : ?bool
    {
        if (trim($url) == '') {
            return false;
        }
        if (trim($save_dir) == '') {
            $save_dir = './';
        }
        if (0 !== strrpos($save_dir, '/')) {
            $save_dir .= '/';
        }
        if (empty($filename)) {
            $f_arr = explode('/', $url);
            $filename = $f_arr[count($f_arr) - 1];
        }
        $this->del($save_dir . $filename);
        $save_dir = $this->base_path($save_dir);
        //创建保存目录
        if (!file_exists($save_dir) && !mkdir($save_dir, 0777, true)) {
            return false;
        }
        $content = '';
        //获取远程文件所采用的方法
        if ($type) {
            $ch = curl_init();
            $timeout = 50000;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            $content = curl_exec($ch);
            curl_close($ch);
        } else {
            ob_start();
            readfile($url);
            $content = ob_get_contents();
            ob_end_clean();
        }
//        $size = strlen($content);

        //文件大小
        $fp2 = @fopen($save_dir . $filename, 'a');
        fwrite($fp2, $content);
        fclose($fp2);
        unset($content, $url);
        $resPath = str_replace(base_path(), '', $save_dir . $filename);
        $resPath = str_replace('\\', '', $resPath);
        $resPath = str_replace('//', '/', $resPath);
        return array(
            'file_name' => $filename,
            'save_path' => $resPath
        );
    }


    /**
     * @Notes:获取文件夹大小
     * @Interface getDirSize
     * @param $dir
     * @return false|int
     * @Author: TS
     * @Time: 2020-06-19   13:25
     */
    public function getDirSize(string $dir):int
    {
        static $totalSize = 0;
        $dir = $this->base_path($dir);
        $ds = opendir($dir);
        while ($file = readdir($ds)) {
            $path = $dir . '/' . $file;
            if (is_dir($file)) {
                if ($file != "." && $file != "..") {
                    $totalSize += $this->getDirSize($file);
                }
            } else {
                $totalSize += filesize($path);
            }
        }
        return $totalSize;
    }

    /**
     * @Notes:获取文件夹文件数量
     * @Interface getDicFileNum
     * @param $dir
     * @return int
     * @Author: TS
     * @Time: 2020-06-19   13:26
     */
    public function getDicFileNum(string $dir) : int
    {
        static $totalNum = 0;
        $dir = $this->base_path($dir);
        $ds = opendir($dir);
        while ($file = readdir($ds)) {
            if (is_dir($file)) {
                if ($file != "." && $file != "..") {
                    $totalNum += $this->getDicFileNum($file);
                }
            } else {
                $totalNum += 1;
            }
        }
        return $totalNum;
    }


    public function base_path(string $filename = '')
    {
        if ($this->base == 'base') {
            return str_replace('server', '', base_path()) . $filename;
        }
        return base_path($filename);
    }


    public function __construct($baseLocal = 'local')
    {
        $this->base = $baseLocal;
        $this->Disk = Storage::disk($baseLocal);
        $this->baseDirectory = base_path();
    }
}