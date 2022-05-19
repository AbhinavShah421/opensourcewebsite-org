<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\models\Gender;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\PaginationButtons;
use app\models\User;
use yii\data\Pagination;

/**
 * Class MyGenderController
 *
 * @package app\modules\bot\controllers\privates
 */
class MyGenderController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex($genderId = null)
    {
        $user = $this->getUser();

        if (isset($genderId)) {
            $gender = Gender::findOne($genderId);

            if (isset($gender)) {
                $user->gender_id = $gender->id;
                $user->save();
            }
        }

        if (!$user->gender_id) {
            return $this->actionUpdate();
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', [
                    'gender' => $user->gender->name,
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
                            'callback_data' => self::createRoute('update'),
                            'text' => Emoji::EDIT,
                        ],
                    ],
                ]
            )
            ->build();
    }

    public function actionUpdate($page = 1)
    {
        $user = $this->getUser();

        $genderQuery = Gender::find();
        $pagination = new Pagination([
            'totalCount' => $genderQuery->count(),
            'pageSize' => 9,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);
        $paginationButtons = PaginationButtons::build($pagination, function ($page) {
            return self::createRoute('update', [
                'page' => $page,
            ]);
        });
        $genders = $genderQuery
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();
        $genderRows = array_map(function ($gender) {
            return [
                [
                    'text' => Yii::t('bot', $gender->name),
                    'callback_data' => self::createRoute('index', [
                        'genderId' => $gender->id,
                    ]),
                ],
            ];
        }, $genders);

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('update'),
                array_merge($genderRows, [$paginationButtons], [
                    [
                        [
                            'callback_data' => ($user->gender_id ? self::createRoute() : MyProfileController::createRoute()),
                            'text' => Emoji::BACK,
                        ],
                    ],
                ])
            )
            ->build();
    }
}
