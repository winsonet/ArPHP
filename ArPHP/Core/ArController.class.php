<?php
/**
 * ArPHP A Strong Performence PHP FrameWork ! You Should Have.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  Core.base
 * @author   yc <ycassnr@gmail.com>
 * @license  http://www.arphp.net/licence BSD Licence
 * @version  GIT: 1: coding-standard-tutorial.xml,v 1.0 2014-5-01 18:16:25 cweiske Exp $
 * @link     http://www.arphp.net
 */

/**
 * class ArController
 *
 * default hash comment :
 *
 * <code>
 *  # This is a hash comment, which is prohibited.
 *  $hello = 'hello';
 * </code>
 *
 * @category ArPHP
 * @package  Core.base
 * @author   yc <ycassnr@gmail.com>
 * @license  http://www.arphp.net/licence BSD Licence
 * @version  Release: @package_version@
 * @link     http://www.arphp.net
 */
class ArController
{
    // assign container
    protected $assign = array();
    // layOut file
    protected $layOutFile = 'NOT_INIT';

    /**
     * init function.
     *
     * @return void
     */
    public function init()
    {

    }

    /**
     * magic function.
     *
     * @param string $name   funcName.
     * @param mixed  $params funcParames.
     *
     * @return mixed
     */
    public function __call($name, $params)
    {
        $mName = empty($params[0]) ? arCfg('requestRoute.a_c') : $params[0];
        if ($name == 'module') :
            return arModule($mName);
        elseif ($name == 'model') :
            $m = $mName . 'Model';
            return ArModel::model($m);
        else :
            throw new ArException("class do not have a method $name");
        endif;

    }

    /**
     * default function.
     *
     * @param array $vals value.
     *
     * @return void
     */
    public function assign(array $vals)
    {
        foreach ($vals as $key => $val) :
            $this->assign[$key] = $val;
        endforeach;

    }

    /**
     * show string function.
     *
     * @param string $ckey          key.
     * @param string $defaultReturn return.
     * @param string $show          display string.
     *
     * @return mixed
     */
    public function show($ckey = '', $defaultReturn = '', $show = true)
    {
        $rt = array();
        if (empty($ckey)) :
            $rt = $this->assign;
        else :
            if (strpos($ckey, '.') === false) :
                if (isset($this->assign[$ckey])) :
                    $rt = $this->assign[$ckey];
                endif;
            else :
                $cE = explode('.', $ckey);
                $rt = $this->assign;
                while ($k = array_shift($cE)) :
                    if (empty($rt[$k])) :
                        $rt = $defaultReturn;
                        break;
                    else :
                        $rt = $rt[$k];
                    endif;
                endwhile;
            endif;
        endif;
        if ($show) :
            echo $rt;
        else :
            return $rt;
        endif;

    }

    /**
     * display function.
     *
     * @param string  $view  view template.
     * @param boolean $fetch fetch view template.
     *
     * @return mixed
     */
    protected function display($view = '', $fetch = false)
    {
        $headerFile = '';
        $footerFile = '';

        if ($this->layOutFile === 'NOT_INIT') :
            $headerFile = AR_APP_VIEW_PATH . 'Layout' . DS . 'header' . '.' . arCfg('TPL_SUFFIX');
            $footerFile = AR_APP_VIEW_PATH . 'Layout' . DS . 'footer' . '.' . arCfg('TPL_SUFFIX');
        elseif ($this->layOutFile) :
            $headerFile = $this->layOutFile . '_header' . '.' . arCfg('TPL_SUFFIX');
            $footerFile = $this->layOutFile . '_footer' . '.' . arCfg('TPL_SUFFIX');
        endif;

        // 加载头
        if ($headerFile) :
            if (is_file($headerFile)) :
                $this->fetch($headerFile);
            else :
                if ($this->layOutFile !== 'NOT_INIT') :
                    throw new ArException("not fount layout header file : " . $headerFile, '2000');
                endif;
            endif;
        endif;

        // 加载模板
        $this->fetch($view, false);

        // 加载尾部
        if ($footerFile) :
            if (is_file($footerFile)) :
                $this->fetch($footerFile);
            else :
                if ($this->layOutFile !== 'NOT_INIT') :
                    throw new ArException("not fount layout footer file : " . $footerFile, '2000');
                endif;
            endif;
        endif;

    }

