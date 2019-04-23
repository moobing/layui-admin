<?php


namespace thans\layuiAdmin\controller;


use thans\layuiAdmin\facade\Json;
use thans\layuiAdmin\facade\Utils;
use thans\layuiAdmin\Form;
use thans\layuiAdmin\Table;
use think\Exception;
use think\Request;
use thans\layuiAdmin\model\Menu as MenuModel;

class Menu
{
    public function index(Request $request)
    {
        if ($request->isAjax()) {
            list($where, $order, $page, $limit) = Utils::buildParams('name|url|permission');
            $menu = new MenuModel();
            $list = $menu->tree();
            $count = $menu->where($where)->count();
            Json::success('success', $list, 200, ['total' => $count]);
        }
        $tb = new Table();
        $tb->url(url('thans\layuiAdmin\controller\Menu/index'));
        $tb->column('id', 'ID');
        $tb->column('label', '菜单名称', 300);
        $tb->icon();
        $tb->column('icon', 'ICON', 200, 'icon', ['align' => 'center']);
        $tb->column('uri', 'URI', 200);
        $tb->column('permission', '权限绑定', 200);

        $tb->action('新增菜单', url('thans\layuiAdmin\controller\Menu/create'));

        $tb->tool('编辑', url('thans\layuiAdmin\controller\Menu/edit', 'id={{ d.id }}'), 'formLayer');
        $tb->title('菜单管理');

        return $tb->render();
    }

    public function create()
    {
        return $this->buildForm(url('thans\layuiAdmin\controller\Menu/save'));
    }

    public function save(Request $request)
    {
        $data = $request->param();
        try {
            $validate = new \thans\layuiAdmin\validate\Menu();

            if (!$validate->check($data)) {
                Json::error($validate->getError());
            }
            $menu = new MenuModel();
            $menu->allowField(true)->save($data);
            Json::success('保存成功');
        } catch (Exception $e) {
            Json::error($e->getMessage(), 500);
        }
    }

    public function edit(Request $request, $id)
    {
        $menu = MenuModel::get($id);
        return $this->buildForm(url('thans\layuiAdmin\controller\Menu/update', 'id=' . $id), 'PUT', $menu);
    }

    public function update(Request $request, $id)
    {
        $data = $request->param();
        try {
            $validate = new \thans\layuiAdmin\validate\Menu();

            if (!$validate->check($data)) {
                Json::error($validate->getError());
            }
            $menu = new MenuModel();
            $menu = $menu->get($id);
            $menu->allowField(true)->save($data);
            Json::success('更新成功');
        } catch (Exception $e) {
            Json::error($e->getMessage(), 500);
        }
    }


    private function buildForm($url, $method = 'POST', $data = [])
    {
        $form = new Form();
        $form->method($method);
        $menu = new MenuModel();
        $form->data($data);
        $form->url($url);
        $parent = [];
        $parent[] = ['val' => 0, 'title' => '根菜单'];
        foreach ($menu->tree() as $val) {
            $parent[] = ['val' => $val['id'], 'title' => $val['label']];
        }
        $form->select()->name('parent_id')->label('上级菜单')->options($parent);
        $form->text()->name('name')->label('菜单名称')->rules('required');
        $form->icon()->name('icon')->label('ICON')->rules('required', 'ICON必须选择')->value('layui-icon-circle-dot');
        $form->number()->name('order')->label('排序')->value(1000);
        $form->text()->name('uri')->label('URI')->placeholder('请输入URI');
        $permission = [];
        $permission[] = ['val' => '', 'title' => '不绑定权限'];
        $form->select()->name('permission')->label('权限选择')->options($permission);
        $status = [];
        $status[] = ['val' => 0, 'title' => '启用'];
        $status[] = ['val' => 1, 'title' => '禁用'];
        $form->select()->name('status')->label('状态')->options($status);
        return $form->render();
    }
}