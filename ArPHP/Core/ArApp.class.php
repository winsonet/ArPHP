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
 * class ArApp
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
class ArApp
{
    /**
     * function run.
     *
     * @return void
     */
    static public function run()
    {
        if (AR_DEBUG) :
            arComp('ext.out')->deBug('[APP_RUN]');
        endif;

        self::_initComponents(Ar::getConfig('components', array()));

        if (AR_RUN_AS_SERVICE_HTTP) :
            $app = self::_createWebApplication('ArApplicationServiceHttp');
            $app->start();
        elseif (!AR_OUTER_START) :
            $app = self::_createWebApplication('ArApplicationWeb');
            $app->start();
        endif;

    }

    /**
     * component generator.
     *
     * @param array $config component config.
     *
     * @return void
     */
    static private function _initComponents(array $config)
    {
        foreach ($config as $driver => $component) :
            if (!is_array($component)) :
                continue;
            endif;

            if (!empty($component['lazy']) && $component['lazy'] == true) :
                continue;
            endif;

            foreach ($component as $engine => $cfg) :
                if (!empty($cfg['lazy']) && $cfg['lazy'] == true || $engine == 'lazy') :
                    continue;
                endif;

                $configC = !empty($cfg['config']) ? $cfg['config'] : array();

                Ar::setC($driver . '.' . $engine, $configC);
            endforeach;
        endforeach;

    }

    /**
     * component generator.
     *
     * @param string $class className.
     *
     * @return Object
     */
    static private function _createWebApplication($class)
    {
        $classkey = strtolower($class);

        if (!Ar::a($classkey)) :
            Ar::setA($classkey, new $class);
        endif;

        return Ar::a($classkey);

    }

}
