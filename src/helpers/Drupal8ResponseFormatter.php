<?php

namespace verbi\yii2Drupal8Application\helpers;
use yii\web\ResponseFormatterInterface;
use yii\base\Component;

/**
 * The drupal8ResponseFormatter will format the output from your
 * Yii2 Application into Drupal Render Array.
 * 
 * @author Philip Verbist <philip.verbist@gmail.com>
 */
class Drupal8ResponseFormatter extends Component implements ResponseFormatterInterface {
    public function format($response)
    {
        if ($response->data !== null) {
            $response->content = is_array($response->data)?$response->data:[$response->data];
        }
    }
}