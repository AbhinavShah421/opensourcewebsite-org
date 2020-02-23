<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use \app\modules\bot\components\response\SendMessageCommand;
use \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use app\modules\bot\components\Controller as Controller;

/**
 * Class MenuController
 *
 * @package app\controllers\bot
 */
class MenuController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        return [
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('index'),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => '/my_profile',
                                'text' => Yii::t('bot', 'Profile')
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/my_rating',
                                'text' => Yii::t('bot', 'Rating')
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/my_referrals',
                                'text' => Yii::t('bot', 'Referrals')
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/help',
                                'text' => Yii::t('bot', 'Available commands')
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/start',
                                'text' => Yii::t('bot', 'Greeting')
                            ],
                            [
                                'callback_data' => '/my_language',
                                'text' => Yii::t('bot', 'Language')
                            ],
                        ],
                    ]),
                ]
            ),
        ];
    }
}
