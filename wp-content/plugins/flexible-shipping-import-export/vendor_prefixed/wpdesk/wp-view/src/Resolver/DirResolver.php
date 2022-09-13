<?php

namespace FlexibleShippingImportExportVendor\WPDesk\View\Resolver;

use FlexibleShippingImportExportVendor\WPDesk\View\Renderer\Renderer;
use FlexibleShippingImportExportVendor\WPDesk\View\Resolver\Exception\CanNotResolve;
/**
 * Class should resolve name by serching in provided dir. If empty then current dir
 *
 * @package WPDesk\View\Resolver
 */
class DirResolver implements \FlexibleShippingImportExportVendor\WPDesk\View\Resolver\Resolver
{
    /** @var string */
    private $dir;
    /**
     * Base path for templates ie. subdir
     *
     * @param $dir
     */
    public function __construct($dir)
    {
        $this->dir = $dir;
    }
    /**
     * Resolve name to full path
     *
     * @param string $name
     * @param Renderer|null $renderer
     *
     * @return string
     */
    public function resolve($name, \FlexibleShippingImportExportVendor\WPDesk\View\Renderer\Renderer $renderer = null)
    {
        $dir = \rtrim($this->dir, '/');
        $fullName = $dir . '/' . $name;
        if (\file_exists($fullName)) {
            return $fullName;
        }
        throw new \FlexibleShippingImportExportVendor\WPDesk\View\Resolver\Exception\CanNotResolve("Cannot resolve {$name}");
    }
}
