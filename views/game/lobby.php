<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\web\View;
use app\assets\AppAsset;

/* @var $this yii\web\View */
$this->title =  Yii::t('game_player', 'Title_Lobby_{params}', ['params' => Yii::$app->session['Game']->getGameName()]);
$ajax_reload = 4000;

// Set JS var
$this->registerJs($this->context->getJSConfig(), View::POS_HEAD);
$this->registerJsFile("@web/js/game/game.js", ['depends' => [AppAsset::className()]]);
$this->registerJsFile("@web/js/game/ajax.js", ['depends' => [AppAsset::className()]]);
?>


<?php
/* Reload gridview JS */
$this->registerJs('$(document).on("pjax:timeout", function(event) {
  // Prevent default timeout redirection behavior
  event.preventDefault()
});');

$this->registerJs(
    '$("document").ready(function(){
        setInterval(function(){
            if($("select:hover").length == 0){
                $.pjax.reload({container:"#GridView-Lobby"});
            }
        }, '.$ajax_reload.'); //Reload GridView
    });'
);
?>
<div class="game-lobby">

    <h1><?= Html::encode($this->title) ?></h1>

	<?php if(Yii::$app->session['Game']->getMapId() == 1): ?>
		<div class="alert alert-info"><?= Yii::t('game', 'Info_Game_Lobby_Antartic') ?></div>
	<?php endif; ?>
	
	<?php if(isset($model->errors["Game"])): ?>
    	<div class='alert alert-danger'><?= $model->errors["Game"][0] ?></div>
    <?php endif;?>
	
    <!-- Top Buttons -->
    <div style="margin: 0 auto;"><table style="border-spacing: 4px;border-collapse: separate;"><tr>
    <!-- Classic -->
    <td><?= Html::a(Yii::t('game_player', 'Button_Add_Friend')." <i class='fa fa-group'></i>", ['/game/lobby'], ['class'=>'btn btn-primary']); ?></td>
   	<!-- Game Owner -->
    <?php if (Yii::$app->session['User']->getId() == Yii::$app->session['Game']->getGameOwnerID()): ?>
    <td><?= Html::a(Yii::t('game_player', 'Button_Add_Bot')." <i class='fa fa-plus'></i>", ['/game/addbot', 'gid' => Yii::$app->session['Game']->getGameId()], ['class'=>'btn btn-info']); ?></td>
    <td><?= Html::a(Yii::t('game_player', 'Button_Sart_Game')." <i class='fa fa-gamepad'></i>", ['/game/start', 'gid' => Yii::$app->session['Game']->getGameId()], ['class'=>'btn btn-warning']); ?></td>
    <?php endif; ?>
    </tr></table></div>
    <br>
    <?php Pjax::begin(['id' => 'GridView-Lobby', 'timeout' => $ajax_reload]); ?>
    <!--  TODO : LOBBY TO FORM = CHECK COLOR EXISTS... -->
    <?= GridView::widget([
        'summary' => '',
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
    	'tableOptions' => ['class' => 'table table-bordered table-hover'],
    	'rowOptions'=>function($model) use ($colorList) {
	    		return ['style' => 'background-color: #'.$colorList[$model->game_player_color_id]->getColorCSS()];
	    	},
        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],
            [
            	'format'    => 'raw',
                'attribute' => Yii::t('game_player', 'Tab_User_Name'),
                'value'     => function ($model, $key, $index, $column) use ($userList, $botList, $colorList) {
                	$returned = "";
                	// Check bot
                	if($model->game_player_bot > 0 && isset($botList[$model->game_player_bot]))
                		$returned = '<font size="4" color="'.$colorList[$model->game_player_color_id]->getColorFontChat().'">'.Yii::t('game_player', 'Bot_Name_{id}', ['id' => $model->game_player_bot]).'</font>  ';
                	elseif(isset($userList[$model->game_player_user_id]))
            			$returned = '<font size="4" color="'.$colorList[$model->game_player_color_id]->getColorFontChat().'">'.$userList[$model->game_player_user_id]->getUserName().'</font>  ';
                		
                	// If admin
                	if(Yii::$app->session['User']->getId() == Yii::$app->session['Game']->getGameOwnerID() && Yii::$app->session['User']->getId() != $model->game_player_user_id)
            			return $returned.Html::a(" <i class='fa fa-sign-out'></i>", ['/game/lobby'], ['class'=>'btn btn-xs btn-danger']);
                	else
                		return $returned;
            	},
            ],
            [
            	'format'    => 'raw',
                'attribute' => Yii::t('game_player', 'Tab_Color_Name'),
                'value'     => function ($model, $key, $index, $column) use ($colorList, $colorSQl){
                	if((Yii::$app->session['User']->getId() == $model->game_player_user_id) || ((Yii::$app->session['User']->getId() == Yii::$app->session['Game']->getGameOwnerID()) && $model->game_player_bot > 0))
                		return Html::activeDropDownList($model, 'game_player_color_id',
                			// TODO : Revoir utilisation de ColorClass
                				ArrayHelper::map($colorSQl,
                				function($model, $defaultValue) {
                					return $model->color_id;
                				},
                				function($model, $defaultValue) {
                					return Yii::t('color', $model->color_name);
                				}
                				),
                				[
                						'prompt'	=> Yii::t('color', $colorList[$model->game_player_color_id]->getColorName()),
                						'class'		=> 'selectpicker',
                						'onchange'	=> 'location = "'.Url::current().'&ui='.$model->game_player_user_id.'&bi='.$model->game_player_bot.'&ci="+this.options[this.selectedIndex].value;',
                				]);
                	else
                		return '<font size="4" color="'.$colorList[$model->game_player_color_id]->getColorFontChat().'">'.$colorList[$model->game_player_color_id]->getColorName().'</font>';
                },
            ],
            [
	            'filter' => false,
            	'format'    => 'raw',
	            'attribute' => Yii::t('game_player', 'Tab_Region_Player'),
	            'value'     => function ($model, $key, $index, $column) use ($continentList, $continentSQl, $colorList){
	            	if(count($continentList) > 0){
		            	if((Yii::$app->session['User']->getId() == $model->game_player_user_id) || ((Yii::$app->session['User']->getId() == Yii::$app->session['Game']->getGameOwnerID()) && $model->game_player_bot > 0))
		           			return Html::activeDropDownList($model, 'game_player_region_id',
		           				ArrayHelper::map($continentSQl,
		           					function($model, $defaultValue) {
		           						return $model->continent_id;
		           					},
		           					function($model, $defaultValue) {
		           						return Yii::t('continent', $model->continent_name);
		           					}
		           				),
		           				[
		           				'prompt'	=> Yii::t('continent', $continentList[$model->game_player_region_id]->getContinentName()),
		           				'class'		=> 'selectpicker',
		           				'onchange'	=> 'location = "'.Url::current().'&ui='.$model->game_player_user_id.'&bi='.$model->game_player_bot.'&ri="+this.options[this.selectedIndex].value;',
			           		]);
		           		else
		           			return '<font size="4" color="'.$colorList[$model->game_player_color_id]->getColorFontChat().'">'.$continentList[$model->game_player_region_id]->getContinentName().'</font>';
	            	}
	            },
            ],
            [
            'filter' => false,
            'format'    => 'raw',
            'attribute' => "",
            'value'     => function ($model, $key, $index, $column){
            	// If ready
            	if($model->game_player_user_id == Yii::$app->session['User']->getId())
            		if($model->game_player_statut == 0)
            			$returned = Html::a(Yii::t('game_player', 'Button_Not_Ready')." <i class='fa fa-check'></i>", ['/game/lobby', 'ui' => $model->game_player_user_id, 'si' => 1], ['class'=>'btn btn-danger']);
            		else
            			$returned = Html::a(Yii::t('game_player', 'Button_Rdy')." <i class='fa fa-check'></i>", ['/game/lobby', 'ui' => $model->game_player_user_id, 'si' => 0], ['class'=>'btn btn-success']);
            	else
            		if($model->game_player_statut == 0)
            			$returned = "<img src='img/site/no.png' width='20px' height='20px'>";
            		else
            			$returned = "<img src='img/site/ready.png' width='20px' height='20px'>";
            	return $returned;
            },
            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>
