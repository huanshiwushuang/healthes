<?php

namespace app\admin\model;

use think\Model;
use think\Config;
use think\Request;
use think\Validate;

/**
 * Description of Action
 * 用户行为模型
 * @author yaozh
 */
class Admin extends Model {

    protected $autoCheckFields =false;

    protected $rule = [
        'username' => "require|max:25",
        'password' => "require|confirm",
    ];
    protected $msg = [
        'username.require' => '管理员名称不能为空',
        'username.max' => '管理员名称最多不能超过25个字符',
        'password.require' => '管理员密码不能为空',
        'password.confirm' => '两次密码不一致'
    ];

    /**
     * 行为列表
     * @param array $map_tmp 临时条件,后期会合并
     * @param string $field 查询的字段
     * @param string $order 排序 默认id asc
     * @author yaozh
     */

    public function actionList(array $map_tmp = [],string $or_map = '', $field = true, string $order = 'id ASC'): array {
        $map = array_merge(['status' => ['neq', '-1']], $map_tmp);
        $object = $this::where($map)->where($or_map)->order($order)->field($field)->paginate(Config::get('list_rows') ?? 10);
        
        return $object ? array_merge($object->toArray(), ['page' => $object->render()]) : [];
    }

    /**
     * 修改状态
     * @param int|array $map 数据的ID或者ID组
     * @param array $data 要修改的数据
     * @author yaozh
     */

    public function setStatus($map = null, $data = null) {
        if (empty($map) || empty($data)) {
            return false;
        }
        return $this::where($map)->update($data);
    }

    /**
     * 查询行为详情
     * @param int $id 行为详情
     * @author yaozh
     */

    public function edit(int $id = 0): array {
        $object = $this::get(function($query)use($id) {
                    $query->where('id', $id);
                });
        return $object ? $object->toArray() : [];
    }

    /**
     * 用户更新或者添加行为
     * @author yaozh
     */

    public function renew() {
        $data = Request::instance()->post();
        $data['head'] = $data['head'] ? $data['head'] : $data['old_head'];
        unset($data['old_head']);
        $validate = new Validate($this->rule, $this->msg);

        if (!$validate->check($data)) {
            // 验证失败 输出错误信息
            return $validate->getError();
        }

        unset($data['password_confirm']);
        $data['addtime'] = time();
        $object = (int) $data['id'] ? $this::update($data) : $this::create($data);

        return $object ? $object->toArray() : null;
    }

}
