<?php
namespace LaraCrud\Helpers;

use Illuminate\Http\Request;
use Intervention\Image\Facades\Image as ImageLib;
use Storage;

/**
 * Tuhin Bepari <digitaldreams40@gmail.com>
 */
trait Image
{
    /**
     * Upload multiple file from requests
     *
     * @param Request $request
     * @return bool
     */
    public function upload(Request $request)
    {
        $models = [];
        $column = $this->getColumnName();
        if ($request->hasFile($column)) {
            $document = $request->file($column);
            if (!$document->isValid()) {
                return false;
            }
            $fileName = uniqid(rand(1000, 99999)) . '.' . $document->getClientOriginalExtension();
            $this->{$column} = $document->storeAs($this->getStoragePath(), $fileName, 'public');
            $this->resize($this->getMainWidth(), $this->getStoragePath(), $this->getMainHeight());
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getColumnName()
    {
        return 'image';
    }

    /**
     *
     */
    public function getStoragePath()
    {
        defined('static::STORAGE_PATH') ? static::STORAGE_PATH : $this->getTable();
    }

    /**
     *
     */
    public function getThumbnailPath()
    {
        defined('static::THUMBNAIL_PATH') ? static::THUMBNAIL_PATH : $this->getTable()."/thumbs";
    }

    /**
     * @return mixed
     */
    public function columnValue()
    {
        $column = $this->getColumnName();
        return $this->{$column};
    }

    public function hasImage()
    {
        $value = $this->columnValue();
        return !empty($value) && $this->isExists();
    }

    /**
     * return base name of the file. e.g. documents/user.png to user.png
     * @return mixed
     */
    public function getBaseName()
    {
        return pathinfo($this->columnValue(), PATHINFO_BASENAME);
    }

    /**
     * get relative path of current document.
     * @return varchar
     */
    public function getPath()
    {
        return '/storage/' . $this->columnValue();
    }

    /**
     * @return mixed
     */
    public function getExtension()
    {
        return pathinfo($this->columnValue(), PATHINFO_EXTENSION);
    }

    /**
     * Get full path of the current document. It can be used in programming
     * @return mixed
     */
    public function getFullPath($thumbnail = false)
    {
        return !empty($thumbnail) ? $this->getThumbPath() : storage_path('app/public/' . $this->columnValue());
    }

    /**
     * Get thumbnail path
     * @return bool|string
     */
    public function getThumbPath()
    {
        if (Storage::disk('public')->exists($this->getThumbnailPath() . "/" . $this->getBaseName())) {
            return config('filesystems.disks.public.root') . '/' . $this->getThumbnailPath() . "/" . $this->getBaseName();
        }
        return false;
    }

    /**
     * Get thumbnail url for web
     *
     * @return bool
     */
    public function getThumbUrl()
    {
        return asset('storage/' . $this->getThumbnailPath() . '/' . $this->getBaseName());
    }

    /**
     * Does file exists on storage
     * @param string $path
     * @return bool
     */
    public function isExists($path = '')
    {
        $path = !empty($path) ? $path : $this->getStoragePath();
        return !empty($this->getBaseName()) && Storage::disk('public')->exists($path . "/" . $this->getBaseName());
    }

    /**
     * Resize an image
     *
     * @param $width int width of the image
     * @param $path relative path to storage
     * @param int|null $height Height of the Image
     * @return  \Intervention\Image\Image
     */
    public function resize($width, $path, $height = null)
    {
        $fullPath = $this->makePath($path);
        $img = ImageLib::make($this->getFullPath());

        $img->fit($width, $height, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
        return $img->save($fullPath . "/" . $this->getBaseName());
    }

    /**
     * Make a Thumbnail  100*150
     * @return bool|\Intervention\Image\Image
     */
    public function makeThumb()
    {
        if (!$this->isExists()) {
            return false;
        }
        return $this->resize($this->getThumbWidth(), $this->getThumbnailPath(), $this->getThumbHeight());
    }

    /**
     * @return int
     */
    public function getThumbWidth()
    {
        return defined('static::THUMBNAIL_WIDTH') ? static::THUMBNAIL_WIDTH : 200;
    }

    /**
     * @return int
     */
    public function getThumbHeight()
    {
        return defined('static::THUMBNAIL_HEIGHT') ? static::THUMBNAIL_HEIGHT : 200;
    }

    /**
     * @return int
     */
    public function getMainWidth()
    {
        return defined('static::STORAGE_WIDTH') ? static::STORAGE_WIDTH : 850;
    }

    /**
     * @return int
     */
    public function getMainHeight()
    {
        return defined('static::STORAGE_HEIGHT') ? static::STORAGE_HEIGHT : 500;
    }

    /**
     * Check if path exists if not then make it or return
     * @param string $path
     * @return string
     */
    public function makePath($path = '')
    {
        $path = !empty($path) ? $path : $this->getStoragePath();

        if (!Storage::disk('public')->exists(trim($path, "/"))) {
            Storage::disk('public')->makeDirectory(trim($path, "/"));
        }
        return config('filesystems.disks.public.root') . '/' . $path;
    }

    /**
     * Get mime type of the file
     * @return bool
     */
    public function getMimeType()
    {
        try {
            if (file_exists($this->getPath())) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
                return finfo_file($finfo, $this->getPath());
            }
            return false;
        } catch (\Exception $ex) {
            return false;
        }
    }
}