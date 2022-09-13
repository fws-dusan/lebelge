<?php

declare (strict_types=1);
/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace FSConditionalMethodsVendor\Monolog\Handler;

use FSConditionalMethodsVendor\Monolog\Formatter\FormatterInterface;
/**
 * Interface to describe loggers that have a formatter
 *
 * This interface is present in monolog 1.x to ease forward compatibility.
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
interface FormattableHandlerInterface
{
    /**
     * Sets the formatter.
     *
     * @param  FormatterInterface $formatter
     * @return HandlerInterface   self
     */
    public function setFormatter(\FSConditionalMethodsVendor\Monolog\Formatter\FormatterInterface $formatter) : \FSConditionalMethodsVendor\Monolog\Handler\HandlerInterface;
    /**
     * Gets the formatter.
     *
     * @return FormatterInterface
     */
    public function getFormatter() : \FSConditionalMethodsVendor\Monolog\Formatter\FormatterInterface;
}
