<?php

namespace app\models;

use Yii;
use app\queries\GamePlayerQuery;
use app\classes\UserClass;

/**
 * This is the model class for table "game_player".
 *
 * @property string $game_player_id
 * @property integer $game_player_region_id
 * @property integer $game_player_difficulty_id
 * @property integer $game_player_statut
 * @property integer $game_player_game_id
 * @property integer $game_player_user_id
 * @property integer $game_player_color_id
 * @property integer $game_player_enter_time
 * @property integer $game_player_order
 * @property integer $game_player_bot
 * @property integer $game_player_quit
 */
class GamePlayer extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'game_player';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['game_player_region_id', 'game_player_game_id', 'game_player_user_id', 'game_player_color_id', 'game_player_enter_time', 'game_player_order', 'game_player_bot', 'game_player_quit'], 'required'],
            [['game_player_region_id', 'game_player_difficulty_id', 'game_player_statut', 'game_player_game_id', 'game_player_user_id', 'game_player_color_id', 'game_player_enter_time', 'game_player_order', 'game_player_bot', 'game_player_quit'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'game_player_id' => 'Game Player ID',
            'game_player_region_id' => 'Game Player Region ID',
            'game_player_difficulty_id' => 'Game Player Difficulty ID',
            'game_player_statut' => 'Game Player Statut',
            'game_player_game_id' => 'Game Player Game ID',
            'game_player_user_id' => 'Game Player User ID',
            'game_player_color_id' => 'Game Player Color ID',
            'game_player_enter_time' => 'Game Player Enter Time',
            'game_player_order' => 'Game Player Order',
            'game_player_bot' => 'Game Player Bot',
            'game_player_quit' => 'Game Player Quit',
        ];
    }

    /**
     *
     * @param unknown $gameId
     * @return \app\classes\GameClass
     */
    public static function userJoinGame($game, $userSpec=false){
    	// set Session Var
    	Yii::$app->session['Game'] = $game;
    	
    	// Insert in BD
    	if(!$userSpec)
    		self::userInsertJoinGame($game->getGameId());
    }
    
    /**
     * 
     * @param unknown $game
     * @return number
     */
    public static function gameCountPlayer($game_Id){
    	return self::find()->where(['game_player_game_id' => $game_Id])->andWhere(['game_player_quit' => 0])->count();
    }
    
    /**
     * 
     */
    public static function gameExitPlayer($user_id, $game_id){   	 
    	Yii::$app->db->createCommand()
    	->update("game_player", [
    			'game_player_quit'		=> 1,
    	],[
    			'game_player_user_id'   => $user_id,
    			'game_player_game_id'   => $game_id,
    	])
    	->execute();
    }
    
    /**
     *
     * @param unknown $gameId
     * @return \app\classes\GameClass
     */
    public static function userInsertJoinGame($gameId){
    	Yii::$app->db->createCommand()->insert("game_player",[
    			'game_player_region_id' => 1,
    			'game_player_difficulty_id' => 1,
    			'game_player_statut' => 0,
    			'game_player_game_id' => $gameId,
    			'game_player_user_id' => Yii::$app->session['User']->getId(),
    			'game_player_color_id' => 1,
    			'game_player_enter_time' => time(),
    	])->execute();
    }
    
    /**
     *
     * @param unknown $color_id
     * @return \app\classes\ColorClass
     */
    public static function findGamePlayerById($player_id){
    	return new ColorClass(self::find()->where(['color_id' => $color_id])->one());
    }
    
    /**
     *
     * @param unknown $colorData
     * @return NULL|\app\classes\ColorClass
     */
    public static function findAllGamePlayerToListUserId($gamePlayerData, $game_id=null){
    	if($gamePlayerData == null && $game_id != null)
    		$gamePlayerData = self::findAllGamePlayer($game_id);
    	$array = null;
    	$i = 0;
    	foreach ($gamePlayerData as $key => $gamePlayer){
    		$array[$i] = $gamePlayer['game_player_user_id'];
    		$i++;
    	}
    	$users = null;
    	foreach ((new Users)->getListUserByListUserId($array) as $key => $user){
    		$users[$user['user_id']] = new UserClass($user);
    	}
    	return $users;
    }
    
    
    /**
     * 
     * @param unknown $game_id
     * @return \app\queries\GamePlayer[]
     */
    public static function findAllGamePlayer($game_id){
    	return self::find()->where(['game_player_game_id' => $game_id])->all();
    }    
    
    /**
     * update gameplayer information
     *
     * @param  integer      $user_id
     * @return static|null
     */
    public static function UpdateGamePlayerById($user_id, $game_id, $region_id, $color_id, $statut)
    {
    	if(isset($user_id) && isset($game_id)){
    		print "Update ri = ".$region_id." ci = ".$color_id." si = ".$statut;
    		$key 	= null;
    		$value 	= null;
    		if(isset($color_id)){
				$key = 'game_player_color_id';
    			$value = $color;
    			if($value < 2 OR $value > 10) $value = 1;
    		}elseif(isset($region_id)){
    			$key = 'game_player_region_id';
    			$value = $region_id;
    			if($value > 6 OR $value < 1) $value = 6;
    		}elseif(isset($statut)){
    			$key = 'game_player_statut';
    			$value = $statut;
    			if($value > 1 OR $value < 0) $value = 0;
    		}
    		if(isset($key) && isset($value))
    			Yii::$app->db->createCommand()->update('game_player', [$key => $value], ['game_player_user_id' => $user_id, 'game_player_game_id' => $game_id])->execute();
    	}
    	return null;
    }
    
    
    /**
     * @inheritdoc
     * @return GamePlayerQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new GamePlayerQuery(get_called_class());
    }
}