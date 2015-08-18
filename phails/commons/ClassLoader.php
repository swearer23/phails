<?php
/**
 * Class加载类，通过用户自定义配置，在指定路径读取指定类
 *
 * @author shiwen.xu <shiwen.xu@edaoyou.com>
 */
class ClassLoader
{
    const MATCH_TYPE_WILDCARD_PREFIX    = "TYPE_WILDCARD_PREFIX";
    const MATCH_TYPE_WILDCARD_ONLY      = "TYPE_WILDCARD_ONLY";
    const MATCH_TYPE_PERFECT            = "MATCH_TYPE_PERFECT";

    protected static $defaultLoader = null;
    protected $loaders = array();
    protected $rootDir = null;
    protected $loaderCache = array();

    /**
     * 构造函数
     *
     * loaders结构解释:
     * 1. key为class名，value为该class名对应的loader配置项
     * 2. 每个类名的配置项为一个array，里面支持三种key:
     *     * path: 所在目录
     *     * skip: 对指定的class名不进行匹配，该配置对非通配符匹配无效
     *     * afterLoadCallback: 对类进行加载之后的回调，必须为callable。在使用通配符匹配的情况下，可使用&替代当前的类名
     *
     * loaders示范:
     * array(
     *     "*Service"   => array(
     *         "path" => "app/services/",
     *         "skip" => array("WeixinService"),
     *     ),
     *     "WeixinService" => array(
     *         "path" => "app/weixin/",
     *         "afterLoadCallback" => array("&", "addListeners")
     *     )
     * )
     *
     * @param array $loaders ClassLoader配置
     * @param string $rootDir 根路径，不可为null
     */
    public function __construct($loaders, $rootDir)
    {
        if (null === $rootDir) {
            throw new InvalidArgumentException('ClassLoader::__construct $rootDir cannot be null');
        }
        if (false === $this->checkLoaders()) {
            throw new InvalidArgumentException('ClassLoader::__construct $loaders format is invalid');
        }

        $this->loaders = $loaders;
        $this->rootDir = $rootDir;
    }

    /**
     * 获取默认的class loader
     * @return ClassLoader 加载了默认配置的loader实例
     */
    public static function getDefaultLoader()
    {
        if (null === self::$defaultLoader) {
            $environmentConfig = Environment::$conf;
            $classLoaders = $environmentConfig["classLoaders"];
            self::$defaultLoader = new self($classLoaders, $environmentConfig["root"]);
        }
        return self::$defaultLoader;
    }

