<?php

/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace FSConditionalMethodsVendor\Monolog\Handler;

use FSConditionalMethodsVendor\Monolog\Logger;
use FSConditionalMethodsVendor\Monolog\Formatter\FormatterInterface;
/**
 * Simple handler wrapper that filters records based on a list of levels
 *
 * It can be configured with an exact list of levels to allow, or a min/max level.
 *
 * @author Hennadiy Verkh
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class FilterHandler extends \FSConditionalMethodsVendor\Monolog\Handler\AbstractHandler
{
    /**
     * Handler or factory callable($record, $this)
     *
     * @var callable|\Monolog\Handler\HandlerInterface
     */
    protected $handler;
    /**
     * Minimum level for logs that are passed to handler
     *
     * @var int[]
     */
    protected $acceptedLevels;
    /**
     * Whether the messages that are handled can bubble up the stack or not
     *
     * @var bool
     */
    protected $bubble;
    /**
     * @param callable|HandlerInterface $handler        Handler or factory callable($record|null, $filterHandler).
     * @param int|array                 $minLevelOrList A list of levels to accept or a minimum level if maxLevel is provided
     * @param int                       $maxLevel       Maximum level to accept, only used if $minLevelOrList is not an array
     * @param bool                      $bubble         Whether the messages that are handled can bubble up the stack or not
     */
    public function __construct($handler, $minLevelOrList = \FSConditionalMethodsVendor\Monolog\Logger::DEBUG, $maxLevel = \FSConditionalMethodsVendor\Monolog\Logger::EMERGENCY, $bubble = \true)
    {
        $this->handler = $handler;
        $this->bubble = $bubble;
        $this->setAcceptedLevels($minLevelOrList, $maxLevel);
        if (!$this->handler instanceof \FSConditionalMethodsVendor\Monolog\Handler\HandlerInterface && !\is_callable($this->handler)) {
            throw new \RuntimeException("The given handler (" . \json_encode($this->handler) . ") is not a callable nor a Monolog\\Handler\\HandlerInterface object");
        }
    }
    /**
     * @return array
     */
    public function getAcceptedLevels()
    {
        return \array_flip($this->acceptedLevels);
    }
    /**
     * @param int|string|array $minLevelOrList A list of levels to accept or a minimum level or level name if maxLevel is provided
     * @param int|string       $maxLevel       Maximum level or level name to accept, only used if $minLevelOrList is not an array
     */
    public function setAcceptedLevels($minLevelOrList = \FSConditionalMethodsVendor\Monolog\Logger::DEBUG, $maxLevel = \FSConditionalMethodsVendor\Monolog\Logger::EMERGENCY)
    {
        if (\is_array($minLevelOrList)) {
            $acceptedLevels = \array_map('Monolog\\Logger::toMonologLevel', $minLevelOrList);
        } else {
            $minLevelOrList = \FSConditionalMethodsVendor\Monolog\Logger::toMonologLevel($minLevelOrList);
            $maxLevel = \FSConditionalMethodsVendor\Monolog\Logger::toMonologLevel($maxLevel);
            $acceptedLevels = \array_values(\array_filter(\FSConditionalMethodsVendor\Monolog\Logger::getLevels(), function ($level) use($minLevelOrList, $maxLevel) {
                return $level >= $minLevelOrList && $level <= $maxLevel;
            }));
        }
        $this->acceptedLevels = \array_flip($acceptedLevels);
    }
    /**
     * {@inheritdoc}
     */
    public function isHandling(array $record)
    {
        return isset($this->acceptedLevels[$record['level']]);
    }
    /**
     * {@inheritdoc}
     */
    public function handle(array $record)
    {
        if (!$this->isHandling($record)) {
            return \false;
        }
        if ($this->processors) {
            foreach ($this->processors as $processor) {
                $record = \call_user_func($processor, $record);
            }
        }
        $this->getHandler($record)->handle($record);
        return \false === $this->bubble;
    }
    /**
     * {@inheritdoc}
     */
    public function handleBatch(array $records)
    {
        $filtered = array();
        foreach ($records as $record) {
            if ($this->isHandling($record)) {
                $filtered[] = $record;
            }
        }
        if (\count($filtered) > 0) {
            $this->getHandler($filtered[\count($filtered) - 1])->handleBatch($filtered);
        }
    }
    /**
     * Return the nested handler
     *
     * If the handler was provided as a factory callable, this will trigger the handler's instantiation.
     *
     * @return HandlerInterface
     */
    public function getHandler(array $record = null)
    {
        if (!$this->handler instanceof \FSConditionalMethodsVendor\Monolog\Handler\HandlerInterface) {
            $this->handler = \call_user_func($this->handler, $record, $this);
            if (!$this->handler instanceof \FSConditionalMethodsVendor\Monolog\Handler\HandlerInterface) {
                throw new \RuntimeException("The factory callable should return a HandlerInterface");
            }
        }
        return $this->handler;
    }
    /**
     * {@inheritdoc}
     */
    public function setFormatter(\FSConditionalMethodsVendor\Monolog\Formatter\FormatterInterface $formatter)
    {
        $this->getHandler()->setFormatter($formatter);
        return $this;
    }
    /**
     * {@inheritdoc}
     */
    public function getFormatter()
    {
        return $this->getHandler()->getFormatter();
    }
}
