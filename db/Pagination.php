<?php
/**
 * @date        2017-12-15
 * @author      qingbing<780042175@qq.com>
 * @version     1.0
 */

namespace pf\db;

use pf\core\Core;
use pf\exception\Exception;
use pf\helper\Unit;
use pf\PFBase;

class Pagination extends Core
{
    public $pageSize = 10;
    public $pageVar = 'page';
    public $route = '';
    public $params;
    public $codingClass; // \pf\db\PagingCode
    public $pagingParam = [];

    private $_currentPage; // 当前页码
    private $_totalCount; // 总页数
    private $_paging; // 数据查询实例

    /**
     * 构造函数
     * @param string|Criteria|ActiveRecord $sqlment
     * @param array $params
     * @param mixed $db
     * @throws Exception
     */
    public function __construct($sqlment, $params = [], $db = null)
    {
        // Set the sql type and sqlment.
        if ($sqlment instanceof Criteria) {
            $this->_paging = new CriteriaQuery($sqlment, $params, $db);
        } else if ($sqlment instanceof ActiveRecord) {
            $this->_paging = new ModelQuery($sqlment, $params);
        } elseif (is_array($sqlment)) {
            $sqlment = new Criteria($sqlment);
            $this->_paging = new CriteriaQuery($sqlment, $params, $db);
        } elseif (is_string($sqlment)) {
            $this->_paging = new SqlQuery($sqlment, $params, $db);
        } else {
            throw new Exception(Unit::replace('The function({function})-parameter({parameter}) is wrong.', [
                'function' => get_class($this) . '=>__construct',
                'parameter' => 'sqlment',
            ]));
        }
    }

    /**
     * 返回符合条件的总条数
     * @return int
     */
    public function getTotalCount()
    {
        if (null === $this->_totalCount) {
            $this->_totalCount = $this->_paging->getTotalCount();
        }
        return $this->_totalCount;
    }

    /**
     * 返回符合条件的总条数
     * @return int
     */
    public function getTotalPage()
    {
        $totalPage = ceil($this->getTotalCount() / $this->pageSize);
        return $totalPage > 0 ? $totalPage : 1;
    }

    /**
     * 获取当前查询页码
     * @return int
     */
    public function getCurrentPage()
    {
        if (null === $this->_currentPage) {
            $page = PFBase::app()->getRequest()->getParam($this->pageVar);
            if (null === $page || $page < 1) {
                $page = 1;
            }
            $totalPage = $this->getTotalPage();
            $this->_currentPage = $page > $totalPage ? $totalPage : $page;
        }
        return $this->_currentPage;
    }

    /**
     * 获取显示数据内容
     * @param null|int $page
     * @return array
     */
    public function getData($page = null)
    {
        if (null === $page) {
            $page = $this->getCurrentPage();
        }
        $totalPage = $this->getTotalPage();
        $page = $page > $totalPage ? $totalPage : $page;
        return $this->_paging->getData($page, $this->pageSize);
    }

    /**
     * 获取分页HTML
     * @return string
     * @throws Exception
     */
    public function getCoding()
    {
        $pagingParam = $this->pagingParam;
        $pagingParam['pageVar'] = $this->pageVar;
        if ($this->codingClass) {
            $class = new $this->codingClass($this->getTotalPage(), $this->getCurrentPage(), $pagingParam);
            if (!$class instanceof \pf\interfaces\PagingCode) {
                throw new Exception(Unit::replace('Page-coding must implements interface "{interface}"', [
                    '{interface}' => '\pf\interfaces\PagingCode',
                ]));
            }
        } else {
            $class = new PagingCode($this->getTotalPage(), $this->getCurrentPage(), $pagingParam);
        }
        return $class->getCode();
    }
}

abstract class DbQuery extends Core
{
    /**
     * @var \pf\db\Connection
     */
    public $db;
    public $sqlment;
    public $params;

    /**
     * 构造函数
     * @param mixed $sqlment
     * @param array $params
     * @param mixed $db
     */
    public function __construct($sqlment, $params = [], $db = null)
    {
        if (null === $db) {
            $this->db = PFBase::app()->getDb();
        } else {
            $this->db = PFBase::app()->getComponent($db);
        }
        $this->sqlment = $sqlment;
        $this->params = $params;
    }

    /**
     * 获取显示数据内容
     * @param int $page
     * @param int $pageSize
     * @return array
     */
    abstract public function getData($page, $pageSize);

    /**
     * 返回符合条件的总条数
     * @return int
     */
    abstract public function getTotalCount();
}

class SqlQuery extends DbQuery
{
    /**
     * 获取显示数据内容
     * @param int $page
     * @param int $pageSize
     * @return array
     */
    public function getData($page, $pageSize)
    {
        $limit = ($page - 1) * $pageSize;
        $offset = $pageSize;
        $sql = "{$this->sqlment} LIMIT {$limit}, {$offset}";
        return $this->db->createCommand()
            ->setText($sql)
            ->queryAll($this->params);
    }

    /**
     * 返回符合条件的总条数
     * @return int
     */
    public function getTotalCount()
    {
        return $this->db->createCommand()
            ->setText($this->sqlment)
            ->queryCount($this->params);
    }
}

