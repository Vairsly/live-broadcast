<?php


namespace App\HttpController\User;


use App\Base\FrontUserController;
use App\lib\ClassArr;
use App\Utility\Message\Status;

class Upload extends FrontUserController
{
    public $needCheckToken = true;
    public $isCheckSign = true;
    function index()
    {
        // TODO: Implement index() method.
        $request = $this->request();
        $file = $request->getUploadedFile('file');
        $sUploadType = $request->getRequestParam('type');
        if (!$sUploadType || !in_array($sUploadType, ['avatar', 'system', 'option', 'other'])) {
            return $this->writeJson(Status::CODE_ERR, '未知的上传类型');
        }
        if (!$file) {
            return $this->writeJson(Status::CODE_ERR, '上传图片为空');
        }
        $isImage = getimagesize($file->getTempName());
        if ($isImage) {
            $type = 'image';
        }

        if (empty($type)) {
            return $this->writeJson(400, '上传文件不合法');
        }
        try {
            $classObj = new ClassArr();
            $classStats = $classObj->uploadClassStat();
            $uploadObj = $classObj->initClass($type, $classStats, [$request, $type]);
            $uploadObj->upload_type = $sUploadType;
            $file = $uploadObj->upload();
        } catch (\Exception $e) {
            return $this->writeJson(400, $e->getMessage(), []);
        }
        if (empty($file)) {
            return $this->writeJson(400, "上传失败", []);
        }

        $data = [
            'url' => $file,
        ];
        //return $this->writeJson(200, "OK", $data);
        $data = ['code' => Status::CODE_OK, 'data' => [
            'src' => $file,
            'title' => '上传图片'
        ]];
        $this->dataJson($data);
    }
}