    /**
     * 加载一个类
     * @param  string $className 类名
     * @return boolean 返回true表示加载成功，返回false表示加载失败
     */
    public function loadClass($className)
    {
        $classPath = $this->getFullPath($className);
        if ($classPath) {
            if (class_exists($className, false) || trait_exists($className, false) || interface_exists($className, false)) {
                error_log("ClassLoader::loadClass $className is already loaded");
                return false;
            }

            require $classPath;
            if (class_exists($className, false) || trait_exists($className, false) || interface_exists($className, false)) {
                $classLoadCallback = $this->getAfterLoadCallback($className);
                if ($classLoadCallback && is_callable($classLoadCallback)) {
                    call_user_func($classLoadCallback);
                }
                return true;
            } else {
                error_log("ClassLoader::loadClass unable to load $className at path $classPath");
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 获取指定类名的路径
     * @param  string $className 类名
     * @return string|null 返回类所对应的地址，若返回null，则表示不存在或为配置
     */
    public function getFullPath($className)
    {
        if (empty($className)) {
            return null;
        } else {
            $loaders = $this->getLoadersForClass($className);
            if ($loaders && isset($loaders["fullPath"])) {
                return $loaders["fullPath"];
            } else {
                return null;
            }
        }
    }

    /**
     * 获取成功加载后的回调
     * @param  string $className 类名
     * @return callable|null     返回callable时为正常，返回null时表示无相应的回调
     */
    public function getAfterLoadCallback($className)
    {
        if (empty($className)) {
            return null;
        } else {
            $loaders = $this->getLoadersForClass($className);
            if ($loaders && isset($loaders["afterLoadCallback"])) {
                return $loaders["afterLoadCallback"];
            } else {
                return null;
            }
        }
    }

    /**
     * 检查loaders配置是否符合规范
     *
     * @return boolean true为符合规范，false为不符合规范
     */
    protected function checkLoaders()
    {
        $isLoadersValid = true;
        foreach ($this->loaders as $key => $value) {
            if (strlen($key) > 1) {
                if (strpos($key, "*", 1) !== false) {
                    $isLoadersValid = false;
                    break;
                }
            }
        }
        return $isLoadersValid;
    }

    /**
     * 根据规则匹配出指定class名对应的loaders配置
     *
     * 匹配规则：
     * 1. 完全匹配
     * 2. 通配符匹配。用*号表示通配符，暂时只支持通配符放在class名的第一位，或只使用通配符表示所有class名
     *
     * 匹配顺序：从第一个开始匹配，匹配成功后不再往下匹配
     *
     * @param  string $className class名
     * @return array|null 指定class名对应的loaders配置
     */
    protected function getLoadersForClass($className)
    {
        if (isset($this->loaderCache[$className])) {
            return $this->loaderCache[$className];
        }

        $loaders = null;
        $matchType = self::MATCH_TYPE_PERFECT;
        $onlyWildcardLoader = null;

        foreach ($this->loaders as $key => $value) {
            if (strlen($key) > 1) {
                if ($key === $className) {
                    $loaders = $value;
                    $matchType = self::MATCH_TYPE_PERFECT;
                    break;
                } else if (strpos($key, "*") === 0) {
                    $key = substr($key, 1);
                    if (strrpos($className, $key) === strlen($className) - strlen($key)) {
                        $loaders = $value;
                        $matchType = self::MATCH_TYPE_WILDCARD_PREFIX;
                        break;
                    }
                }
            } else {
                if ($key === "*") {
                    $onlyWildcardLoader = $value;
                }
            }
        }

        if (null === $loaders && null !== $onlyWildcardLoader) {
            $loaders = $onlyWildcardLoader;
            $matchType = self::MATCH_TYPE_WILDCARD_ONLY;
        }

        if ($loaders && in_array($matchType, array(self::MATCH_TYPE_WILDCARD_PREFIX, self::MATCH_TYPE_WILDCARD_ONLY))) {
            if (isset($loaders["skip"]) && !empty($loaders["skip"])) {
                if (is_array($loaders["skip"])) {
                    if (in_array($className, $loaders["skip"])) {
                        $loaders = null;
                    }
                } else {
                    if ($className === $loaders["skip"]) {
                        $loaders = null;
                    }
                }
            }
        }

        if ($loaders && isset($loaders["path"])) {
            $classFilePath =  $this->getFilePath($this->rootDir, $loaders["path"], $className . ".php");
            if (!file_exists($classFilePath)) {
                error_log("File '$classFilePath' does not exist");
                $loaders = null;
            } else {
                $loaders["fullPath"] = $classFilePath;
            }
        }

        if ($loaders && isset($loaders["afterLoadCallback"])) {
            foreach ($loaders["afterLoadCallback"] as $index => $value) {
                if ("&" === $value) {
                    $loaders["afterLoadCallback"][$index] = $className;
                }
            }
        }

        $this->loaderCache[$className] = $loaders;
        return $loaders;
    }

    /**
     * 获取文件路径
     * @param  string $rootDir  根目录
     * @param  string $path     项目相对路径
     * @param  string $fileName 项目文件名
     * @return string           文件路径
     */
    protected function getFilePath($rootDir, $path, $fileName)
    {
        return implode(DIRECTORY_SEPARATOR, array(
            rtrim($rootDir, DIRECTORY_SEPARATOR),
            trim($path, DIRECTORY_SEPARATOR),
            $fileName
        ));
    }
}
