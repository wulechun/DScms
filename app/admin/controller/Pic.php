<?php

namespace app\admin\controller;
class Pic extends AdminControl
{
    /**
     * 图片上传
     */
    public function upload()
    {
        if (!empty($_FILES['file']['name'])) {

            $pic_type_id = intval(input('param.pic_type_id'));
            $pic_type = input('param.pic_type');
            switch ($pic_type){
                case 'product':
                    $pic_type_url = ATTACH_PRODUCT;
                    break;
                case 'cases':
                    $pic_type_url = ATTACH_CASES;
                    break;
                case 'news':
                    $pic_type_url = ATTACH_NEWS;
                    break;
                default:
                    break;
            }
            $upload_file = BASE_UPLOAD_PATH . DIRECTORY_SEPARATOR . $pic_type_url;
            $file = request()->file('file');
            $file_config = array(
                    'disks' => array(
                        'local' => array(
                            'root' => $upload_file
                        )
                    )
                );
                config($file_config, 'filesystem');
                try {
                    validate(['image' => 'fileExt:' . ALLOW_IMG_EXT])
                            ->check(['image' => $file]);
                    $save_name = \think\facade\Filesystem::putFile('', $file, 'uniqid');
                    $file_name = $_FILES['file']['name'];
                    list($width, $height, $type, $attr) = getimagesize($upload_file . DIRECTORY_SEPARATOR . $save_name);
                $insert = array(
                    'pic_type' => $pic_type,
                    'pic_type_id' => $pic_type_id,
                    'pic_name' => $file_name,
                    'pic_cover' => $save_name,
                    'pic_size' => intval($_FILES['file']['size']),
                    'pic_time' => TIMESTAMP,
                );
                $result = model('pic')->addpic($insert);
                $file_url = "get_".$pic_type."_img";
                if ($result) {
                    $data = array(
                        'file_id' => $result,
                        'file_name' => $file_name,
                        'file_url' => $file_url($save_name)
                    );
                    $output = json_encode($data);
                    echo $output;
                }
                } catch (\Exception $e) {
//                    $this->error($e->getMessage());
                }
        }
    }

    /**
     * 删除图片
     */
    function del()
    {
        $pic_id = intval(input('param.file_id'));
        $pic_type = intval(input('param.pic_type'));
        if ($pic_id > 0) {
            $result = model('pic')->delPic(array(array('pic_id' ,'=', $pic_id)),$pic_type);
            if ($result > 0) {
                echo 'true';
                exit;
            }
        }
        echo 'false';
    }
}