class CriteriaQuery extends DbQuery
{
    /**
     * 获取显示数据内容
     * @param int $page
     * @param int $pageSize
     * @return array
     */
    public function getData($page, $pageSize)
    {
        $criteria = clone($this->sqlment);
        /* @var Criteria $criteria */
        $criteria->setLimit(($page - 1) * $pageSize);
        $criteria->setOffset($pageSize);
        return $this->db->createFindCommand()
            ->addCriteria($criteria)
            ->queryAll($this->params);
    }

    /**
     * 返回符合条件的总条数
     * @return int
     */
    public function getTotalCount()
    {
        return $this->db->createFindCommand()
            ->addCriteria($this->sqlment)
            ->queryCount($this->params);
    }
}

class ModelQuery extends DbQuery
{
    /**
     * 获取查询标准
     * @param bool|false $clone
     * @return Criteria
     */
    protected function getCriteria($clone = false)
    {
        if (!$this->params instanceof Criteria) {
            $this->params = new Criteria();
        }
        if ($clone) {
            return clone($this->params);
        }
        return $this->params;
    }

    /**
     * 获取显示数据内容
     * @param int $page
     * @param int $pageSize
     * @return array
     */
    public function getData($page, $pageSize)
    {
        $criteria = $this->getCriteria(true);
        /* @var Criteria $criteria */
        $criteria->setLimit(($page - 1) * $pageSize);
        $criteria->setOffset($pageSize);
        $model = $this->sqlment;
        /* @var $model ActiveRecord */
        return $model->findAll($criteria);
    }

    /**
     * 返回符合条件的总条数
     * @return int
     */
    public function getTotalCount()
    {
        $model = $this->sqlment;
        /* @var $model ActiveRecord */
        $criteria = $this->getCriteria(true);
        return $model->count($criteria);
    }
}

class PagingCode extends Core implements \pf\interfaces\PagingCode
{
    public $totalPage; // 总页数
    public $currentPage; // 当前页
    public $pageVar; // 页码标识
    public $pageList = 3; // 分页显示的项目个数
    public $lang = [
        'firstPage' => 'First',
        'prePage' => 'Pre',
        'nextPage' => 'Next',
        'lastPage' => 'Last',
    ];

    private $_params;
    private $_route;

    /**
     * 构造函数
     * @param int $totalPage
     * @param int $currentPage
     * @param array $properties
     */
    public function __construct($totalPage, $currentPage, $properties = [])
    {
        $this->totalPage = $totalPage;
        $this->currentPage = $currentPage;
        $controller = PFBase::app()->getController();
        $action = $controller->getAction();
        $this->configure($properties);
        $this->_route = $controller->getUniqueId() . '/' . $action->getId();
        $this->_params = $controller->getActionParams();
    }

    /**
     * 创建URL
     * @param int $page
     * @return string
     */
    protected function createUrl($page = 1)
    {
        return PFBase::app()->createUrl($this->_route, array_merge($this->_params, [
            $this->pageVar => $page,
        ]));
    }

    /**
     * 构建分页选项 list
     * @param int $start
     * @param int $end
     * @return string
     */
    protected function generateListPaging($start, $end)
    {
        $pagingCode = '';
        for ($i = $start; $i <= $end; $i++) {
            $pagingCode .= '<li><a href="' . $this->createUrl($i) . '">' . $i . '</a></li>' . "\n";
        }
        return $pagingCode;
    }

    /**
     * 返回分页显示代码
     * @return string
     */
    public function getCode()
    {
        if ($this->totalPage == 1) {
            return '';
        }
        $lang = $this->lang;
        $pagingCode = '<ul class="pf-paging">';
        // Generate front paging.
        if ($this->currentPage > 1) {
            $pagingCode .= '<li><a href="' . $this->createUrl(1) . '">' . $lang['firstPage'] . '</a></li>' . "\n";
            $pagingCode .= '<li><a href="' . $this->createUrl($this->currentPage - 1) . '">' . $lang['prePage'] . '</a></li>' . "\n";
        }
        if ($this->currentPage - $this->pageList > 1) {
            $pagingCode .= '<li>...</li>' . "\n";
        }
        if ($this->currentPage <= $this->pageList) {
            $pagingCode .= $this->generateListPaging(1, $this->currentPage - 1);
        } else {
            $pagingCode .= $this->generateListPaging($this->currentPage - $this->pageList, $this->currentPage - 1);
        }
        // Generate current paging code.
        $pagingCode .= '<li class="cur"><a href="javascript:void(0)">' . $this->currentPage . '</a></li>';
        // Generate back paging.
        if ($this->totalPage - $this->currentPage > $this->pageList)
            $pagingCode .= $this->generateListPaging($this->currentPage + 1, $this->currentPage + $this->pageList);
        else
            $pagingCode .= $this->generateListPaging($this->currentPage + 1, $this->totalPage);
        if ($this->totalPage - $this->currentPage - $this->pageList > 0)
            $pagingCode .= '<li>...</li>' . "\n";
        if ($this->currentPage < $this->totalPage) {
            $pagingCode .= '<li><a href="' . $this->createUrl($this->currentPage + 1) . '">' . $lang['nextPage'] . '</a></li>' . "\n";
            $pagingCode .= '<li><a href="' . $this->createUrl($this->totalPage) . '">' . $lang['lastPage'] . '</a></li>' . "\n";
        }
        $pagingCode .= '</ul>';
        return $pagingCode;
    }
}