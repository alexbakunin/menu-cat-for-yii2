<?php

namespace app\components;

use app\models\Category;
use yii\base\Widget;

class MenuCategoryWidget extends Widget
{

    public $tpl;
    public $ul_class;
    public $all_cat;
    public $tree;
    public $menuHtml;

    public function init()
    {
        parent::init();
        // class и шаблон для Меню Категорий по умолчанию
        if($this->ul_class === null){
            $this->ul_class = 'menu-cat';
        }
        if($this->tpl === null){
            $this->tpl = 'menu_cat';
        }
        $this->tpl .= '.php';
    }

    public function run()
    {
        // get cache
        $menu = \Yii::$app->cache->get('category');
        if($menu){
            return $menu;
        }

        $this->all_cat = Category::find()->select('id, parent_id, title')->indexBy('id')->asArray()->all();
        $this->tree = $this->getTree();
        $this->menuHtml = '<ul class="' . $this->ul_class . '">';
        $this->menuHtml .= $this->getMenuHtml($this->tree);
        $this->menuHtml .= '</ul>';

        // set cache
        \Yii::$app->cache->set('category', $this->menuHtml, 3600);
        return $this->menuHtml;
    }

    protected function getTree(){
        $tree = [];
        foreach ($this->all_cat as $id=>&$node) {
            if (!$node['parent_id'])
                $tree[$id] = &$node;
            else
                $this->all_cat[$node['parent_id']]['children'][$node['id']] = &$node;
        }
        return $tree;
    }

    protected function getMenuHtml($tree){
        $str = '';
        foreach ($tree as $category) {
            $str .= $this->catToTemplate($category);
        }
        return $str;
    }

    protected function catToTemplate($category){
        ob_start();
        include __DIR__ . '/menu_cat_tpl/' . $this->tpl;
        return ob_get_clean();
    }

}