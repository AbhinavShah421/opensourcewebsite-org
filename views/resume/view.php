<?php

declare(strict_types=1);

use app\components\helpers\ArrayHelper;
use app\models\Resume;
use app\components\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\DetailView;
use app\widgets\buttons\EditButton;

/**
 * @var View $this
 * @var Resume $model
 */

$this->title = Yii::t('app', 'Resume') . ' #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Resumes'), 'url' => ['index']];
$this->params['breadcrumbs'][] = '#' . $model->id;
?>
<div class="index">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex p-0">
                    <ul class="nav nav-pills ml-auto p-2">
                        <li class="nav-item align-self-center mr-3">
                            <div class="input-group-prepend">
                                <div class="dropdown">
                                    <a class="btn <?= $model->isActive() ? 'btn-primary' : 'btn-default' ?> dropdown-toggle"
                                       href="#" role="button"
                                       id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true"
                                       aria-expanded="false">
                                        <?= $model->isActive() ?
                                            Yii::t('app', 'Active') :
                                            Yii::t('app', 'Inactive') ?>
                                    </a>
                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                        <h6 class="dropdown-header"><?= $model->getAttributeLabel('Status') ?></h6>
                                        <a class="dropdown-item status-update <?= $model->isActive() ? 'active' : '' ?>"
                                           href="#"
                                           data-value="<?= Resume::STATUS_ON ?>">
                                            <?= Yii::t('app', 'Active') ?>
                                        </a>
                                        <a class="dropdown-item status-update <?= $model->isActive() ? '' : 'active' ?>"
                                           href="#"
                                           data-value="<?= Resume::STATUS_OFF ?>">
                                            <?= Yii::t('app', 'Inactive') ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="nav-item align-self-center mr-3">
                            <?= EditButton::widget([
                                'url' => ['resume/update', 'id' => $model->id],
                                'options' => [
                                    'title' => 'Edit Resume',
                                ]
                            ]); ?>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <?= DetailView::widget([
                            'model' => $model,
                            'attributes' => [
                                'id',
                                'name',
                                'skills:ntext',
                                'experiences:ntext',
                                'expectations:ntext',
                                [
                                    'label' => Yii::t('app', 'Keywords'),
                                    'visible' => (bool)$model->keywords,
                                    'value' => function () use ($model) {
                                        $text = '';

                                        foreach (ArrayHelper::getColumn($model->keywords, 'keyword') as $keyword) {
                                            $text .= Html::tag('span', $keyword, ['class' => 'badge badge-primary']) . '&nbsp';
                                        }

                                        return $text;
                                    },
                                    'format' => 'raw',
                                ],
                                [
                                    'attribute' => 'min_hourly_rate',
                                    'value' => $model->min_hourly_rate ? $model->min_hourly_rate . ' ' . $model->currency->code : '∞',
                                ],
                                'remote_on:boolean',
                                [
                                    'label' => Yii::t('jo', 'Offline work'),
                                    'value' => (bool)$model->location ? Yii::t('app', 'Yes') : Yii::t('app', 'No'),
                                ],
                                [
                                    'attribute' => 'location',
                                    'visible' => (bool)$model->location,
                                    'value' => function () use ($model) {
                                        return Html::a(
                                            $model->location,
                                            Url::to(['view-location', 'id' => $model->id]),
                                            ['class' => 'modal-btn-ajax']
                                        );
                                    },
                                    'format' => 'raw',
                                ],
                                [
                                    'attribute' => 'search_radius',
                                    'visible' => (bool)$model->location,
                                    'value' => $model->search_radius ? $model->search_radius . ' ' . Yii::t('app', 'km') : '',
                                ],
                                [
                                    'label' => Yii::t('app', 'Offers'),
                                    'visible' => $model->getMatchesCount(),
                                    'format' => 'raw',
                                    'value' => function () use ($model) {
                                        return $model->getMatchesCount() ?
                                            Html::a(
                                                $model->getNewMatchesCount() ? Html::badge('info', 'new') : $model->getMatchesCount(),
                                                Url::to(['/vacancy/show-matches', 'resumeId' => $model->id]),
                                            ) : '';
                                    },
                                ],
                            ]
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="languages-index">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?= Yii::t('jo', 'Available languages'); ?></h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <div id="w0" class="grid-view">
                            <table class="table table-condensed table-hover" style="margin-bottom: 0;">
                                <tbody>
                                <?php foreach ($model->user->languages as $userLanguage) : ?>
                                    <tr>
                                        <td><?= $userLanguage->getLabel() ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$statusActiveUrl = Yii::$app->urlManager->createUrl(['resume/set-active?id=' . $model->id]);
$statusInactiveUrl = Yii::$app->urlManager->createUrl(['resume/set-inactive?id=' . $model->id]);

$script = <<<JS

$('.status-update').on("click", function(event) {
    const status = $(this).data('value');
    const active_url = '{$statusActiveUrl}';
    const inactive_url = '{$statusInactiveUrl}';
    const url = (parseInt(status) === 1) ? active_url : inactive_url;

        $.post(url, {}, function(result) {
            if (result === true) {
                location.reload();
            }
            else {

                $('#main-modal-header').text('Warning!');

                for (const [, errorMsg] of Object.entries(result)) {
                    $('#main-modal-body').append('<p>' + errorMsg + '</p>');
                }

                $('#main-modal').show();
                $('.close').on('click', function() {
                    $("#main-modal-body").html("");
                    $('#main-modal').hide();
                });
            }
        });

    return false;
});
JS;
$this->registerJs($script);
