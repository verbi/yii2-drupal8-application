<?php
namespace verbi\yii2Drupal8Application\helpers;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\web\ResponseFormatterInterface;
use yii\helpers\Inflector;
use yii\helpers\Url;
use yii\helpers\FileHelper;
use yii\helpers\StringHelper;
use verbi\yii2Drupal8Application\helpers\Drupal8ResponseFormatter;
class Response extends \yii\web\Response {
    const FORMAT_DRUPAL8 = 'drupal8';
    
    public $format = self::FORMAT_DRUPAL8;
    
    /**
     * @return array the formatters that are supported by default
     */
    protected function defaultFormatters()
    {
        return array_merge(parent::defaultFormatters(),[
            self::FORMAT_DRUPAL8 => Drupal8ResponseFormatter::className(),
        ]);
    }
    
    protected function sendContent()
    {
        if ($this->stream === null && is_array($this->content)) {
            return $this->content;
        }
        
        return parent::sendContent();
    }
    
    public function send() {
        
    }
    
    protected function prepare()
    {
        if ($this->stream !== null) {
            return;
        }
        if (isset($this->formatters[$this->format])) {
            $formatter = $this->formatters[$this->format];
            if (!is_object($formatter)) {
                $this->formatters[$this->format] = $formatter = Yii::createObject($formatter);
            }
            if ($formatter instanceof ResponseFormatterInterface) {
                $formatter->format($this);
            } else {
                throw new InvalidConfigException("The '{$this->format}' response formatter is invalid. It must implement the ResponseFormatterInterface.");
            }
        } elseif ($this->format === self::FORMAT_RAW) {
            if ($this->data !== null) {
                $this->content = $this->data;
            }
        } else {
            throw new InvalidConfigException("Unsupported response format: {$this->format}");
        }
        if(!($this->format === self::FORMAT_DRUPAL8 && is_array($this->content))) {
            parent::prepare();
        }
    }
}