    /**
     * fetch function.
     *
     * @param string  $view  view template.
     * @param boolean $fetch fetch view template.
     *
     * @return mixed
     */
    protected function fetch($view = '', $fetch = false)
    {
        if (is_file($view)) :
            $viewFile = $view;
        else :
            $viewPath = '';
            $viewBasePath = arCfg('PATH.VIEW');
            $overRide = false;
            $absolute = false;

            if (strpos($view, '@') === 0) :
                $overRide = true;
                $view = ltrim($view, '@');
            endif;

            $r = Ar::a('ArApplicationWeb')->route;

            if (empty($view)) :
                $viewPath .= $r['a_c'] . DS . $r['a_a'];
            elseif(strpos($view, '/') !== false) :
                if (substr($view, 0, 1) == '/') :
                    $absolute = true;
                    $viewPath .= str_replace('/', DS, ltrim($view, '/'));
                else :
                    $viewPath .= $r['a_c'] . DS  . str_replace('/', DS, ltrim($view, '/'));
                endif;
                if (substr($view, -1) == '/') :
                    $viewPath .= $r['a_a'];
                endif;
            else :
                $viewPath .= $r['a_c'] . DS . $view;
            endif;

            $currentC = $tempC = $r['a_c'] . 'Controller';

            $preFix = '';

            if (!$absolute) :
                while ($cP = get_parent_class($tempC)) :
                    if (!in_array(substr($cP, 0, -10), array('Ar', 'Base'))) :
                        $preFix = substr($cP, 0, -10) . DS . $preFix;
                        if (!$overRide && method_exists($cP, $r['a_a'] . 'Action')) :
                            $viewPath = str_replace(substr($tempC, 0, -10) . DS, '', $viewPath);
                        endif;
                        $tempC = $cP;
                    else :
                        break;
                    endif;
                endwhile;
            endif;
            $viewFile = $viewBasePath . $preFix . $viewPath . '.' . arCfg('TPL_SUFFIX');
        endif;

        if (is_file($viewFile)) :
            extract($this->assign);
            if ($fetch === true) :
                ob_start();
                include $viewFile;
                $fetchStr = ob_get_contents();
                ob_end_clean();
                return $fetchStr;
            else :
                include $viewFile;
            endif;
        else :
            throw new ArException('view : ' . $viewFile . ' not found');
        endif;

    }

    /**
     * redirect function.
     *
     * @param mixed  $r    route.
     * @param string $show show string.
     * @param string $time time display.
     *
     * @return mixed
     */
    public function redirect($r = '', $show = '', $time = '0')
    {
        return arComp('url.route')->redirect($r, $show, $time);

    }

    /**
     * redirect function.
     *
     * @param mixed  $r    route.
     * @param string $show show string.
     * @param string $time time display.
     *
     * @return mixed
     */
    public function redirectSuccess($r = '', $show = '', $time = '1')
    {
        $this->redirect($r, '成功:' . $show, $time);

    }

    /**
     * redirect function.
     *
     * @param mixed  $r    route.
     * @param string $show show string.
     * @param string $time time display.
     *
     * @return mixed
     */
    public function redirectError($r = '', $show = '' , $time = '4')
    {
        $this->redirect($r, '失败:' . $show, $time);

    }

    /**
     * redirect function.
     *
     * @param string $msg message.
     *
     * @return void
     */
    public function showJsonSuccess($msg = ' ')
    {
        $this->showJson(array('ret_msg' => $msg, 'ret_code' => '1000', 'success' => "1"));

    }

    /**
     * redirect function.
     *
     * @param string $msg  message.
     * @param string $code code.
     *
     * @return void
     */
    public function showJsonError($msg = ' ', $code = '1001')
    {
        $this->showJson(array('ret_msg' => $msg, 'ret_code' => $code, 'error_msg' => $msg, 'success' => "0"));

    }

    /**
     * json display.
     *
     * @param mixed $data    jsondata.
     * @param array $options option.
     *
     * @return mixed
     */
    public function showJson($data = array(), array $options = array())
    {
        return arComp('ext.out')->json($data, $options);

    }

    /**
     * check login.
     *
     * @return boolean
     */
    public function ifLogin()
    {
        return !!arComp('list.session')->get('uid');

    }

    /**
     * logout.
     *
     * @return void
     */
    public function logOut()
    {
        arComp('list.session')->set('uid', null);

    }

    /**
     * auth function.
     *
     * @return void
     */
    public function auth()
    {

    }

    /**
     * start controller.
     *
     * @param string $module module.
     *
     * @return void
     */
    public function runController($module)
    {
        $route = explode('/', $module);

        $requestRoute = array(
                'a_m' => arCfg('requestRoute.a_m'),
                'a_c' => $route[0],
                'a_a' => $route[1],
            );

        Ar::a('ArApplicationWeb')->runController($requestRoute);

    }

    /**
     * start controller.
     *
     * @param string $layoutFileName.
     *
     * @return void
     */
    public function setLayoutFile($layoutFileName = '')
    {
        if ($layoutFileName) :
            if (!is_file($layoutFileName)) :
                $layoutFileName = AR_APP_VIEW_PATH . 'Layout' . DS . $layoutFileName;
            endif;
        endif;

        $this->layOutFile = $layoutFileName;

    }

}
