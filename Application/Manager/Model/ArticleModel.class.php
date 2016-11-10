<?php
namespace Manager\Model;

/**
 * Class ArticleModel
 * @package Manager\Model
 * 文章咨询模型
 */
class ArticleModel extends BaseModel {

    /**
     * @var array
     * 自动验证规则
     */
    protected $_validate = array (
        array('title', 'require', '文章标题未填写', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('title', '0,90', '标题长度在0--90位', self::EXISTS_VALIDATE, 'length', self::MODEL_BOTH),
        array('cate_id', 'require', '文章分类未选择', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('content', 'require', '文章内容不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('link', '/http(s)?:\/\/([\w-]+\.)+[\w-]+(\/[\w- .\/?%&=]*)?/', '连接地址非法', self::VALUE_VALIDATE, 'regex', self::MODEL_BOTH),
    );

    /**
     * @var array
     * 自动完成规则
     */
    protected $_auto = array (
        array('create_time', 'time', self::MODEL_INSERT, 'function'),
        array('update_time', 'time', self::MODEL_UPDATE, 'function'),
    );

    /**
     * @param array $param  综合条件参数
     * @return array
     */
    function getList($param = array()) {
        if(!empty($param['page_size'])) {
            $total      = $this->alias('art')->where($param['where'])->count();
            $Page       = $this->getPage($total, $param['page_size'], $param['parameter']);
            $page_show  = $Page->show();
        }

        $model  = $this->alias('art')
                        ->field('art.*,art_cate.name cate_name,art_cate.status cate_status')
                        ->where($param['where'])
                        ->join(array(
                            'LEFT JOIN '.C('DB_PREFIX').'article_category art_cate ON art_cate.id = art.cate_id AND art_cate.status < 9',
                        ))
                        ->order($param['order']);

        //是否分页
        !empty($param['page_size'])  ? $model = $model->limit($Page->firstRow,$Page->listRows) : '';

        $list = $model->select();

        return array('list'=>$list,'page'=>!empty($page_show) ? $page_show : '');
    }

    /**
     * @param $param
     * @return mixed
     */
    function findRow($param = array()) {

        $row = $this->alias('art')
                    ->field('art.*,art_cate.name cate_name')
                    ->where($param['where'])
                    ->join(array(
                        'LEFT JOIN '.C('DB_PREFIX').'article_category art_cate ON art_cate.id = art.cate_id AND art_cate.status < 9',
                    ))
                    ->find();

        return $row;
    }
}