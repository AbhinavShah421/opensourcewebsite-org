<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\PaginationButtons;
use yii\data\Pagination;
use app\components\helpers\TimeHelper;
use app\modules\bot\components\helpers\Emoji;

/**
 * Class MyTimezoneController
 *
 * @package app\modules\bot\controllers\privates
 */
class MyTimezoneController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        $this->getState()->setName(null);

        $user = $this->getUser();

        $timezones = TimeHelper::timezonesList();

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', [
                    'timezone' => TimeHelper::getNameByOffset($user->timezone),
                ]),
                [
                    [
                        [
                            'callback_data' => MyProfileController::createRoute(),
                            'text' => Emoji::BACK,
                        ],
                        [
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::MENU,
                        ],
                        [
                            'callback_data' => self::createRoute('list'),
                            'text' => Emoji::EDIT,
                        ],
                    ],
                ]
            )
            ->build();
    }

    public function actionList($page = 2)
    {
        $this->getState()->setName(self::createRoute('input'));

        $timezones = TimeHelper::timezonesList();

        $pagination = new Pagination([
            'totalCount' => count($timezones),
            'pageSize' => 9,
            'params' => [
                'page' => $page,
            ],
        ]);

        $pagination->pageSizeParam = false;
        $pagination->validatePage = true;
        $timezones = array_slice($timezones, $pagination->offset, $pagination->limit, true);

        $paginationButtons = PaginationButtons::build($pagination, function ($page) {
            return self::createRoute('list', [
                'page' => $page,
            ]);
        });

        $buttons = [];

        foreach ($timezones as $timezone => $name) {
            $buttons[][] = [
                'text' => $name,
                'callback_data' => self::createRoute('select', [
                    'timezone' => $timezone,
                ]),
            ];
        }

        if ($paginationButtons) {
            $buttons[] = $paginationButtons;
        }

        $buttons[][] = [
            'callback_data' => self::createRoute(),
            'text' => Emoji::BACK,
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('list'),
                $buttons
            )
            ->build();
    }

    public function actionSelect($timezone = null)
    {
        if (!$timezone) {
            return $this->actionList();
        }

        $user = $this->getUser();

        $user->timezone = $timezone;

        if ($user->validate('timezone') && $user->save()) {
            return $this->actionIndex();
        }

        return $this->getResponseBuilder()
            ->answerCallbackQuery()
            ->build();
    }

    public function actionInput()
    {
        // TODO add text input to set timezone (Examples: 07, 06:30, -07, -06:30)
    }
}
